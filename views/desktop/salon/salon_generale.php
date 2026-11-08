<?php
/**
 * Variables supplied by SalonController::index() before this view is included.
 *
 * @var string|null $baseUrl
 * @var string|null $assetBase
 * @var array|null $zones
 * @var array|null $feedPosts
 * @var array|null $postAttachments
 * @var array|null $savedAttachments
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
$pollOptions = is_array($pollOptions ?? null) ? $pollOptions : [];
$salonDocuments = is_array($salonDocuments ?? null) ? $salonDocuments : [];
$postAttachments = is_array($postAttachments ?? null) ? $postAttachments : [];
$savedAttachments = is_array($savedAttachments ?? null) ? $savedAttachments : [];
$unreadCount = max(0, (int)($unreadCount ?? 0));
$flashSuccess = is_string($flashSuccess ?? null) ? $flashSuccess : '';
$flashError = is_string($flashError ?? null) ? $flashError : '';
if (!isset($_SESSION) || !is_array($_SESSION)) {
  $_SESSION = [];
}
$viewLabels = ['latest' => 'Pour toi', 'popular' => 'Populaires', 'saved' => 'Sauvegardées', 'documents' => 'Documents'];
$zoneContext = $selectedZone !== null ? '&zone=' . (int)$selectedZone['id'] : '';
$categoryContext = $categoryId ? '&category=' . (int)$categoryId : '';
$pageTitle = $selectedZone !== null ? $selectedZone['nom'] : 'Tous vos salons';
$roleLabels = [
  'etudiant' => 'Étudiant',
  'professeur' => 'Professeur',
  'moderateur' => 'Modérateur',
  'delegue_general' => 'Délégué général',
  'administrateur' => 'Administrateur',
];

if (!function_exists('formatSalonDate')) {
  function formatSalonDate(string $date): string {
    try {
      $createdAt = new DateTimeImmutable($date);
      $seconds = max(0, time() - $createdAt->getTimestamp());
      if ($seconds < 60) return "À l'instant";
      if ($seconds < 3600) return 'Il y a ' . (int)floor($seconds / 60) . ' min';
      if ($seconds < 86400) return 'Il y a ' . (int)floor($seconds / 3600) . ' h';
      return 'Il y a ' . (int)floor($seconds / 86400) . ' j';
    } catch (Exception $exception) {
      return $date;
    }
  }
}

// Catégories disponibles pour le composer
$postCategories = [];
foreach ($composerZones as $composerZone) {
  foreach ($composerZone['categories'] as $composerCategory) {
    $postCategories[] = [
      'id' => (int)$composerCategory['id'],
      'zone_id' => (int)$composerZone['id'],
      'zone_name' => (string)$composerZone['nom'],
      'name' => (string)$composerCategory['nom'],
    ];
  }
}
$defaultPostCategoryId = $categoryId !== null && $categoryId > 0
  ? $categoryId
  : (int)($postCategories[0]['id'] ?? 0);
$sessionAvatarUrl = !empty($_SESSION['user_avatar'])
  ? url('public/' . ltrim((string)$_SESSION['user_avatar'], '/'))
  : '';
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
  <link rel="stylesheet" href="<?= $asset ?>/css/desktop/community-shell.css" />
  <link rel="stylesheet" href="<?= $asset ?>/css/desktop/salon.css?v=<?= (int)filemtime(ROOT_PATH . '/public/assets/css/desktop/salon.css') ?>" />
  <link rel="stylesheet" href="<?= $asset ?>/css/feedback.css" />
  <?php include ROOT_PATH . '/views/partials/theme-head.php'; ?>
</head>
<body class="community-shell community-salon" data-base-url="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>">
  <?php include ROOT_PATH . '/views/partials/theme-toggle.php'; ?>
  <?php include ROOT_PATH . '/views/partials/feedback-toast.php'; ?>
  
  <!-- Signature Tricolore EPI -->
  <div class="tricolor-bar"></div>

  <!-- ==============================================================
   * BARRE SUPÉRIEURE (EPI STYLE TOPBAR)
   * Logo, Menu salon, Barre de recherche centrale, Actions droites
   * ============================================================== -->
  <header class="epi-topbar">
    <div class="epi-topbar-inner">
      
      <!-- GAUCHE : Logo & Brand -->
      <div class="epi-topbar-left">
        <a class="epi-brand" href="<?= $base ?>/salon" aria-label="Accueil EPI Tchach">
          <div class="epi-brand-icon">
            <img src="<?= htmlspecialchars($logoFile, ENT_QUOTES, 'UTF-8') ?>" alt="EPI" onerror="this.style.display='none'" />
          </div>
          <span class="epi-brand-title">EPI <span class="accent">Tchach</span></span>
        </a>

        <a class="epi-community-pill" href="<?= $base ?>/salon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          <span><?= htmlspecialchars($selectedZone !== null ? $selectedZone['nom'] : 'Accueil') ?></span>
        </a>
      </div>

      <!-- CENTRE : Barre de Recherche EPI -->
      <div class="epi-topbar-center">
        <div class="epi-search-box">
          <span class="epi-search-icon" aria-hidden="true">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          </span>
          <input id="publicationSearch" type="search" class="epi-search-input" placeholder="Rechercher des publications ou des profils..." autocomplete="off">
          <button type="button" class="epi-search-clear" id="searchClearBtn" aria-label="Effacer la recherche" hidden>&times;</button>
        </div>
      </div>

      <!-- DROITE : Messagerie, + Créer, Notifications, Profil -->
      <div class="epi-topbar-right">
        
        <!-- Messagerie -->
        <a href="<?= $base ?>/messages" class="epi-icon-action" title="Messagerie" aria-label="Messagerie">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
          </svg>
          <?php if ($unreadMessagesCount > 0): ?><span class="epi-badge-count"><?= $unreadMessagesCount > 99 ? '99+' : (int)$unreadMessagesCount ?></span><?php endif; ?>
        </a>

        <!-- Bouton + Créer une publication -->
        <button type="button" class="epi-create-post-btn" id="openCreatePostModal" data-open-create-post-modal aria-label="Créer une publication">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          <span>Créer</span>
        </button>

        <!-- Notifications -->
        <a href="<?= $base ?>/notifications" class="epi-icon-action epi-notif-action" title="Notifications" aria-label="Notifications">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
          </svg>
          <?php if ($unreadCount > 0): ?>
            <span class="epi-badge-count"><?= $unreadCount ?></span>
          <?php endif; ?>
        </a>

        <!-- Profil Utilisateur -->
        <?php if (!empty($_SESSION['user_id'])): ?>
          <div class="epi-user-menu-wrapper">
            <button type="button" class="epi-profile-pill" id="userMenuToggle" aria-label="Menu du profil utilisateur">
              <div class="epi-avatar-bubble">
                <span class="epi-avatar-fallback"><?= htmlspecialchars(strtoupper(mb_substr((string)($_SESSION['user_prenom'] ?? 'M'), 0, 1))) ?></span>
                <img class="epi-avatar-image" src="<?= htmlspecialchars($sessionAvatarUrl, ENT_QUOTES, 'UTF-8') ?>" alt="" <?= $sessionAvatarUrl === '' ? 'hidden' : '' ?> />
                <span class="epi-status-dot" aria-label="En ligne"></span>
              </div>
              <div class="epi-profile-info">
                <span class="epi-profile-name"><?= htmlspecialchars($_SESSION['user_prenom'] ?? 'Membre') ?></span>
                <span class="epi-profile-role"><?= htmlspecialchars($roleLabels[$_SESSION['user_role'] ?? 'etudiant'] ?? 'Étudiant') ?></span>
              </div>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
            </button>
            <div class="epi-user-dropdown" id="userMenuDropdown" hidden>
              <div class="epi-dropdown-header">
                <strong><?= htmlspecialchars(($_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? '')) ?></strong>
                <small><?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></small>
              </div>
              <a href="<?= $base ?>/profile" class="epi-dropdown-item">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span>Mon profil</span>
              </a>
              <a href="<?= $base ?>/salon?view=saved" class="epi-dropdown-item">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                <span>Mes sauvegardes</span>
              </a>
              <?php if (strtolower($_SESSION['user_role'] ?? '') === 'administrateur'): ?>
                <a href="<?= $base ?>/admin" class="epi-dropdown-item" style="color:var(--community-orange);">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                  <span>Panneau Admin</span>
                </a>
              <?php endif; ?>
              <div class="epi-dropdown-divider"></div>
              <a href="<?= $base ?>/logout" class="epi-dropdown-item epi-logout-item">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                <span>Déconnexion</span>
              </a>
            </div>
          </div>
        <?php else: ?>
          <a href="<?= $base ?>/login" class="epi-auth-pill">Connexion</a>
        <?php endif; ?>

      </div>
    </div>
  </header>

  <!-- ==============================================================
   * LAYOUT PRINCIPAL (GRID POINT TEMPLATE EPI SCOPÉ SALON)
   * Colonne gauche: Navigation & Salons | Centre: Fil | Droite: Widgets
   * ============================================================== -->
  <div class="epi-salon-grid">

    <!-- ========== SIDEBAR GAUCHE (grid-area: sidebar) ========== -->
    <aside class="epi-sidebar-left">
      <div class="epi-sidebar-box">
        <div class="epi-nav-heading">Feeds</div>
        <a href="<?= $base ?>/salon" class="epi-nav-item <?= $selectedZone === null && $feedView === 'latest' && !$categoryId ? 'is-active' : '' ?>">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          <span>Tous les salons</span>
        </a>
        <a href="<?= $base ?>/salon?view=popular" class="epi-nav-item <?= $feedView === 'popular' ? 'is-active' : '' ?>">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>
          <span>Populaires</span>
        </a>
        <a href="<?= $base ?>/salon?view=saved" class="epi-nav-item <?= $feedView === 'saved' ? 'is-active' : '' ?>">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
          <span>Sauvegardés</span>
        </a>
        <a href="<?= $base ?>/salon?view=documents" class="epi-nav-item <?= $feedView === 'documents' ? 'is-active' : '' ?>">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          <span>Documents</span>
        </a>

        <!-- Salons accessibles & Zones -->
        <div class="epi-zone-group">
          <div class="epi-nav-heading">Vos Salons</div>
          <?php if (empty($zones)): ?>
            <p style="padding:0.5rem 0.75rem; font-size:0.8rem; color:var(--community-muted);">Aucun salon rattaché pour le moment.</p>
          <?php else: ?>
            <?php foreach ($zones as $zone): ?>
              <a class="epi-zone-header <?= $selectedZone !== null && (int)$selectedZone['id'] === (int)$zone['id'] ? 'is-selected' : '' ?>"
                 href="<?= $base ?>/salon?view=<?= rawurlencode($feedView) ?>&zone=<?= (int)$zone['id'] ?>">
                <span><?= htmlspecialchars($zone['nom']) ?></span>
                <span class="epi-zone-count"><?= (int)$zone['membres_count'] ?></span>
              </a>
              <?php foreach ($zone['categories'] as $category): ?>
                <a href="<?= $base ?>/salon?view=<?= rawurlencode($feedView) ?>&zone=<?= (int)$zone['id'] ?>&category=<?= (int)$category['id'] ?>"
                   class="epi-category-item <?= $categoryId === (int)$category['id'] ? 'is-selected' : '' ?>">
                  <span><span class="epi-category-hash">#</span><?= htmlspecialchars($category['nom']) ?></span>
                  <span class="epi-zone-count"><?= (int)$category['publications_count'] ?></span>
                </a>
              <?php endforeach; ?>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

      </div>
    </aside>

    <!-- ========== FIL CENTRAL (grid-area: main) ========== -->
    <main class="epi-main-feed">

      <!-- Messages Flash -->
      <?php if ($flashSuccess): ?>
        <div style="background:rgba(16,185,129,0.12); border:1px solid #10b981; color:#34d399; padding:0.75rem 1rem; border-radius:var(--community-radius-md); font-size:0.9rem;" role="status">
          <?= htmlspecialchars($flashSuccess) ?>
        </div>
      <?php endif; ?>
      <?php if ($flashError): ?>
        <div style="background:rgba(239,68,68,0.12); border:1px solid #ef4444; color:#f87171; padding:0.75rem 1rem; border-radius:var(--community-radius-md); font-size:0.9rem;" role="alert">
          <?= htmlspecialchars($flashError) ?>
        </div>
      <?php endif; ?>

      <!-- 1. Quick Composer EPI Style (En haut du fil) -->
      <div class="epi-quick-composer">
        <div class="epi-composer-avatar">
          <span class="epi-avatar-fallback"><?= htmlspecialchars(strtoupper(mb_substr((string)($_SESSION['user_prenom'] ?? 'M'), 0, 1))) ?></span>
          <img class="epi-avatar-image" src="<?= htmlspecialchars($sessionAvatarUrl, ENT_QUOTES, 'UTF-8') ?>" alt="" <?= $sessionAvatarUrl === '' ? 'hidden' : '' ?> />
        </div>
        <button type="button" class="epi-composer-dummy-input" id="triggerComposerClick">
          Créer une publication...
        </button>
        <div class="epi-composer-quick-actions">
          <button type="button" class="epi-quick-action-btn" id="quickAddMedia" title="Ajouter une image ou un média">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
          </button>
          <button type="button" class="epi-quick-action-btn" id="quickAddFile" title="Joindre un document PDF">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
          </button>
          <button type="button" class="epi-quick-action-btn" id="quickAddPoll" title="Créer un sondage">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
          </button>
        </div>
      </div>

      <!-- 2. Onglets de Navigation EPI (Pour toi, Populaires, etc.) -->
      <div class="epi-feed-nav">
        <div class="epi-feed-tabs">
          <a class="epi-tab <?= $feedView === 'latest' ? 'is-active' : '' ?>" href="<?= $base ?>/salon?view=latest<?= $zoneContext ?><?= $categoryContext ?>" aria-label="Pour toi" title="Pour toi">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            <span>Pour toi</span>
          </a>
          <a class="epi-tab <?= $feedView === 'popular' ? 'is-active' : '' ?>" href="<?= $base ?>/salon?view=popular<?= $zoneContext ?><?= $categoryContext ?>" aria-label="Populaires" title="Populaires">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>
            <span>Populaires</span>
          </a>
          <a class="epi-tab <?= $feedView === 'saved' ? 'is-active' : '' ?>" href="<?= $base ?>/salon?view=saved<?= $zoneContext ?><?= $categoryContext ?>" aria-label="Sauvegardées" title="Sauvegardées">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
            <span>Sauvegardées</span>
          </a>
          <a class="epi-tab <?= $feedView === 'documents' ? 'is-active' : '' ?>" href="<?= $base ?>/salon?view=documents<?= $zoneContext ?><?= $categoryContext ?>" aria-label="Documents" title="Documents">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            <span>Documents</span>
          </a>
        </div>
        <?php if ($categoryId): ?>
          <a class="epi-tab-clear" href="<?= $base ?>/salon?view=<?= rawurlencode($feedView) ?><?= $zoneContext ?>" title="Effacer le filtre">
            <span>Catégorie active</span> &times;
          </a>
        <?php endif; ?>
      </div>

      <!-- Conteneur des Résultats de Recherche en Direct -->
      <div id="searchResults" class="posts" hidden aria-live="polite"></div>

      <!-- 3. Fil de Publications (EPI Cards) -->
      <div class="posts" id="publicationList">
        
        <?php if ($feedView === 'documents'): ?>
          <section class="shared-documents">
            <h2 style="font-size:1.1rem; font-weight:800; margin-bottom:0.75rem;">Documents partagés<?= $selectedZone !== null ? ' · ' . htmlspecialchars($selectedZone['nom']) : '' ?></h2>
            <?php if (!$salonDocuments): ?>
              <div style="background:var(--community-surface); border:1px solid var(--community-line); border-radius:var(--community-radius-lg); padding:2rem; text-align:center;">
                <h3>Aucun document partagé</h3>
                <p style="color:var(--community-muted);">Les PDF et fichiers ajoutés aux publications apparaîtront ici.</p>
              </div>
            <?php else: ?>
              <?php foreach ($salonDocuments as $document): ?>
                <?php
                  $documentIsPdf = (string)$document['type_mime'] === 'application/pdf';
                  $documentPublicationUrl = $base . '/salon?view=latest&zone=' . (int)$document['zone_id']
                    . '&category=' . (int)$document['category_id'] . '#publication-' . (int)$document['publication_id'];
                ?>
                <article class="epi-file-pill" style="margin-bottom:0.5rem;">
                  <span class="epi-file-badge"><?= $documentIsPdf ? 'PDF' : 'DOC' ?></span>
                  <div style="flex:1;">
                    <strong><?= htmlspecialchars($document['nom_fichier']) ?></strong>
                    <div style="font-size:0.75rem; color:var(--community-muted);">
                      <?= htmlspecialchars($document['zone_name']) ?> · <?= htmlspecialchars($document['category_name']) ?> · <?= htmlspecialchars(trim($document['prenom'] . ' ' . $document['nom'])) ?>
                    </div>
                  </div>
                  <?php if ($documentIsPdf): ?>
                    <a href="<?= $base ?>/attachment/view/<?= (int)$document['id'] ?>" target="_blank" rel="noopener" class="epi-pill-btn">Ouvrir</a>
                  <?php endif; ?>
                  <a href="<?= $base ?>/attachment/download/<?= (int)$document['id'] ?>" class="epi-pill-btn">Télécharger</a>
                </article>
              <?php endforeach; ?>
            <?php endif; ?>
          </section>
        <?php endif; ?>

        <?php if ($feedView !== 'documents' && empty($feedPosts) && empty($savedAttachments)): ?>
          <div style="background:var(--community-surface); border:1px solid var(--community-line); border-radius:var(--community-radius-lg); padding:3rem 1.5rem; text-align:center;">
            <h2 style="font-size:1.2rem; font-weight:800;"><?= $feedView === 'saved' ? 'Aucun élément sauvegardé' : 'Aucune publication à afficher' ?></h2>
            <p style="color:var(--community-muted); max-width:400px; margin:0.5rem auto 1rem;">
              <?= $feedView === 'saved' ? 'Sauvegardez une publication pour la retrouver facilement ici.' : 'Participez ou lancez une nouvelle discussion dans vos salons.' ?>
            </p>
            <button type="button" class="epi-create-post-btn" style="margin:0 auto;" onclick="document.getElementById('openCreatePostModal').click();">
              Créer une publication
            </button>
          </div>
        <?php endif; ?>

        <?php foreach ($feedPosts as $post):
          $postId = (int)$post['id'];
          $authorName = trim($post['prenom'] . ' ' . $post['nom']);
          $authorRole = $roleLabels[$post['author_role']] ?? ucfirst($post['author_role']);
          $isSaved = !empty($post['saved_id']);
          $excerpt = mb_strimwidth(strip_tags($post['contenu']), 0, 500, '…');
        ?>
          <article class="epi-post-card" id="publication-<?= $postId ?>"
            data-publication-card="<?= $postId ?>" data-live-publication-id="<?= $postId ?>">
            
            <!-- Header du post (Subepi / Salon, Auteur, Rôle, Date) -->
            <div class="epi-card-header">
              <div class="epi-card-meta">
                <a class="epi-salon-tag" href="<?= $base ?>/salon?view=<?= rawurlencode($feedView) ?>&zone=<?= (int)$post['zone_id'] ?>&category=<?= (int)$post['category_id'] ?>">
                  <span>s/<?= htmlspecialchars($post['category_name']) ?></span>
                </a>
                <span>•</span>
                <span class="epi-author-badge">Posté par <?= htmlspecialchars($authorName) ?></span>
                <span class="epi-role-pill"><?= htmlspecialchars($authorRole) ?></span>
                <span>•</span>
                <span><?= htmlspecialchars(formatSalonDate($post['date_creation'])) ?></span>
              </div>

              <!-- Badges Statut (Épinglée, Résolue) -->
              <div class="epi-post-badges">
                <?php if ($post['statut'] === 'epingle'): ?>
                  <span class="epi-status-pill is-pinned">📌 Épinglée</span>
                <?php endif; ?>
                <?php if ($post['statut'] === 'resolu'): ?>
                  <span class="epi-status-pill is-solved">✓ Résolue</span>
                <?php endif; ?>
              </div>
            </div>

            <!-- Titre du post -->
            <h2 class="epi-post-title">
              <a href="<?= $base ?>/publication/<?= $postId ?>">
                <?= htmlspecialchars($post['titre']) ?>
              </a>
            </h2>

            <!-- Contenu / Extrait -->
            <div class="epi-post-body">
              <?= nl2br(htmlspecialchars($excerpt)) ?>
            </div>

            <!-- Médias & Pièces Jointes Style EPI -->
            <?php if (!empty($postAttachments[$postId])): ?>
              <div class="epi-media-container">
                <?php foreach ($postAttachments[$postId] as $attachment): ?>
                  <?php $isImageAttachment = str_starts_with((string)$attachment['type_mime'], 'image/'); ?>
                  <?php if ($isImageAttachment): ?>
                    <a href="<?= $base ?>/attachment/view/<?= (int)$attachment['id'] ?>" target="_blank" rel="noopener">
                      <img class="epi-media-image" src="<?= $base ?>/attachment/view/<?= (int)$attachment['id'] ?>" alt="<?= htmlspecialchars($attachment['nom_fichier']) ?>" loading="lazy">
                    </a>
                  <?php else: ?>
                    <div class="epi-file-pill">
                      <span class="epi-file-badge"><?= strtoupper(pathinfo($attachment['nom_fichier'], PATHINFO_EXTENSION) ?: 'DOC') ?></span>
                      <div style="flex:1;">
                        <strong><?= htmlspecialchars($attachment['nom_fichier']) ?></strong>
                        <div style="font-size:0.75rem; color:var(--community-muted);"><?= number_format(((int)$attachment['taille']) / 1048576, 2, ',', ' ') ?> Mo</div>
                      </div>
                      <a class="epi-pill-btn" href="<?= $base ?>/attachment/download/<?= (int)$attachment['id'] ?>">Télécharger</a>
                    </div>
                  <?php endif; ?>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <!-- Sondage (si question) -->
            <?php if ($post['type_publication'] === 'question'): ?>
              <?php
                $options = $pollOptions[$postId] ?? [];
                $totalVotes = array_sum(array_map(static fn (array $option): int => (int)$option['votes'], $options));
                $hasVoted = (bool)array_filter($options, static fn (array $option): bool => (bool)$option['user_voted']);
              ?>
              <?php if ($options): ?>
                <form class="poll-form" action="<?= $base ?>/publication/vote" method="post" data-publication-id="<?= $postId ?>" style="margin-top:0.5rem;">
                  <fieldset style="border:none; padding:0; margin:0;" <?= $hasVoted ? 'class="poll-has-voted"' : '' ?>>
                    <?php foreach ($options as $option): ?>
                      <?php $votePercent = $totalVotes > 0 ? (int)round(((int)$option['votes'] / $totalVotes) * 100) : 0; ?>
                      <label class="poll-option">
                        <input type="radio" name="option_id" value="<?= (int)$option['id'] ?>" <?= $option['user_voted'] ? 'checked' : '' ?> required style="margin-right:8px;">
                        <span class="poll-option-result" style="--vote-percent: <?= $votePercent ?>%">
                          <span><?= htmlspecialchars($option['texte']) ?></span>
                          <strong><?= (int)$option['votes'] ?> vote(s) · <?= $votePercent ?>%</strong>
                        </span>
                      </label>
                    <?php endforeach; ?>
                  </fieldset>
                  <input type="hidden" name="publication_id" value="<?= $postId ?>">
                  <div style="display:flex; align-items:center; gap:0.75rem; margin-top:0.5rem;">
                    <button class="poll-vote-button" type="submit"><?= $hasVoted ? 'Modifier mon vote' : 'Voter' ?></button>
                    <span class="poll-feedback" role="status" aria-live="polite" style="font-size:0.8rem; color:var(--community-muted);"></span>
                  </div>
                </form>
              <?php endif; ?>
            <?php endif; ?>

            <!-- Actions Style EPI (Capsule de vote, Commentaires, Partager, Sauvegarder) -->
            <div class="epi-card-actions">
              
              <!-- Capsule Vote EPI [ ▲ Vote ▼ ] -->
              <div class="epi-vote-capsule">
                <button type="button" class="epi-vote-btn upvote <?= !empty($post['user_liked']) ? 'is-voted' : '' ?>"
                  data-toggle-reaction data-target-type="publication" data-target-id="<?= $postId ?>"
                  aria-label="Voter ou aimer">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
                </button>
                <span class="epi-vote-count publication-like-count" data-live-likes><?= (int)$post['likes_count'] ?></span>
                <button type="button" class="epi-vote-btn downvote" aria-label="Vote bas">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
              </div>

              <!-- Bouton Commentaires -->
              <a class="epi-pill-btn post-discussion-button" href="<?= $base ?>/publication/<?= $postId ?>" aria-label="Commentaires" title="Commentaires">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <span><span data-live-comment-count><?= (int)$post['comments_count'] ?></span><span class="epi-action-label"> Commentaires</span></span>
              </a>

              <!-- Bouton Partager -->
              <button type="button" class="epi-pill-btn epi-share-btn" data-share-url="<?= $base ?>/publication/<?= $postId ?>" aria-label="Partager" title="Partager">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                <span>Partager</span>
              </button>

              <!-- Bouton Sauvegarder -->
              <form action="<?= $base ?>/salon/save" method="post" style="display:inline;">
                <input type="hidden" name="target_type" value="publication">
                <input type="hidden" name="target_id" value="<?= $postId ?>">
                <input type="hidden" name="return_view" value="<?= htmlspecialchars($feedView) ?>">
                <input type="hidden" name="return_category" value="<?= (int)($categoryId ?? 0) ?>">
                <button type="submit" class="epi-pill-btn epi-save-post-btn <?= $isSaved ? 'is-saved' : '' ?>" aria-label="<?= $isSaved ? 'Retirer des sauvegardes' : 'Sauvegarder' ?>" title="<?= $isSaved ? 'Retirer des sauvegardes' : 'Sauvegarder' ?>">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="<?= $isSaved ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                  <span><?= $isSaved ? 'Sauvegardé' : 'Sauvegarder' ?></span>
                </button>
              </form>

            </div>

          </article>
        <?php endforeach; ?>

        <!-- Documents Sauvegardés si vue saved -->
        <?php if ($feedView === 'saved' && $savedAttachments): ?>
          <h2 style="font-size:1.1rem; font-weight:800; margin:1.5rem 0 0.75rem;">Documents sauvegardés</h2>
          <?php foreach ($savedAttachments as $attachment): ?>
            <article class="epi-file-pill" style="margin-bottom:0.5rem;">
              <span class="epi-file-badge"><?= strtoupper(pathinfo($attachment['nom_fichier'], PATHINFO_EXTENSION) ?: 'DOC') ?></span>
              <div style="flex:1;">
                <strong><?= htmlspecialchars($attachment['nom_fichier']) ?></strong>
                <div style="font-size:0.75rem; color:var(--community-muted);">
                  <?= htmlspecialchars($attachment['titre']) ?> · <?= htmlspecialchars($attachment['zone_name']) ?> · <?= number_format(((int)$attachment['taille']) / 1048576, 2, ',', ' ') ?> Mo
                </div>
              </div>
              <a class="epi-pill-btn" href="<?= $base ?>/attachment/download/<?= (int)$attachment['id'] ?>">Télécharger</a>
              <form action="<?= $base ?>/salon/save" method="post" style="display:inline;">
                <input type="hidden" name="target_type" value="attachment">
                <input type="hidden" name="target_id" value="<?= (int)$attachment['id'] ?>">
                <input type="hidden" name="return_view" value="saved">
                <button type="submit" class="epi-pill-btn" style="color:#ef4444;">Retirer</button>
              </form>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>

      </div>

    </main>

    <!-- ========== SIDEBAR DROITE (grid-area: rightbar) ========== -->
    <aside class="epi-sidebar-right">

      <!-- Widget 1 : À propos du salon / Communauté -->
      <div class="epi-widget-card">
        <div class="epi-widget-banner"></div>
        <div class="epi-widget-title">
          <span>À propos du salon</span>
        </div>
        <p class="epi-about-text">
          <?= $selectedZone !== null
            ? htmlspecialchars((string)($selectedZone['description'] ?? 'Espace officiel de discussion, de questions et d’entraide académique.'))
            : 'Bienvenue sur EPI Tchach, la plateforme collaborative et d’échange des étudiants et professeurs.' ?>
        </p>
        <div class="epi-widget-stats">
          <div class="epi-stat-block">
            <span class="epi-stat-value"><?= count($zones) ?></span>
            <span class="epi-stat-label">Salons actifs</span>
          </div>
        </div>
        <button type="button" class="epi-btn-submit" style="width:100%; text-align:center;" onclick="document.getElementById('openCreatePostModal').click();">
          + Créer une publication
        </button>
      </div>

      <!-- Widget 2 : Règles d'EPI Tchach -->
      <div class="epi-widget-card">
        <div class="epi-widget-title">
          <span>Règles d'EPI Tchach</span>
        </div>
        <ol style="padding-left:1.2rem; font-size:0.82rem; color:var(--community-muted); line-height:1.6; margin:0;">
          <li>Bienveillance et respect mutuel.</li>
          <li>Publiez dans le salon approprié.</li>
          <li>Privilégiez les titres clairs et descriptifs.</li>
          <li>Partagez des documents libres de droit.</li>
        </ol>
      </div>

    </aside>

  </div>

  <!-- ==============================================================
   * MODALE COMPOSER "+ CRÉER UNE PUBLICATION" STYLE EPI
   * Accessible depuis le bouton "+ Créer" du Topbar et du Fil
   * ============================================================== -->
  <div id="epiPostModal" class="epi-modal-backdrop" hidden aria-hidden="true" style="display:none;">
    <div class="epi-modal-container" role="dialog" aria-modal="true" aria-labelledby="modalComposerHeading">
      
      <div class="epi-modal-header">
        <div class="epi-modal-title-group">
          <h2 id="modalComposerHeading">Créer une publication</h2>
          <div style="font-size:0.78rem; color:var(--community-muted);">Partagez vos questions, ressources et idées avec l'école</div>
        </div>
        <button type="button" class="epi-modal-close" id="closeCreatePostModal" aria-label="Fermer la boîte de dialogue">&times;</button>
      </div>

      <!-- Onglets de type de publication -->
      <div class="epi-modal-type-tabs">
        <button type="button" class="epi-type-tab is-active" data-type-target="discussion">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          <span>Discussion</span>
        </button>
        <button type="button" class="epi-type-tab" data-type-target="question">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
          <span>Question / Sondage</span>
        </button>
        <button type="button" class="epi-type-tab" data-type-target="annonce">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <span>Annonce</span>
        </button>
      </div>

      <form id="salonPostForm" action="<?= $base ?>/publication/create" method="post" enctype="multipart/form-data" class="epi-modal-form">
        <input type="hidden" name="ajax" value="1">
        <input type="hidden" name="return_view" value="<?= htmlspecialchars($feedView, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="return_zone" value="<?= (int)($selectedZone['id'] ?? 0) ?>">
        <input type="hidden" name="return_category" value="<?= (int)($categoryId ?? 0) ?>">

        <!-- Sélecteur de type masqué synchronisé avec les onglets -->
        <select class="epi-form-control" id="publicationType" name="type_publication" style="display:none;" aria-label="Type de publication">
          <option value="discussion" selected>Discussion</option>
          <option value="question">Question / sondage</option>
          <option value="annonce">Annonce</option>
        </select>

        <!-- Destination (Salon / Catégorie) -->
        <div>
          <label style="display:block; font-size:0.8rem; font-weight:700; color:var(--community-muted); margin-bottom:0.35rem;" for="modalCategorySelect">Choisir un salon de destination</label>
          <select class="epi-form-control" id="modalCategorySelect" name="categorie_id" required>
            <?php foreach ($postCategories as $postCategory): ?>
              <option value="<?= $postCategory['id'] ?>" data-zone-id="<?= $postCategory['zone_id'] ?>"
                <?= $postCategory['id'] === $defaultPostCategoryId ? 'selected' : '' ?>>
                <?= htmlspecialchars($postCategory['zone_name'] . ' · ' . $postCategory['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Titre -->
        <div>
          <label style="display:block; font-size:0.8rem; font-weight:700; color:var(--community-muted); margin-bottom:0.35rem;" for="salonPostTitle">Titre de la publication</label>
          <input class="epi-form-control" id="salonPostTitle" name="titre" type="text" maxlength="255" required placeholder="Titre descriptif et clair">
        </div>

        <!-- Contenu -->
        <div>
          <label style="display:block; font-size:0.8rem; font-weight:700; color:var(--community-muted); margin-bottom:0.35rem;" id="salonContentLabel" for="salonPostContent">Votre message</label>
          <textarea class="epi-form-control" id="salonPostContent" name="contenu" rows="5" required placeholder="Partagez une question, une idée ou une ressource avec la communauté..."></textarea>
        </div>

        <!-- Sondage Options (si question) -->
        <div id="pollOptionsFields" class="poll-options-fields" hidden style="background:var(--community-raised); border:1px solid var(--community-line); padding:1rem; border-radius:var(--community-radius-md);">
          <strong style="display:block; font-size:0.85rem; margin-bottom:0.5rem;">Choix du sondage</strong>
          <div id="pollOptionsList" style="display:flex; flex-direction:column; gap:0.5rem;">
            <input class="epi-form-control" name="options[]" maxlength="255" placeholder="Choix 1" aria-label="Choix 1">
            <input class="epi-form-control" name="options[]" maxlength="255" placeholder="Choix 2" aria-label="Choix 2">
          </div>
          <button class="epi-btn-cancel" type="button" id="addPollOption" style="margin-top:0.6rem; font-size:0.8rem; padding:0.35rem 0.85rem;">+ Ajouter un choix</button>
          <div style="font-size:0.75rem; color:var(--community-muted); margin-top:0.4rem;">De 2 à 10 choix. Chaque membre vote une seule fois.</div>
        </div>

        <!-- Pièces Jointes & Fichiers -->
        <div>
          <label style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.6rem 1rem; border-radius:var(--community-radius-pill); border:1px dashed var(--community-line); cursor:pointer; background:var(--community-raised); font-size:0.85rem;" for="salonPostAttachments">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
            <span>Ajouter des fichiers ou des images (PDF, photos, etc.)</span>
          </label>
          <input id="salonPostAttachments" class="salon-attachment-input" type="file" name="pieces_jointes[]" multiple
            accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.png,.jpg,.jpeg,.gif" style="display:none;">
          <div id="salonAttachmentPreview" class="salon-attachment-preview" aria-live="polite" hidden style="margin-top:0.75rem; display:flex; flex-wrap:wrap; gap:0.5rem;"></div>
        </div>

        <p id="salonPostFeedback" class="salon-composer-feedback" role="status" aria-live="polite" style="font-size:0.85rem; color:var(--community-orange); margin:0;"></p>

        <!-- Boutons d'action de la modale -->
        <div class="epi-modal-footer">
          <button type="button" class="epi-btn-cancel" id="cancelPostBtn">Annuler</button>
          <button type="submit" class="epi-btn-submit" id="submitPostBtn">Publier</button>
        </div>
      </form>

    </div>
  </div>

  <script src="<?= $asset ?>/js/desktop/salon.js?v=<?= (int)filemtime(ROOT_PATH . '/public/assets/js/desktop/salon.js') ?>"></script>
  <script src="<?= $asset ?>/js/feedback.js" defer></script>
</body>
</html>
