<?php

require_once dirname(__DIR__) . '/models/Social.php';
require_once dirname(__DIR__) . '/models/Notification.php';

class SocialController
{
    private PDO $pdo;
    private Social $social;

    public function __construct()
    {
        global $pdo;
        if (!$pdo instanceof PDO) {
            throw new RuntimeException('La base de données est indisponible.');
        }
        $this->pdo = $pdo;
        $this->social = new Social($pdo);
    }

    public function messages(): void
    {
        $userId = $this->requireAuthentication();
        global $baseUrl, $base, $assetBase, $asset;
        $friends = $this->social->getInbox($userId);
        $requests = $this->social->getPendingRequests($userId);
        $selectedId = filter_var($_GET['user'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $selectedFriend = null;
        $messages = [];

        if ($selectedId !== false && $selectedId !== null) {
            foreach ($friends as $friend) {
                if ((int)$friend['id'] === (int)$selectedId) {
                    $selectedFriend = $friend;
                    break;
                }
            }
            if ($selectedFriend !== null) {
                $messages = $this->social->getMessages($userId, (int)$selectedId);
            } else {
                $_SESSION['flash_error'] = 'Vous pouvez discuter uniquement avec vos amis.';
            }
        }
        $friends = $this->social->getInbox($userId);
        $unreadMessagesCount = $this->social->getUnreadMessageCount($userId);

        if (empty($_SESSION['social_csrf'])) {
            $_SESSION['social_csrf'] = bin2hex(random_bytes(32));
        }
        $csrfToken = $_SESSION['social_csrf'];
        $flashSuccess = (string)($_SESSION['flash_success'] ?? '');
        $flashError = (string)($_SESSION['flash_error'] ?? '');
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
        require ROOT_PATH . '/views/desktop/messages/index.php';
    }

    public function friendAction(): void
    {
        $userId = $this->requireAuthentication();
        $this->requirePostAndCsrf();
        $otherUserId = filter_var($_POST['user_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($otherUserId === false || $otherUserId === null || $otherUserId === $userId) {
            $this->redirectWithError('Profil utilisateur invalide.', 'profile');
        }

        $otherUser = $this->pdo->prepare('SELECT id, prenom, nom FROM utilisateurs WHERE id = ? AND est_actif = 1 LIMIT 1');
        $otherUser->execute([$otherUserId]);
        $target = $otherUser->fetch(PDO::FETCH_ASSOC);
        if (!$target) {
            $this->redirectWithError('Utilisateur introuvable.', 'profile');
        }

        $action = (string)($_POST['action'] ?? '');
        try {
            if ($action === 'request') {
                $result = $this->social->requestFriend($userId, (int)$otherUserId);
                if ($result === 'sent') {
                    $this->notifySafely(
                        (int)$otherUserId,
                        'friend_request',
                        trim((string)($_SESSION['user_prenom'] ?? '') . ' ' . (string)($_SESSION['user_nom'] ?? '')) . ' vous a envoyé une demande d’ami.',
                        url('messages')
                    );
                    $_SESSION['flash_success'] = 'Demande d’ami envoyée.';
                } elseif ($result === 'incoming_request') {
                    $_SESSION['flash_error'] = 'Cette personne vous a déjà envoyé une demande. Consultez vos demandes reçues.';
                } else {
                    $_SESSION['flash_error'] = $result === 'already_friends'
                        ? 'Vous êtes déjà amis.'
                        : 'Une demande est déjà en attente.';
                }
            } elseif (in_array($action, ['accept', 'decline', 'cancel'], true)) {
                $changed = $this->social->respondToRequest($userId, (int)$otherUserId, $action);
                if (!$changed) {
                    $_SESSION['flash_error'] = 'Cette demande n’est plus disponible.';
                } elseif ($action === 'accept') {
                    $this->notifySafely(
                        (int)$otherUserId,
                        'friend_accepted',
                        trim((string)($_SESSION['user_prenom'] ?? '') . ' ' . (string)($_SESSION['user_nom'] ?? '')) . ' a accepté votre demande d’ami.',
                        url('messages?user=' . $userId)
                    );
                    $_SESSION['flash_success'] = 'Demande acceptée. Vous pouvez maintenant discuter.';
                } else {
                    $_SESSION['flash_success'] = $action === 'cancel'
                        ? 'Demande d’ami annulée.'
                        : 'Demande refusée.';
                }
            } elseif ($action === 'remove') {
                $_SESSION['flash_success'] = $this->social->removeFriend($userId, (int)$otherUserId)
                    ? 'Amitié supprimée.'
                    : 'Cette personne ne figure pas dans vos amis.';
            } else {
                $_SESSION['flash_error'] = 'Action non reconnue.';
            }
        } catch (PDOException $exception) {
            error_log('Mise à jour d’une relation sociale impossible : ' . $exception->getMessage());
            $_SESSION['flash_error'] = 'La demande n’a pas pu être traitée. Vérifiez que la mise à jour de la base de données est appliquée.';
        } catch (InvalidArgumentException $exception) {
            $_SESSION['flash_error'] = $exception->getMessage();
        }

        header('Location: ' . url('profile?id=' . (int)$otherUserId));
        exit;
    }

    public function sendMessage(): void
    {
        $userId = $this->requireAuthentication();
        $this->requirePostAndCsrf();
        $recipientId = filter_var($_POST['user_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($recipientId === false || $recipientId === null || $recipientId === $userId) {
            $this->redirectWithError('Destinataire invalide.', 'messages');
        }

        try {
            $this->social->sendMessage(
                $userId,
                (int)$recipientId,
                (string)($_POST['contenu'] ?? ''),
                $_FILES['pieces_jointes'] ?? []
            );
            $senderName = trim((string)($_SESSION['user_prenom'] ?? '') . ' ' . (string)($_SESSION['user_nom'] ?? ''));
            $this->notifySafely(
                (int)$recipientId,
                'private_message',
                $senderName . ' vous a envoyé un message privé.',
                url('messages?user=' . $userId)
            );
            $_SESSION['flash_success'] = 'Message envoyé.';
        } catch (InvalidArgumentException|DomainException $exception) {
            $_SESSION['flash_error'] = $exception->getMessage();
        } catch (PDOException $exception) {
            error_log('Envoi du message privé impossible : ' . $exception->getMessage());
            $_SESSION['flash_error'] = 'Le message n’a pas pu être envoyé.';
        } catch (RuntimeException $exception) {
            error_log('Stockage du message privé impossible : ' . $exception->getMessage());
            $_SESSION['flash_error'] = 'Le message ou ses pièces jointes n’ont pas pu être envoyés.';
        }

        header('Location: ' . url('messages?user=' . (int)$recipientId));
        exit;
    }

    public function deleteMessage(): void
    {
        $userId = $this->requireAuthentication();
        $this->requirePostAndCsrf();
        $friendId = filter_var($_POST['user_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $messageId = filter_var($_POST['message_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $action = (string)($_POST['delete_action'] ?? '');
        if ($friendId === false || $friendId === null || $friendId === $userId
            || $messageId === false || $messageId === null) {
            $this->redirectWithError('Message ou conversation invalide.', 'messages');
        }

        try {
            if ($action === 'me') {
                $deleted = $this->social->deleteMessageForMe($userId, (int)$messageId, (int)$friendId);
                $_SESSION[$deleted ? 'flash_success' : 'flash_error'] = $deleted
                    ? 'Message supprimé pour vous.'
                    : 'Ce message n’est plus disponible dans cette conversation.';
            } elseif ($action === 'everyone') {
                $deleted = $this->social->deleteMessageForEveryone($userId, (int)$messageId, (int)$friendId);
                $_SESSION[$deleted ? 'flash_success' : 'flash_error'] = $deleted
                    ? 'Message supprimé pour tout le monde.'
                    : 'Seuls vos messages non supprimés peuvent être retirés pour tout le monde.';
            } else {
                $_SESSION['flash_error'] = 'Action de suppression inconnue.';
            }
        } catch (PDOException $exception) {
            error_log('Suppression d’un message privé impossible : ' . $exception->getMessage());
            $_SESSION['flash_error'] = 'Le message n’a pas pu être supprimé.';
        } catch (RuntimeException $exception) {
            error_log('Suppression des pièces jointes d’un message privé impossible : ' . $exception->getMessage());
            $_SESSION['flash_error'] = 'Le message a été supprimé, mais un fichier associé n’a pas pu être nettoyé.';
        }

        header('Location: ' . url('messages?user=' . (int)$friendId));
        exit;
    }

    public function messageAttachment(int $attachmentId): void
    {
        $userId = $this->requireAuthentication();
        $statement = $this->pdo->prepare(
            "SELECT attachment.nom_fichier, attachment.chemin_fichier,
                    attachment.type_mime, attachment.taille
             FROM private_message_attachments attachment
             JOIN private_messages message ON message.id = attachment.message_id
             JOIN friendships friendship
               ON friendship.user_one_id = LEAST(message.sender_id, message.recipient_id)
              AND friendship.user_two_id = GREATEST(message.sender_id, message.recipient_id)
              AND friendship.status = 'accepted'
             WHERE attachment.id = ?
               AND (message.sender_id = ? OR message.recipient_id = ?)
               AND NOT EXISTS (
                    SELECT 1 FROM private_message_deletions hidden
                    WHERE hidden.message_id = message.id AND hidden.user_id = ?
               )
             LIMIT 1"
        );
        $statement->execute([$attachmentId, $userId, $userId, $userId]);
        $attachment = $statement->fetch(PDO::FETCH_ASSOC);
        if (!$attachment) {
            http_response_code(404);
            exit('Pièce jointe introuvable.');
        }

        $uploadDirectory = realpath(ROOT_PATH . '/public/uploads/private-messages');
        $filePath = realpath(ROOT_PATH . '/public/uploads/' . ltrim((string)$attachment['chemin_fichier'], '/'));
        if (!$uploadDirectory || !$filePath
            || !str_starts_with($filePath, $uploadDirectory . DIRECTORY_SEPARATOR)
            || !is_file($filePath)) {
            http_response_code(404);
            exit('Fichier indisponible.');
        }

        $inlineTypes = [
            'image/png', 'image/jpeg', 'image/gif', 'image/webp',
            'audio/mpeg', 'audio/mp3', 'audio/mp4', 'audio/x-m4a',
            'audio/wav', 'audio/x-wav', 'audio/ogg', 'application/ogg',
            'audio/webm', 'video/webm',
        ];
        $inline = in_array($attachment['type_mime'], $inlineTypes, true)
            && (string)($_GET['download'] ?? '') !== '1';
        $filename = (string)$attachment['nom_fichier'];
        $safeFilename = preg_replace('/[\r\n"\\\\]/', '_', $filename);

        header('Content-Type: ' . $attachment['type_mime']);
        header('Content-Length: ' . (int)$attachment['taille']);
        header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment')
            . '; filename="' . $safeFilename . '"; filename*=UTF-8\'\'' . rawurlencode($filename));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store');
        readfile($filePath);
        exit;
    }

    private function notificationModel(): Notification
    {
        return new Notification($this->pdo);
    }

    private function notifySafely(int $userId, string $type, string $content, string $link): void
    {
        try {
            if (!$this->notificationModel()->create($userId, $type, $content, $link)) {
                error_log('Notification sociale non créée pour l’utilisateur ' . $userId . ' (' . $type . ').');
            }
        } catch (PDOException $exception) {
            error_log('Notification sociale impossible : ' . $exception->getMessage());
        }
    }

    private function requireAuthentication(): int
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . url('login'));
            exit;
        }
        return (int)$_SESSION['user_id'];
    }

    private function requirePostAndCsrf(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            exit('Méthode non autorisée.');
        }
        $token = (string)($_POST['csrf_token'] ?? '');
        if (empty($_SESSION['social_csrf']) || !hash_equals((string)$_SESSION['social_csrf'], $token)) {
            http_response_code(403);
            exit('Formulaire expiré. Rechargez la page et réessayez.');
        }
    }

    private function redirectWithError(string $message, string $route): void
    {
        $_SESSION['flash_error'] = $message;
        header('Location: ' . url($route));
        exit;
    }
}
