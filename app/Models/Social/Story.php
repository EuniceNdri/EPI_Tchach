<?php

final class Story
{
    public function __construct(private PDO $pdo)
    {
    }

    public function getVisibleStories(int $viewerId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT s.id, s.utilisateur_id, s.type_contenu, s.texte, s.image_url,
                    s.visibilite, s.date_creation, s.date_expiration,
                    u.prenom, u.nom, u.avatar_url,
                    EXISTS(
                        SELECT 1 FROM story_vues sv
                        WHERE sv.story_id = s.id AND sv.utilisateur_id = :viewer_view
                    ) AS est_vue,
                    (SELECT COUNT(*) FROM story_vues sv2 WHERE sv2.story_id = s.id) AS nombre_vues
             FROM stories s
             JOIN utilisateurs u ON u.id = s.utilisateur_id
             WHERE s.est_active = 1
               AND s.date_expiration > NOW()
               AND (
                    s.utilisateur_id = :viewer_owner
                    OR s.visibilite = 'public'
                    OR (
                        s.visibilite = 'amis'
                        AND EXISTS (
                            SELECT 1 FROM amis a
                            WHERE a.statut = 'accepte'
                              AND (
                                  (a.utilisateur_id = s.utilisateur_id AND a.ami_id = :friend_viewer)
                                  OR (a.ami_id = s.utilisateur_id AND a.utilisateur_id = :story_author)
                              )
                        )
                    )
               )
             ORDER BY s.utilisateur_id = :viewer_order DESC, s.date_creation ASC"
        );
        $statement->execute([
            'viewer_view' => $viewerId,
            'viewer_owner' => $viewerId,
            'friend_viewer' => $viewerId,
            'story_author' => $viewerId,
            'viewer_order' => $viewerId,
        ]);

