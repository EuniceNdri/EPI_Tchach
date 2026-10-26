<?php
/**
 * =============================================================
 * config/database.php – Connexion à la base de données (PDO)
 * =============================================================
 */

// Charger les variables d'environnement depuis .env si présent
$envFile = dirname(__DIR__) . '/.env';
$env = [];

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            [$k, $v] = explode('=', $line, 2);
            $env[trim($k)] = trim($v, " \t\n\r\0\x0B\"'");
        }
    }
}

$host = $env['DB_HOST'] ?? 'localhost';
$dbname = $env['DB_NAME'] ?? 'forum_ecole';
$username = $env['DB_USER'] ?? 'admin';
$password = $env['DB_PASS'] ?? 'admin';

$pdo = null;

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    // Si la base n'est pas encore accessible, on conserve l'erreur sans bloquer tout le script
    $dbError = $e->getMessage();
}
