<?php

require_once ROOT_PATH . '/app/Models/Publications/PublicationMetadata.php';
require_once ROOT_PATH . '/app/Models/Notifications/Notification.php';
require_once ROOT_PATH . '/app/Services/MentionService.php';

final class PublicationService
{
    private ?PDO $pdo;
    private PublicationMetadata $metadata;
    private Notification $notifications;
    private MentionService $mentions;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
        $this->metadata = new PublicationMetadata($pdo);
        $this->notifications = new Notification($pdo);
        $this->mentions = new MentionService($pdo);
    }

    /**
     * Attache les métadonnées d'une publication après son insertion.
     *
     * @param int[] $tagIds
     * @param string[] $proposedTags
     * @param int[] $mentionedUserIds
     */
    public function attachMetadata(
        int $publicationId,
        int $authorId,
        int $categoryId,
        array $tagIds,
        array $proposedTags,
        array $mentionedUserIds,
        string $title,
        bool $sendNotifications = true
    ): array {
        if (!$this->pdo || $publicationId <= 0 || $authorId <= 0) {
            throw new InvalidArgumentException('La publication est invalide.');
        }

        $savedTagIds = $this->metadata->attachTagsToPublication(
            $publicationId,
            $authorId,
            $tagIds,
            $proposedTags
        );
        $savedMentionIds = $this->mentions->attachToPublication(
            $publicationId,
            $authorId,
            $categoryId,
            $mentionedUserIds,
            $title,
            url('salon?pub=' . $publicationId . '#publication-' . $publicationId),
            $sendNotifications
        );

        return [
            'tagIds' => $savedTagIds,
            'mentionedUserIds' => $savedMentionIds,
        ];
    }

    public function notifyMembers(
        int $authorId,
        int $categoryId,
        string $title,
        int $publicationId
    ): int {
        return $this->notifications->notifyPublicationMembers(
            $authorId,
            $categoryId,
            $title,
            $publicationId
        );
    }
}
