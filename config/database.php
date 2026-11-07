<?php
/**
 * =============================================================
 * config/database.php – Connexion à la base de données (PDO)
 * =============================================================
 */

if (!function_exists('mb_strlen')) {
    function mb_strlen(string $value): int {
        return strlen($value);
    }
}

if (!function_exists('mb_substr')) {
    function mb_substr(string $value, int $start, ?int $length = null): string {
        if ($length === null) {
            return substr($value, $start);
        }
        return substr($value, $start, $length);
    }
}

if (!function_exists('mb_stripos')) {
    function mb_stripos(string $haystack, string $needle, int $offset = 0): int|false {
        return stripos($haystack, $needle, $offset);
    }
}

if (!function_exists('mb_strimwidth')) {
    function mb_strimwidth(string $string, int $start, int $width, string $trimmarker = '...'): string {
        if ($width <= 0) {
            return '';
        }

        if (strlen($string) <= $width) {
            return $string;
        }

        $trimLength = max(0, $width - strlen($trimmarker));
        return substr($string, $start, $trimLength) . $trimmarker;
    }
}

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
