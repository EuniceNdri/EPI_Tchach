<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session logging for dev server (from ArMi branch)
if (PHP_SAPI === 'cli-server') {
    error_log(sprintf(
        '[SESSION] %s %s cookie=%s active=%s user_id=%s',
        $_SERVER['REQUEST_METHOD'] ?? 'GET',
        parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/',
        isset($_COOKIE[session_name()]) ? 'present' : 'absent',
        session_status() === PHP_SESSION_ACTIVE ? 'yes' : 'no',
        !empty($_SESSION['user_id']) ? 'set' : 'empty'
    ));
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
            'BinomageController' => '/app/Controllers/Social/BinomageController.php',
            'StoryController' => '/app/Controllers/Social/StoryController.php',
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

if (!function_exists('renderAvatar')) {
    /**
     * Génère l'HTML d'un avatar moderne avec photo ou initiale dégradée
     *
     * @param string $prenom Prénom
     * @param string $nom Nom
     * @param string|null $avatarUrl Chemin relatif ou URL de l'avatar
     * @param int $size Taille en pixels (par défaut 42)
     * @param bool $showDot Afficher l'indicateur en ligne
     * @param string $extraClass Classes CSS additionnelles
     * @return string HTML de l'avatar
     */
    function renderAvatar(string $prenom, string $nom = '', ?string $avatarUrl = null, int $size = 42, bool $showDot = false, string $extraClass = ''): string
    {
        $fullName = trim($prenom . ' ' . $nom);
        if ($fullName === '') {
            $fullName = 'Membre';
        }
        $initial = mb_strtoupper(mb_substr(trim($prenom !== '' ? $prenom : $fullName), 0, 1));
        if ($initial === '') {
            $initial = 'M';
        }

        // Gradients modernes basés sur l'initiale
        $char = $initial;
        if (in_array($char, ['A', 'B', 'C', 'D'], true)) {
            $bg = 'linear-gradient(135deg, #0d9488 0%, #059669 100%)'; // Teal / Emerald
        } elseif (in_array($char, ['E', 'F', 'G', 'H'], true)) {
            $bg = 'linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)'; // Royal Blue
        } elseif (in_array($char, ['I', 'J', 'K', 'L'], true)) {
            $bg = 'linear-gradient(135deg, #7c3aed 0%, #8b5cf6 100%)'; // Purple / Violet
        } elseif (in_array($char, ['M', 'N', 'O', 'P'], true)) {
            $bg = 'linear-gradient(135deg, #db2777 0%, #ec4899 100%)'; // Pink / Rose
        } elseif (in_array($char, ['Q', 'R', 'S', 'T'], true)) {
            $bg = 'linear-gradient(135deg, #d97706 0%, #f59e0b 100%)'; // Amber / Orange
        } else {
            $bg = 'linear-gradient(135deg, #0284c7 0%, #06b6d4 100%)'; // Cyan / Sky
        }

        $fontSize = round($size * 0.42);
        $escapedName = htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8');
        $classes = 'campus-avatar-wrap ' . trim($extraClass);

        $dotHtml = $showDot ? '<span class="campus-online-dot"></span>' : '';

        $cleanAvatar = $avatarUrl !== null ? trim($avatarUrl) : '';
        if ($cleanAvatar !== '') {
            $imgSrc = url('public/' . ltrim($cleanAvatar, '/'));
            $escapedSrc = htmlspecialchars($imgSrc, ENT_QUOTES, 'UTF-8');
            return "<div class=\"{$classes}\" style=\"width:{$size}px; height:{$size}px; font-size:{$fontSize}px;\" title=\"{$escapedName}\">"
                 . "<img class=\"campus-avatar-img\" src=\"{$escapedSrc}\" alt=\"{$escapedName}\" loading=\"lazy\" onerror=\"this.style.display='none'; this.nextElementSibling.style.display='flex';\" />"
                 . "<span class=\"campus-avatar-fallback\" style=\"display:none; background:{$bg};\">{$initial}</span>"
                 . $dotHtml
                 . "</div>";
        }

        return "<div class=\"{$classes}\" style=\"width:{$size}px; height:{$size}px; font-size:{$fontSize}px;\" title=\"{$escapedName}\">"
             . "<span class=\"campus-avatar-fallback\" style=\"background:{$bg};\">{$initial}</span>"
             . $dotHtml
             . "</div>";
    }
}
