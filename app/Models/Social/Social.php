<?php

class Social
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère la relation d'amitié entre deux utilisateurs.
     */
    public function getFriendship(int $userId, int $otherUserId): ?array
    {
        try {
            $statement = $this->pdo->prepare(
                "SELECT id, utilisateur_id, ami_id, statut,
                        CASE WHEN statut = 'accepte' THEN 'accepted'
                             WHEN statut = 'en_attente' THEN 'pending'
                             WHEN statut = 'refuse' THEN 'declined'
                             ELSE statut END AS status,
                        utilisateur_id AS requested_by
                 FROM amis
                 WHERE (utilisateur_id = :u1 AND ami_id = :o1)
                    OR (utilisateur_id = :o2 AND ami_id = :u2)
                 LIMIT 1"
            );
            $statement->execute([
                'u1' => $userId,
                'o1' => $otherUserId,
                'o2' => $otherUserId,
                'u2' => $userId,
            ]);
            $friendship = $statement->fetch(PDO::FETCH_ASSOC);

            return $friendship ?: null;
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Envoie une demande d'ami.
     */
    public function requestFriend(int $senderId, int $recipientId): string
    {
        if ($senderId <= 0 || $recipientId <= 0 || $senderId === $recipientId) {
            throw new InvalidArgumentException('Cette demande d’ami n’est pas valide.');
        }

        $existing = $this->getFriendship($senderId, $recipientId);

        if ($existing && $existing['statut'] === 'accepte') {
            return 'already_friends';
        }
        if ($existing && $existing['statut'] === 'en_attente') {
            return (int)$existing['utilisateur_id'] === $senderId ? 'already_sent' : 'incoming_request';
        }

        if ($existing) {
            $statement = $this->pdo->prepare(
                "UPDATE amis
                 SET utilisateur_id = ?, ami_id = ?, statut = 'en_attente', date_demande = NOW(), date_reponse = NULL
                 WHERE id = ?"
            );
            $statement->execute([$senderId, $recipientId, (int)$existing['id']]);
        } else {
            $statement = $this->pdo->prepare(
                "INSERT INTO amis (utilisateur_id, ami_id, statut, date_demande)
                 VALUES (?, ?, 'en_attente', NOW())"
            );
            $statement->execute([$senderId, $recipientId]);
        }

        return 'sent';
    }

    /**
     * Répond à une demande d'ami (accept, decline, cancel).
     */
    public function respondToRequest(int $userId, int $otherUserId, string $action): bool
    {
        $existing = $this->getFriendship($userId, $otherUserId);
        if (!$existing || $existing['statut'] !== 'en_attente') {
            return false;
        }

        $senderId = (int)$existing['utilisateur_id'];

        if ($action === 'accept' && $senderId !== $userId) {
            $statement = $this->pdo->prepare(
                "UPDATE amis SET statut = 'accepte', date_reponse = NOW() WHERE id = ?"
            );
            $statement->execute([(int)$existing['id']]);
            return $statement->rowCount() === 1;
        }

        if (in_array($action, ['decline', 'reject'], true) && $senderId !== $userId) {
            $statement = $this->pdo->prepare(
                "UPDATE amis SET statut = 'refuse', date_reponse = NOW() WHERE id = ?"
            );
            $statement->execute([(int)$existing['id']]);
            return $statement->rowCount() === 1;
        }

        if ($action === 'cancel' && $senderId === $userId) {
            $statement = $this->pdo->prepare("DELETE FROM amis WHERE id = ?");
            $statement->execute([(int)$existing['id']]);
            return $statement->rowCount() === 1;
        }

        return false;
    }

    /**
     * Supprime un ami.
     */
    public function removeFriend(int $userId, int $otherUserId): bool
    {
        $statement = $this->pdo->prepare(
            "DELETE FROM amis
             WHERE ((utilisateur_id = ? AND ami_id = ?) OR (utilisateur_id = ? AND ami_id = ?))
               AND statut = 'accepte'"
        );
        $statement->execute([$userId, $otherUserId, $otherUserId, $userId]);
        return $statement->rowCount() > 0;
    }

    public function sendFriendRequest(int $senderId, int $recipientId): string
    {
        return $this->requestFriend($senderId, $recipientId);
    }

    public function acceptFriendRequest(int $userId, int $senderId): bool
    {
        return $this->respondToRequest($userId, $senderId, 'accept');
    }

    public function rejectFriendRequest(int $userId, int $senderId): bool
    {
        return $this->respondToRequest($userId, $senderId, 'decline');
    }

    public function cancelFriendRequest(int $userId, int $recipientId): bool
    {
        return $this->respondToRequest($userId, $recipientId, 'cancel');
    }

    /**
     * Vérifie si deux utilisateurs sont amis.
     */
    public function areFriends(int $firstUserId, int $secondUserId): bool
    {
        try {
            $statement = $this->pdo->prepare(
                "SELECT 1 FROM amis
                 WHERE ((utilisateur_id = ? AND ami_id = ?) OR (utilisateur_id = ? AND ami_id = ?))
                   AND statut = 'accepte'
                 LIMIT 1"
            );
            $statement->execute([$firstUserId, $secondUserId, $secondUserId, $firstUserId]);
            return (bool)$statement->fetchColumn();
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Récupère la liste des amis d'un utilisateur.
     */
    public function getFriends(int $userId): array
    {
        try {
            $statement = $this->pdo->prepare(
                "SELECT u.id, u.prenom, u.nom, u.role, u.avatar_url, f.nom AS filiere
                 FROM amis a
                 JOIN utilisateurs u ON (u.id = CASE WHEN a.utilisateur_id = :u1 THEN a.ami_id ELSE a.utilisateur_id END)
                 LEFT JOIN filieres f ON f.id = u.filiere_id
                 WHERE (a.utilisateur_id = :u2 OR a.ami_id = :u3)
                   AND a.statut = 'accepte'
                   AND u.est_actif = 1
                 ORDER BY u.prenom ASC, u.nom ASC"
            );
            $statement->execute([
                'u1' => $userId,
                'u2' => $userId,
                'u3' => $userId,
            ]);
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Récupère les demandes d'amis en attente reçues par un utilisateur.
     */
    public function getPendingRequests(int $userId): array
    {
        try {
            $statement = $this->pdo->prepare(
                "SELECT u.id, u.prenom, u.nom, u.role, u.avatar_url, f.nom AS filiere, a.date_demande AS updated_at
                 FROM amis a
                 JOIN utilisateurs u ON u.id = a.utilisateur_id
                 LEFT JOIN filieres f ON f.id = u.filiere_id
                 WHERE a.ami_id = :user_id
                   AND a.statut = 'en_attente'
                   AND u.est_actif = 1
                 ORDER BY a.date_demande DESC"
            );
            $statement->execute(['user_id' => $userId]);
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Suggestions de membres à ajouter.
     */
    public function getSuggestedMembers(int $userId, int $limit = 8): array
    {
        try {
            $userStmt = $this->pdo->prepare('SELECT filiere_id FROM utilisateurs WHERE id = ?');
            $userStmt->execute([$userId]);
            $userFiliereId = (int)($userStmt->fetchColumn() ?: 0);

            $statement = $this->pdo->prepare(
                "SELECT u.id, u.prenom, u.nom, u.role, u.avatar_url, f.nom AS filiere,
                        CASE WHEN u.filiere_id = :filiere_id AND :filiere_id > 0 THEN 1 ELSE 0 END AS same_filiere
                 FROM utilisateurs u
                 LEFT JOIN filieres f ON f.id = u.filiere_id
                 WHERE u.id <> :user_id
                   AND u.est_actif = 1
                   AND u.id NOT IN (
                       SELECT CASE WHEN a.utilisateur_id = :uid1 THEN a.ami_id ELSE a.utilisateur_id END
                       FROM amis a
                       WHERE (a.utilisateur_id = :uid2 OR a.ami_id = :uid3)
                         AND a.statut IN ('accepte', 'en_attente')
                   )
                 ORDER BY same_filiere DESC, u.prenom ASC, u.nom ASC
                 LIMIT :limit"
            );
            $statement->bindValue(':filiere_id', $userFiliereId, PDO::PARAM_INT);
            $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $statement->bindValue(':uid1', $userId, PDO::PARAM_INT);
            $statement->bindValue(':uid2', $userId, PDO::PARAM_INT);
            $statement->bindValue(':uid3', $userId, PDO::PARAM_INT);
            $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Trouve ou crée une conversation privée entre 2 utilisateurs.
     */
    public function getOrCreateConversation(int $userId, int $friendId): int
    {
        [$u1, $u2] = [$userId < $friendId ? $userId : $friendId, $userId < $friendId ? $friendId : $userId];

        $stmt = $this->pdo->prepare(
            'SELECT id FROM conversations WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?) LIMIT 1'
        );
        $stmt->execute([$u1, $u2, $u2, $u1]);
        $convId = (int)$stmt->fetchColumn();

        if ($convId > 0) {
            return $convId;
        }

        $ins = $this->pdo->prepare(
            'INSERT INTO conversations (user1_id, user2_id, date_creation, derniere_activite) VALUES (?, ?, NOW(), NOW())'
        );
        $ins->execute([$u1, $u2]);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Récupère le nombre total de messages privés non lus.
     */
    public function getUnreadMessageCount(int $userId): int
    {
        try {
            $statement = $this->pdo->prepare(
                'SELECT COUNT(*)
                 FROM messages_prives mp
                 JOIN conversations c ON c.id = mp.conversation_id
                 WHERE (c.user1_id = :u1 OR c.user2_id = :u2)
                   AND mp.expediteur_id <> :u3
                   AND mp.lu = 0'
            );
            $statement->execute(['u1' => $userId, 'u2' => $userId, 'u3' => $userId]);
            return (int)$statement->fetchColumn();
        } catch (Throwable $e) {
            return 0;
        }
    }

    /**
     * Boîte de réception des amis avec le dernier message et le compteur non lus.
     */
    public function getInbox(int $userId): array
    {
        try {
            $statement = $this->pdo->prepare(
                "SELECT u.id, u.prenom, u.nom, u.role, u.avatar_url, f.nom AS filiere,
                        c.id AS conversation_id,
                        (SELECT mp.contenu FROM messages_prives mp WHERE mp.conversation_id = c.id ORDER BY mp.date_creation DESC, mp.id DESC LIMIT 1) AS last_message,
                        (SELECT mp.date_creation FROM messages_prives mp WHERE mp.conversation_id = c.id ORDER BY mp.date_creation DESC, mp.id DESC LIMIT 1) AS last_message_date,
                        (SELECT COUNT(*) FROM messages_prives mp WHERE mp.conversation_id = c.id AND mp.expediteur_id = u.id AND mp.lu = 0) AS unread_count
                 FROM amis a
                 JOIN utilisateurs u ON (u.id = CASE WHEN a.utilisateur_id = :u1 THEN a.ami_id ELSE a.utilisateur_id END)
                 LEFT JOIN filieres f ON f.id = u.filiere_id
                 LEFT JOIN conversations c ON (c.user1_id = :u2 AND c.user2_id = u.id) OR (c.user1_id = u.id AND c.user2_id = :u3)
                 WHERE (a.utilisateur_id = :u4 OR a.ami_id = :u5)
                   AND a.statut = 'accepte'
                   AND u.est_actif = 1
                 ORDER BY COALESCE(last_message_date, a.date_reponse) DESC, u.prenom ASC, u.nom ASC"
            );
            $statement->execute([
                'u1' => $userId,
                'u2' => $userId,
                'u3' => $userId,
                'u4' => $userId,
                'u5' => $userId,
            ]);
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Récupère les messages d'une conversation privée entre deux amis.
     */
    public function getMessages(int $userId, int $friendId, int $limit = 100): array
    {
        try {
            if (!$this->areFriends($userId, $friendId)) {
                return [];
            }

            $convId = $this->getOrCreateConversation($userId, $friendId);
            $limit = max(1, min(200, $limit));

            $statement = $this->pdo->prepare(
                "SELECT m.id, m.conversation_id, m.expediteur_id AS sender_id,
                        CASE WHEN m.expediteur_id = :u1 THEN :friend1 ELSE :u2 END AS recipient_id,
                        m.contenu, m.lu, m.date_creation, NULL AS deleted_for_everyone_at
                 FROM messages_prives m
                 WHERE m.conversation_id = :conv_id
                 ORDER BY m.date_creation ASC, m.id ASC
                 LIMIT {$limit}"
            );
            $statement->execute([
                'u1' => $userId,
                'friend1' => $friendId,
                'u2' => $userId,
                'conv_id' => $convId,
            ]);
            $messages = $statement->fetchAll(PDO::FETCH_ASSOC);

            // Charger les pièces jointes associées
            if ($messages) {
                $messageIds = array_column($messages, 'id');
                $placeholders = implode(',', array_fill(0, count($messageIds), '?'));
                $attachStmt = $this->pdo->prepare(
                    "SELECT id, cible_id AS message_id, nom_fichier, chemin_fichier, type_mime, taille
                     FROM pieces_jointes
                     WHERE type_cible = 'message_prive' AND cible_id IN ({$placeholders})
                     ORDER BY id ASC"
                );
                $attachStmt->execute($messageIds);
                $attachmentsByMsg = [];
                foreach ($attachStmt->fetchAll(PDO::FETCH_ASSOC) as $attachment) {
                    $attachmentsByMsg[(int)$attachment['message_id']][] = $attachment;
                }
                foreach ($messages as &$msg) {
                    $msg['attachments'] = $attachmentsByMsg[(int)$msg['id']] ?? [];
                }
                unset($msg);
            }

            // Marquer les messages reçus comme lus
            $markRead = $this->pdo->prepare(
                'UPDATE messages_prives SET lu = 1 WHERE conversation_id = ? AND expediteur_id = ? AND lu = 0'
            );
            $markRead->execute([$convId, $friendId]);

            return $messages;
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Envoie un message privé à un ami.
     */
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
            $convId = $this->getOrCreateConversation($senderId, $recipientId);

            $stmt = $this->pdo->prepare(
                'INSERT INTO messages_prives (conversation_id, expediteur_id, contenu, lu, date_creation)
                 VALUES (?, ?, ?, 0, NOW())'
            );
            $stmt->execute([$convId, $senderId, $content]);
            $messageId = (int)$this->pdo->lastInsertId();

            $upConv = $this->pdo->prepare('UPDATE conversations SET derniere_activite = NOW() WHERE id = ?');
            $upConv->execute([$convId]);

            if ($validatedFiles !== []) {
                $attachStmt = $this->pdo->prepare(
                    "INSERT INTO pieces_jointes (type_cible, cible_id, nom_fichier, chemin_fichier, type_mime, taille)
                     VALUES ('message_prive', ?, ?, ?, ?, ?)"
                );

                foreach ($validatedFiles as $file) {
                    $storedName = bin2hex(random_bytes(16)) . '.' . $file['extension'];
                    $destination = $uploadDirectory . DIRECTORY_SEPARATOR . $storedName;
                    if (!move_uploaded_file($file['tmp_name'], $destination)) {
                        throw new RuntimeException('Impossible de stocker une pièce jointe.');
                    }
                    $storedPaths[] = $destination;
                    $attachStmt->execute([
                        $messageId,
                        $file['name'],
                        'private-messages/' . $storedName,
                        $file['type'],
                        $file['size'],
                    ]);
                }
            }

            $this->pdo->commit();
            return $messageId;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            foreach ($storedPaths as $p) {
                if (is_file($p)) {
                    unlink($p);
                }
            }
            throw $e;
        }
    }

    /**
     * Suppression d'un message privé.
     */
    public function deleteMessageForMe(int $userId, int $messageId, int $friendId): bool
    {
        return $this->deleteMessage($userId, $messageId);
    }

    public function deleteMessageForEveryone(int $userId, int $messageId, int $friendId): bool
    {
        return $this->deleteMessage($userId, $messageId);
    }

    public function deleteMessage(int $userId, int $messageId): bool
    {
        if ($messageId <= 0 || $userId <= 0) {
            return false;
        }

        $stmt = $this->pdo->prepare(
            "DELETE mp FROM messages_prives mp
             JOIN conversations c ON c.id = mp.conversation_id
             WHERE mp.id = :mid AND (mp.expediteur_id = :uid OR c.user1_id = :uid OR c.user2_id = :uid)"
        );
        $stmt->execute(['mid' => $messageId, 'uid' => $userId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Valide les pièces jointes pour les messages privés.
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
            'zip' => ['application/zip', 'application/x-zip-compressed'],
        ];

        $validated = [];
        $fileCount = is_array($files['name']) ? count($files['name']) : 1;
        if ($fileCount > 5) {
            throw new InvalidArgumentException('Vous pouvez joindre au maximum 5 fichiers.');
        }

        for ($i = 0; $i < $fileCount; $i++) {
            $error = is_array($files['error']) ? $files['error'][$i] : $files['error'];
            if ($error === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            if ($error !== UPLOAD_ERR_OK) {
                throw new RuntimeException('Le transfert d’une pièce jointe a échoué.');
            }

            $name = (string)(is_array($files['name']) ? $files['name'][$i] : $files['name']);
            $tmpName = (string)(is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name']);
            $size = (int)(is_array($files['size']) ? $files['size'][$i] : $files['size']);

            if ($size > 10 * 1024 * 1024) {
                throw new InvalidArgumentException('Chaque pièce jointe est limitée à 10 Mo.');
            }

            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!isset($allowedTypes[$extension])) {
                throw new InvalidArgumentException('Ce format de fichier n’est pas accepté.');
            }

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = (string)$finfo->file($tmpName);
            if (!in_array($mime, $allowedTypes[$extension], true)) {
                $mime = $allowedTypes[$extension][0];
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
}
