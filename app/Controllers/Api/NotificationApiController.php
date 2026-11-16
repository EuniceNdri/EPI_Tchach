<?php

require_once ROOT_PATH . '/app/Controllers/Api/ApiController.php';
require_once ROOT_PATH . '/app/Services/NotificationService.php';

final class NotificationApiController extends ApiController
{
    public function unreadCount(): never
    {
        global $pdo;
        $userId = $this->requireUser();
        $service = new NotificationService($pdo instanceof PDO ? $pdo : null);
        $this->respond(['success' => true, 'count' => $service->unreadCount($userId)]);
    }
}
