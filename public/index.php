<?php
/**
 * =============================================================
 * public/index.php – Point d'entrée unique (Front Controller)
 * =============================================================
 *
 * 🔹 COMMENT ÇA MARCHE ?
 *    Grâce au .htaccess, TOUTES les URLs tapées dans le navigateur
 *    passent par CE fichier. Exemple :
 *
 *       http://localhost/EPI_Tchach/public/login
 *       → index.php reçoit "login" dans $_GET['url']
 *       → Le switch ci-dessous appelle AuthController->loginPage()
 *
 * 🔹 COMMENT AJOUTER UNE NOUVELLE PAGE ?
 *    1. Créer ton Controller dans controllers/ (ex: MonController.php)
 *    2. Créer ta View dans views/ (ex: views/ma-page.php)
 *    3. Ajouter un "case" dans le switch ci-dessous
 *    4. C'est tout !
 *
 *    Exemple pour ajouter une page "profil" :
 *
 *       case 'profil':
 *           require_once ROOT_PATH . '/controllers/ProfilController.php';
 *           $controller = new ProfilController();
 *           $controller->show();
 *           break;
 *
 * 🔹 COMMENT ÇA GÈRE GET vs POST ?
 *    - GET  = l'utilisateur AFFICHE la page (il tape l'URL ou clique un lien)
 *    - POST = l'utilisateur ENVOIE un formulaire (il clique "Connexion", "S'inscrire"...)
 *
 *    On vérifie avec : $_SERVER['REQUEST_METHOD'] === 'POST'
 *
 * 🔹 COMMENT PROTÉGER UNE PAGE ? (utilisateur connecté uniquement)
 *    Ajouter ceci au début du case :
 *
 *       if (!isset($_SESSION['user_id'])) {
 *           header('Location: login');
 *           exit;
 *       }
 *
 * =============================================================
 */

// ========================
// 1. DÉMARRAGE DE SESSION
// ========================
// session_start() permet de garder en mémoire les infos de l'utilisateur
// connecté entre les pages (son ID, son nom, etc.)
session_start();

// ========================
// 2. CONFIGURATION
// ========================

// ROOT_PATH = chemin vers la racine du projet (un niveau au-dessus de public/)
// Ça permet d'écrire ROOT_PATH . '/controllers/...' au lieu de '../controllers/...'
define('ROOT_PATH', dirname(__DIR__));

// Charger la connexion à la base de données
// → Après configuration, la variable $pdo sera disponible partout
require_once ROOT_PATH . '/config/database.php';

// ========================
// 3. RÉCUPÉRATION DE L'URL
// ========================

// Le .htaccess envoie l'URL dans $_GET['url']
// Exemple : "login", "register", "publications/3"
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';

// On découpe l'URL en morceaux
// "publications/3" → ["publications", "3"]
$segments = $url ? explode('/', $url) : [];

// Premier morceau = la page demandée
$page = $segments[0] ?? '';

// Deuxième morceau = un paramètre (ID, slug, etc.)
$param = $segments[1] ?? null;

// ========================
// 4. ROUTEUR
// ========================
// C'est ici qu'on décide quel Controller appeler selon l'URL.
// Chaque "case" correspond à une URL possible.

switch ($page) {

    // =====================================================
    // 🔐 AUTHENTIFICATION
    // =====================================================
    // Ces routes sont gérées par AuthController.php
    // Les vues sont dans views/auth/
    // =====================================================

    case 'login':
        // URL : /login
        // GET  → Affiche le formulaire de connexion
        // POST → Vérifie l'email + mot de passe et connecte l'utilisateur
        require_once ROOT_PATH . '/controllers/AuthController.php';
        $controller = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->loginPost();
        } else {
            $controller->loginPage();
        }
        break;

    case 'register':
        // URL : /register
        // GET  → Affiche le formulaire d'inscription
        // POST → Crée le compte en BDD et redirige vers login
        require_once ROOT_PATH . '/controllers/AuthController.php';
        $controller = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->registerPost();
        } else {
            $controller->registerPage();
        }
        break;

    case 'logout':
        // URL : /logout
        // Détruit la session et redirige vers login
        require_once ROOT_PATH . '/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->logout();
        break;

    case 'forgot-password':
        // URL : /forgot-password
        // GET  → Affiche le formulaire "email pour réinitialiser"
        // POST → Envoie un email avec le lien de réinitialisation
        require_once ROOT_PATH . '/controllers/AuthController.php';
        $controller = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->forgotPasswordPost();
        } else {
            $controller->forgotPasswordPage();
        }
        break;

    case 'reset-password':
        // URL : /reset-password
        // GET  → Affiche le formulaire "nouveau mot de passe"
        // POST → Met à jour le mot de passe en BDD
        require_once ROOT_PATH . '/controllers/AuthController.php';
        $controller = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->resetPasswordPost();
        } else {
            $controller->resetPasswordPage();
        }
        break;

    // =====================================================
    // 🏠 PAGE D'ACCUEIL
    // =====================================================

    case '':
    case 'home':
        // URL : / ou /home
        // Si l'utilisateur n'est pas connecté → redirige vers login
        // Sinon → affiche la page d'accueil
        if (!isset($_SESSION['user_id'])) {
            header('Location: login');
            exit;
        }
        // TODO: Créer HomeController + vue accueil
        echo "Bienvenue sur EPI Tchach ! (Page d'accueil à créer)";
        break;

    // =====================================================
    // 📝 PUBLICATIONS (à implémenter)
    // =====================================================
    // Décommenter quand PublicationController.php sera créé
    //
    // case 'publications':
    //     require_once ROOT_PATH . '/controllers/PublicationController.php';
    //     $controller = new PublicationController();
    //     if ($param) {
    //         $controller->show($param);  // /publications/3 → détail
    //     } else {
    //         $controller->index();       // /publications → liste
    //     }
    //     break;

    // =====================================================
    // 🗂️ ZONES / CATÉGORIES (à implémenter)
    // =====================================================
    // Décommenter quand ZoneController.php sera créé
    //
    // case 'zones':
    //     require_once ROOT_PATH . '/controllers/ZoneController.php';
    //     $controller = new ZoneController();
    //     $controller->index();
    //     break;

    // =====================================================
    // 💬 COMMENTAIRES (à implémenter)
    // =====================================================
    // Les commentaires seront probablement gérés via POST
    // depuis la page de détail d'une publication
    //
    // case 'commentaire':
    //     require_once ROOT_PATH . '/controllers/CommentaireController.php';
    //     $controller = new CommentaireController();
    //     $controller->store();  // POST uniquement
    //     break;

    // =====================================================
    // ❌ PAGE 404 – URL NON RECONNUE
    // =====================================================

    default:
        http_response_code(404);
        echo "<h1>404 – Page non trouvée</h1>";
        echo "<p>La page <strong>" . htmlspecialchars($page) . "</strong> n'existe pas.</p>";
        echo "<a href='login'>← Retour à la connexion</a>";
        break;
}
