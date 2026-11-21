<?php

require_once ROOT_PATH . '/app/Models/Social/Binomage.php';
require_once ROOT_PATH . '/app/Models/Notifications/Notification.php';

class BinomageController
{
    private PDO $pdo;
    private Binomage $binomage;
    private Notification $notifications;

    public function __construct()
    {
        global $pdo;
        if (!$pdo instanceof PDO) {
            throw new RuntimeException('La base de données est indisponible.');
        }
        $this->pdo = $pdo;
        $this->binomage = new Binomage($pdo);
        $this->notifications = new Notification($pdo);
    }

    public function index(): void
    {
        $userId = $this->requireAuthentication();
        global $baseUrl, $base, $assetBase, $asset;
        if (empty($_SESSION['binomage_csrf'])) {
            $_SESSION['binomage_csrf'] = bin2hex(random_bytes(32));
        }

        $userRole = $this->binomage->getUserBinomageRole($userId);
        $mentors = $this->binomage->getAvailableMentors($userId);
        $incoming = $this->binomage->getIncomingRequests($userId);
        $outgoing = $this->binomage->getOutgoingRequests($userId);
        $mentees = $this->binomage->getMentees($userId);
        $mentor = $this->binomage->getMentor($userId);
        $csrfToken = $_SESSION['binomage_csrf'];

        $flashSuccess = (string)($_SESSION['flash_success'] ?? '');
        $flashError = (string)($_SESSION['flash_error'] ?? '');
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require_once ROOT_PATH . '/resources/views/desktop/binomages/index.php';
    }

    public function action(): void
    {
        $userId = $this->requireAuthentication();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            return;
        }

        $csrf = (string)($_POST['csrf_token'] ?? '');
        $returnUrl = (string)($_POST['return_url'] ?? 'binomage');

        if (!hash_equals((string)($_SESSION['binomage_csrf'] ?? ''), $csrf)
            && !hash_equals((string)($_SESSION['csrf_token'] ?? ''), $csrf)
            && !hash_equals((string)($_SESSION['social_csrf'] ?? ''), $csrf)) {
            $_SESSION['flash_error'] = 'Session invalide. Veuillez réessayer.';
            header('Location: ' . url($returnUrl));
            exit;
        }

        $action = (string)($_POST['action'] ?? '');
        $relationId = (int)($_POST['relation_id'] ?? 0);
        $mentorId = (int)($_POST['mentor_id'] ?? 0);
        $message = trim((string)($_POST['message'] ?? ''));

        $senderName = trim(($_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? ''));
        if ($senderName === '') {
            $senderName = 'Un étudiant';
        }

        try {
            if ($action === 'send' && $mentorId > 0) {
                $result = $this->binomage->sendRequest($userId, $mentorId, $message);
                if ($result === 'sent') {
                    // Notification au mentor : "Momo veut que tu sois son mentor" -> lien vers /binomage
                    $this->notify(
                        $mentorId,
                        'demande_binome',
                        $senderName . ' veut que vous soyez son mentor.',
                        'binomage'
                    );
                    $_SESSION['flash_success'] = 'Votre demande de mentorat a été envoyée avec succès.';
                } else {
                    $_SESSION['flash_error'] = 'Une demande ou un binômage existe déjà avec ce mentor.';
                }
            } elseif ($action === 'accept' && $relationId > 0) {
                $relation = $this->getRelationById($relationId);
                if ($this->binomage->respond($relationId, $userId, 'accepte') && $relation) {
                    // Notification au mentoré : "Awa a accepté d'être ton mentor" -> lien vers le profil d'Awa
                    $this->notify(
                        (int)$relation['mentore_id'],
                        'binome_accepte',
                        $senderName . ' a accepté d’être votre mentor.',
                        'profile?id=' . $userId
                    );
                    $_SESSION['flash_success'] = 'Félicitations ! Vous accompagnez désormais cet étudiant.';
                } else {
                    $_SESSION['flash_error'] = 'Cette demande ne peut pas être acceptée.';
                }
            } elseif ($action === 'refuse' && $relationId > 0) {
                $relation = $this->getRelationById($relationId);
                if ($this->binomage->respond($relationId, $userId, 'refuse') && $relation) {
                    $this->notify(
                        (int)$relation['mentore_id'],
                        'binome_refuse',
                        $senderName . ' a décliné votre demande de mentorat.',
                        'binomage'
                    );
                    $_SESSION['flash_success'] = 'La demande a été déclinée.';
                } else {
                    $_SESSION['flash_error'] = 'Cette demande ne peut pas être refusée.';
                }
            } elseif (in_array($action, ['cancel', 'terminate'], true) && $relationId > 0) {
                $relation = $this->getRelationById($relationId);
                if ($this->binomage->cancelOrTerminate($relationId, $userId)) {
                    if ($relation && $action === 'terminate') {
                        $otherId = (int)$relation['mentor_id'] === $userId
                            ? (int)$relation['mentore_id']
                            : (int)$relation['mentor_id'];
                        $this->notify(
                            $otherId,
                            'binome_termine',
                            $senderName . ' a mis fin au binômage de mentorat.',
                            'binomage'
                        );
                    }
                    $_SESSION['flash_success'] = $action === 'cancel'
                        ? 'La demande de mentorat a été annulée.'
                        : 'Le binômage de mentorat a pris fin.';
                } else {
                    $_SESSION['flash_error'] = 'Cette relation ne peut pas être modifiée.';
                }
            } else {
                $_SESSION['flash_error'] = 'Action de binômage invalide.';
            }
        } catch (InvalidArgumentException $exception) {
            $_SESSION['flash_error'] = $exception->getMessage();
        }

        header('Location: ' . url($returnUrl));
        exit;
    }

    private function notify(int $recipientId, string $type, string $content, string $route): void
    {
        $this->notifications->create(
            $recipientId,
            $type,
            $content,
            url($route)
        );
    }

    private function getRelationById(int $relationId): ?array
    {
        $statement = $this->pdo->prepare("SELECT * FROM binomages WHERE id = ? LIMIT 1");
        $statement->execute([$relationId]);
        $relation = $statement->fetch(PDO::FETCH_ASSOC);
        return $relation ?: null;
    }

    private function requireAuthentication(): int
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . url('login'));
            exit;
        }
        return (int)$_SESSION['user_id'];
    }
}
