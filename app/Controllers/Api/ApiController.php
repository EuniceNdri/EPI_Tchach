
21<?php

abstract class ApiController
{
    protected function input(): array
    {
        if ($_POST) {
            return $_POST;
        }

        $raw = file_get_contents('php://input');
        if ($raw === false || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    protected function requireUser(): int
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userId = (int)($_SESSION['user_id'] ?? 0);
        if ($userId <= 0) {
            $this->respond(['success' => false, 'message' => 'Non authentifié.'], 401);
        }

        return $userId;
    }

    protected function requireMethod(string $method): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== $method) {
            header('Allow: ' . $method);
            $this->respond(['success' => false, 'message' => 'Méthode non autorisée.'], 405);
        }
    }

    protected function respond(array $payload, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
