<?php

final class GuestMiddleware
{
    public static function handle(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!empty($_SESSION['user_id'])) {
            $role = strtolower(trim((string)($_SESSION['user_role'] ?? '')));
            $destination = ($role === 'administrateur' || $role === 'admin') ? url('admin') : url('salon');
            header('Location: ' . $destination);
            exit;
        }
    }
}
