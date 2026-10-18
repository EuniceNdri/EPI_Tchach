<?php
/**
 * =============================================================
 * index.php – Point d'entrée unique & Routeur Universel (MVC)
 * =============================================================
 * Fonctionne parfaitement sous :
 * - Apache / XAMPP (avec ou sans mod_rewrite, dans sous-dossier /EPI_Tchach/)
 * - Serveur PHP intégré (php -S localhost:8000)
 * - Alias / VHost (http://localhost/ ou http://epi-tchach.local/)
 * =============================================================
 */

// 1. Démarrage de session sécurisé
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// 2. Définition de la racine absolue du projet
define('ROOT_PATH', __DIR__);

// 3. Chargement de la configuration base de données si existante
if (file_exists(ROOT_PATH . '/config/database.php')) {
    require_once ROOT_PATH . '/config/database.php';
}

if (!function_exists('getRequestedRoute')) {
    /**
     * Extraction robuste et universelle de la route demandée
     */
    function getRequestedRoute(): string {
        // 1. Paramètre explicite transmis par réécriture .htaccess (?url=...)
        if (!empty($_GET['url'])) {
            return trim($_GET['url'], '/');
        }

        // Paramètres alternatifs
        if (!empty($_GET['route'])) {
            return trim($_GET['route'], '/');
        }
        if (!empty($_GET['page'])) {
            return trim($_GET['page'], '/');
        }

        // 2. PATH_INFO si supporté par le serveur
        if (!empty($_SERVER['PATH_INFO'])) {
            return trim($_SERVER['PATH_INFO'], '/');
        }

        // 3. Détection automatique via REQUEST_URI (XAMPP, Apache, php -S)
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($requestUri, PHP_URL_PATH) ?? '/';

        // Nettoyage du préfixe de script (ex: /EPI_Tchach/index.php ou /EPI_Tchach/)
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $scriptDir = rtrim(dirname($scriptName), '/\\');

        if ($scriptName && strpos($path, $scriptName) === 0) {
            $path = substr($path, strlen($scriptName));
        } elseif ($scriptDir && $scriptDir !== '/' && $scriptDir !== '.' && strpos($path, $scriptDir) === 0) {
            $path = substr($path, strlen($scriptDir));
        }

        // Nettoyer si le chemin commence par /public/ ou public
        $cleanPath = trim($path, '/');
        if (strpos($cleanPath, 'public/') === 0) {
            $cleanPath = substr($cleanPath, 7);
        } elseif ($cleanPath === 'public') {
            $cleanPath = '';
        }

        // Retirer l'éventuelle extension .php
        if (substr($cleanPath, -4) === '.php') {
            $cleanPath = substr($cleanPath, 0, -4);
        }

        return trim($cleanPath, '/');
    }
}

// Récupération de la route demandée
$fullRoute = getRequestedRoute();
$segments = $fullRoute ? explode('/', $fullRoute) : [];
$route = $segments[0] ?? '';

// =============================================================
// CALCUL DE LA RACINE WEB ($baseUrl) ET DES ASSETS ($assetBase)
// =============================================================
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$scriptDir = rtrim(dirname($scriptName), '/\\');

// Détection de l'accès via /public ou à la racine
$isPublicEntry = (substr($scriptDir, -7) === '/public');

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

// Helpers globaux d'URL et d'Assets
if (!function_exists('url')) {
    function url(string $path = ''): string {
        global $baseUrl;
        $trimmed = ltrim($path, '/');
        if ($trimmed === '' || $trimmed === 'landing' || $trimmed === 'home') {
            return $baseUrl !== '' ? $baseUrl : '/';
        }
        return ($baseUrl !== '' ? $baseUrl : '') . '/' . $trimmed;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string {
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

// =============================================================
// GARDE-FOU ASSETS MANQUANTS (Évite de renvoyer du HTML sur un CSS/JS)
// =============================================================
$ext = pathinfo($fullRoute, PATHINFO_EXTENSION);
if (in_array(strtolower($ext), ['css', 'js', 'png', 'jpg', 'jpeg', 'svg', 'gif', 'webp', 'ico', 'woff', 'woff2', 'ttf', 'map'])) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "Fichier statique introuvable : " . htmlspecialchars($fullRoute);
    exit;
}

// =============================================================
// ROUTEUR UNIVERSEL DES VUES DESKTOP
// =============================================================
switch ($fullRoute) {

    // ---------------------------------------------------------
    // PAGE D'ACCUEIL (Landing Page)
    // ---------------------------------------------------------
    case '':
    case 'landing':
    case 'home':
        require_once ROOT_PATH . '/views/desktop/landing.php';
        break;

    // ---------------------------------------------------------
    // AUTHENTIFICATION (Traitée par AuthController)
    // ---------------------------------------------------------
    case 'login':
        require_once ROOT_PATH . '/controllers/AuthController.php';
        (new AuthController())->login();
        break;

    case 'register':
        require_once ROOT_PATH . '/controllers/AuthController.php';
        (new AuthController())->register();
        break;

    case 'forgot-password':
        require_once ROOT_PATH . '/controllers/AuthController.php';
        (new AuthController())->forgotPassword();
        break;

    case 'reset-password':
        require_once ROOT_PATH . '/controllers/AuthController.php';
        (new AuthController())->resetPassword();
        break;

    case 'logout':
        require_once ROOT_PATH . '/controllers/AuthController.php';
        (new AuthController())->logout();
        break;

    // ---------------------------------------------------------
    // ESPACE PROFIL (Géré par ProfileController)
    // ---------------------------------------------------------
    case 'profile':
    case 'profile/view':
        require_once ROOT_PATH . '/controllers/ProfileController.php';
        (new ProfileController())->index();
        break;

    case 'profile/update':
        require_once ROOT_PATH . '/controllers/ProfileController.php';
        (new ProfileController())->update();
        break;

    case 'profile/edit':
        // La modification s'effectue directement en popup modale sur la page profil
        require_once ROOT_PATH . '/controllers/ProfileController.php';
        (new ProfileController())->index();
        break;

    // ---------------------------------------------------------
    // ESPACE ADMINISTRATION
    // ---------------------------------------------------------
    case 'admin':
    case 'admin/dashboard':
        require_once ROOT_PATH . '/views/desktop/admin/dashboard.php';
        break;

    case 'admin/users':
        require_once ROOT_PATH . '/views/desktop/admin/users.php';
        break;

    case 'admin/moderation':
        require_once ROOT_PATH . '/views/desktop/admin/moderation.php';
        break;

    case 'admin/categories':
    case 'admin/filieres':
        require_once ROOT_PATH . '/views/desktop/admin/categories.php';
        break;

    case 'admin/settings':
        require_once ROOT_PATH . '/views/desktop/admin/settings.php';
        break;

    // ---------------------------------------------------------
    // ROUTE PAR DÉFAUT / SOUS-ROUTER
    // ---------------------------------------------------------
    default:
        if (strpos($fullRoute, 'admin') === 0) {
            require_once ROOT_PATH . '/views/desktop/admin/dashboard.php';
        } elseif (strpos($fullRoute, 'profile') === 0) {
            require_once ROOT_PATH . '/views/desktop/profile/index.php';
        } else {
            require_once ROOT_PATH . '/views/desktop/landing.php';
        }
        break;
}
