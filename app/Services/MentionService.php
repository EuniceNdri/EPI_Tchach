<?php

require_once ROOT_PATH . '/app/Models/Publications/PublicationMetadata.php';
require_once ROOT_PATH . '/app/Services/NotificationService.php';

final class MentionService
{
    private ?PDO $pdo;
    private PublicationMetadata $metadata;
    private NotificationService $notifications;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
        $this->metadata = new PublicationMetadata($pdo);
        $this->notifications = new NotificationService($pdo);
    }

    public function search(int $requesterId, int $categoryId, string $query = '', int $limit = 12): array
    {
        return $this->metadata->findMentionableUsers(
            $requesterId,
            $categoryId,
            trim($query),
            $limit
        );
    }

    /**
     * @param int[] $userIds
     * @return int[]
     */
    public function attachToPublication(
        int $publicationId,
        int $authorId,
        int $categoryId,
        array $userIds,
        string $title,
        string $link,
        bool $sendNotifications = true
    ): array {
        if (!$this->pdo || $publicationId <= 0 || $authorId <= 0 || $categoryId <= 0) {
            throw new InvalidArgumentException('Les informations de mention sont invalides.');
        }

        $userIds = array_values(array_unique(array_filter(
            array_map('intval', $userIds),
            static fn (int $id): bool => $id > 0 && $id !== $authorId
        )));
        if (count($userIds) > 10) {
            throw new InvalidArgumentException('Vous pouvez mentionner au maximum 10 personnes.');
        }
        if (!$userIds) {
            return [];
        }

        $mentionable = $this->metadata->findMentionableUsers(
            $authorId,
            $categoryId,
            '',
            50,
            $userIds
        );
        $validIds = array_map('intval', array_column($mentionable, 'id'));
        if (count($validIds) !== count($userIds)) {
            throw new InvalidArgumentException('Une personne sélectionnée ne peut pas être mentionnée dans ce salon.');
        }

        $statement = $this->pdo->prepare(
            'INSERT INTO publications_mentions (publication_id, utilisateur_id, mentionne_par)
             VALUES (:publication_id, :user_id, :author_id)'
        );
        foreach ($userIds as $userId) {
            $statement->execute([
                'publication_id' => $publicationId,
                'user_id' => $userId,
                'author_id' => $authorId,
            ]);
            if ($sendNotifications && !$this->notifications->notifyMention($userId, $authorId, $title, $link)) {
                throw new RuntimeException('La notification de mention n’a pas pu être enregistrée.');
            }
        }

        return $userIds;
    }
}
