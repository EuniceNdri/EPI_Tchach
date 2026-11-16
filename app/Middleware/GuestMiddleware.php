<?php

final class GuestMiddleware
{
    public static function handle(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!empty($_SESSION['user_id'])) {
            header('Location: ' . url('dashboard'));
            exit;
        }
    }
}
