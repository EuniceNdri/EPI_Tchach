<?php
/**
 * =============================================================
 * public/index.php – Point d'entrée unique (Front Controller)
 * =============================================================
 *
 * Toutes les requêtes HTTP passent par ce fichier grâce au .htaccess.
 * Il récupère l'URL demandée et appelle le bon Controller.
 */

// 1. DÉMARRAGE DE SESSION
session_start();

// 2. CONFIGURATION & CONSTANTES
define('ROOT_PATH', dirname(__DIR__));

// Charger la configuration de la base de données
if (file_exists(ROOT_PATH . '/config/database.php')) {
    require_once ROOT_PATH . '/config/database.php';
}

// 3. RÉCUPÉRATION DE L'URL
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
$segments = $url ? explode('/', $url) : [];
$page = $segments[0] ?? '';
$param = $segments[1] ?? null;

// 4. ROUTEUR
switch ($page) {

    // ---------------------------------------------------------
    // PAGE D'ACCUEIL
    // ---------------------------------------------------------
    case '':
    case 'home':
        if (!isset($_SESSION['user_id'])) {
            header('Location: login');
            exit;
        }
        echo "<h1>Bienvenue sur EPI Tchach !</h1>";
        echo "<p>Connecté en tant que membre.</p>";
        echo "<a href='logout'>Se déconnecter</a>";
        break;

    // ---------------------------------------------------------
    // AUTHENTIFICATION (login, register, logout, etc.)
    // ---------------------------------------------------------
    case 'login':
    case 'register':
    case 'logout':
    case 'forgot-password':
    case 'reset-password':
        $authControllerFile = ROOT_PATH . '/controllers/AuthController.php';

        // Si le contrôleur et la méthode existent, on les exécute
        if (file_exists($authControllerFile)) {
            require_once $authControllerFile;
            if (class_exists('AuthController')) {
                $controller = new AuthController();
                $method = match ($page) {
                    'login' => ($_SERVER['REQUEST_METHOD'] === 'POST') ? 'loginPost' : 'loginPage',
                    'register' => ($_SERVER['REQUEST_METHOD'] === 'POST') ? 'registerPost' : 'registerPage',
                    'logout' => 'logout',
                    'forgot-password' => ($_SERVER['REQUEST_METHOD'] === 'POST') ? 'forgotPasswordPost' : 'forgotPasswordPage',
                    'reset-password' => ($_SERVER['REQUEST_METHOD'] === 'POST') ? 'resetPasswordPost' : 'resetPasswordPage',
                    default => 'index',
                };

                if (method_exists($controller, $method)) {
                    $controller->$method();
                    break;
                }
            }
        }

        // Affichage propre d'attente tant que le code n'est pas encore implémenté
        echo "<!DOCTYPE html><html lang='fr'><head><meta charset='UTF-8'><title>EPI Tchach - " . ucfirst($page) . "</title></head><body style='font-family:sans-serif;padding:2rem;'>";
        echo "<h2>Page : " . htmlspecialchars($page) . "</h2>";
        echo "<p><em>Cette fonctionnalité est en cours de développement.</em></p>";
        echo "<p><a href='home'>← Retour à l'accueil</a></p>";
        echo "</body></html>";
        break;

    // ---------------------------------------------------------
    // PUBLICATIONS (à venir)
    // ---------------------------------------------------------
    // case 'publications':
    //     ...
    //     break;

    // ---------------------------------------------------------
    // ZONES (à venir)
    // ---------------------------------------------------------
    // case 'zones':
    //     ...
    //     break;

    // ---------------------------------------------------------
    // PAGE 404
    // ---------------------------------------------------------
    default:
        http_response_code(404);
        echo "<!DOCTYPE html><html lang='fr'><head><meta charset='UTF-8'><title>404 - Introuvable</title></head><body style='font-family:sans-serif;padding:2rem;'>";
        echo "<h1>404 – Page non trouvée</h1>";
        echo "<p>L'URL demandée (<strong>" . htmlspecialchars($page) . "</strong>) n'existe pas.</p>";
        echo "<a href='home'>← Retour à l'accueil</a>";
        echo "</body></html>";
        break;
}
