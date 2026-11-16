<?php

require_once ROOT_PATH . '/app/Models/Moderation/RoleAccess.php';

final class RoleService
{
    public function __construct(private ?PDO $pdo)
    {
    }

    public function canAccessCategory(int $userId, int $categoryId, string $role): bool
    {
        if (!$this->pdo || $userId <= 0 || $categoryId <= 0) {
            return false;
        }

        require_once ROOT_PATH . '/app/Models/Salon/Salon.php';
        return (new Salon($this->pdo))->canAccessCategory($userId, $categoryId, $role);
    }

    public function canPublishAnnouncement(int $userId, int $categoryId): bool
    {
        return $this->pdo instanceof PDO
            && RoleAccess::canPublishAnnouncement($this->pdo, $userId, $categoryId);
    }
}
