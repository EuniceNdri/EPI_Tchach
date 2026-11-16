<?php
/**
 * =============================================================
 * models/Notification.php – Modèle de Gestion des Notifications
 * =============================================================
 * Gère la création, la lecture et l'envoi de notifications
 * aux utilisateurs (réactions, avertissements, modération, etc.)
 * =============================================================
 */

if (defined('ROOT_PATH')) {
    require_once ROOT_PATH . '/config/database.php';
} else {
    require_once dirname(__DIR__) . '/config/database.php';
}

if (!function_exists('url')) {
    function url(string $path = ''): string {
        global $baseUrl;
        $base = $baseUrl ?? '';
        $trimmed = ltrim($path, '/');
        return $base ? $base . '/' . $trimmed : '/' . $trimmed;
    }
}

class Notification {
    private ?PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        if ($pdo !== null) {
            $this->pdo = $pdo;
        } else {
            global $pdo;
            $this->pdo = $pdo;
        }
    }

    /**
     * Crée une notification pour un utilisateur
     *
     * @param int $userId ID de l'utilisateur destinataire
     * @param string $type Type (ex: 'reaction', 'avertissement', 'moderation', 'systeme')
     * @param string $contenu Message de la notification
     * @param string|null $lien URL de redirection optionnelle
     * @return int|false ID de la notification créée ou false
     */
    public function create(int $userId, string $type, string $contenu, ?string $lien = null) {
        if (!$this->pdo || $userId <= 0) {
            return false;
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO notifications (utilisateur_id, type_notification, contenu, lien, lu, date_creation)
            VALUES (:user_id, :type, :contenu, :lien, 0, NOW())
        ");

        $success = $stmt->execute([
            'user_id' => $userId,
            'type'    => $type,
            'contenu' => mb_substr($contenu, 0, 500),
            'lien'    => $lien ? mb_substr($lien, 0, 255) : null
        ]);

        return $success ? (int)$this->pdo->lastInsertId() : false;
    }

    /**
     * Envoie une notification lors d'une réaction sur une publication
     */
    public function notifyReaction(int $auteurPublicationId, int $auteurReactionId, string $typeReaction, string $titrePublication, int $publicationId): bool {
        // Ne pas s'auto-notifier
        if ($auteurPublicationId <= 0 || $auteurPublicationId === $auteurReactionId) {
            return false;
        }

        // Récupérer le nom de la personne qui réagit
        $userName = 'Un membre';
        if ($this->pdo) {
            $uStmt = $this->pdo->prepare("SELECT prenom, nom FROM utilisateurs WHERE id = :id LIMIT 1");
            $uStmt->execute(['id' => $auteurReactionId]);
            if ($u = $uStmt->fetch()) {
                $userName = trim($u['prenom'] . ' ' . $u['nom']);
            }
        }

        $reactionLabels = [
            'like'   => 'a aimé 👍',
            'jadore' => 'a adoré ❤️',
            'utile'  => 'a trouvé utile 💡'
        ];
        $label = $reactionLabels[$typeReaction] ?? 'a réagi à';

        $titreCourt = mb_strimwidth($titrePublication, 0, 45, '...');
        $contenu = "{$userName} {$label} votre publication : \"{$titreCourt}\"";
        $lien = url("salon?pub=" . $publicationId);

        return (bool)$this->create($auteurPublicationId, 'reaction', $contenu, $lien);
    }

    /**
     * Notifie les membres de la zone lorsqu'une publication est visible.
     */
    public function notifyPublicationMembers(int $auteurId, int $categorieId, string $titrePublication, int $publicationId): int {
        if (!$this->pdo || $auteurId <= 0 || $categorieId <= 0 || $publicationId <= 0) {
            return 0;
        }

        $authorStatement = $this->pdo->prepare(
            'SELECT prenom, nom FROM utilisateurs WHERE id = :id LIMIT 1'
        );
        $authorStatement->execute(['id' => $auteurId]);
        $author = $authorStatement->fetch(PDO::FETCH_ASSOC);
        $authorName = $author
            ? trim((string)$author['prenom'] . ' ' . (string)$author['nom'])
            : 'Un membre';

        $membersStatement = $this->pdo->prepare(
            'SELECT DISTINCT membership.utilisateur_id
             FROM categories category
             JOIN appartenance_zones membership ON membership.zone_id = category.zone_id
             JOIN utilisateurs member ON member.id = membership.utilisateur_id AND member.est_actif = 1
             WHERE category.id = :category_id
               AND membership.utilisateur_id <> :author_id'
        );
        $membersStatement->execute([
            'category_id' => $categorieId,
            'author_id' => $auteurId,
        ]);

        $shortTitle = mb_strimwidth($titrePublication, 0, 60, '...');
        $message = "{$authorName} a publié « {$shortTitle} » dans un salon que vous avez rejoint.";
        $link = url('salon?pub=' . $publicationId);
        $count = 0;

        foreach ($membersStatement->fetchAll(PDO::FETCH_COLUMN) as $memberId) {
            if (!$this->create((int)$memberId, 'publication', $message, $link)) {
                throw new RuntimeException('La notification de nouvelle publication n’a pas pu être enregistrée.');
            }
            $count++;
        }

        return $count;
    }

    /**
     * Notifie l'auteur d'une publication d'un nouveau commentaire visible.
     */
    public function notifyComment(int $auteurPublicationId, int $auteurCommentaireId, string $titrePublication, int $publicationId): bool {
        if (!$this->pdo || $auteurPublicationId <= 0 || $publicationId <= 0
            || $auteurPublicationId === $auteurCommentaireId) {
            return false;
        }

        $userStatement = $this->pdo->prepare(
            'SELECT prenom, nom FROM utilisateurs WHERE id = :id LIMIT 1'
        );
        $userStatement->execute(['id' => $auteurCommentaireId]);
        $user = $userStatement->fetch(PDO::FETCH_ASSOC);
        $userName = $user
            ? trim((string)$user['prenom'] . ' ' . (string)$user['nom'])
            : 'Un membre';

        $shortTitle = mb_strimwidth($titrePublication, 0, 60, '...');
        $message = "{$userName} a commenté votre publication : « {$shortTitle} »";

        $created = $this->create(
            $auteurPublicationId,
            'reponse',
            $message,
            url('salon?pub=' . $publicationId)
        );
        if (!$created) {
            throw new RuntimeException('La notification de commentaire n’a pas pu être enregistrée.');
        }
        return true;
    }

    public function notifyMention(int $mentionedUserId, int $authorId, string $publicationTitle, string $link): bool {
        if (!$this->pdo || $mentionedUserId <= 0 || $mentionedUserId === $authorId) {
            return false;
        }

        $userStatement = $this->pdo->prepare(
            'SELECT prenom, nom FROM utilisateurs WHERE id = :id LIMIT 1'
        );
        $userStatement->execute(['id' => $authorId]);
        $user = $userStatement->fetch(PDO::FETCH_ASSOC);
        $authorName = $user
            ? trim((string)$user['prenom'] . ' ' . (string)$user['nom'])
            : 'Un membre';
        $shortTitle = mb_strimwidth($publicationTitle, 0, 60, '...');

        $created = $this->create(
            $mentionedUserId,
            'mention',
            "{$authorName} vous a mentionné dans la publication « {$shortTitle} ».",
            $link
        );
        if (!$created) {
            throw new RuntimeException('La notification de mention n’a pas pu être enregistrée.');
        }
        return true;
    }

    /**
     * Notifie un utilisateur d'un avertissement de modération
     */
    public function notifyAvertissement(int $userId, string $motif, ?string $titreContenu = null): bool {
        $msg = "⚠️ Avertissement de modération : ";
        if ($titreContenu) {
            $titreCourt = mb_strimwidth($titreContenu, 0, 40, '...');
            $msg .= "Concernant votre publication \"{$titreCourt}\" : ";
        }
        $msg .= $motif . ". Merci de respecter la charte de l'école.";

        return (bool)$this->create($userId, 'avertissement', $msg, url('profile'));
    }

    /**
     * Notifie la suppression d'une publication par la modération
     */
    public function notifySuppression(int $userId, string $titrePublication, string $motif = ''): bool {
        $titreCourt = mb_strimwidth($titrePublication, 0, 40, '...');
        $msg = "❌ Votre publication \"{$titreCourt}\" a été supprimée par un responsable de la modération.";
        if (!empty($motif)) {
            $msg .= " Motif : " . $motif;
        }

        return (bool)$this->create($userId, 'moderation', $msg, url('profile'));
    }

    /**
     * Notifie le blocage automatique d'un contenu suite à de multiples signalements
     */
    public function notifyBlocageAutomatique(int $userId, string $titrePublication, int $nbSignalements): bool {
        $titreCourt = mb_strimwidth($titrePublication, 0, 40, '...');
        $msg = "🛑 Votre publication \"{$titreCourt}\" a été temporairement masquée/bloquée suite à {$nbSignalements} signalements de la communauté. Elle est en attente d'examen par les administrateurs.";

        return (bool)$this->create($userId, 'moderation', $msg, url('profile'));
    }

    /**
     * Envoie une notification aux administrateurs et responsables de zone
     */
    public function notifyAdmins(string $type, string $contenu, ?string $lien = null): int {
        if (!$this->pdo) {
            return 0;
        }

        $stmt = $this->pdo->query("SELECT id FROM utilisateurs WHERE role = 'administrateur'");
        $admins = $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];

        $count = 0;
        foreach ($admins as $adminId) {
            if ($this->create((int)$adminId, $type, $contenu, $lien)) {
                $count++;
            }
        }

        return $count;
    }

    public function notifyClassDelegatesForReport(int $reportId, string $message, ?string $link = null): int
    {
        if (!$this->pdo || $reportId <= 0) {
            return 0;
        }

        $statement = $this->pdo->prepare(
            "SELECT DISTINCT membership.utilisateur_id
             FROM signalements report
             LEFT JOIN publications publication
               ON report.type_cible = 'publication' AND publication.id = report.cible_id
             LEFT JOIN commentaires comment
               ON report.type_cible = 'commentaire' AND comment.id = report.cible_id
             LEFT JOIN publications comment_publication
               ON comment_publication.id = comment.publication_id
             JOIN categories category
               ON category.id = COALESCE(publication.categorie_id, comment_publication.categorie_id)
             JOIN zones zone ON zone.id = category.zone_id AND zone.type_zone = 'filiere' AND zone.est_active = 1
             JOIN appartenance_zones membership
               ON membership.zone_id = zone.id
              AND membership.role_dans_zone = 'delegue_de_classe'
             JOIN utilisateurs recipient
               ON recipient.id = membership.utilisateur_id AND recipient.est_actif = 1
             WHERE report.id = :report_id"
        );
        $statement->execute(['report_id' => $reportId]);

        $count = 0;
        foreach ($statement->fetchAll(PDO::FETCH_COLUMN) as $userId) {
            if ($this->create((int)$userId, 'moderation', $message, $link)) {
                $count++;
            }
        }
        return $count;
    }

    public function notifyGlobalDelegates(int $reportId, string $message, ?string $link = null): int
    {
        if (!$this->pdo || $reportId <= 0) {
            return 0;
        }

        $statement = $this->pdo->query(
            "SELECT DISTINCT membership.utilisateur_id
             FROM appartenance_zones membership
             JOIN zones zone ON zone.id = membership.zone_id
             JOIN utilisateurs recipient
               ON recipient.id = membership.utilisateur_id AND recipient.est_actif = 1
             WHERE membership.role_dans_zone = 'delegue_general'
               AND zone.type_zone = 'delegation' AND zone.est_active = 1"
        );

        $count = 0;
        foreach ($statement->fetchAll(PDO::FETCH_COLUMN) as $userId) {
            if ($this->create((int)$userId, 'moderation', $message, $link)) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Récupère les notifications d'un utilisateur
     */
    public function getUserNotifications(int $userId, int $limit = 30, bool $unreadOnly = false): array {
        if (!$this->pdo || $userId <= 0) {
            return [];
        }
        $limit = max(1, min($limit, 100));

        $sql = "SELECT id, type_notification, contenu, lien, lu, date_creation 
                FROM notifications 
                WHERE utilisateur_id = :user_id";

        if ($unreadOnly) {
            $sql .= " AND lu = 0";
        }

        $sql .= " ORDER BY date_creation DESC LIMIT :limit";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Compte les notifications non lues
     */
    public function countUnread(int $userId): int {
        if (!$this->pdo || $userId <= 0) {
            return 0;
        }

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM notifications WHERE utilisateur_id = :user_id AND lu = 0");
        $stmt->execute(['user_id' => $userId]);

        return (int)$stmt->fetchColumn();
    }

    /**
     * Marque une notification comme lue
     */
    public function markAsRead(int $notificationId, int $userId): bool {
        if (!$this->pdo || $notificationId <= 0 || $userId <= 0) {
            return false;
        }

        $stmt = $this->pdo->prepare("UPDATE notifications SET lu = 1 WHERE id = :id AND utilisateur_id = :user_id");
        return $stmt->execute(['id' => $notificationId, 'user_id' => $userId]) && $stmt->rowCount() > 0;
    }

    /**
     * Marque toutes les notifications d'un utilisateur comme lues
     */
    public function markAllAsRead(int $userId): bool {
        if (!$this->pdo || $userId <= 0) {
            return false;
        }

        $stmt = $this->pdo->prepare("UPDATE notifications SET lu = 1 WHERE utilisateur_id = :user_id AND lu = 0");
        return $stmt->execute(['user_id' => $userId]);
    }

    /**
     * Supprime une notification
     */
    public function delete(int $notificationId, int $userId): bool {
        if (!$this->pdo || $notificationId <= 0 || $userId <= 0) {
            return false;
        }

        $stmt = $this->pdo->prepare("DELETE FROM notifications WHERE id = :id AND utilisateur_id = :user_id");
        return $stmt->execute(['id' => $notificationId, 'user_id' => $userId]) && $stmt->rowCount() > 0;
    }
}
