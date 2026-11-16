<?php

class FiliereGroups
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAccessibleGroups(int $userId): array
    {
        $user = $this->getActiveUser($userId);
        if (!$user) {
            return [];
        }

        $this->ensureGroups();
        $role = strtolower(trim((string)$user['role']));
        $conditions = [];
        $parameters = [];

        if (in_array($role, ['administrateur', 'admin', 'moderateur'], true)) {
            $conditions[] = '1 = 1';
        } elseif ($role === 'professeur') {
            $conditions[] = 'EXISTS (
                SELECT 1 FROM filiere_group_professors access
                WHERE access.group_id = message_group.id AND access.user_id = :user_id
            )';
            $parameters['user_id'] = $userId;
        } elseif ($role === 'etudiant') {
            $conditions[] = 'message_group.filiere_id = COALESCE(
                :filiere_id,
                (SELECT legacy_filiere.id FROM filieres legacy_filiere
                 WHERE legacy_filiere.nom = TRIM(:filiere_name)
                    OR legacy_filiere.code = TRIM(:filiere_name)
                    OR CONCAT("BTS ", legacy_filiere.code) = TRIM(:filiere_name)
                    OR TRIM(:filiere_name) LIKE CONCAT("%", legacy_filiere.code, "%")
                 LIMIT 1)
            )';
            $parameters['filiere_id'] = $user['filiere_id'];
            $parameters['filiere_name'] = (string)($user['filiere'] ?? '');
        } else {
            return [];
        }

        $statement = $this->pdo->prepare(
            'SELECT message_group.id, filiere.nom AS name,
                    COALESCE(unread.message_count, 0) AS unread_count
             FROM filiere_message_groups message_group
             JOIN filieres filiere ON filiere.id = message_group.filiere_id AND filiere.est_active = 1
             LEFT JOIN (
                 SELECT message.group_id, COUNT(*) AS message_count
                 FROM filiere_group_messages message
                 LEFT JOIN filiere_group_reads read_state
                   ON read_state.group_id = message.group_id AND read_state.user_id = :read_user
                 WHERE message.sender_id <> :sender_user
                   AND (read_state.last_read_at IS NULL OR message.date_creation > read_state.last_read_at)
                 GROUP BY message.group_id
             ) unread ON unread.group_id = message_group.id
             WHERE ' . implode(' AND ', $conditions) . '
             ORDER BY filiere.nom'
        );
        $statement->execute(array_merge([
            'read_user' => $userId,
            'sender_user' => $userId,
        ], $parameters));

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMessages(int $userId, int $groupId, int $limit = 100): array
    {
        if (!$this->canAccessGroup($userId, $groupId)) {
            throw new DomainException('Vous n’avez pas accès à ce groupe.');
        }

        $markRead = $this->pdo->prepare(
            'INSERT INTO filiere_group_reads (group_id, user_id, last_read_at)
             VALUES (?, ?, NOW())
             ON DUPLICATE KEY UPDATE last_read_at = NOW()'
        );
        $markRead->execute([$groupId, $userId]);

        $limit = max(1, min(200, $limit));
        $statement = $this->pdo->prepare(
            "SELECT message.id, message.group_id, message.sender_id, message.contenu, message.date_creation,
                    user.prenom, user.nom, user.role, user.avatar_url
             FROM filiere_group_messages message
             JOIN utilisateurs user ON user.id = message.sender_id
             WHERE message.group_id = ?
             ORDER BY message.date_creation DESC, message.id DESC
             LIMIT {$limit}"
        );
        $statement->execute([$groupId]);

        return array_reverse($statement->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getGroupMembers(int $userId, int $groupId): array
    {
        if (!$this->canAccessGroup($userId, $groupId)) {
            throw new DomainException('Vous n’avez pas accès à ce groupe.');
        }

        $statement = $this->pdo->prepare(
            "SELECT member.id, member.prenom, member.nom, member.role, member.avatar_url
             FROM filiere_message_groups message_group
             JOIN filieres filiere ON filiere.id = message_group.filiere_id
             JOIN utilisateurs member ON member.est_actif = 1
             WHERE message_group.id = ?
               AND (
                    (member.role = 'etudiant' AND COALESCE(
                        member.filiere_id,
                        (SELECT legacy_filiere.id
                         FROM filieres legacy_filiere
                         WHERE legacy_filiere.nom = TRIM(member.filiere)
                             OR legacy_filiere.code = TRIM(member.filiere)
                             OR CONCAT('BTS ', legacy_filiere.code) = TRIM(member.filiere)
                             OR TRIM(member.filiere) LIKE CONCAT('%', legacy_filiere.code, '%')
                         LIMIT 1)
                    ) = filiere.id)
                    OR (
                        member.role = 'professeur'
                        AND EXISTS (
                            SELECT 1
                            FROM filiere_group_professors access
                            WHERE access.group_id = message_group.id
                              AND access.user_id = member.id
                        )
                    )
               )
             ORDER BY CASE member.role WHEN 'professeur' THEN 0 ELSE 1 END,
                      member.nom, member.prenom"
        );
        $statement->execute([$groupId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function sendMessage(int $userId, int $groupId, string $content): int
    {
        $content = trim($content);
        if ($groupId <= 0 || $content === '' || mb_strlen($content, 'UTF-8') > 2000) {
            throw new InvalidArgumentException('Le message doit contenir entre 1 et 2 000 caractères.');
        }
        if (!$this->canAccessGroup($userId, $groupId)) {
            throw new DomainException('Vous n’avez pas accès à ce groupe.');
        }

        $statement = $this->pdo->prepare(
            'INSERT INTO filiere_group_messages (group_id, sender_id, contenu) VALUES (?, ?, ?)'
        );
        $statement->execute([$groupId, $userId, $content]);

        return (int)$this->pdo->lastInsertId();
    }

    public function getActiveGroups(): array
    {
        $this->ensureGroups();
        $statement = $this->pdo->query(
            'SELECT message_group.id, filiere.nom AS name
             FROM filiere_message_groups message_group
             JOIN filieres filiere ON filiere.id = message_group.filiere_id
             WHERE filiere.est_active = 1
             ORDER BY filiere.nom'
        );

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProfessorAssignments(): array
    {
        $statement = $this->pdo->query(
            'SELECT access.user_id, access.group_id
             FROM filiere_group_professors access
             JOIN utilisateurs professor ON professor.id = access.user_id
             JOIN filiere_message_groups message_group ON message_group.id = access.group_id
             JOIN filieres filiere ON filiere.id = message_group.filiere_id
             WHERE professor.role = \'professeur\'
               AND professor.est_actif = 1
               AND filiere.est_active = 1'
        );
        $assignments = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $assignment) {
            $assignments[(int)$assignment['user_id']][] = (int)$assignment['group_id'];
        }

        return $assignments;
    }

    public function getActiveProfessors(): array
    {
        $statement = $this->pdo->query(
            "SELECT id, prenom, nom, email
             FROM utilisateurs
             WHERE role = 'professeur' AND est_actif = 1
             ORDER BY nom, prenom"
        );

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function replaceProfessorGroups(int $professorId, array $groupIds, int $adminId): void
    {
        $professor = $this->pdo->prepare(
            "SELECT id FROM utilisateurs
             WHERE id = ? AND role = 'professeur' AND est_actif = 1
             LIMIT 1"
        );
        $professor->execute([$professorId]);
        if (!$professor->fetchColumn()) {
            throw new InvalidArgumentException('Le professeur sélectionné est introuvable ou inactif.');
        }

        $validatedGroupIds = [];
        foreach ($groupIds as $groupId) {
            if (!is_scalar($groupId)) {
                throw new InvalidArgumentException('La liste des groupes sélectionnés est invalide.');
            }
            $validatedId = filter_var((string)$groupId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($validatedId === false) {
                throw new InvalidArgumentException('La liste des groupes sélectionnés est invalide.');
            }
            $validatedGroupIds[] = (int)$validatedId;
        }
        $groupIds = array_values(array_unique($validatedGroupIds));
        $activeGroups = $this->getActiveGroups();
        $validGroupIds = array_map(static fn (array $group): int => (int)$group['id'], $activeGroups);
        if (array_diff($groupIds, $validGroupIds)) {
            throw new InvalidArgumentException('Un ou plusieurs groupes sélectionnés ne sont plus disponibles.');
        }

        $this->pdo->beginTransaction();
        try {
            $delete = $this->pdo->prepare('DELETE FROM filiere_group_professors WHERE user_id = ?');
            $delete->execute([$professorId]);
            $insert = $this->pdo->prepare(
                'INSERT INTO filiere_group_professors (group_id, user_id, assigned_by) VALUES (?, ?, ?)'
            );
            foreach ($groupIds as $groupId) {
                $insert->execute([$groupId, $professorId, $adminId]);
            }
            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    private function canAccessGroup(int $userId, int $groupId): bool
    {
        $groups = $this->getAccessibleGroups($userId);
        foreach ($groups as $group) {
            if ((int)$group['id'] === $groupId) {
                return true;
            }
        }

        return false;
    }

    private function getActiveUser(int $userId): ?array
    {
        if ($userId <= 0) {
            return null;
        }
        $statement = $this->pdo->prepare(
            'SELECT role, filiere_id, filiere
             FROM utilisateurs
             WHERE id = ? AND est_actif = 1
             LIMIT 1'
        );
        $statement->execute([$userId]);
        $user = $statement->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    private function ensureGroups(): void
    {
        $this->pdo->exec(
            'INSERT IGNORE INTO filiere_message_groups (filiere_id)
             SELECT filiere.id FROM filieres filiere
             WHERE filiere.est_active = 1'
        );
    }
}
