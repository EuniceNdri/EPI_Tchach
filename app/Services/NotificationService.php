<?php

require_once ROOT_PATH . '/app/Models/Notifications/Notification.php';

final class NotificationService
{
    private Notification $notifications;

    public function __construct(?PDO $pdo = null)
    {
        $this->notifications = new Notification($pdo);
    }

    public function unreadCount(int $userId): int
    {
        return $userId > 0 ? $this->notifications->countUnread($userId) : 0;
    }

    public function markAsRead(int $notificationId, int $userId): bool
    {
        return $notificationId > 0 && $userId > 0
            && $this->notifications->markAsRead($notificationId, $userId);
    }

    public function markAllAsRead(int $userId): bool
    {
        return $userId > 0 && $this->notifications->markAllAsRead($userId);
    }

    public function delete(int $notificationId, int $userId): bool
    {
        return $notificationId > 0 && $userId > 0
            && $this->notifications->delete($notificationId, $userId);
    }

    public function notifyMention(
        int $mentionedUserId,
        int $authorId,
        string $publicationTitle,
        string $link
    ): bool {
        return $this->notifications->notifyMention(
            $mentionedUserId,
            $authorId,
            $publicationTitle,
            $link
        );
    }

    public function notifyPublicationMembers(
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
