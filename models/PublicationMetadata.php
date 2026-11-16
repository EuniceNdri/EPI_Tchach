<?php

if (defined('ROOT_PATH')) {
    require_once ROOT_PATH . '/config/database.php';
} else {
    require_once dirname(__DIR__) . '/config/database.php';
}

class PublicationMetadata {
    private ?PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        if ($pdo !== null) {
            $this->pdo = $pdo;
        } else {
            global $pdo;
            $this->pdo = $pdo;
        }
    }

    public function getActiveTags(): array {
        if (!$this->pdo) {
            return [];
        }

        $statement = $this->pdo->query(
            "SELECT id, nom FROM tags_publication WHERE statut = 'actif' ORDER BY nom"
        );
        return $statement ? $statement->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function getAdminTags(): array {
        if (!$this->pdo) {
            return [];
        }

        $statement = $this->pdo->query(
            "SELECT tag.id, tag.nom, tag.statut, tag.date_creation,
                    creator.prenom AS creator_prenom, creator.nom AS creator_nom
             FROM tags_publication tag
             LEFT JOIN utilisateurs creator ON creator.id = tag.cree_par
             ORDER BY FIELD(tag.statut, 'en_attente', 'actif', 'inactif'), tag.nom"
        );
        return $statement ? $statement->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function getTag(int $tagId): ?array {
        if (!$this->pdo || $tagId <= 0) {
            return null;
        }

        $statement = $this->pdo->prepare(
            "SELECT id, nom FROM tags_publication WHERE id = :id AND statut = 'actif' LIMIT 1"
        );
        $statement->execute(['id' => $tagId]);
        $tag = $statement->fetch(PDO::FETCH_ASSOC);
        return $tag ?: null;
    }

    public function getPublicationTags(array $publicationIds): array {
        if (!$this->pdo || !$publicationIds) {
            return [];
        }

        $publicationIds = array_values(array_unique(array_filter(
            array_map('intval', $publicationIds),
            static fn (int $id): bool => $id > 0
        )));
        if (!$publicationIds) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($publicationIds), '?'));
        $statement = $this->pdo->prepare(
            "SELECT link.publication_id, tag.id, tag.nom
             FROM publications_tags link
             JOIN tags_publication tag ON tag.id = link.tag_id AND tag.statut = 'actif'
             WHERE link.publication_id IN ($placeholders)
             ORDER BY tag.nom"
        );
        $statement->execute($publicationIds);

        $tagsByPublication = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $tag) {
            $tagsByPublication[(int)$tag['publication_id']][] = $tag;
        }
        return $tagsByPublication;
    }

    public function getPublicationMentions(array $publicationIds): array {
        if (!$this->pdo || !$publicationIds) {
            return [];
        }

        $publicationIds = array_values(array_unique(array_filter(
            array_map('intval', $publicationIds),
            static fn (int $id): bool => $id > 0
        )));
        if (!$publicationIds) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($publicationIds), '?'));
        $statement = $this->pdo->prepare(
            "SELECT mention.publication_id, mentioned_user.id, mentioned_user.prenom, mentioned_user.nom, mentioned_user.role
             FROM publications_mentions mention
             JOIN utilisateurs mentioned_user ON mentioned_user.id = mention.utilisateur_id
             WHERE mention.publication_id IN ($placeholders)
             ORDER BY mentioned_user.prenom, mentioned_user.nom"
        );
        $statement->execute($publicationIds);

        $mentionsByPublication = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $mention) {
            $mentionsByPublication[(int)$mention['publication_id']][] = $mention;
        }
        return $mentionsByPublication;
    }

    public function findMentionableUsers(
        int $requesterId,
        int $categoryId,
        string $query = '',
        int $limit = 12,
        array $userIds = []
    ): array {
        if (!$this->pdo || $requesterId <= 0 || $categoryId <= 0) {
            return [];
        }

        $limit = max(1, min($limit, 50));
        $parameters = [
            'requester_id' => $requesterId,
            'category_id' => $categoryId,
        ];
        $searchCondition = '';
        $userIdCondition = '';
        if ($userIds) {
            $userIds = array_values(array_unique(array_filter(
                array_map('intval', $userIds),
                static fn (int $id): bool => $id > 0
            )));
            if (!$userIds) {
                return [];
            }
            $userIdPlaceholders = [];
            foreach ($userIds as $index => $userId) {
                $parameter = 'target_' . $index;
                $userIdPlaceholders[] = ':' . $parameter;
                $parameters[$parameter] = $userId;
            }
            $userIdCondition = ' AND u.id IN (' . implode(',', $userIdPlaceholders) . ')';
        } elseif (trim($query) !== '') {
            $searchCondition = ' AND (u.prenom LIKE :search_first OR u.nom LIKE :search_last OR CONCAT(u.prenom, \' \', u.nom) LIKE :search_full)';
            $search = '%' . trim($query) . '%';
            $parameters['search_first'] = $search;
            $parameters['search_last'] = $search;
            $parameters['search_full'] = $search;
        }

        $statement = $this->pdo->prepare(
            "SELECT DISTINCT u.id, u.prenom, u.nom, u.role,
                    CASE
                        WHEN u.role = 'professeur' THEN 'Professeurs'
                        WHEN u.role = 'administrateur' THEN 'Administration'
                        ELSE 'Délégation'
                    END AS role_group
             FROM utilisateurs u
             JOIN categories category ON category.id = :category_id
             JOIN zones zone ON zone.id = category.zone_id
             LEFT JOIN appartenance_zones membership
               ON membership.zone_id = zone.id AND membership.utilisateur_id = u.id
             WHERE u.est_actif = 1
               AND u.id <> :requester_id
               AND (
                    (u.role = 'professeur' AND membership.utilisateur_id IS NOT NULL)
                    OR u.role = 'administrateur'
                    OR membership.role_dans_zone IN ('delegue_general', 'delegue_de_classe')
               )
               $searchCondition
               $userIdCondition
             ORDER BY FIELD(role_group, 'Professeurs', 'Délégation', 'Administration'),
                      u.prenom, u.nom
             LIMIT $limit"
        );
        $statement->execute($parameters);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function attachTagsToPublication(int $publicationId, int $userId, array $approvedTagIds, array $proposedNames): array {
        if (!$this->pdo || $publicationId <= 0 || $userId <= 0) {
            throw new InvalidArgumentException('La publication ou le compte est invalide.');
        }

        $tagIds = array_values(array_unique(array_filter(
            array_map('intval', $approvedTagIds),
            static fn (int $id): bool => $id > 0
        )));
        if (count($tagIds) > 10) {
            throw new InvalidArgumentException('Vous pouvez sélectionner au maximum 10 tags.');
        }

        if ($tagIds) {
            $placeholders = implode(',', array_fill(0, count($tagIds), '?'));
            $statement = $this->pdo->prepare(
                "SELECT id FROM tags_publication WHERE statut = 'actif' AND id IN ($placeholders)"
            );
            $statement->execute($tagIds);
            $validIds = array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));
            if (count($validIds) !== count($tagIds)) {
                throw new InvalidArgumentException('Un ou plusieurs tags sélectionnés ne sont plus disponibles.');
            }
        }

        $normalizedNames = [];
        foreach ($proposedNames as $proposedName) {
            if (!is_scalar($proposedName)) {
                continue;
            }
            $name = trim(ltrim(trim((string)$proposedName), '#'));
            if ($name === '') {
                continue;
            }
            if (mb_strlen($name) > 50 || !preg_match('/^[\p{L}\p{N}][\p{L}\p{N} _-]*$/u', $name)) {
                throw new InvalidArgumentException('Chaque proposition de tag doit contenir au maximum 50 caractères et uniquement des lettres, chiffres, espaces, tirets ou underscores.');
            }
            $normalizedNames[mb_strtolower($name)] = $name;
        }
        if (count($normalizedNames) > 5) {
            throw new InvalidArgumentException('Vous pouvez proposer au maximum 5 nouveaux tags par publication.');
        }

        $findTag = $this->pdo->prepare(
            'SELECT id, statut FROM tags_publication WHERE nom = :nom LIMIT 1'
        );
        $createTag = $this->pdo->prepare(
            "INSERT IGNORE INTO tags_publication (nom, statut, cree_par, date_creation)
             VALUES (:nom, 'en_attente', :user_id, NOW())"
        );
        foreach ($normalizedNames as $name) {
            $findTag->execute(['nom' => $name]);
            $existing = $findTag->fetch(PDO::FETCH_ASSOC);
            if ($existing) {
                if ($existing['statut'] === 'inactif') {
                    throw new InvalidArgumentException("Le tag « {$name} » est actuellement désactivé.");
                }
                $tagIds[] = (int)$existing['id'];
                continue;
            }

            $createTag->execute(['nom' => $name, 'user_id' => $userId]);
            if ($createTag->rowCount() > 0) {
                $tagIds[] = (int)$this->pdo->lastInsertId();
                continue;
            }

            $findTag->execute(['nom' => $name]);
            $existing = $findTag->fetch(PDO::FETCH_ASSOC);
            if (!$existing || $existing['statut'] === 'inactif') {
                throw new RuntimeException('La proposition de tag n’a pas pu être enregistrée.');
            }
            $tagIds[] = (int)$existing['id'];
        }

        $tagIds = array_values(array_unique($tagIds));
        if (count($tagIds) > 10) {
            throw new InvalidArgumentException('Vous pouvez associer au maximum 10 tags à une publication.');
        }
        if ($tagIds) {
            $attach = $this->pdo->prepare(
                'INSERT IGNORE INTO publications_tags (publication_id, tag_id) VALUES (:publication_id, :tag_id)'
            );
            foreach ($tagIds as $tagId) {
                $attach->execute(['publication_id' => $publicationId, 'tag_id' => $tagId]);
            }
        }

        return $tagIds;
    }

    public function createAdminTag(string $name, int $adminId): void {
        $name = $this->validateTagName($name);
        $statement = $this->pdo->prepare(
            "INSERT INTO tags_publication (nom, statut, cree_par, valide_par, date_creation)
             VALUES (:nom, 'actif', :admin_id, :admin_id, NOW())"
        );
        $statement->execute(['nom' => $name, 'admin_id' => $adminId]);
    }

    public function renameTag(int $tagId, string $name): void {
        $name = $this->validateTagName($name);
        $statement = $this->pdo->prepare(
            'UPDATE tags_publication SET nom = :nom WHERE id = :id'
        );
        $statement->execute(['nom' => $name, 'id' => $tagId]);
        if ($statement->rowCount() === 0) {
            $exists = $this->pdo->prepare('SELECT 1 FROM tags_publication WHERE id = :id');
            $exists->execute(['id' => $tagId]);
            if (!$exists->fetchColumn()) {
                throw new InvalidArgumentException('Tag introuvable.');
            }
        }
    }

    public function setTagStatus(int $tagId, string $status, int $adminId): void {
        if (!in_array($status, ['en_attente', 'actif', 'inactif'], true)) {
            throw new InvalidArgumentException('Statut de tag invalide.');
        }
        $statement = $this->pdo->prepare(
            'UPDATE tags_publication
             SET statut = :statut, valide_par = :admin_id
             WHERE id = :id'
        );
        $statement->execute(['statut' => $status, 'admin_id' => $adminId, 'id' => $tagId]);
        if ($statement->rowCount() === 0) {
            $exists = $this->pdo->prepare('SELECT 1 FROM tags_publication WHERE id = :id');
            $exists->execute(['id' => $tagId]);
            if (!$exists->fetchColumn()) {
                throw new InvalidArgumentException('Tag introuvable.');
            }
        }
    }

    public function deleteTag(int $tagId): void {
        $statement = $this->pdo->prepare('DELETE FROM tags_publication WHERE id = :id');
        $statement->execute(['id' => $tagId]);
        if ($statement->rowCount() === 0) {
            throw new InvalidArgumentException('Tag introuvable.');
        }
    }

    private function validateTagName(string $name): string {
        $name = trim(ltrim(trim($name), '#'));
        if ($name === '' || mb_strlen($name) > 50
            || !preg_match('/^[\p{L}\p{N}][\p{L}\p{N} _-]*$/u', $name)) {
            throw new InvalidArgumentException('Le nom du tag est invalide (50 caractères maximum).');
        }
        return $name;
    }
}
