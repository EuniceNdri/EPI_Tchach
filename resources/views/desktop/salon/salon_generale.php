<?php
/**
 * =============================================================
 * resources/views/desktop/salon/salon_generale.php
 * Espace Salon Général – EPI Tchach (École Polytechnique Internationale)
 * =============================================================
 *
 * @var string|null $baseUrl
 * @var string|null $assetBase
 * @var array|null  $zones
 * @var array|null  $composerZones
 * @var array|null  $selectedZone
 * @var array|null  $feedPosts
 * @var array|null  $activeTags
 * @var array|null  $selectedTag
 * @var array|null  $publicationTags
 * @var array|null  $publicationMentions
 * @var array|null  $pollOptions
 * @var array|null  $salonDocuments
 * @var array|null  $postAttachments
 * @var array|null  $savedAttachments
 * @var array|null  $popularPosts
 * @var array|null  $onlineUsers
 * @var string|null $feedView
 * @var int|null    $categoryId
 * @var int|null    $unreadCount
 * @var string|null $flashSuccess
 * @var string|null $flashError
 */

$base = is_string($baseUrl ?? null) ? $baseUrl : '';
$asset = is_string($assetBase ?? null)
  ? $assetBase
  : ($base !== '' ? $base . '/public/assets' : '/public/assets');
$logoFile = $asset . '/images/logo.jpeg';
$feedView = isset($feedView) && in_array($feedView, ['latest', 'popular', 'saved', 'documents'], true)
  ? $feedView
  : 'latest';
$categoryId = isset($categoryId) ? max(0, (int)$categoryId) : null;
$zones = is_array($zones ?? null) ? $zones : [];
$composerZones = is_array($composerZones ?? null) ? $composerZones : [];
$selectedZone = is_array($selectedZone ?? null) ? $selectedZone : null;
$feedPosts = is_array($feedPosts ?? null) ? $feedPosts : [];
$activeTags = is_array($activeTags ?? null) ? $activeTags : [];
$selectedTag = is_array($selectedTag ?? null) ? $selectedTag : null;
$publicationTags = is_array($publicationTags ?? null) ? $publicationTags : [];
$publicationMentions = is_array($publicationMentions ?? null) ? $publicationMentions : [];
$pollOptions = is_array($pollOptions ?? null) ? $pollOptions : [];
$salonDocuments = is_array($salonDocuments ?? null) ? $salonDocuments : [];
$postAttachments = is_array($postAttachments ?? null) ? $postAttachments : [];
$savedAttachments = is_array($savedAttachments ?? null) ? $savedAttachments : [];
$popularPosts = is_array($popularPosts ?? null) ? $popularPosts : [];
$onlineUsers = is_array($onlineUsers ?? null) ? $onlineUsers : [];
$unreadCount = max(0, (int)($unreadCount ?? 0));
$flashSuccess = is_string($flashSuccess ?? null) ? $flashSuccess : '';
$flashError = is_string($flashError ?? null) ? $flashError : '';

if (!isset($_SESSION) || !is_array($_SESSION)) {
  $_SESSION = [];
}

$currentUserRole = strtolower(trim((string)($_SESSION['user_role'] ?? 'etudiant')));
$currentUserName = trim(($_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? 'Membre'));
$currentUserInitials = strtoupper(
  mb_substr($_SESSION['user_prenom'] ?? 'E', 0, 1) . mb_substr($_SESSION['user_nom'] ?? 'P', 0, 1)
);

$zoneContext = $selectedZone !== null ? '&zone=' . (int)$selectedZone['id'] : '';
$categoryContext = $categoryId ? '&category=' . (int)$categoryId : '';
$pageTitle = $selectedZone !== null ? $selectedZone['nom'] : 'Tous les salons';

$roleLabels = [
  'etudiant'           => 'Étudiant',
  'professeur'         => 'Professeur',
  'delegue_de_classe'  => 'Délégué de classe',
  'delegue_general'   => 'Délégué général',
  'administrateur'     => 'Administration',
  'admin'              => 'Administration',
];

if (!function_exists('formatSalonDateRelative')) {
  function formatSalonDateRelative(string $date): string {
    try {
      $createdAt = new DateTimeImmutable($date);
      $seconds = max(0, time() - $createdAt->getTimestamp());
      if ($seconds < 60) return "À l'instant";
      if ($seconds < 3600) return 'Il y a ' . (int)floor($seconds / 60) . ' min';
      if ($seconds < 86400) return 'Il y a ' . (int)floor($seconds / 3600) . ' h';
      if ($seconds < 604800) return 'Il y a ' . (int)floor($seconds / 86400) . ' j';
      return $createdAt->format('d/m/Y');
    } catch (Exception $exception) {
      return $date;
    }
  }
}

