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
require_once ROOT_PATH . '/models/LandingContent.php';

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

if (!function_exists('loadActiveFilieres')) {
    function loadActiveFilieres(?PDO $pdo): array {
        if (!$pdo) {
            $error = $GLOBALS['dbError'] ?? 'Connexion à la base de données indisponible.';
            error_log('Chargement des filières impossible : ' . $error);
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

if (!empty($_SESSION['user_id']) && $pdo instanceof PDO) {
    $activityStatement = $pdo->prepare('UPDATE utilisateurs SET derniere_activite = NOW() WHERE id = :user_id');
    $activityStatement->execute(['user_id' => (int)$_SESSION['user_id']]);
}

// =============================================================
// ROUTEUR UNIVERSEL DES VUES DESKTOP
// =============================================================
if (preg_match('#^attachment/view/([1-9][0-9]*)$#', $fullRoute, $attachmentViewMatch)) {
    require_once ROOT_PATH . '/controllers/AttachmentController.php';
    (new AttachmentController())->view((int)$attachmentViewMatch[1]);
}
if (preg_match('#^attachment/download/([1-9][0-9]*)$#', $fullRoute, $attachmentMatch)) {
    require_once ROOT_PATH . '/controllers/AttachmentController.php';
    (new AttachmentController())->download((int)$attachmentMatch[1]);
}

switch ($fullRoute) {

    // ---------------------------------------------------------
    // PAGE D'ACCUEIL (Landing Page)
    // ---------------------------------------------------------
    case '':
    case 'landing':
    case 'home':
        [$filieres, $filieresLoadError] = loadActiveFilieres($pdo);
        $landingSettings = (new LandingContent($pdo))->getSettings();
        header('Vary: User-Agent');
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (preg_match('/(?:iphone|ipod|windows phone|blackberry|opera mini|mobile|android.*mobile)/i', $userAgent)) {
            require_once ROOT_PATH . '/views/smartphone/landing.php';
        } else {
            require_once ROOT_PATH . '/views/desktop/landing.php';
        }
        break;

    case 'salon':
    case 'salons':
    case 'Salon':
        require_once ROOT_PATH . '/controllers/SalonController.php';
        (new SalonController())->index();
        break;

    case 'salon/save':
        require_once ROOT_PATH . '/controllers/SalonController.php';
        (new SalonController())->toggleSaved();
        break;

    case 'salon/presence':
        require_once ROOT_PATH . '/controllers/SalonController.php';
        (new SalonController())->presence();
        break;

    case 'salons/create-zone':
        require_once ROOT_PATH . '/controllers/SalonController.php';
        (new SalonController())->createZone();
        break;

    case 'salons/create-category':
        require_once ROOT_PATH . '/controllers/SalonController.php';
        (new SalonController())->createCategory();
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
    // PUBLICATIONS & COMMENTAIRES (Géré par PublicationController)
    // ---------------------------------------------------------
    case 'publication/create':
        require_once ROOT_PATH . '/controllers/PublicationController.php';
        (new PublicationController())->create();
        break;

    case 'publication/discussion':
        require_once ROOT_PATH . '/controllers/PublicationController.php';
        (new PublicationController())->discussion();
        break;

    case 'publication/vote':
        require_once ROOT_PATH . '/controllers/PublicationController.php';
        (new PublicationController())->vote();
        break;

    case 'comment/create':
        require_once ROOT_PATH . '/controllers/PublicationController.php';
        (new PublicationController())->createComment();
        break;

    case 'search':
        require_once ROOT_PATH . '/controllers/SearchController.php';
        (new SearchController())->search();
        break;

    // ---------------------------------------------------------
    // SIGNALEMENTS & MODERATION
    // ---------------------------------------------------------
    case 'signalement':
    case 'signalement/create':
        require_once ROOT_PATH . '/controllers/SignalementController.php';
        (new SignalementController())->create();
        break;

    case 'moderation':
        require_once ROOT_PATH . '/controllers/ModerationController.php';
        (new ModerationController())->index();
        break;

    case 'moderation/action':
    case 'moderation/decision':
        require_once ROOT_PATH . '/controllers/ModerationController.php';
        (new ModerationController())->decision();
        break;

    // ---------------------------------------------------------
    // REACTIONS (Like, J'adore, Utile)
    // ---------------------------------------------------------
    case 'reaction':
    case 'reaction/toggle':
        require_once ROOT_PATH . '/controllers/ReactionController.php';
        (new ReactionController())->toggle();
        break;

    // ---------------------------------------------------------
    // NOTIFICATIONS
    // ---------------------------------------------------------
    case 'notifications':
        require_once ROOT_PATH . '/controllers/NotificationController.php';
        (new NotificationController())->index();
        break;

    case 'notifications/unread-count':
        require_once ROOT_PATH . '/controllers/NotificationController.php';
        (new NotificationController())->unreadCount();
        break;

    case 'notifications/mark-read':
        require_once ROOT_PATH . '/controllers/NotificationController.php';
        (new NotificationController())->markAsRead();
        break;

    case 'notifications/mark-all-read':
        require_once ROOT_PATH . '/controllers/NotificationController.php';
        (new NotificationController())->markAllRead();
        break;

    case 'notifications/delete':
        require_once ROOT_PATH . '/controllers/NotificationController.php';
        (new NotificationController())->delete();
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
    // ESPACE ADMINISTRATION (Géré par AdminController)
    // ---------------------------------------------------------
    case 'dashboard':
    case 'dashboard/index':
        require_once ROOT_PATH . '/controllers/DashboardController.php';
        (new DashboardController())->index();
        break;

    case 'dashboard/admin':
        require_once ROOT_PATH . '/controllers/DashboardController.php';
        (new DashboardController())->adminDashboard();
        break;

    case 'dashboard/delegue':
        require_once ROOT_PATH . '/controllers/DashboardController.php';
        (new DashboardController())->delegueDashboard();
        break;

    case 'dashboard/chef-classe':
    case 'dashboard/chef_classe':
        require_once ROOT_PATH . '/controllers/DashboardController.php';
        (new DashboardController())->chefClasseDashboard();
        break;

    case 'admin':
    case 'admin/dashboard':
        require_once ROOT_PATH . '/controllers/AdminController.php';
        (new AdminController())->dashboard();
        break;

    case 'admin/users':
        require_once ROOT_PATH . '/controllers/AdminController.php';
        (new AdminController())->users();
        break;

    case 'admin/users/json':
        require_once ROOT_PATH . '/controllers/AdminController.php';
        (new AdminController())->getUserJson();
        break;

    case 'admin/users/edit':
        require_once ROOT_PATH . '/controllers/AdminController.php';
        (new AdminController())->editUser();
        break;

    case 'admin/users/delete':
        require_once ROOT_PATH . '/controllers/AdminController.php';
        (new AdminController())->deleteUser();
        break;

    case 'admin/moderation':
        require_once ROOT_PATH . '/controllers/ModerationController.php';
        (new ModerationController())->index();
        break;

    case 'admin/moderation/action':
        require_once ROOT_PATH . '/controllers/ModerationController.php';
        (new ModerationController())->decision();
        break;

    case 'admin/categories':
    case 'admin/filieres':
        require_once ROOT_PATH . '/controllers/AdminController.php';
        (new AdminController())->categories();
        break;

    case 'admin/categories/create-zone':
        require_once ROOT_PATH . '/controllers/AdminController.php';
        (new AdminController())->createZone();
        break;

    case 'admin/categories/create-category':
        require_once ROOT_PATH . '/controllers/AdminController.php';
        (new AdminController())->createCategory();
        break;

    case 'admin/settings':
        require_once ROOT_PATH . '/controllers/AdminController.php';
        (new AdminController())->settings();
        break;

    // ---------------------------------------------------------
    // ROUTE PAR DÉFAUT / SOUS-ROUTER
    // ---------------------------------------------------------
    default:
        http_response_code(404);
        require_once ROOT_PATH . '/views/errors/404.php';
        break;
}
