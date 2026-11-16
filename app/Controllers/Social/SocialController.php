<?php

require_once ROOT_PATH . '/app/Models/Social/Social.php';
require_once ROOT_PATH . '/app/Models/Social/FiliereGroups.php';
require_once ROOT_PATH . '/app/Models/Notifications/Notification.php';
require_once ROOT_PATH . '/app/Models/Identity/User.php';

class SocialController
{
    private PDO $pdo;
    private Social $social;
    private FiliereGroups $filiereGroups;

    public function __construct()
    {
        global $pdo;
        if (!$pdo instanceof PDO) {
            throw new RuntimeException('La base de données est indisponible.');
        }
        $this->pdo = $pdo;
        $this->social = new Social($pdo);
        $this->filiereGroups = new FiliereGroups($pdo);
    }

    public function messages(): void
    {
        $userId = $this->requireAuthentication();
        global $baseUrl, $base, $assetBase, $asset;
        $friends = $this->social->getInbox($userId);
        $requests = $this->social->getPendingRequests($userId);
        $groups = $this->filiereGroups->getAccessibleGroups($userId);
        $selectedId = array_key_exists('user', $_GET)
            ? filter_var($_GET['user'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
            : null;
        $selectedGroupId = array_key_exists('group', $_GET)
            ? filter_var($_GET['group'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
            : null;
        $selectedFriend = null;
        $selectedGroup = null;
        $messages = [];
        $groupMessages = [];
        $groupMembers = [];

        if ($selectedGroupId !== false && $selectedGroupId !== null) {
            foreach ($groups as $group) {
                if ((int)$group['id'] === (int)$selectedGroupId) {
                    $selectedGroup = $group;
                    break;
                }
            }
            if ($selectedGroup !== null) {
                $groupMessages = $this->filiereGroups->getMessages($userId, (int)$selectedGroupId);
                $groupMembers = $this->filiereGroups->getGroupMembers($userId, (int)$selectedGroupId);
            } else {
                $_SESSION['flash_error'] = 'Vous n’avez pas accès à ce groupe.';
            }
        } elseif ($selectedGroupId === false) {
            $_SESSION['flash_error'] = 'Groupe invalide.';
        } elseif ($selectedId !== false && $selectedId !== null) {
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
        } elseif ($selectedId === false) {
            $_SESSION['flash_error'] = 'Conversation invalide.';
        }
        $friends = $this->social->getInbox($userId);
        $groups = $this->filiereGroups->getAccessibleGroups($userId);
        $unreadMessagesCount = $this->social->getUnreadMessageCount($userId)
            + array_sum(array_map(static fn (array $group): int => (int)$group['unread_count'], $groups));

        if (empty($_SESSION['social_csrf'])) {
            $_SESSION['social_csrf'] = bin2hex(random_bytes(32));
        }
        $csrfToken = $_SESSION['social_csrf'];
        $flashSuccess = (string)($_SESSION['flash_success'] ?? '');
        $flashError = (string)($_SESSION['flash_error'] ?? '');
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
        require ROOT_PATH . '/resources/views/desktop/messages/index.php';
    }

    public function sendGroupMessage(): void
    {
        $userId = $this->requireAuthentication();
        $this->requirePostAndCsrf();
        $groupId = filter_var($_POST['group_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($groupId === false || $groupId === null) {
            $this->redirectWithError('Groupe invalide.', 'messages');
        }

        try {
            $this->filiereGroups->sendMessage($userId, (int)$groupId, (string)($_POST['contenu'] ?? ''));
            $_SESSION['flash_success'] = 'Message envoyé au groupe.';
        } catch (InvalidArgumentException|DomainException $exception) {
            $_SESSION['flash_error'] = $exception->getMessage();
        } catch (PDOException $exception) {
            error_log('Envoi du message de groupe impossible : ' . $exception->getMessage());
            $_SESSION['flash_error'] = 'Le message n’a pas pu être envoyé.';
        }

        header('Location: ' . url('messages?group=' . (int)$groupId));
        exit;
    }

    public function friendsList(): void
    {
        $currentUserId = $this->requireAuthentication();
        global $baseUrl, $base, $assetBase, $asset;
        $targetUserId = !empty($_GET['user'])
            ? filter_var($_GET['user'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
            : (!empty($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : $currentUserId);
        if ($targetUserId === false || $targetUserId === null) {
            $targetUserId = $currentUserId;
        }

        $userModel = new User();
        $targetUser = $userModel->findById((int)$targetUserId);
        if (!$targetUser) {
            $this->redirectWithError('Utilisateur introuvable.', 'salon');
        }

        $isOwnProfile = ((int)$currentUserId === (int)$targetUserId);
        $friends = $this->social->getFriends((int)$targetUserId);
        $pendingRequests = $isOwnProfile ? $this->social->getPendingRequests((int)$currentUserId) : [];
        $suggestions = $isOwnProfile ? $this->social->getSuggestedMembers((int)$currentUserId, 8) : [];
        $unreadMessagesCount = $this->social->getUnreadMessageCount($currentUserId);

        if (empty($_SESSION['social_csrf'])) {
            $_SESSION['social_csrf'] = bin2hex(random_bytes(32));
        }
        $csrfToken = $_SESSION['social_csrf'];
        $flashSuccess = (string)($_SESSION['flash_success'] ?? '');
        $flashError = (string)($_SESSION['flash_error'] ?? '');
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require ROOT_PATH . '/resources/views/desktop/friends/index.php';
    }

    public function friendAction(): void
    {
        $userId = $this->requireAuthentication();
        $this->requirePostAndCsrf();
        $targetId = filter_var($_POST['target_user_id'] ?? $_POST['user_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $action = (string)($_POST['action'] ?? '');
        $returnUrl = (string)($_POST['return_url'] ?? 'messages');

        if ($targetId === false || $targetId === null) {
            $this->redirectWithError('Utilisateur cible invalide.', $returnUrl);
        }

        try {
            switch ($action) {
                case 'request':
                    $this->social->sendFriendRequest($userId, (int)$targetId);
                    $this->notifySafely(
                        (int)$targetId,
                        'friend_request',
                        ($_SESSION['user_prenom'] ?? 'Un membre') . ' vous a envoyé une demande d’ami.',
                        url('friends')
                    );
                    $_SESSION['flash_success'] = 'Demande d’ami envoyée.';
                    break;
                case 'accept':
                    $this->social->acceptFriendRequest($userId, (int)$targetId);
                    $this->notifySafely(
                        (int)$targetId,
                        'friend_accept',
                        ($_SESSION['user_prenom'] ?? 'Un membre') . ' a accepté votre demande d’ami.',
                        url('messages?user=' . $userId)
                    );
                    $_SESSION['flash_success'] = 'Demande d’ami acceptée.';
                    break;
                case 'reject':
                case 'decline':
                    $this->social->rejectFriendRequest($userId, (int)$targetId);
                    $_SESSION['flash_success'] = 'Demande d’ami refusée.';
                    break;
                case 'cancel':
                    $this->social->cancelFriendRequest($userId, (int)$targetId);
                    $_SESSION['flash_success'] = 'Demande d’ami annulée.';
                    break;
                case 'remove':
                    $this->social->removeFriend($userId, (int)$targetId);
                    $_SESSION['flash_success'] = 'Ami retiré de vos contacts.';
                    break;
                default:
                    $this->redirectWithError('Action sociale non reconnue.', $returnUrl);
            }
        } catch (DomainException $exception) {
            $_SESSION['flash_error'] = $exception->getMessage();
        } catch (PDOException $exception) {
            error_log('Action relationnelle impossible : ' . $exception->getMessage());
            $_SESSION['flash_error'] = 'L’opération a échoué. Veuillez réessayer.';
        }

        header('Location: ' . url($returnUrl));
        exit;
    }

    public function sendMessage(): void
    {
        $userId = $this->requireAuthentication();
        $this->requirePostAndCsrf();
        $recipientId = filter_var($_POST['recipient_id'] ?? $_POST['user_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $content = (string)($_POST['contenu'] ?? '');
        $attachments = $_FILES['attachments'] ?? $_FILES['pieces_jointes'] ?? [];

        if ($recipientId === false || $recipientId === null) {
            $this->redirectWithError('Destinataire invalide.', 'messages');
        }

        try {
            $messageId = $this->social->sendMessage($userId, (int)$recipientId, $content, $attachments);
            if ($messageId > 0) {
                $this->notifySafely(
                    (int)$recipientId,
                    'message',
                    ($_SESSION['user_prenom'] ?? 'Un membre') . ' vous a envoyé un message privé.',
                    url('messages?user=' . $userId)
                );
            }
            $_SESSION['flash_success'] = 'Message envoyé.';
        } catch (InvalidArgumentException|DomainException $exception) {
            $_SESSION['flash_error'] = $exception->getMessage();
        } catch (PDOException $exception) {
            error_log('Envoi du message privé impossible : ' . $exception->getMessage());
            $_SESSION['flash_error'] = 'Le message n’a pas pu être envoyé.';
        } catch (RuntimeException $exception) {
            error_log('Erreur de fichier lors de l’envoi de message privé : ' . $exception->getMessage());
            $_SESSION['flash_error'] = $exception->getMessage();
        }

        header('Location: ' . url('messages?user=' . (int)$recipientId));
        exit;
    }

    public function deleteMessage(): void
    {
        $userId = $this->requireAuthentication();
        $this->requirePostAndCsrf();
        $messageId = filter_var($_POST['message_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $friendId = filter_var($_POST['friend_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        if ($messageId === false || $messageId === null) {
            $this->redirectWithError('Message introuvable.', 'messages');
        }

        try {
            $this->social->deleteMessageForUser($userId, (int)$messageId);
            $_SESSION['flash_success'] = 'Message supprimé.';
        } catch (DomainException $exception) {
            $_SESSION['flash_error'] = $exception->getMessage();
        } catch (PDOException $exception) {
            error_log('Suppression du message privé impossible : ' . $exception->getMessage());
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
