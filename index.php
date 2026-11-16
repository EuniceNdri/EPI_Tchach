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

require_once __DIR__ . '/app/Bootstrap.php';

// Récupération de la route demandée
$fullRoute = getRequestedRoute();
$segments = $fullRoute ? explode('/', $fullRoute) : [];
$route = $segments[0] ?? '';

// Routing starts after the shared bootstrap.

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
    require_once controllerFile('AttachmentController');
    (new AttachmentController())->view((int)$attachmentViewMatch[1]);
}
if (preg_match('#^attachment/download/([1-9][0-9]*)$#', $fullRoute, $attachmentMatch)) {
    require_once controllerFile('AttachmentController');
    (new AttachmentController())->download((int)$attachmentMatch[1]);
}
if (preg_match('#^messages/attachment/([1-9][0-9]*)$#', $fullRoute, $messageAttachmentMatch)) {
    require_once controllerFile('SocialController');
    (new SocialController())->messageAttachment((int)$messageAttachmentMatch[1]);
}
if (preg_match('#^publication/(?:show/)?([1-9][0-9]*)$#', $fullRoute, $publicationMatch)) {
    require_once controllerFile('PublicationController');
    (new PublicationController())->show((int)$publicationMatch[1]);
    exit;
}


if (in_array($fullRoute, ['', 'landing', 'home'], true)) {
    [$filieres, $filieresLoadError] = loadActiveFilieres($pdo);
    $landingSettings = (new LandingContent($pdo))->getSettings();
    header('Vary: User-Agent');
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (preg_match('/(?:iphone|ipod|windows phone|blackberry|opera mini|mobile|android.*mobile)/i', $userAgent)) {
        require_once ROOT_PATH . '/resources/views/smartphone/landing.php';
    } else {
        require_once ROOT_PATH . '/resources/views/desktop/landing.php';
    }
    exit;
}

$routes = require ROOT_PATH . '/routes/web.php';
(new Router($routes))->dispatch(
    $fullRoute,
    (string)($_SERVER['REQUEST_METHOD'] ?? 'GET')
);
exit;
