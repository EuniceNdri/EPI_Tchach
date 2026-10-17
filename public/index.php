<?php
/**
 * =============================================================
 * public/index.php – Point d'entrée unique (Front Controller)
 * =============================================================
 *
 * Toutes les requêtes HTTP passent par ce fichier grâce au .htaccess.
 * Il récupère l'URL demandée et appelle le bon Controller.
 */

// ========================
// 1. DÉMARRAGE DE SESSION
// ========================
session_start();

// ========================
// 2. CONFIGURATION
// ========================

// Chemin racine du projet (un niveau au-dessus de public/)
define('ROOT_PATH', dirname(__DIR__));

// Charger la configuration de la base de données
require_once ROOT_PATH . '/config/database.php';

// ========================
// 3. RÉCUPÉRATION DE L'URL
// ========================

// Récupère l'URL demandée, ex: "login", "register", "publications/3"
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';

// Séparer l'URL en segments : ["login"] ou ["publications", "3"]
$segments = $url ? explode('/', $url) : [];

// Le premier segment = la page demandée
$page = $segments[0] ?? '';

// Le deuxième segment = un paramètre (ID, etc.)
$param = $segments[1] ?? null;

// ========================
// 4. ROUTEUR
// ========================

switch ($page) {

    // --------------------
    // AUTHENTIFICATION
    // --------------------

    case 'login':
        require_once ROOT_PATH . '/controllers/AuthController.php';
        $controller = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->loginPost();
        } else {
            $controller->loginPage();
        }
        break;

    case 'register':
        require_once ROOT_PATH . '/controllers/AuthController.php';
        $controller = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->registerPost();
        } else {
            $controller->registerPage();
        }
        break;

    case 'logout':
        require_once ROOT_PATH . '/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->logout();
        break;

    case 'forgot-password':
        require_once ROOT_PATH . '/controllers/AuthController.php';
        $controller = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->forgotPasswordPost();
        } else {
            $controller->forgotPasswordPage();
        }
        break;

    case 'reset-password':
        require_once ROOT_PATH . '/controllers/AuthController.php';
        $controller = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->resetPasswordPost();
        } else {
            $controller->resetPasswordPage();
        }
        break;

    // --------------------
    // PAGE D'ACCUEIL
    // --------------------

    case '':
    case 'home':
        // TODO: Créer HomeController ou charger une vue d'accueil
        // Pour l'instant, rediriger vers login si pas connecté
        if (!isset($_SESSION['user_id'])) {
            header('Location: login');
            exit;
        }
        // TODO: Charger la page d'accueil pour les utilisateurs connectés
        // require_once ROOT_PATH . '/controllers/HomeController.php';
        // $controller = new HomeController();
        // $controller->index();
        echo "Bienvenue sur EPI Tchach ! (Page d'accueil à créer)";
        break;

    // --------------------
    // PUBLICATIONS (à venir)
    // --------------------

    // case 'publications':
    //     require_once ROOT_PATH . '/controllers/PublicationController.php';
    //     $controller = new PublicationController();
    //     $controller->index();
    //     break;

    // --------------------
    // ZONES (à venir)
    // --------------------

    // case 'zones':
    //     require_once ROOT_PATH . '/controllers/ZoneController.php';
    //     $controller = new ZoneController();
    //     $controller->index();
    //     break;

    // --------------------
    // PAGE 404
    // --------------------

    default:
        http_response_code(404);
        echo "<h1>404 – Page non trouvée</h1>";
        echo "<p>La page <strong>" . htmlspecialchars($page) . "</strong> n'existe pas.</p>";
        echo "<a href='login'>← Retour à la connexion</a>";
        break;
}

// Le deuxième segment = un paramètre (ID, etc.)
$param = $segments[1] ?? null;

// ========================
// 4. ROUTEUR
// ========================

switch ($page) {

    // --------------------
    // AUTHENTIFICATION
    // --------------------

    case 'login':
        require_once ROOT_PATH . '/controllers/AuthController.php';
        $controller = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->loginPost();
        } else {
            $controller->loginPage();
        }
        break;

    case 'register':
        require_once ROOT_PATH . '/controllers/AuthController.php';
        $controller = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->registerPost();
        } else {
            $controller->registerPage();
        }
        break;

    case 'logout':
        require_once ROOT_PATH . '/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->logout();
        break;

    case 'forgot-password':
        require_once ROOT_PATH . '/controllers/AuthController.php';
        $controller = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->forgotPasswordPost();
        } else {
            $controller->forgotPasswordPage();
        }
        break;

    case 'reset-password':
        require_once ROOT_PATH . '/controllers/AuthController.php';
        $controller = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->resetPasswordPost();
        } else {
            $controller->resetPasswordPage();
        }
        break;

    // --------------------
    // PAGE D'ACCUEIL
    // --------------------

    case '':
    case 'home':
        // TODO: Créer HomeController ou charger une vue d'accueil
        // Pour l'instant, rediriger vers login si pas connecté
        if (!isset($_SESSION['user_id'])) {
            header('Location: login');
            exit;
        }
        // TODO: Charger la page d'accueil pour les utilisateurs connectés
        // require_once ROOT_PATH . '/controllers/HomeController.php';
        // $controller = new HomeController();
        // $controller->index();
        echo "Bienvenue sur EPI Tchach ! (Page d'accueil à créer)";
        break;

    // --------------------
    // PUBLICATIONS (à venir)
    // --------------------

    // case 'publications':
    //     require_once ROOT_PATH . '/controllers/PublicationController.php';
    //     $controller = new PublicationController();
    //     $controller->index();
    //     break;

    // --------------------
    // ZONES (à venir)
    // --------------------

    // case 'zones':
    //     require_once ROOT_PATH . '/controllers/ZoneController.php';
    //     $controller = new ZoneController();
    //     $controller->index();
    //     break;

    // --------------------
    // PAGE 404
    // --------------------

    default:
        http_response_code(404);
        echo "<h1>404 – Page non trouvée</h1>";
        echo "<p>La page <strong>" . htmlspecialchars($page) . "</strong> n'existe pas.</p>";
        echo "<a href='login'>← Retour à la connexion</a>";
        break;
}

