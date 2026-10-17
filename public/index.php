<?php
/**
 * =============================================================
 * public/index.php – Point d'entrée unique (Front Controller)
 * =============================================================
 *
 * Toutes les requêtes HTTP passent par ce fichier grâce au .htaccess.
 * Il récupère l'URL demandée et appelle le bon Controller.
 *
 * Exemple :
 *   /login        → AuthController -> login()
 *   /register     → AuthController -> register()
 *   /publications → PublicationController -> index()
 */

// Démarrer la session pour gérer l'authentification
session_start();

// Charger la configuration de la base de données
require_once __DIR__ . '/../config/database.php';

// Récupérer l'URL demandée
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';

// TODO: Implémenter le routeur qui appelle le bon Controller
// selon l'URL demandée. Exemple :
//
// switch ($url) {
//     case 'login':
//         require_once __DIR__ . '/../controllers/AuthController.php';
//         $controller = new AuthController();
//         $controller->login();
//         break;
//
//     case 'register':
//         require_once __DIR__ . '/../controllers/AuthController.php';
//         $controller = new AuthController();
//         $controller->register();
//         break;
//
//     case 'logout':
//         require_once __DIR__ . '/../controllers/AuthController.php';
//         $controller = new AuthController();
//         $controller->logout();
//         break;
//
//     default:
//         // Page d'accueil ou 404
//         break;
// }