// Catégories disponibles pour le composer
$postCategories = [];
foreach ($composerZones as $cZone) {
  foreach (($cZone['categories'] ?? []) as $cCategory) {
    $cId = (int)$cCategory['id'];
    $postCategories[] = [
      'id'           => $cId,
      'zone_id'      => (int)$cZone['id'],
      'zone_name'    => (string)$cZone['nom'],
      'name'         => (string)$cCategory['nom'],
      'can_announce' => !empty($announcementCategoryPermissions[$cId]),
    ];
  }
}
$defaultPostCategoryId = $categoryId !== null && $categoryId > 0
  ? $categoryId
  : (int)($postCategories[0]['id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?> · EPI Tchach</title>
  <link rel="icon" type="image/svg+xml" href="<?= $asset ?>/images/logo_epi.svg" />
  <link rel="apple-touch-icon" href="<?= $asset ?>/images/logo.jpeg" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="<?= $asset ?>/css/desktop/salon.css" />
  <link rel="stylesheet" href="<?= $asset ?>/css/feedback.css" />
</head>
<body data-base-url="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>">
  <?php include ROOT_PATH . '/resources/views/partials/feedback-toast.php'; ?>

  <!-- Barre tricolore officielle -->
  <div class="tricolor-bar" aria-hidden="true"></div>

  <div class="app-layout">

    <!-- ========================================================= -->
    <!-- 1. SIDEBAR GAUCHE : Navigation, Filières & Salons, Profil -->
    <!-- ========================================================= -->
    <aside class="sidebar-left" aria-label="Navigation principale">
      <!-- En-tête / Logo -->
      <div class="sidebar-header">
        <a href="<?= $base ?>/salon" class="brand-link" title="Accueil EPI Tchach">
          <img src="<?= htmlspecialchars($logoFile) ?>" alt="EPI Logo" class="brand-logo" onerror="this.style.display='none'" />
          <div class="brand-text">
            <span class="brand-title">EPI <span class="brand-accent">Tchach</span></span>
            <span class="brand-subtitle">Plateforme d’échange</span>
          </div>
        </a>
      </div>

      <!-- Navigation d'accès rapide -->
      <nav class="nav-section" aria-label="Sections du forum">
        <div class="nav-section-label">Navigation</div>
        <a href="<?= $base ?>/salon" class="nav-link <?= $selectedZone === null && $feedView === 'latest' && !$categoryId ? 'is-active' : '' ?>">
          <svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
          </svg>
          <span class="nav-text">Tous les salons</span>
        </a>
        <a href="<?= $base ?>/salon?view=popular<?= $zoneContext ?><?= $categoryContext ?>" class="nav-link <?= $feedView === 'popular' ? 'is-active' : '' ?>">
          <svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>
          </svg>
          <span class="nav-text">Populaires</span>
        </a>
        <a href="<?= $base ?>/salon?view=saved<?= $zoneContext ?><?= $categoryContext ?>" class="nav-link <?= $feedView === 'saved' ? 'is-active' : '' ?>">
          <svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
          </svg>
          <span class="nav-text">Sauvegardés</span>
        </a>
        <a href="<?= $base ?>/salon?view=documents<?= $zoneContext ?><?= $categoryContext ?>" class="nav-link <?= $feedView === 'documents' ? 'is-active' : '' ?>">
          <svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
          </svg>
          <span class="nav-text">Rubrique PDF & Docs</span>
        </a>
      </nav>

      <!-- Liste des Filières & Salons -->
      <div class="sidebar-zones-wrapper">
        <div class="nav-section-label">Filières & Salons</div>
        <div class="zones-accordion">
          <?php if (empty($zones)): ?>
            <p class="sidebar-empty-state">Aucun salon n'est encore associé à votre compte.</p>
          <?php else: ?>
            <?php foreach ($zones as $zone):
              $isZoneSelected = ($selectedZone !== null && (int)$selectedZone['id'] === (int)$zone['id']);
            ?>
              <div class="zone-group <?= $isZoneSelected ? 'is-open' : '' ?>">
                <a href="<?= $base ?>/salon?view=<?= rawurlencode($feedView) ?>&zone=<?= (int)$zone['id'] ?>"
                   class="zone-heading <?= $isZoneSelected ? 'is-active' : '' ?>"
                   title="Voir toutes les publications de <?= htmlspecialchars($zone['nom']) ?>">
                  <span class="zone-title-text"><?= htmlspecialchars($zone['nom']) ?></span>
                  <span class="zone-badge" title="<?= (int)$zone['membres_count'] ?> membres">
                    <?= (int)$zone['membres_count'] ?>
                  </span>
                </a>

                <div class="zone-channels">
                  <?php foreach ($zone['categories'] as $category):
                    $isCategorySelected = ($categoryId === (int)$category['id']);
                  ?>
                    <a href="<?= $base ?>/salon?view=<?= rawurlencode($feedView) ?>&zone=<?= (int)$zone['id'] ?>&category=<?= (int)$category['id'] ?>"
                       class="channel-link <?= $isCategorySelected ? 'is-active' : '' ?>"
                       title="<?= htmlspecialchars($category['description'] ?? $category['nom']) ?>">
                      <span class="channel-hash" aria-hidden="true">#</span>
                      <span class="channel-name"><?= htmlspecialchars($category['nom']) ?></span>
                      <?php if ((int)$category['publications_count'] > 0): ?>
                        <span class="channel-count"><?= (int)$category['publications_count'] ?></span>
                      <?php endif; ?>
                    </a>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <?php if ($categoryId || $selectedZone): ?>
          <div class="clear-filters-box">
            <a href="<?= $base ?>/salon?view=<?= rawurlencode($feedView) ?>" class="btn-clear-channel-filter">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
              <span>Voir tous les salons</span>
            </a>
          </div>
        <?php endif; ?>
      </div>

      <!-- Pied de Sidebar : Bouton Publier & Profil -->
      <div class="sidebar-user-footer">
        <?php if (!empty($_SESSION['user_id'])): ?>
          <button type="button" class="btn-sidebar-new-post" id="btnSidebarCreatePost" aria-haspopup="dialog" title="Créer une nouvelle publication">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <line x1="12" y1="5" x2="12" y2="19"></line>
              <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Publier un sujet</span>
          </button>

          <div class="user-profile-card">
            <div class="user-avatar-initials" aria-hidden="true">
              <?= htmlspecialchars($currentUserInitials) ?>
            </div>
            <div class="user-meta-block">
              <span class="user-fullname" title="<?= htmlspecialchars($currentUserName) ?>">
                <?= htmlspecialchars($currentUserName) ?>
              </span>
              <span class="user-role-pill">
                <?= htmlspecialchars($roleLabels[$currentUserRole] ?? ucfirst($currentUserRole)) ?>
              </span>
            </div>
          </div>

          <div class="user-quick-actions">
            <a href="<?= $base ?>/profile" class="user-quick-link" title="Mon Profil">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
              </svg>
              <span>Profil</span>
            </a>
            <button type="button" class="user-quick-link nav-item-notifications" id="btnToggleNotifPopup" title="Ouvrir les notifications" aria-haspopup="true">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
              </svg>
              <span>Alertes</span>
              <span class="nav-notification-count" data-notification-count<?= $unreadCount > 0 ? '' : ' hidden' ?> style="<?= $unreadCount > 0 ? '' : 'display:none;' ?>">
                <?= (int)$unreadCount ?>
              </span>
            </button>
            <?php if (in_array($currentUserRole, ['administrateur', 'admin'], true)): ?>
              <a href="<?= $base ?>/admin" class="user-quick-link is-admin-link" title="Administration">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
                <span>Admin</span>
              </a>
            <?php endif; ?>
            <a href="<?= $base ?>/logout" class="user-quick-link is-danger-link" title="Se déconnecter">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
              </svg>
            </a>
          </div>
        <?php else: ?>
          <div class="auth-buttons-grid">
            <a href="<?= $base ?>/login" class="btn-auth-login">Connexion</a>
            <a href="<?= $base ?>/register" class="btn-auth-register">Inscription</a>
          </div>
        <?php endif; ?>
      </div>
    </aside>

    <!-- ========================================================= -->
    <!-- 2. CONTENU CENTRAL : Recherche, Composer, Feed, Documents -->
    <!-- ========================================================= -->
    <main class="main-content" id="mainFeedArea">
      <!-- En-tête de page / bannière de la zone -->
      <header class="feed-header">
        <div class="feed-header-content">
          <div class="feed-header-tag">
            <span class="feed-tag-dot" aria-hidden="true"></span>
            <span><?= $selectedZone !== null ? 'Filière · ' . htmlspecialchars($selectedZone['nom']) : 'EPI Yamoussoukro · Espace Général' ?></span>
          </div>
          <h1 class="feed-title"><?= htmlspecialchars($pageTitle) ?></h1>
          <p class="feed-description">
            <?= $selectedZone !== null
              ? htmlspecialchars((string)($selectedZone['description'] ?? 'Espace d’échange, questions de cours et ressources de la filière.'))
              : 'Partagez vos interrogations, découvrez les discussions récentes et téléchargez les supports de cours.' ?>
          </p>
        </div>
      </header>

      <!-- Barre de recherche instantanée -->
      <div class="feed-search-container">
        <div class="search-input-wrapper">
          <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
          <input id="publicationSearch" type="search" class="search-field"
                 placeholder="Rechercher des discussions, mots-clés, auteurs, sondages ou fichiers..."
                 autocomplete="off" aria-label="Rechercher dans les publications">
          <button type="button" id="clearSearchBtn" class="search-clear-btn" aria-label="Effacer la recherche" hidden>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
          </button>
        </div>
      </div>

      <!-- Notifications Flash -->
      <?php if ($flashSuccess): ?>
        <div class="salon-alert salon-alert-success" role="status">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          <span><?= htmlspecialchars($flashSuccess) ?></span>
        </div>
      <?php endif; ?>
      <?php if ($flashError): ?>
        <div class="salon-alert salon-alert-error" role="alert">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <span><?= htmlspecialchars($flashError) ?></span>
        </div>
      <?php endif; ?>

      <!-- Onglets de tri du fil -->
      <div class="feed-tabs-row" aria-label="Trier le contenu">
        <div class="feed-tabs-group">
          <a class="feed-tab <?= $feedView === 'latest' ? 'is-active' : '' ?>"
             href="<?= $base ?>/salon?view=latest<?= $zoneContext ?><?= $categoryContext ?>">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span>Récentes</span>
          </a>
          <a class="feed-tab <?= $feedView === 'popular' ? 'is-active' : '' ?>"
             href="<?= $base ?>/salon?view=popular<?= $zoneContext ?><?= $categoryContext ?>">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>
            <span>Populaires</span>
          </a>
          <a class="feed-tab <?= $feedView === 'saved' ? 'is-active' : '' ?>"
             href="<?= $base ?>/salon?view=saved<?= $zoneContext ?><?= $categoryContext ?>">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
            <span>Sauvegardées</span>
          </a>
          <a class="feed-tab <?= $feedView === 'documents' ? 'is-active' : '' ?>"
             href="<?= $base ?>/salon?view=documents<?= $zoneContext ?><?= $categoryContext ?>">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            <span>Rubrique PDF</span>
          </a>
        </div>

        <?php if ($categoryId): ?>
          <a class="feed-tab-clear" href="<?= $base ?>/salon?view=<?= rawurlencode($feedView) ?><?= $zoneContext ?>">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            <span>Effacer le filtre salon</span>
          </a>
        <?php endif; ?>
      </div>

      <!-- Filtres par Tags défilables -->
      <?php if ($activeTags && $feedView !== 'documents'): ?>
        <nav class="tags-scroller-row" aria-label="Filtrer par tag">
          <a class="tag-scroller-pill <?= $selectedTag === null ? 'is-active' : '' ?>"
             href="<?= $base ?>/salon?view=<?= rawurlencode($feedView) ?><?= $zoneContext ?><?= $categoryContext ?>">
            Tous les tags
          </a>
          <?php foreach ($activeTags as $activeTag):
            $tagQuery = ['view' => $feedView, 'tag' => (int)$activeTag['id']];
            if ($selectedZone !== null) $tagQuery['zone'] = (int)$selectedZone['id'];
            if ($categoryId !== null && $categoryId > 0) $tagQuery['category'] = $categoryId;
          ?>
            <a class="tag-scroller-pill <?= (int)($selectedTag['id'] ?? 0) === (int)$activeTag['id'] ? 'is-active' : '' ?>"
               href="<?= $base ?>/salon?<?= htmlspecialchars(http_build_query($tagQuery), ENT_QUOTES, 'UTF-8') ?>">
              #<?= htmlspecialchars($activeTag['nom']) ?>
            </a>
          <?php endforeach; ?>
        </nav>
      <?php endif; ?>

      <!-- Conteneur des Résultats de recherche -->
      <div id="searchResults" class="posts-stream search-stream" hidden aria-live="polite"></div>

      <!-- ========================================================= -->
      <!-- STREAM DES PUBLICATIONS                                   -->
      <!-- ========================================================= -->
      <div class="posts-stream" id="publicationList">

        <!-- Vue DOCUMENTS / PDF PARTAGÉS -->
        <?php if ($feedView === 'documents'): ?>
          <section class="shared-documents-section" aria-labelledby="sharedDocsTitle">
            <div class="shared-docs-header">
              <h2 id="sharedDocsTitle" class="section-heading">Supports de cours, PDF et images</h2>
              <p class="section-subtext">Retrouvez l’ensemble des pièces jointes et polycopiés partagés dans vos salons.</p>
            </div>

            <?php if (empty($salonDocuments)): ?>
              <div class="empty-state-card">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="empty-state-icon">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
                <h3>Aucun document partagé pour le moment</h3>
                <p>Les fichiers PDF, Word ou images téléversés dans vos salons apparaîtront ici.</p>
              </div>
            <?php else: ?>
              <div class="documents-grid">
                <?php foreach ($salonDocuments as $doc):
                  $isPdf = ((string)$doc['type_mime'] === 'application/pdf');
                  $isImage = str_starts_with((string)$doc['type_mime'], 'image/');
                  $docViewUrl = $base . '/attachment/view/' . (int)$doc['id'];
                  $docDownloadUrl = $base . '/attachment/download/' . (int)$doc['id'];
                  $docPublicationUrl = $base . '/salon?view=latest&zone=' . (int)$doc['zone_id']
                    . '&category=' . (int)$doc['category_id'] . '#publication-' . (int)$doc['publication_id'];
                ?>
                  <article class="doc-card">
                    <div class="doc-card-badge-row">
                      <span class="doc-type-badge <?= $isPdf ? 'is-pdf' : ($isImage ? 'is-img' : 'is-file') ?>">
                        <?= $isPdf ? 'PDF' : ($isImage ? 'IMAGE' : 'FICHIER') ?>
                      </span>
                      <span class="doc-channel-tag"><?= htmlspecialchars($doc['category_name']) ?></span>
                    </div>

                    <?php if ($isImage): ?>
                      <a href="<?= $docViewUrl ?>" target="_blank" rel="noopener" class="doc-card-preview-link">
                        <img src="<?= $docViewUrl ?>" alt="<?= htmlspecialchars($doc['nom_fichier']) ?>" class="doc-card-img" loading="lazy" />
                      </a>
                    <?php else: ?>
                      <div class="doc-card-icon-area">
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
                        </svg>
                      </div>
                    <?php endif; ?>

                    <div class="doc-card-body">
                      <strong class="doc-card-filename" title="<?= htmlspecialchars($doc['nom_fichier']) ?>">
                        <?= htmlspecialchars($doc['nom_fichier']) ?>
                      </strong>
                      <div class="doc-card-origin">
                        <span>Par <?= htmlspecialchars(trim($doc['prenom'] . ' ' . $doc['nom'])) ?></span>
                        <a href="<?= htmlspecialchars($docPublicationUrl, ENT_QUOTES, 'UTF-8') ?>" class="doc-card-post-link">
                          <?= htmlspecialchars($doc['titre']) ?>
                        </a>
                      </div>
                    </div>

                    <div class="doc-card-footer">
                      <?php if ($isPdf || $isImage): ?>
                        <a href="<?= $docViewUrl ?>" target="_blank" rel="noopener" class="btn-doc-action is-preview">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                          <span>Aperçu</span>
                        </a>
                      <?php endif; ?>
                      <a href="<?= $docDownloadUrl ?>" class="btn-doc-action is-download">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        <span>Télécharger</span>
                      </a>
                    </div>
                  </article>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </section>
        <?php endif; ?>

        <!-- État vide pour le flux normal -->
        <?php if ($feedView !== 'documents' && empty($feedPosts) && empty($savedAttachments)): ?>
          <div class="empty-state-card">
            <svg width="54" height="54" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="empty-state-icon">
              <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
            <h2><?= $selectedTag
              ? 'Aucune publication trouvée avec #' . htmlspecialchars($selectedTag['nom'])
              : ($feedView === 'saved' ? 'Aucun élément sauvegardé' : 'Aucune publication pour l’instant') ?></h2>
            <p><?= $selectedTag
              ? 'Essayez un autre mot-clé ou revenez à l’ensemble des publications.'
              : ($feedView === 'saved'
              ? 'Cliquez sur l’étoile pour garder des cours et astuces sous la main.'
              : 'Soyez le premier à poser une question ou partager un document dans ce salon !') ?></p>
            <?php if ($feedView === 'latest'): ?>
              <button type="button" class="btn-empty-action" id="btnEmptyCreatePost">Créer une publication</button>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <!-- LISTE DES CARTES DE PUBLICATION -->
        <?php foreach ($feedPosts as $post):
          $postId = (int)$post['id'];
          $authorName = trim($post['prenom'] . ' ' . $post['nom']);
          $authorInitials = strtoupper(mb_substr($post['prenom'], 0, 1) . mb_substr($post['nom'], 0, 1));
          $authorRole = $roleLabels[$post['author_role']] ?? ucfirst($post['author_role']);
          $isSaved = !empty($post['saved_id']);
          $postType = $post['type_publication'];
          $isPoll = ($postType === 'question');
          $isAnnouncement = ($postType === 'annonce');
          $isDiscussion = ($postType === 'discussion');
          $excerpt = mb_strimwidth(strip_tags($post['contenu']), 0, 600, '…');
        ?>
          <article class="post-card post-card-<?= htmlspecialchars($postType) ?> <?= $isAnnouncement ? 'is-announcement-card' : '' ?>"
                   id="publication-<?= $postId ?>"
                   data-publication-id="<?= $postId ?>"
                   <?= $isDiscussion ? 'data-discussion-card="' . $postId . '"' : '' ?>>

            <!-- En-tête de la publication -->
            <div class="post-header">
              <div class="post-author-box">
                <div class="author-avatar" aria-hidden="true"><?= htmlspecialchars($authorInitials) ?></div>
                <div class="author-details">
                  <div class="author-line">
                    <strong class="author-name"><?= htmlspecialchars($authorName) ?></strong>
                    <span class="badge-role-pill role-<?= htmlspecialchars($post['author_role']) ?>">
                      <?= htmlspecialchars($authorRole) ?>
                    </span>
                    <?php if ($post['statut'] === 'epingle'): ?>
                      <span class="badge-pinned">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <span>Épinglée</span>
                      </span>
                    <?php endif; ?>
                  </div>
                  <div class="post-meta-subline">
                    <span class="post-meta-zone"><?= htmlspecialchars($post['zone_name']) ?></span>
                    <span class="meta-dot">·</span>
                    <span class="post-meta-category">#<?= htmlspecialchars($post['category_name']) ?></span>
                    <span class="meta-dot">·</span>
                    <time class="post-meta-time" datetime="<?= htmlspecialchars($post['date_creation']) ?>">
                      <?= htmlspecialchars(formatSalonDateRelative($post['date_creation'])) ?>
                    </time>
                  </div>
                </div>
              </div>

              <!-- Actions supérieures : Type badge & Favori -->
              <div class="post-header-actions">
                <span class="post-type-tag type-<?= htmlspecialchars($postType) ?>">
                  <?php if ($isAnnouncement): ?>
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    <span>Annonce</span>
                  <?php elseif ($isPoll): ?>
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    <span>Sondage</span>
                  <?php else: ?>
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <span>Discussion</span>
                  <?php endif; ?>
                </span>

                <button type="button"
                        class="btn-save-post <?= $isSaved ? 'is-saved' : '' ?>"
                        data-toggle-save
                        data-target-type="publication"
                        data-target-id="<?= $postId ?>"
                        aria-label="<?= $isSaved ? 'Retirer des sauvegardes' : 'Sauvegarder' ?>"
                        title="<?= $isSaved ? 'Retirer des sauvegardes' : 'Sauvegarder cette publication' ?>">
                  <svg width="17" height="17" viewBox="0 0 24 24" fill="<?= $isSaved ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2">
                    <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
                  </svg>
                </button>
              </div>
            </div>

            <!-- Titre et Corps du Message -->
            <div class="post-content-container">
              <h2 class="post-title-heading">
                <?php if ($isDiscussion): ?>
                  <a href="<?= $base ?>/salon?pub=<?= $postId ?>" class="post-title-link" data-open-discussion="<?= $postId ?>">
                    <?= htmlspecialchars($post['titre']) ?>
                  </a>
                <?php else: ?>
                  <?= htmlspecialchars($post['titre']) ?>
                <?php endif; ?>
              </h2>
              <div class="post-text-body">
                <?= nl2br(htmlspecialchars($excerpt)) ?>
              </div>
            </div>

            <!-- Mentions d'utilisateurs (@nom) -->
            <?php if (!empty($publicationMentions[$postId])): ?>
              <div class="post-mentions-bar">
                <span class="mentions-label">Mentionné(s) :</span>
                <?php foreach ($publicationMentions[$postId] as $mUser):
                  $mRole = $roleLabels[$mUser['role']] ?? ucfirst($mUser['role']);
                ?>
                  <span class="mention-chip">
                    <span class="mention-symbol">@</span>
                    <span class="mention-name"><?= htmlspecialchars(trim($mUser['prenom'] . ' ' . $mUser['nom'])) ?></span>
                    <span class="mention-role"><?= htmlspecialchars($mRole) ?></span>
                  </span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <!-- ========================================================= -->
            <!-- AFFICHAGE DES PIÈCES JOINTES (Images / Fichiers)          -->
            <!-- ========================================================= -->
            <?php if (!empty($postAttachments[$postId])):
              $attachmentsList = $postAttachments[$postId];
              $imagesList = [];
              $filesList = [];
              foreach ($attachmentsList as $att) {
                if (str_starts_with((string)$att['type_mime'], 'image/')) {
                  $imagesList[] = $att;
                } else {
                  $filesList[] = $att;
                }
              }
            ?>
              <!-- Grille d'images -->
              <?php if (!empty($imagesList)): ?>
                <div class="attachments-gallery-grid count-<?= min(4, count($imagesList)) ?>">
                  <?php foreach ($imagesList as $imgAtt):
                    $viewUrl = $base . '/attachment/view/' . (int)$imgAtt['id'];
                  ?>
                    <a href="<?= $viewUrl ?>" target="_blank" rel="noopener" class="gallery-item-link"
                       title="<?= htmlspecialchars($imgAtt['nom_fichier']) ?>">
                      <img src="<?= $viewUrl ?>" alt="<?= htmlspecialchars($imgAtt['nom_fichier']) ?>" class="gallery-thumb" loading="lazy" />
                      <span class="gallery-overlay">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
                      </span>
                    </a>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>

              <!-- Cartes de Documents & Fichiers -->
              <?php if (!empty($filesList)): ?>
                <div class="attachments-files-list">
                  <?php foreach ($filesList as $fAtt):
                    $fId = (int)$fAtt['id'];
                    $isPdfFile = ((string)$fAtt['type_mime'] === 'application/pdf');
                    $isDocWord = str_contains((string)$fAtt['type_mime'], 'word') || str_ends_with($fAtt['nom_fichier'], '.docx');
                    $isArchive = str_contains((string)$fAtt['type_mime'], 'zip');
                    $downloadUrl = $base . '/attachment/download/' . $fId;
                    $viewUrl = $base . '/attachment/view/' . $fId;
                    $sizeFormatted = number_format(((int)$fAtt['taille']) / 1048576, 2, ',', ' ') . ' Mo';
                  ?>
                    <div class="file-attachment-card">
                      <div class="file-icon-box <?= $isPdfFile ? 'is-pdf' : ($isDocWord ? 'is-word' : ($isArchive ? 'is-zip' : 'is-generic')) ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                        </svg>
                        <span class="file-ext-label"><?= strtoupper(pathinfo($fAtt['nom_fichier'], PATHINFO_EXTENSION) ?: 'DOC') ?></span>
                      </div>

                      <div class="file-details">
                        <span class="file-name" title="<?= htmlspecialchars($fAtt['nom_fichier']) ?>">
                          <?= htmlspecialchars($fAtt['nom_fichier']) ?>
                        </span>
                        <span class="file-size"><?= htmlspecialchars($sizeFormatted) ?></span>
                      </div>

                      <div class="file-actions">
                        <?php if ($isPdfFile): ?>
                          <a href="<?= $viewUrl ?>" target="_blank" rel="noopener" class="btn-file-icon is-preview" title="Ouvrir l’aperçu PDF">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                          </a>
                        <?php endif; ?>
                        <a href="<?= $downloadUrl ?>" class="btn-file-icon is-download" title="Télécharger le fichier">
                          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        </a>
                        <button type="button"
                                class="btn-file-icon btn-save-att <?= !empty($fAtt['saved_id']) ? 'is-saved' : '' ?>"
                                data-toggle-save
                                data-target-type="attachment"
                                data-target-id="<?= $fId ?>"
                                title="<?= !empty($fAtt['saved_id']) ? 'Retirer des favoris' : 'Sauvegarder ce document' ?>">
                          <svg width="15" height="15" viewBox="0 0 24 24" fill="<?= !empty($fAtt['saved_id']) ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2">
                            <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
                          </svg>
                        </button>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            <?php endif; ?>

            <!-- ========================================================= -->
            <!-- SECTION SONDAGE INTERACTIF (Si type question)             -->
            <!-- ========================================================= -->
            <?php if ($isPoll):
              $pOptions = $pollOptions[$postId] ?? [];
              $totalVotes = array_sum(array_map(static fn (array $opt): int => (int)$opt['votes'], $pOptions));
              $userHasVoted = (bool)array_filter($pOptions, static fn (array $opt): bool => (bool)$opt['user_voted']);
            ?>
              <div class="poll-container-box">
                <?php if (empty($pOptions)): ?>
                  <p class="poll-feedback-empty">Ce sondage ne contient pas encore d’options configurées.</p>
                <?php else: ?>
                  <form class="poll-form-widget" action="<?= $base ?>/publication/vote" method="post" data-publication-id="<?= $postId ?>">
                    <input type="hidden" name="publication_id" value="<?= $postId ?>">
                    <fieldset class="poll-fieldset <?= $userHasVoted ? 'has-voted' : '' ?>">
                      <legend class="poll-legend">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
                        <span><?= $userHasVoted ? 'Résultats en direct' : 'Choisissez votre réponse' ?></span>
                        <span class="poll-total-counter">(<?= $totalVotes ?> vote<?= $totalVotes > 1 ? 's' : '' ?> au total)</span>
                      </legend>

                      <div class="poll-options-stack">
                        <?php foreach ($pOptions as $option):
                          $voteCount = (int)$option['votes'];
                          $pct = ($totalVotes > 0) ? (int)round(($voteCount / $totalVotes) * 100) : 0;
                          $isSelected = !empty($option['user_voted']);
                        ?>
                          <label class="poll-option-row <?= $isSelected ? 'is-selected' : '' ?>">
                            <input type="radio" name="option_id" value="<?= (int)$option['id'] ?>" <?= $isSelected ? 'checked' : '' ?> required class="poll-radio-input" />
                            <div class="poll-bar-track">
                              <div class="poll-bar-fill" style="width: <?= $pct ?>%;"></div>
                              <div class="poll-content-overlay">
                                <span class="poll-option-text"><?= htmlspecialchars($option['texte']) ?></span>
                                <span class="poll-stat-badge">
                                  <strong><?= $pct ?>%</strong>
                                  <small>(<?= $voteCount ?>)</small>
                                </span>
                              </div>
                            </div>
                          </label>
                        <?php endforeach; ?>
                      </div>

                      <div class="poll-footer-controls">
                        <button type="submit" class="btn-vote-submit">
                          <?= $userHasVoted ? 'Modifier mon vote' : 'Voter' ?>
                        </button>
                        <span class="poll-vote-feedback" role="status" aria-live="polite"></span>
                      </div>
                    </fieldset>
                  </form>
                <?php endif; ?>
              </div>
            <?php endif; ?>

            <!-- ========================================================= -->
            <!-- FOOTER DE PUBLICATION : Tags & Boutons d'Action           -->
            <!-- ========================================================= -->
            <div class="post-footer-actions-row">
              <!-- Tags de la publication -->
              <div class="post-tags-list">
                <?php foreach ($publicationTags[$postId] ?? [] as $pTag): ?>
                  <a href="<?= $base ?>/salon?view=latest&tag=<?= (int)$pTag['id'] ?>" class="tag-pill-item">
                    #<?= htmlspecialchars($pTag['nom']) ?>
                  </a>
                <?php endforeach; ?>
              </div>

              <!-- Barres d'actions interactives (Likes, Commentaires, Discussion) -->
              <div class="post-interactive-buttons">
                <!-- Bouton Like -->
                <button type="button"
                        class="btn-interaction btn-like-action <?= !empty($post['user_liked']) ? 'is-liked' : '' ?>"
                        data-toggle-reaction
                        data-target-type="publication"
                        data-target-id="<?= $postId ?>"
                        aria-pressed="<?= !empty($post['user_liked']) ? 'true' : 'false' ?>">
                  <svg class="heart-icon" width="16" height="16" viewBox="0 0 24 24" fill="<?= !empty($post['user_liked']) ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                  </svg>
                  <span class="like-counter-text"><?= (int)$post['likes_count'] ?></span>
                </button>

                <!-- Bouton Discussion / Commentaires -->
                <?php if ($isDiscussion): ?>
                  <button type="button"
                          class="btn-interaction btn-discussion-action"
                          data-open-discussion="<?= $postId ?>"
                          aria-label="Ouvrir la discussion : <?= htmlspecialchars($post['titre'], ENT_QUOTES, 'UTF-8') ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                    <span><?= (int)$post['comments_count'] ?> réponse<?= (int)$post['comments_count'] > 1 ? 's' : '' ?></span>
                  </button>
                <?php endif; ?>

                <!-- Bouton Partager / Copier le lien -->
                <button type="button"
                        class="btn-interaction btn-share-action"
                        data-share-url="<?= $base ?>/salon?pub=<?= $postId ?>"
                        title="Copier le lien de cette publication">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/>
                  </svg>
                </button>
              </div>
            </div>

          </article>
        <?php endforeach; ?>

        <!-- SECTION DOCUMENTS SAUVEGARDÉS (Si vue saved) -->
        <?php if ($feedView === 'saved' && !empty($savedAttachments)): ?>
          <section class="saved-attachments-wrapper">
            <h2 class="section-heading">Documents sauvegardés dans vos favoris</h2>
            <div class="documents-grid">
              <?php foreach ($savedAttachments as $sAtt):
                $isImage = str_starts_with((string)$sAtt['type_mime'], 'image/');
                $sViewUrl = $base . '/attachment/view/' . (int)$sAtt['id'];
                $sDownloadUrl = $base . '/attachment/download/' . (int)$sAtt['id'];
                $sSize = number_format(((int)$sAtt['taille']) / 1048576, 2, ',', ' ') . ' Mo';
              ?>
                <article class="doc-card">
                  <div class="doc-card-badge-row">
                    <span class="doc-type-badge <?= $isImage ? 'is-img' : 'is-file' ?>">
                      <?= $isImage ? 'IMAGE' : 'FICHIER' ?>
                    </span>
                    <span class="doc-channel-tag"><?= htmlspecialchars($sAtt['zone_name']) ?></span>
                  </div>

                  <div class="doc-card-body">
                    <strong class="doc-card-filename" title="<?= htmlspecialchars($sAtt['nom_fichier']) ?>">
                      <?= htmlspecialchars($sAtt['nom_fichier']) ?>
                    </strong>
                    <div class="doc-card-origin">
                      <span>Source : <?= htmlspecialchars($sAtt['titre']) ?></span>
                      <span><?= htmlspecialchars($sSize) ?></span>
                    </div>
                  </div>

                  <div class="doc-card-footer">
                    <a href="<?= $sDownloadUrl ?>" class="btn-doc-action is-download">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                      <span>Télécharger</span>
                    </a>
                    <button type="button" class="btn-doc-action is-remove"
                            data-toggle-save data-target-type="attachment" data-target-id="<?= (int)$sAtt['id'] ?>">
                      Retirer
                    </button>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
          </section>
        <?php endif; ?>

      </div>
    </main>

    <!-- ========================================================= -->
    <!-- 3. SIDEBAR DROITE : Populaires, Membres en Ligne & Infos -->
    <!-- ========================================================= -->
    <aside class="sidebar-right" aria-label="Informations complémentaires">

      <!-- Widget : Publications Populaires -->
      <section class="sidebar-widget popular-widget">
        <div class="widget-header">
          <div class="widget-title">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2.5">
              <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>
            </svg>
            <span>Top Tendances</span>
          </div>
          <a href="<?= $base ?>/salon?view=popular" class="widget-link">Voir tout</a>
        </div>

        <div class="widget-body">
          <?php if (empty($popularPosts)): ?>
            <p class="sidebar-empty-state">Aucune publication populaire pour le moment.</p>
          <?php else: ?>
            <div class="trending-posts-list">
              <?php foreach ($popularPosts as $rank => $pPost): 
                $pPostType = $pPost['type_publication'] ?? 'discussion';
                $pIsDiscussion = ($pPostType === 'discussion');
              ?>
                <a href="<?= $base ?>/salon?pub=<?= (int)$pPost['id'] ?>"
                   class="trending-post-item"
                   data-publication-id="<?= (int)$pPost['id'] ?>"
                   data-publication-type="<?= htmlspecialchars($pPostType) ?>"
                   <?= $pIsDiscussion ? 'data-open-discussion="' . (int)$pPost['id'] . '"' : 'data-scroll-to-post="' . (int)$pPost['id'] . '"' ?>>
                  <span class="trending-rank-num"><?= $rank + 1 ?></span>
                  <div class="trending-post-info">
                    <div style="display:flex; align-items:center; gap:0.4rem;">
                      <strong class="trending-title"><?= htmlspecialchars($pPost['titre']) ?></strong>
                      <?php if (!$pIsDiscussion): ?>
                        <span class="post-type-tag type-<?= htmlspecialchars($pPostType) ?>" style="font-size:0.65rem; padding:1px 6px;">
                          <?= htmlspecialchars(ucfirst($pPostType)) ?>
                        </span>
                      <?php endif; ?>
                    </div>
                    <div class="trending-meta-stats">
                      <span>♥ <?= (int)$pPost['likes_count'] ?></span>
                      <span class="meta-dot">·</span>
                      <span>💬 <?= (int)$pPost['comments_count'] ?></span>
                    </div>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </section>

      <!-- Widget : Membres En Ligne -->
      <section class="sidebar-widget online-widget">
        <div class="widget-header">
          <div class="widget-title">
            <span class="online-pulse-dot" aria-hidden="true"></span>
            <span>En ligne (<?= count($onlineUsers) ?>)</span>
          </div>
          <span class="widget-badge-info">Actifs (5 min)</span>
        </div>

        <div class="widget-body">
          <?php if (empty($onlineUsers)): ?>
            <p class="sidebar-empty-state">Aucun utilisateur connecté pour l’instant.</p>
          <?php else: ?>
            <div class="online-users-list">
              <?php foreach ($onlineUsers as $oUser):
                $oInitials = strtoupper(mb_substr($oUser['prenom'], 0, 1) . mb_substr($oUser['nom'], 0, 1));
                $oName = trim($oUser['prenom'] . ' ' . $oUser['nom']);
                $oRole = $roleLabels[$oUser['role']] ?? ucfirst($oUser['role']);
              ?>
                <div class="online-user-row">
                  <div class="online-user-avatar">
                    <?= htmlspecialchars($oInitials) ?>
                    <span class="online-indicator" aria-label="En ligne"></span>
                  </div>
                  <div class="online-user-info">
                    <span class="online-user-name" title="<?= htmlspecialchars($oName) ?>"><?= htmlspecialchars($oName) ?></span>
                    <span class="online-user-filiere">
                      <?= htmlspecialchars($oRole) ?><?= !empty($oUser['filiere']) ? ' · ' . htmlspecialchars($oUser['filiere']) : '' ?>
                    </span>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </section>

      <!-- Widget : Charte & Directives de l'École -->
      <section class="sidebar-widget policy-widget">
        <div class="policy-card-inner">
          <div class="policy-icon-header">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
            <strong>Charte EPI Yamoussoukro</strong>
          </div>
          <p class="policy-text">
            Favorisez l’entraide studieuse, la courtoisie et le respect mutuel. Les annonces officielles sont certifiées par les délégués et l’administration.
          </p>
        </div>
      </section>

    </aside>

  </div>

  <!-- ========================================================= -->
  <!-- 4. MODAL DE PUBLICATION (REDESIGN COMPLET)                -->
  <!-- ========================================================= -->
  <div id="postComposerModal" class="modal-backdrop" hidden aria-hidden="true">
    <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="postModalHeading">
      
      <!-- En-tête du modal -->
      <div class="modal-header">
        <div class="modal-title-group">
          <h2 id="postModalHeading" class="modal-title">Créer une publication</h2>
          <p class="modal-subtitle">Partagez vos questions, ressources et idées avec l’EPI</p>
        </div>
        <button type="button" class="btn-modal-close" id="btnClosePostModal" aria-label="Fermer la boîte de dialogue">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
      </div>

      <!-- Onglets de sélection du type de publication -->
      <div class="modal-type-tabs" role="tablist">
        <button type="button" class="modal-type-tab is-active" data-type-target="discussion" role="tab" aria-selected="true">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          <span>Discussion</span>
        </button>
        <button type="button" class="modal-type-tab" data-type-target="question" role="tab" aria-selected="false">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
          <span>Question / Sondage</span>
        </button>
        <button type="button" class="modal-type-tab" data-type-target="annonce" role="tab" aria-selected="false" id="modalAnnonceTab">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <span>Annonce</span>
        </button>
      </div>

      <!-- Formulaire de Publication -->
      <form id="salonPostForm" action="<?= $base ?>/publication/create" method="post" enctype="multipart/form-data" class="modal-form-body">
        <input type="hidden" name="ajax" value="1">
        <input type="hidden" name="return_view" value="<?= htmlspecialchars($feedView, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="return_zone" value="<?= (int)($selectedZone['id'] ?? 0) ?>">
        <input type="hidden" name="return_category" value="<?= (int)($categoryId ?? 0) ?>">

        <select class="form-control" id="publicationType" name="type_publication" style="display:none;" aria-label="Type">
          <option value="discussion" selected>Discussion</option>
          <option value="question">Question / sondage</option>
          <option value="annonce">Annonce</option>
        </select>

        <!-- Bannière dynamique pour les Annonces Officielles -->
        <div id="annonceOfficialBanner" class="annonce-official-banner" hidden>
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
          </svg>
          <div>
            <strong>Annonce Officielle de la Filière</strong>
            <span>Cette annonce sera certifiée par votre délégation, mise en avant sur le salon et visible par l'ensemble des étudiants.</span>
          </div>
        </div>

        <!-- Destination : Salon & Filière -->
        <div class="form-group">
          <label class="form-label" for="modalCategorySelect">Salon de destination</label>
          <select class="form-control form-select" id="modalCategorySelect" name="categorie_id" required>
            <?php foreach ($postCategories as $pCat): ?>
              <option value="<?= (int)$pCat['id'] ?>"
                      data-zone-id="<?= (int)$pCat['zone_id'] ?>"
                      data-can-announce="<?= $pCat['can_announce'] ? '1' : '0' ?>"
                      <?= (int)$pCat['id'] === $defaultPostCategoryId ? 'selected' : '' ?>>
                <?= htmlspecialchars($pCat['zone_name'] . ' · #' . $pCat['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Titre -->
        <div class="form-group">
          <label class="form-label" for="salonPostTitle">Titre de la publication</label>
          <input class="form-control" id="salonPostTitle" name="titre" type="text" maxlength="255" required
                 placeholder="Ex: Exercice d’algorithmique sur les graphes / Date du prochain devoir...">
        </div>

        <!-- Contenu -->
        <div class="form-group">
          <label class="form-label" id="salonContentLabel" for="salonPostContent">Message</label>
          <textarea class="form-control form-textarea" id="salonPostContent" name="contenu" rows="4" required
                    placeholder="Détaillez votre question, votre démarche ou partagez une explication claire..."></textarea>
        </div>

        <!-- Options spécifiques au SONDAGE (Question) - Strictement masqué pour Discussion et Annonce -->
        <div id="pollOptionsFields" class="poll-composer-section is-hidden" hidden style="display:none !important;">
          <div class="poll-composer-header">
            <strong>Choix du sondage</strong>
            <small>Chaque étudiant pourra voter pour un choix.</small>
          </div>
          <div id="pollOptionsList" class="poll-inputs-stack">
            <div class="poll-input-row">
              <span class="poll-input-num">1</span>
              <input class="form-control" name="options[]" maxlength="255" placeholder="Premier choix" aria-label="Choix 1" disabled>
            </div>
            <div class="poll-input-row">
              <span class="poll-input-num">2</span>
              <input class="form-control" name="options[]" maxlength="255" placeholder="Deuxième choix" aria-label="Choix 2" disabled>
            </div>
          </div>
          <button type="button" class="btn-add-poll-option" id="addPollOption">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>Ajouter une option (max 10)</span>
          </button>
        </div>

        <!-- Pièces Jointes : Upload & Preview -->
        <div class="form-group composer-dynamic-field" id="composerAttachmentsGroup">
          <label class="form-label" id="composerAttachmentsLabel">Pièces jointes & Documents</label>
          <div class="attachments-dropzone" id="attachmentsDropzone">
            <label for="salonPostAttachments" class="dropzone-label">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2">
                <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>
              </svg>
              <div class="dropzone-text">
                <strong>Glissez des fichiers ou cliquez pour sélectionner</strong>
                <span>PDF, Word, Excel, PowerPoint, ZIP ou Photos · 5 fichiers max (10 Mo max)</span>
              </div>
            </label>
            <input id="salonPostAttachments" class="attachment-native-input" type="file" name="pieces_jointes[]" multiple
                   accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.png,.jpg,.jpeg,.gif,.webp">
          </div>
          <div id="salonAttachmentPreview" class="attachments-preview-grid" aria-live="polite" hidden></div>
        </div>

        <!-- Tags & Mentions (Sélecteurs Popup Interactifs) -->
        <div class="tags-mentions-accordion composer-dynamic-field" id="composerTagsMentionsGroup">
          <details class="composer-details" open>
            <summary class="composer-details-summary">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
              <span>Tags thématiques & Mentions des professeurs (optionnel)</span>
            </summary>
            
            <div class="composer-details-body">
              <!-- Sélecteur Popup de Tags avec Suggestions -->
              <div class="form-group interactive-picker-group" id="tagPickerGroup">
                <label class="form-sublabel" for="tagPickerInput">Tags thématiques</label>
                <div id="tagSelectedChips" class="interactive-chips-row" aria-live="polite"></div>
                <div class="picker-input-wrapper">
                  <span class="picker-prefix-icon" aria-hidden="true">#</span>
                  <input class="form-control picker-input" id="tagPickerInput" type="text" autocomplete="off"
                         placeholder="Rechercher un tag ou taper pour en suggérer un (ex: algo, tp, cours)...">
                  <button type="button" class="btn-picker-toggle" id="btnToggleTagDropdown" title="Ouvrir la liste des tags" aria-haspopup="listbox">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                  </button>
                </div>
                
                <!-- Popup Déroulant des Tags -->
                <div id="tagPickerDropdown" class="interactive-picker-dropdown" hidden role="listbox">
                  <div class="picker-dropdown-header">
                    <span>Tags du salon</span>
                    <small>Cliquez pour ajouter</small>
                  </div>
                  <!-- Option de suggestion automatique de nouveau tag -->
                  <div id="tagSuggestionItem" class="picker-suggestion-banner" hidden>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span>Suggérer le nouveau tag <strong id="tagSuggestionLabel">#...</strong></span>
                  </div>
                  <div id="tagPickerList" class="picker-dropdown-items-grid">
                    <?php if ($activeTags): ?>
                      <?php foreach ($activeTags as $aTag): ?>
                        <button type="button" class="tag-picker-badge" data-tag-id="<?= (int)$aTag['id'] ?>" data-tag-name="<?= htmlspecialchars($aTag['nom'], ENT_QUOTES, 'UTF-8') ?>">
                          <span>#<?= htmlspecialchars($aTag['nom']) ?></span>
                        </button>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <div class="picker-dropdown-empty">Aucun tag pour le moment. Tapez un nom pour en proposer un.</div>
                    <?php endif; ?>
                  </div>
                </div>
                <input type="hidden" id="publicationTagProposals" name="tag_proposals" value="">
                <div id="tagHiddenInputsContainer"></div>
              </div>

              <!-- Sélecteur Popup de Mentions des Professeurs & Membres -->
              <div class="form-group interactive-picker-group" id="mentionPickerGroup">
                <label class="form-sublabel" for="mentionPickerInput">Mentionner des professeurs ou camarades</label>
                <div id="mentionSelectedChips" class="interactive-chips-row" aria-live="polite"></div>
                <div class="picker-input-wrapper">
                  <span class="picker-prefix-icon" aria-hidden="true">@</span>
                  <input class="form-control picker-input" id="mentionPickerInput" type="text" autocomplete="off"
                         placeholder="Rechercher un professeur (ex: Dr. Koné) ou un étudiant...">
                  <button type="button" class="btn-picker-toggle" id="btnToggleMentionDropdown" title="Ouvrir la liste des professeurs et membres" aria-haspopup="listbox">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                  </button>
                </div>
                
                <!-- Popup Déroulant des Mentions -->
                <div id="mentionPickerDropdown" class="interactive-picker-dropdown" hidden role="listbox">
                  <div class="picker-dropdown-tabs">
                    <button type="button" class="mention-filter-tab is-active" data-mention-filter="all">Tous</button>
                    <button type="button" class="mention-filter-tab" data-mention-filter="professeur">Professeurs</button>
                    <button type="button" class="mention-filter-tab" data-mention-filter="delegation">Délégués</button>
                  </div>
                  <div id="mentionPickerList" class="picker-dropdown-users-list">
                    <div class="picker-dropdown-loading">
                      <div class="loader-circle" style="width:18px; height:18px;"></div>
                      <span>Chargement des professeurs et membres…</span>
                    </div>
                  </div>
                </div>
                <div id="mentionHiddenInputsContainer"></div>
              </div>
            </div>
          </details>
        </div>

        <!-- Retour / Message d'erreur ou succès -->
        <p id="salonPostFeedback" class="composer-feedback-text" role="status" aria-live="polite"></p>

        <!-- Footer du Modal -->
        <div class="modal-footer">
          <button type="button" class="btn-modal-cancel" id="btnCancelPostModal">Annuler</button>
          <button type="submit" class="btn-modal-submit" id="btnSubmitPost">
            <svg class="submit-spinner" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" hidden>
              <line x1="12" y1="2" x2="12" y2="6"></line><line x1="12" y1="18" x2="12" y2="22"></line>
              <line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line>
              <line x1="2" y1="12" x2="6" y2="12"></line><line x1="18" y1="12" x2="22" y2="12"></line>
              <line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line><line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line>
            </svg>
            <span class="btn-text">Publier</span>
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- ========================================================= -->
  <!-- 5. MODAL DE DISCUSSION (TIROIR / DIALOGUE DE DISCUSSION)    -->
  <!-- ========================================================= -->
  <div id="discussionModal" class="modal-backdrop discussion-modal-backdrop" hidden aria-hidden="true">
    <div class="modal-dialog discussion-dialog" role="dialog" aria-modal="true" aria-labelledby="discussionModalTitle">
      
      <div class="modal-header discussion-modal-header">
        <div class="modal-title-group">
          <span class="modal-badge-type">Fil de discussion</span>
          <h2 id="discussionModalTitle" class="modal-title">Publication</h2>
        </div>
        <button type="button" class="btn-modal-close" data-close-discussion aria-label="Fermer la discussion">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
      </div>

      <div id="discussionModalContent" class="discussion-dialog-scrollable-body" aria-live="polite">
        <!-- Rendu dynamique via salon.js -->
        <div class="discussion-loading-spinner">
          <div class="loader-circle"></div>
          <span>Chargement de la discussion…</span>
        </div>
      </div>

    </div>
  </div>

  <!-- ========================================================= -->
  <!-- 6. POPUP DE NOTIFICATIONS FLOTTANT                        -->
  <!-- ========================================================= -->
  <div id="notifPopupDropdown" class="notif-popup-dropdown" hidden aria-hidden="true">
    <div class="notif-popup-header">
      <div class="notif-popup-title-box">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2.5"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        <span class="notif-popup-title">Notifications</span>
        <span class="tab-badge-count is-unread-badge" id="popupUnreadCount" style="<?= $unreadCount > 0 ? '' : 'display:none;' ?>"><?= (int)$unreadCount ?></span>
      </div>
      <div class="notif-popup-actions">
        <button type="button" class="btn-popup-readall" id="btnPopupMarkAllRead" title="Tout marquer comme lu">Tout marquer lu</button>
        <button type="button" class="btn-popup-close" id="btnCloseNotifPopup" aria-label="Fermer le popup">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
      </div>
    </div>
    <div class="notif-popup-body" id="notifPopupBody">
      <div style="text-align:center; padding:1.75rem 1rem; color:var(--text-muted); font-size:0.85rem;">
        Chargement des notifications…
      </div>
    </div>
    <div class="notif-popup-footer">
      <a href="<?= $base ?>/notifications" class="notif-popup-all-link">Consulter toutes les alertes &rarr;</a>
    </div>
  </div>

  <!-- Scripts -->
  <script src="<?= $asset ?>/js/desktop/notification-badge.js"></script>
  <script src="<?= $asset ?>/js/desktop/salon.js"></script>
  <script src="<?= $asset ?>/js/feedback.js" defer></script>
</body>
</html>