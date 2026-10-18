<?php
/**
 * =============================================================
 * index.php – Point d'entrée unique (Front Controller à la racine)
 * =============================================================
 * Route unique active : Page Landing Desktop
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

// Routeur : uniquement la page Landing Desktop
$landingView = ROOT_PATH . '/views/desktop/landing.php';

if (file_exists($landingView)) {
    require_once $landingView;
    exit;
} else {
    echo "<h1>EPI Tchach</h1><p>Fichier views/desktop/landing.php introuvable.</p>";
}

