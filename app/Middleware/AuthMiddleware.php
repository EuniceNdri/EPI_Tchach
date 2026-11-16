<?php

final class AuthMiddleware
{
    public static function requireUser(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user_id'])) {
            $acceptsJson = strpos((string)($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json') !== false
                || strpos((string)($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json') !== false;
            if ($acceptsJson) {
                http_response_code(401);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => 'Non authentifié.']);
                exit;
            }
            header('Location: ' . url('login'));
            exit;
        }
    }
}
