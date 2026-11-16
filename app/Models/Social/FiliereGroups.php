<?php

class FiliereGroups
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère la liste des zones accessibles avec leur chat de groupe.
     * Règle : 1 zone = 1 conversation de groupe automatique.
     */
    public function getAccessibleGroups(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }

        $userStmt = $this->pdo->prepare('SELECT role FROM utilisateurs WHERE id = ? LIMIT 1');
        $userStmt->execute([$userId]);
        $role = strtolower(trim((string)$userStmt->fetchColumn()));
        $isAdmin = in_array($role, ['administrateur', 'admin'], true);

        $statement = $this->pdo->prepare(
            'SELECT z.id, z.nom AS name, z.type_zone, z.visibilite, z.description,
                    COALESCE(unread.unread_count, 0) AS unread_count,
                    latest.contenu AS last_message,
                    latest.date_creation AS last_message_date
             FROM zones z
             LEFT JOIN (
                 SELECT zm.zone_id, COUNT(*) AS unread_count
                 FROM zone_messages zm
                 LEFT JOIN zone_message_reads zmr
                   ON zmr.zone_id = zm.zone_id AND zmr.user_id = :read_user
                 WHERE zm.expediteur_id <> :sender_user
                   AND (zmr.last_read_at IS NULL OR zm.date_creation > zmr.last_read_at)
                 GROUP BY zm.zone_id
             ) unread ON unread.zone_id = z.id
             LEFT JOIN (
                 SELECT zm1.zone_id, zm1.contenu, zm1.date_creation
                 FROM zone_messages zm1
                 INNER JOIN (
                     SELECT zone_id, MAX(id) AS max_id
                     FROM zone_messages
                     GROUP BY zone_id
                 ) zm2 ON zm1.id = zm2.max_id
             ) latest ON latest.zone_id = z.id
             WHERE z.est_active = 1
               AND (
                   :is_admin = 1
                   OR z.visibilite = \'public\'
                   OR EXISTS (
                       SELECT 1 FROM appartenance_zones az
                       WHERE az.zone_id = z.id AND az.utilisateur_id = :access_user
                   )
               )
             ORDER BY COALESCE(latest.date_creation, z.date_creation) DESC, z.nom ASC'
        );
        $statement->execute([
            'read_user' => $userId,
            'sender_user' => $userId,
            'is_admin' => $isAdmin ? 1 : 0,
            'access_user' => $userId,
        ]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les messages d'un salon/groupe.
     */
    public function getMessages(int $userId, int $groupId, int $limit = 100): array
    {
        if (!$this->canAccessGroup($userId, $groupId)) {
            throw new DomainException('Vous n’avez pas accès à ce salon.');
        }

        // Marquer comme lu
        $markRead = $this->pdo->prepare(
            'INSERT INTO zone_message_reads (zone_id, user_id, last_read_at)
             VALUES (?, ?, NOW())
             ON DUPLICATE KEY UPDATE last_read_at = NOW()'
        );
        $markRead->execute([$groupId, $userId]);

        $limit = max(1, min(200, $limit));
        $statement = $this->pdo->prepare(
            "SELECT zm.id, zm.zone_id AS group_id, zm.expediteur_id AS sender_id, zm.contenu, zm.date_creation,
                    u.prenom, u.nom, u.role, u.avatar_url
             FROM zone_messages zm
             JOIN utilisateurs u ON u.id = zm.expediteur_id
             WHERE zm.zone_id = ?
             ORDER BY zm.date_creation DESC, zm.id DESC
             LIMIT {$limit}"
        );
        $statement->execute([$groupId]);

        return array_reverse($statement->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Envoie un message dans un salon/groupe.
     */
    public function sendMessage(int $userId, int $groupId, string $content): int
    {
        $content = trim($content);
        if ($userId <= 0 || $groupId <= 0) {
            throw new InvalidArgumentException('Paramètres invalides.');
        }
        if ($content === '') {
            throw new InvalidArgumentException('Le message ne peut pas être vide.');
        }
        if (mb_strlen($content, 'UTF-8') > 2000) {
            throw new InvalidArgumentException('Le message ne peut pas dépasser 2 000 caractères.');
        }
        if (!$this->canAccessGroup($userId, $groupId)) {
            throw new DomainException('Vous n’avez pas accès à ce salon.');
        }

        $statement = $this->pdo->prepare(
            'INSERT INTO zone_messages (zone_id, expediteur_id, contenu, date_creation)
             VALUES (?, ?, ?, NOW())'
        );
        $statement->execute([$groupId, $userId, $content]);

        // Marquer comme lu pour l'expéditeur
        $markRead = $this->pdo->prepare(
            'INSERT INTO zone_message_reads (zone_id, user_id, last_read_at)
             VALUES (?, ?, NOW())
             ON DUPLICATE KEY UPDATE last_read_at = NOW()'
        );
        $markRead->execute([$groupId, $userId]);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Liste des membres d'un salon/groupe.
     */
    public function getGroupMembers(int $userId, int $groupId): array
    {
        if (!$this->canAccessGroup($userId, $groupId)) {
            throw new DomainException('Vous n’avez pas accès à ce groupe.');
        }

        $statement = $this->pdo->prepare(
            "SELECT u.id, u.prenom, u.nom, u.role, u.avatar_url,
                    COALESCE(az.role_dans_zone, 'membre') AS role_dans_zone
             FROM utilisateurs u
             JOIN appartenance_zones az ON az.utilisateur_id = u.id AND az.zone_id = ?
             WHERE u.est_actif = 1
             ORDER BY FIELD(az.role_dans_zone, 'admin_zone', 'delegue_general', 'delegue_de_classe', 'membre'),
                      u.prenom ASC, u.nom ASC"
        );
        $statement->execute([$groupId]);
        $members = $statement->fetchAll(PDO::FETCH_ASSOC);

        // Si la zone est générale/publique et qu'il y a peu ou pas d'appartenance_zones explicite
        if (empty($members)) {
            $genStmt = $this->pdo->prepare(
                "SELECT u.id, u.prenom, u.nom, u.role, u.avatar_url, 'membre' AS role_dans_zone
                 FROM utilisateurs u
                 WHERE u.est_actif = 1
                 ORDER BY u.prenom ASC, u.nom ASC
                 LIMIT 50"
            );
            $genStmt->execute();
            $members = $genStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $members;
    }

    /**
     * Vérifie si un utilisateur a accès à une zone/groupe.
     */
    public function canAccessGroup(int $userId, int $groupId): bool
    {
        if ($userId <= 0 || $groupId <= 0) {
            return false;
        }

        $stmt = $this->pdo->prepare(
            "SELECT z.visibilite,
                    EXISTS (SELECT 1 FROM appartenance_zones az WHERE az.zone_id = z.id AND az.utilisateur_id = :u1) AS is_member,
                    EXISTS (SELECT 1 FROM utilisateurs adm WHERE adm.id = :u2 AND adm.role = 'administrateur') AS is_admin
             FROM zones z
             WHERE z.id = :group_id AND z.est_active = 1
             LIMIT 1"
        );
        $stmt->execute([
            'group_id' => $groupId,
            'u1' => $userId,
            'u2' => $userId,
        ]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$res) {
            return false;
        }

        return (bool)$res['is_admin'] || $res['visibilite'] === 'public' || (bool)$res['is_member'];
    }

    /**
     * Récupère toutes les zones/groupes actifs pour l'administration.
     */
    public function getActiveGroups(): array
    {
        $stmt = $this->pdo->query(
            "SELECT z.id, z.nom AS name, z.type_zone, z.visibilite, z.description
             FROM zones z
             WHERE z.est_active = 1
             ORDER BY z.nom ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère la liste des professeurs actifs.
     */
    public function getActiveProfessors(): array
    {
        $stmt = $this->pdo->query(
            "SELECT u.id, u.nom, u.prenom, u.email, u.avatar_url
             FROM utilisateurs u
             WHERE u.role = 'professeur' AND u.est_actif = 1
             ORDER BY u.nom ASC, u.prenom ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les assignations actuelles groupe-professeur sous forme [profId => [groupId, ...]].
     */
    public function getProfessorAssignments(): array
    {
        $stmt = $this->pdo->query(
            "SELECT az.utilisateur_id, az.zone_id
             FROM appartenance_zones az
             JOIN utilisateurs u ON u.id = az.utilisateur_id
             WHERE u.role = 'professeur'"
        );
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $assignments = [];
        foreach ($rows as $row) {
            $pId = (int)$row['utilisateur_id'];
            $assignments[$pId][] = (int)$row['zone_id'];
        }
        return $assignments;
    }

    /**
     * Remplace les groupes assignés à un professeur.
     */
    public function replaceProfessorGroups(int $professorId, array $groupIds, int $adminId = 0): void
    {
        if ($professorId <= 0) {
            throw new InvalidArgumentException('Professeur spécifié invalide.');
        }

        $cleanGroupIds = array_values(array_unique(array_filter(
            array_map('intval', $groupIds),
            fn($id) => $id > 0
        )));

        $this->pdo->beginTransaction();
        try {
            $del = $this->pdo->prepare('DELETE FROM appartenance_zones WHERE utilisateur_id = ?');
            $del->execute([$professorId]);

            if (!empty($cleanGroupIds)) {
                $ins = $this->pdo->prepare(
                    "INSERT INTO appartenance_zones (utilisateur_id, zone_id, role_dans_zone, date_ajout)
                     VALUES (?, ?, 'membre', NOW())"
                );
                foreach ($cleanGroupIds as $gId) {
                    $ins->execute([$professorId, $gId]);
                }
            }

            $this->pdo->commit();
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }
}
