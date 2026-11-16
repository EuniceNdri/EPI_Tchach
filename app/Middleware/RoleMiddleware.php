<?php

require_once ROOT_PATH . '/app/Middleware/AuthMiddleware.php';
require_once ROOT_PATH . '/app/Models/Moderation/RoleAccess.php';

final class RoleMiddleware
{
    public static function handle(string $requiredRole): void
    {
        AuthMiddleware::requireUser();
        global $pdo;

        if (!$pdo instanceof PDO) {
            http_response_code(503);
            exit('Base de données indisponible.');
        }

        $userId = (int)$_SESSION['user_id'];
        $allowed = match ($requiredRole) {
            'administrateur', 'admin' => RoleAccess::isAdministrator($pdo, $userId),
            'delegue_general' => RoleAccess::isGlobalDelegate($pdo, $userId),
            'delegue_de_classe' => RoleAccess::isClassDelegate($pdo, $userId),
            default => false,
        };

        if (!$allowed) {
            http_response_code(403);
            require ROOT_PATH . '/resources/views/errors/404.php';
            exit;
        }
    }
}
