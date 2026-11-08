<?php

class Social
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getFriendship(int $userId, int $otherUserId): ?array
    {
        [$firstId, $secondId] = $this->orderedPair($userId, $otherUserId);
        $statement = $this->pdo->prepare(
            'SELECT requested_by, status FROM friendships WHERE user_one_id = ? AND user_two_id = ? LIMIT 1'
        );
        $statement->execute([$firstId, $secondId]);
        $friendship = $statement->fetch(PDO::FETCH_ASSOC);

        return $friendship ?: null;
    }

    public function requestFriend(int $senderId, int $recipientId): string
    {
        if ($senderId <= 0 || $recipientId <= 0 || $senderId === $recipientId) {
            throw new InvalidArgumentException('Cette demande d’ami n’est pas valide.');
        }

        [$firstId, $secondId] = $this->orderedPair($senderId, $recipientId);
        $friendship = $this->getFriendship($senderId, $recipientId);

        if ($friendship && $friendship['status'] === 'accepted') {
            return 'already_friends';
        }
        if ($friendship && $friendship['status'] === 'pending') {
            return (int)$friendship['requested_by'] === $senderId ? 'already_sent' : 'incoming_request';
        }

        if ($friendship) {
            $statement = $this->pdo->prepare(
                "UPDATE friendships
                 SET requested_by = ?, status = 'pending', updated_at = NOW()
                 WHERE user_one_id = ? AND user_two_id = ?"
            );
            $statement->execute([$senderId, $firstId, $secondId]);
        } else {
            $statement = $this->pdo->prepare(
                "INSERT INTO friendships (user_one_id, user_two_id, requested_by, status)
                 VALUES (?, ?, ?, 'pending')"
            );
            $statement->execute([$firstId, $secondId, $senderId]);
        }

        return 'sent';
    }

    public function respondToRequest(int $userId, int $otherUserId, string $action): bool
    {
        [$firstId, $secondId] = $this->orderedPair($userId, $otherUserId);
        $friendship = $this->getFriendship($userId, $otherUserId);
        if (!$friendship || $friendship['status'] !== 'pending') {
            return false;
        }

        $requestedBy = (int)$friendship['requested_by'];
        if ($action === 'accept' && $requestedBy === $userId) {
            return false;
        }
        if (in_array($action, ['accept', 'decline'], true) && $requestedBy !== $userId) {
            $status = $action === 'accept' ? 'accepted' : 'declined';
            $statement = $this->pdo->prepare(
                'UPDATE friendships SET status = ?, updated_at = NOW()
                 WHERE user_one_id = ? AND user_two_id = ? AND status = \'pending\''
            );
            $statement->execute([$status, $firstId, $secondId]);
            return $statement->rowCount() === 1;
        }

        if ($action === 'cancel' && $requestedBy === $userId) {
            $statement = $this->pdo->prepare(
                "DELETE FROM friendships
                 WHERE user_one_id = ? AND user_two_id = ? AND status = 'pending' AND requested_by = ?"
            );
            $statement->execute([$firstId, $secondId, $userId]);
            return $statement->rowCount() === 1;
        }

        return false;
    }

    public function removeFriend(int $userId, int $otherUserId): bool
    {
        [$firstId, $secondId] = $this->orderedPair($userId, $otherUserId);
        $statement = $this->pdo->prepare(
            "DELETE FROM friendships
             WHERE user_one_id = ? AND user_two_id = ? AND status = 'accepted'"
        );
        $statement->execute([$firstId, $secondId]);
        return $statement->rowCount() === 1;
    }

    public function getFriends(int $userId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT user.id, user.prenom, user.nom, user.role, user.filiere, user.avatar_url
             FROM friendships friendship
             JOIN utilisateurs user
               ON user.id = CASE
                    WHEN friendship.user_one_id = :first_user THEN friendship.user_two_id
                    ELSE friendship.user_one_id
                  END
             WHERE (friendship.user_one_id = :second_user OR friendship.user_two_id = :third_user)
               AND friendship.status = 'accepted'
               AND user.est_actif = 1
             ORDER BY user.prenom, user.nom"
        );
        $statement->execute([
            'first_user' => $userId,
            'second_user' => $userId,
            'third_user' => $userId,
        ]);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPendingRequests(int $userId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT sender.id, sender.prenom, sender.nom, sender.role, sender.filiere, sender.avatar_url,
                    friendship.updated_at
             FROM friendships friendship
             JOIN utilisateurs sender ON sender.id = friendship.requested_by
             WHERE (friendship.user_one_id = :first_user OR friendship.user_two_id = :second_user)
               AND friendship.status = 'pending'
               AND friendship.requested_by <> :recipient
             ORDER BY friendship.updated_at DESC"
        );
        $statement->execute([
            'first_user' => $userId,
            'second_user' => $userId,
            'recipient' => $userId,
        ]);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUnreadMessageCount(int $userId): int
    {
        $statement = $this->pdo->prepare(
            'SELECT COUNT(*) FROM private_messages message
             WHERE message.recipient_id = ? AND message.read_at IS NULL
               AND NOT EXISTS (
                    SELECT 1 FROM private_message_deletions hidden
                    WHERE hidden.message_id = message.id AND hidden.user_id = ?
               )'
        );
        $statement->execute([$userId, $userId]);
        return (int)$statement->fetchColumn();
    }

    public function getInbox(int $userId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT friend.id, friend.prenom, friend.nom, friend.role, friend.filiere, friend.avatar_url,
                    CASE
                        WHEN latest.deleted_for_everyone_at IS NOT NULL THEN '[Message supprimé]'
                        WHEN latest.contenu <> '' THEN latest.contenu
                        WHEN EXISTS (
                            SELECT 1 FROM private_message_attachments attachment
                            WHERE attachment.message_id = latest.id
                        ) THEN '[Pièce jointe]'
                        ELSE ''
                    END AS last_message,
                    latest.date_creation AS last_message_at,
                    (SELECT COUNT(*) FROM private_messages unread
                     WHERE unread.sender_id = friend.id AND unread.recipient_id = :unread_user
                       AND unread.read_at IS NULL
                       AND NOT EXISTS (
                            SELECT 1 FROM private_message_deletions hidden
                            WHERE hidden.message_id = unread.id AND hidden.user_id = :unread_hidden_user
                       )) AS unread_count
             FROM friendships friendship
             JOIN utilisateurs friend
               ON friend.id = CASE
                    WHEN friendship.user_one_id = :join_first THEN friendship.user_two_id
                    ELSE friendship.user_one_id
                  END
             LEFT JOIN private_messages latest
               ON latest.id = (
                    SELECT message.id
                    FROM private_messages message
                    WHERE ((message.sender_id = :sender_user AND message.recipient_id = friend.id)
                       OR (message.sender_id = friend.id AND message.recipient_id = :recipient_user))
                      AND NOT EXISTS (
                            SELECT 1 FROM private_message_deletions hidden
                            WHERE hidden.message_id = message.id AND hidden.user_id = :latest_hidden_user
                      )
                    ORDER BY message.date_creation DESC, message.id DESC
                    LIMIT 1
               )
             WHERE (friendship.user_one_id = :first_user OR friendship.user_two_id = :second_user)
               AND friendship.status = 'accepted'
               AND friend.est_actif = 1
             ORDER BY COALESCE(latest.date_creation, friendship.updated_at) DESC, friend.prenom, friend.nom"
        );
        $statement->execute([
            'unread_user' => $userId,
            'unread_hidden_user' => $userId,
            'join_first' => $userId,
            'sender_user' => $userId,
            'recipient_user' => $userId,
            'latest_hidden_user' => $userId,
            'first_user' => $userId,
            'second_user' => $userId,
        ]);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMessages(int $userId, int $friendId, int $limit = 100): array
    {
        if (!$this->areFriends($userId, $friendId)) {
            return [];
        }
        $limit = max(1, min(200, $limit));
        $statement = $this->pdo->prepare(
            "SELECT id, sender_id, recipient_id, contenu, date_creation, deleted_for_everyone_at
             FROM private_messages message
             WHERE ((sender_id = ? AND recipient_id = ?)
                OR (sender_id = ? AND recipient_id = ?))
               AND NOT EXISTS (
                    SELECT 1 FROM private_message_deletions hidden
                    WHERE hidden.message_id = message.id AND hidden.user_id = ?
               )
             ORDER BY date_creation DESC, id DESC
             LIMIT {$limit}"
        );
        $statement->execute([$userId, $friendId, $friendId, $userId, $userId]);
        $messages = array_reverse($statement->fetchAll(PDO::FETCH_ASSOC));
        if ($messages) {
            $messageIds = array_column($messages, 'id');
            $placeholders = implode(',', array_fill(0, count($messageIds), '?'));
            $attachmentsStatement = $this->pdo->prepare(
                "SELECT id, message_id, nom_fichier, type_mime, taille
                 FROM private_message_attachments
                 WHERE message_id IN ({$placeholders})
                 ORDER BY id"
            );
            $attachmentsStatement->execute($messageIds);
            $attachmentsByMessage = [];
            foreach ($attachmentsStatement->fetchAll(PDO::FETCH_ASSOC) as $attachment) {
                $attachmentsByMessage[(int)$attachment['message_id']][] = $attachment;
            }
            foreach ($messages as &$message) {
                $message['attachments'] = $attachmentsByMessage[(int)$message['id']] ?? [];
            }
            unset($message);
        }
        $markRead = $this->pdo->prepare(
            'UPDATE private_messages SET read_at = NOW()
             WHERE sender_id = ? AND recipient_id = ? AND read_at IS NULL'
        );
        $markRead->execute([$friendId, $userId]);
        return $messages;
    }

    public function deleteMessageForMe(int $userId, int $messageId, int $friendId): bool
    {
        if ($messageId <= 0 || !$this->areFriends($userId, $friendId)) {
            return false;
        }

        $statement = $this->pdo->prepare(
            'INSERT IGNORE INTO private_message_deletions (message_id, user_id)
             SELECT message.id, ?
             FROM private_messages message
             WHERE message.id = ?
               AND ((message.sender_id = ? AND message.recipient_id = ?)
                 OR (message.sender_id = ? AND message.recipient_id = ?))'
        );
        $statement->execute([$userId, $messageId, $userId, $friendId, $friendId, $userId]);
        return $statement->rowCount() === 1;
    }

    public function deleteMessageForEveryone(int $userId, int $messageId, int $friendId): bool
    {
        if ($messageId <= 0 || !$this->areFriends($userId, $friendId)) {
            return false;
        }

        $this->pdo->beginTransaction();
        try {
            $attachments = $this->pdo->prepare(
                'SELECT attachment.chemin_fichier
                 FROM private_message_attachments attachment
                 JOIN private_messages message ON message.id = attachment.message_id
                 WHERE message.id = ? AND message.sender_id = ? AND message.recipient_id = ?
                   AND message.deleted_for_everyone_at IS NULL
                 FOR UPDATE'
            );
            $attachments->execute([$messageId, $userId, $friendId]);
            $storedPaths = $attachments->fetchAll(PDO::FETCH_COLUMN);

            $update = $this->pdo->prepare(
                'UPDATE private_messages
                 SET contenu = \'\', deleted_for_everyone_at = NOW()
                 WHERE id = ? AND sender_id = ? AND recipient_id = ?
                   AND deleted_for_everyone_at IS NULL'
            );
            $update->execute([$messageId, $userId, $friendId]);
            if ($update->rowCount() !== 1) {
                $this->pdo->rollBack();
                return false;
            }

            $deleteAttachments = $this->pdo->prepare('DELETE FROM private_message_attachments WHERE message_id = ?');
            $deleteAttachments->execute([$messageId]);
            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }

        $uploadDirectory = realpath(ROOT_PATH . '/public/uploads/private-messages');
        foreach ($storedPaths as $relativePath) {
            $filePath = realpath(ROOT_PATH . '/public/uploads/' . ltrim((string)$relativePath, '/'));
            if ($filePath && $uploadDirectory
                && str_starts_with($filePath, $uploadDirectory . DIRECTORY_SEPARATOR)
                && is_file($filePath) && !unlink($filePath)) {
                error_log('Impossible de supprimer la pièce jointe du message privé supprimé : ' . $filePath);
            }
        }
        return true;
    }

    public function sendMessage(int $senderId, int $recipientId, string $content, array $files = []): int
    {
        $content = trim($content);
        if ($senderId <= 0 || $recipientId <= 0 || $senderId === $recipientId) {
            throw new InvalidArgumentException('Destinataire invalide.');
        }
        if (mb_strlen($content, 'UTF-8') > 2000) {
            throw new InvalidArgumentException('Le message ne peut pas dépasser 2 000 caractères.');
        }
        $validatedFiles = $this->validateMessageFiles($files);
        if ($content === '' && $validatedFiles === []) {
            throw new InvalidArgumentException('Écrivez un message ou ajoutez au moins une pièce jointe.');
        }
        if (!$this->areFriends($senderId, $recipientId)) {
            throw new DomainException('Vous devez être amis pour envoyer un message privé.');
        }

        $uploadDirectory = ROOT_PATH . '/public/uploads/private-messages';
        if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0750, true) && !is_dir($uploadDirectory)) {
            throw new RuntimeException('Le dossier de stockage des pièces jointes est indisponible.');
        }

        $storedPaths = [];
        $this->pdo->beginTransaction();
        try {
            $statement = $this->pdo->prepare(
                'INSERT INTO private_messages (sender_id, recipient_id, contenu) VALUES (?, ?, ?)'
            );
            $statement->execute([$senderId, $recipientId, $content]);
            $messageId = (int)$this->pdo->lastInsertId();
            $attachmentStatement = $this->pdo->prepare(
                'INSERT INTO private_message_attachments
                    (message_id, nom_fichier, chemin_fichier, type_mime, taille)
                 VALUES (?, ?, ?, ?, ?)'
            );

            foreach ($validatedFiles as $file) {
                $storedName = bin2hex(random_bytes(16)) . '.' . $file['extension'];
                $destination = $uploadDirectory . DIRECTORY_SEPARATOR . $storedName;
                if (!move_uploaded_file($file['tmp_name'], $destination)) {
                    throw new RuntimeException('Impossible de stocker une pièce jointe.');
                }
                $storedPaths[] = $destination;
                $attachmentStatement->execute([
                    $messageId,
                    $file['name'],
                    'private-messages/' . $storedName,
                    $file['type'],
                    $file['size'],
                ]);
            }

            $this->pdo->commit();
            return $messageId;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            foreach ($storedPaths as $storedPath) {
                if (is_file($storedPath)) {
                    unlink($storedPath);
                }
            }
            throw $exception;
        }
    }

    /**
     * @return array<int, array{name: string, tmp_name: string, size: int, type: string, extension: string}>
     */
    private function validateMessageFiles(array $files): array
    {
        if (!isset($files['name'])) {
            return [];
        }

        $allowedTypes = [
            'png' => ['image/png'],
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'gif' => ['image/gif'],
            'webp' => ['image/webp'],
            'mp3' => ['audio/mpeg', 'audio/mp3'],
            'm4a' => ['audio/mp4', 'audio/x-m4a'],
            'mp4' => ['audio/mp4'],
            'wav' => ['audio/wav', 'audio/x-wav'],
            'ogg' => ['audio/ogg', 'application/ogg'],
            'oga' => ['audio/ogg'],
            'webm' => ['audio/webm', 'video/webm'],
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'xls' => ['application/vnd.ms-excel'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'ppt' => ['application/vnd.ms-powerpoint'],
            'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation'],
            'txt' => ['text/plain'],
            'zip' => ['application/zip', 'application/x-zip-compressed', 'multipart/x-zip'],
        ];
        $names = is_array($files['name']) ? $files['name'] : [$files['name']];
        $tmpNames = is_array($files['tmp_name'] ?? null) ? $files['tmp_name'] : [$files['tmp_name'] ?? ''];
        $sizes = is_array($files['size'] ?? null) ? $files['size'] : [$files['size'] ?? 0];
        $errors = is_array($files['error'] ?? null) ? $files['error'] : [$files['error'] ?? UPLOAD_ERR_NO_FILE];
        $validated = [];
        $totalSize = 0;

        foreach ($names as $index => $originalName) {
            $error = (int)($errors[$index] ?? UPLOAD_ERR_NO_FILE);
            if ($error === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $name = basename(str_replace('\\', '/', (string)$originalName));
            $name = preg_replace('/[\x00-\x1F\x7F]/u', '_', $name) ?? '';
            $name = mb_strcut($name, 0, 255, 'UTF-8');
            if ($error !== UPLOAD_ERR_OK) {
                throw new InvalidArgumentException("La pièce jointe « {$name} » n’a pas pu être envoyée.");
            }
            if (count($validated) >= 5) {
                throw new InvalidArgumentException('Vous pouvez joindre au maximum 5 fichiers par message.');
            }

            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $size = (int)($sizes[$index] ?? 0);
            $tmpName = (string)($tmpNames[$index] ?? '');
            if (!isset($allowedTypes[$extension])) {
                throw new InvalidArgumentException("Le format du fichier « {$name} » n’est pas autorisé.");
            }
            if ($size <= 0 || $size > 10 * 1024 * 1024) {
                throw new InvalidArgumentException("Chaque fichier doit peser au maximum 10 Mo.");
            }
            if ($tmpName === '' || !is_uploaded_file($tmpName)) {
                throw new InvalidArgumentException("La pièce jointe « {$name} » est invalide.");
            }

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($tmpName);
            if (!is_string($mime) || !in_array($mime, $allowedTypes[$extension], true)) {
                throw new InvalidArgumentException("Le type réel du fichier « {$name} » ne correspond pas à son extension.");
            }
            if ($extension === 'webm' && $mime === 'video/webm') {
                $mime = 'audio/webm';
            }
            $totalSize += $size;
            if ($totalSize > 20 * 1024 * 1024) {
                throw new InvalidArgumentException('La taille totale des pièces jointes ne peut pas dépasser 20 Mo.');
            }

            $validated[] = [
                'name' => $name,
                'tmp_name' => $tmpName,
                'size' => $size,
                'type' => $mime,
                'extension' => $extension,
            ];
        }

        return $validated;
    }

    private function areFriends(int $firstUserId, int $secondUserId): bool
    {
        $friendship = $this->getFriendship($firstUserId, $secondUserId);
        return $friendship !== null && $friendship['status'] === 'accepted';
    }

    private function orderedPair(int $firstId, int $secondId): array
    {
        return $firstId < $secondId ? [$firstId, $secondId] : [$secondId, $firstId];
    }
}
