<?php

require_once ROOT_PATH . '/app/Middleware/AuthMiddleware.php';
require_once ROOT_PATH . '/app/Models/Moderation/RoleAccess.php';

final class AdminMiddleware
{
    public static function handle(): void
    {
        AuthMiddleware::requireUser();
        global $pdo;

        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$pdo instanceof PDO || !RoleAccess::isAdministrator($pdo, $userId)) {
            http_response_code(403);
            require ROOT_PATH . '/resources/views/errors/404.php';
            exit;
        }
    }
}
