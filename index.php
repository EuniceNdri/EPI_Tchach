<?php
/**
 * =============================================================
 * index.php – Point d'entrée unique (Front Controller à la racine)
 * =============================================================
 * Résolution universelle des routes (Apache, php -S, sous-dossiers)
 * =============================================================
 */

session_start();

define('ROOT_PATH', __DIR__);

// Chargement de la base de données (si configurée)
if (file_exists(ROOT_PATH . '/config/database.php')) {
    require_once ROOT_PATH . '/config/database.php';
}

if (!function_exists('getRequestedRoute')) {
    /**
     * Extraction universelle et robuste de la route demandée
     */
    function getRequestedRoute(): string {
    // 1. Paramètre explicite transmis par réécriture .htaccess (?url=...)
    if (!empty($_GET['url'])) {
        return trim($_GET['url'], '/');
    }

    // 2. PATH_INFO si supporté par le serveur
    if (!empty($_SERVER['PATH_INFO'])) {
        return trim($_SERVER['PATH_INFO'], '/');
    }

    // 3. Détection automatique via REQUEST_URI (php -S, Apache sans rewrite ?url=, etc.)
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($requestUri, PHP_URL_PATH) ?? '/';

    // Nettoyage du préfixe de script ou sous-dossier (ex: /EPI_Tchach/ ou /EPI_Tchach/index.php)
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $scriptDir = rtrim(dirname($scriptName), '/\\');

    if ($scriptName && strpos($path, $scriptName) === 0) {
        $path = substr($path, strlen($scriptName));
    } elseif ($scriptDir && $scriptDir !== '/' && strpos($path, $scriptDir) === 0) {
        $path = substr($path, strlen($scriptDir));
    }

    return trim($path, '/');
    }
}

// Récupération de la route demandée
$fullRoute = getRequestedRoute();
$segments = $fullRoute ? explode('/', $fullRoute) : [];
$route = $segments[0] ?? '';

// Calcul automatique de la racine Web pour les liens et assets
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
$baseUrl = ($scriptDir === '' || $scriptDir === '.') ? '' : $scriptDir;

// Routage des vues
switch ($route) {

    // ---------------------------------------------------------
    // PAGE D'ACCUEIL (Landing)
    // ---------------------------------------------------------
    case '':
    case 'landing':
    case 'home':
        require_once ROOT_PATH . '/views/desktop/landing.php';
        break;

    // ---------------------------------------------------------
    // AUTHENTIFICATION
    // ---------------------------------------------------------
    case 'login':
        require_once ROOT_PATH . '/views/auth/login.php';
        break;

    case 'register':
        require_once ROOT_PATH . '/views/auth/register.php';
        break;

    case 'forgot-password':
        require_once ROOT_PATH . '/views/auth/forgot-password.php';
        break;

    case 'reset-password':
        require_once ROOT_PATH . '/views/auth/reset-password.php';
        break;

    case 'logout':
        $_SESSION = [];
        if (session_id() !== '' || headers_sent() === false) {
            session_destroy();
        }
        header('Location: ' . ($baseUrl ? $baseUrl . '/login' : 'login'));
        exit;

    // ---------------------------------------------------------
    // URL INCONNUE -> Affiche la landing par défaut
    // ---------------------------------------------------------
    default:
        require_once ROOT_PATH . '/views/desktop/landing.php';
        break;
}