        $groups = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $story) {
            $authorId = (int)$story['utilisateur_id'];
            if (!isset($groups[$authorId])) {
                $groups[$authorId] = [
                    'user_id' => $authorId,
                    'name' => trim($story['prenom'] . ' ' . $story['nom']),
                    'avatar_url' => $story['avatar_url'],
                    'is_owner' => $authorId === $viewerId,
                    'has_unseen' => false,
                    'stories' => [],
                ];
            }
            $viewed = (bool)$story['est_vue'];
            $groups[$authorId]['stories'][] = [
                'id' => (int)$story['id'],
                'type' => $story['type_contenu'],
                'text' => $story['texte'],
                'image_url' => $story['image_url'],
                'visibility' => $story['visibilite'],
                'created_at' => $story['date_creation'],
                'expires_at' => $story['date_expiration'],
                'viewed' => $viewed,
                'views' => (int)$story['nombre_vues'],
            ];
            if ($authorId !== $viewerId && !$viewed) {
                $groups[$authorId]['has_unseen'] = true;
            }
        }

        return array_values($groups);
    }

    public function create(
        int $userId,
        ?string $text,
        ?string $imageUrl,
        string $visibility
    ): int {
        if (!in_array($visibility, ['public', 'amis'], true)) {
            throw new InvalidArgumentException('La visibilité choisie est invalide.');
        }

        $type = $text !== null && $imageUrl !== null
            ? 'texte_image'
            : ($imageUrl !== null ? 'image' : 'texte');

        $this->pdo->beginTransaction();
        try {
            $lock = $this->pdo->prepare('SELECT id FROM utilisateurs WHERE id = ? FOR UPDATE');
            $lock->execute([$userId]);
            if (!$lock->fetchColumn()) {
                throw new RuntimeException('Le compte utilisateur est introuvable.');
            }

            $count = $this->pdo->prepare(
                'SELECT COUNT(*) FROM stories WHERE utilisateur_id = ? AND est_active = 1 AND date_expiration > NOW()'
            );
            $count->execute([$userId]);
            if ((int)$count->fetchColumn() >= 10) {
                throw new InvalidArgumentException('Vous avez déjà atteint la limite de 10 stories actives.');
            }

            $insert = $this->pdo->prepare(
                'INSERT INTO stories (utilisateur_id, type_contenu, texte, image_url, visibilite, date_expiration)
                 VALUES (?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR))'
            );
            $insert->execute([$userId, $type, $text, $imageUrl, $visibility]);
            $storyId = (int)$this->pdo->lastInsertId();
            $this->pdo->commit();

            return $storyId;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function markViewed(int $storyId, int $viewerId): bool
    {
        $story = $this->findVisibleStory($storyId, $viewerId);
        if (!$story) {
            return false;
        }

        $statement = $this->pdo->prepare(
            'INSERT IGNORE INTO story_vues (story_id, utilisateur_id) VALUES (?, ?)'
        );
        $statement->execute([$storyId, $viewerId]);
        return true;
    }

    public function getImagePath(int $storyId, int $viewerId): ?string
    {
        $story = $this->findVisibleStory($storyId, $viewerId);
        $imageUrl = $story['image_url'] ?? null;
        if (!is_string($imageUrl) || !preg_match('/\Astories\/[a-f0-9]{32}\.(?:jpg|png|webp)\z/', $imageUrl)) {
            return null;
        }

        $directory = realpath(ROOT_PATH . '/storage/stories');
        $path = realpath(ROOT_PATH . '/storage/' . $imageUrl);
        if ($directory === false || $path === false || !str_starts_with($path, $directory . DIRECTORY_SEPARATOR)) {
            return null;
        }

        return is_file($path) ? $path : null;
    }

    public function getViewers(int $storyId, int $ownerId): ?array
    {
        $story = $this->pdo->prepare(
            'SELECT id FROM stories
             WHERE id = ? AND utilisateur_id = ? AND est_active = 1 AND date_expiration > NOW()
             LIMIT 1'
        );
        $story->execute([$storyId, $ownerId]);
        if (!$story->fetchColumn()) {
            return null;
        }

        $statement = $this->pdo->prepare(
            'SELECT u.id, u.prenom, u.nom, u.avatar_url, sv.date_vue
             FROM story_vues sv
             JOIN utilisateurs u ON u.id = sv.utilisateur_id
             WHERE sv.story_id = ?
             ORDER BY sv.date_vue DESC'
        );
        $statement->execute([$storyId]);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete(int $storyId, int $ownerId): ?string
    {
        $this->pdo->beginTransaction();
        try {
            $statement = $this->pdo->prepare(
                'SELECT image_url FROM stories WHERE id = ? AND utilisateur_id = ? LIMIT 1 FOR UPDATE'
            );
            $statement->execute([$storyId, $ownerId]);
            $imageUrl = $statement->fetchColumn();
            if ($imageUrl === false) {
                $this->pdo->commit();
                return null;
            }

            $delete = $this->pdo->prepare('DELETE FROM stories WHERE id = ? AND utilisateur_id = ?');
            $delete->execute([$storyId, $ownerId]);
            $this->pdo->commit();
            return is_string($imageUrl) && $imageUrl !== '' ? $imageUrl : '';
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    private function findVisibleStory(int $storyId, int $viewerId): ?array
    {
        $statement = $this->pdo->prepare(
            "SELECT s.id, s.image_url
             FROM stories s
             WHERE s.id = ? AND s.est_active = 1 AND s.date_expiration > NOW()
               AND (
                    s.utilisateur_id = ?
                    OR s.visibilite = 'public'
                    OR (
                        s.visibilite = 'amis'
                        AND EXISTS (
                            SELECT 1 FROM amis a
                            WHERE a.statut = 'accepte'
                              AND (
                                  (a.utilisateur_id = s.utilisateur_id AND a.ami_id = ?)
                                  OR (a.ami_id = s.utilisateur_id AND a.utilisateur_id = ?)
                              )
                        )
                    )
               )
             LIMIT 1"
        );
        $statement->execute([$storyId, $viewerId, $viewerId, $viewerId]);
        return $statement->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
