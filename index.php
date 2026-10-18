<?php
/**
 * =============================================================
 * index.php – Point d'entrée unique (Front Controller à la racine)
 * =============================================================
 * Routes : Landing page + Pages d'Authentification (Desktop)
 * =============================================================
 */

session_start();

define('ROOT_PATH', __DIR__);

// Chargement de la base de données (si configurée)
if (file_exists(ROOT_PATH . '/config/database.php')) {
    require_once ROOT_PATH . '/config/database.php';
}

// Récupération de l'URL demandée
$url = isset($_GET['url']) ? trim($_GET['url'], '/') : '';

// Séparation des segments (ex: "reset-password/token123")
$segments = $url ? explode('/', $url) : [];
$route = $segments[0] ?? '';

// Routage des vues
switch ($route) {

    // ---------------------------------------------------------
    // PAGE D'ACCUEIL (Landing)
    // ---------------------------------------------------------
    case '':
    case 'landing':
    case 'home':
        $landingView = ROOT_PATH . '/views/desktop/landing.php';
        if (file_exists($landingView)) {
            require_once $landingView;
        } else {
            echo "<h1>EPI Tchach</h1><p>Vue landing introuvable.</p>";
        }
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
        header('Location: login');
        exit;

    // ---------------------------------------------------------
    // AUTRES URLS -> Redirection vers la landing
    // ---------------------------------------------------------
    default:
        header('Location: landing');
        exit;
}
