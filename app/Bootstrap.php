<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

if (!function_exists('controllerFile')) {
    function controllerFile(string $controller): string
    {
        static $files = [
            'MentionApiController' => '/app/Controllers/Api/MentionApiController.php',
            'TagApiController' => '/app/Controllers/Api/TagApiController.php',
            'NotificationApiController' => '/app/Controllers/Api/NotificationApiController.php',
            'ReactionApiController' => '/app/Controllers/Api/ReactionApiController.php',
            'AdminController' => '/app/Controllers/Admin/AdminController.php',
            'AttachmentController' => '/app/Controllers/Attachments/AttachmentController.php',
            'AuthController' => '/app/Controllers/Auth/AuthController.php',
            'DashboardController' => '/app/Controllers/Dashboard/DashboardController.php',
            'ModerationController' => '/app/Controllers/Moderation/ModerationController.php',
            'NotificationController' => '/app/Controllers/Notifications/NotificationController.php',
            'ProfileController' => '/app/Controllers/Profile/ProfileController.php',
            'PublicationController' => '/app/Controllers/Publications/PublicationController.php',
            'ReactionController' => '/app/Controllers/Publications/ReactionController.php',
            'SalonController' => '/app/Controllers/Salon/SalonController.php',
            'SearchController' => '/app/Controllers/Search/SearchController.php',
            'SignalementController' => '/app/Controllers/Moderation/SignalementController.php',
            'SocialController' => '/app/Controllers/Social/SocialController.php',
        ];

        if (!isset($files[$controller])) {
            throw new InvalidArgumentException(sprintf('Contrôleur inconnu : %s.', $controller));
        }

        return ROOT_PATH . $files[$controller];
    }
}

require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/Models/Platform/LandingContent.php';
require_once ROOT_PATH . '/app/Routing/Router.php';

$appConfiguration = require ROOT_PATH . '/config/app.php';
date_default_timezone_set($appConfiguration['timezone'] ?? 'UTC');

if (!function_exists('getRequestedRoute')) {
    function getRequestedRoute(): string
    {
        foreach (['url', 'route', 'page'] as $parameter) {
            if (!empty($_GET[$parameter])) {
                return trim((string) $_GET[$parameter], '/');
            }
        }

        if (!empty($_SERVER['PATH_INFO'])) {
            return trim((string) $_SERVER['PATH_INFO'], '/');
        }

        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($requestUri, PHP_URL_PATH) ?? '/';
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $scriptDir = rtrim(dirname($scriptName), '/\\');

        if ($scriptName && strpos($path, $scriptName) === 0) {
            $path = substr($path, strlen($scriptName));
        } elseif ($scriptDir && $scriptDir !== '/' && $scriptDir !== '.' && strpos($path, $scriptDir) === 0) {
            $path = substr($path, strlen($scriptDir));
        }

        $cleanPath = trim($path, '/');
        if (strpos($cleanPath, 'public/') === 0) {
            $cleanPath = substr($cleanPath, 7);
        } elseif ($cleanPath === 'public') {
            $cleanPath = '';
        }

        if (substr($cleanPath, -4) === '.php') {
            $cleanPath = substr($cleanPath, 0, -4);
        }

        return trim($cleanPath, '/');
    }
}

$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$scriptDir = rtrim(dirname($scriptName), '/\\');
$isPublicEntry = substr($scriptDir, -7) === '/public';

if ($isPublicEntry) {
    $projectBase = substr($scriptDir, 0, -7);
    $baseUrl = $scriptDir;
    $assetBase = $projectBase . '/public/assets';
} else {
    $projectBase = ($scriptDir === '' || $scriptDir === '.' || $scriptDir === '/') ? '' : $scriptDir;
    $baseUrl = $projectBase;
    $assetBase = ($projectBase !== '' ? $projectBase : '') . '/public/assets';
}

$base = $baseUrl;
$assets = $assetBase;

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        global $baseUrl;
        $trimmed = ltrim($path, '/');
        if ($trimmed === '' || $trimmed === 'landing' || $trimmed === 'home') {
            return $baseUrl !== '' ? $baseUrl : '/';
        }

        return ($baseUrl !== '' ? $baseUrl : '') . '/' . $trimmed;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        global $assetBase;
        $clean = ltrim($path, '/');
        if (strpos($clean, 'public/assets/') === 0) {
            $clean = substr($clean, 14);
        } elseif (strpos($clean, 'assets/') === 0) {
            $clean = substr($clean, 7);
        }

        return $assetBase . '/' . $clean;
    }
}

if (!function_exists('loadActiveFilieres')) {
    function loadActiveFilieres(?PDO $pdo): array
    {
        if (!$pdo) {
            error_log('Chargement des filières impossible : ' . ($GLOBALS['dbError'] ?? 'Connexion indisponible.'));
            return [[], true];
        }

        try {
            $statement = $pdo->query(
                'SELECT id, nom, code, description, image_url, slogan
                 FROM filieres
                 WHERE est_active = 1
                 ORDER BY nom ASC'
            );
            return [$statement->fetchAll(), false];
        } catch (PDOException $exception) {
            error_log('Chargement des champs visuels des filières impossible : ' . $exception->getMessage());
            try {
                $statement = $pdo->query(
                    'SELECT id, nom, code, description, NULL AS image_url, NULL AS slogan
                     FROM filieres
                     WHERE est_active = 1
                     ORDER BY nom ASC'
                );
                return [$statement->fetchAll(), false];
            } catch (PDOException $fallbackException) {
                error_log('Chargement des filières impossible : ' . $fallbackException->getMessage());
                return [[], true];
            }
        }
    }
}
