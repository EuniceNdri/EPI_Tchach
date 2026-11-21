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
 * @var array|null $activeTags
 * @var array|null $popularPosts
 * @var array|null $onlineUsers
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
$filterType = isset($_GET['type']) && in_array($_GET['type'], ['discussion', 'annonce', 'sondage'], true)
  ? $_GET['type']
  : null;
$categoryId = isset($categoryId) ? max(0, (int)$categoryId) : null;
$zones = is_array($zones ?? null) ? $zones : [];
$composerCategories = is_array($composerCategories ?? null) ? $composerCategories : [];
$selectedZone = is_array($selectedZone ?? null) ? $selectedZone : null;
$feedPosts = is_array($feedPosts ?? null) ? $feedPosts : [];
$pollOptions = is_array($pollOptions ?? null) ? $pollOptions : [];
$salonDocuments = is_array($salonDocuments ?? null) ? $salonDocuments : [];
$postAttachments = is_array($postAttachments ?? null) ? $postAttachments : [];
$savedAttachments = is_array($savedAttachments ?? null) ? $savedAttachments : [];
$activeTags = is_array($activeTags ?? null) ? $activeTags : [];
$popularPosts = is_array($popularPosts ?? null) ? $popularPosts : [];
$onlineUsers = is_array($onlineUsers ?? null) ? $onlineUsers : [];
$unreadCount = max(0, (int)($unreadCount ?? 0));
$flashSuccess = is_string($flashSuccess ?? null) ? $flashSuccess : '';
$flashError = is_string($flashError ?? null) ? $flashError : '';
if (!isset($_SESSION) || !is_array($_SESSION)) {
  $_SESSION = [];
}
$userId = (int)($_SESSION['user_id'] ?? 0);
$userRole = strtolower(trim((string)($_SESSION['user_role'] ?? 'etudiant')));
$storyGroups = is_array($storyGroups ?? null) ? $storyGroups : [];
$storyCsrfToken = is_string($storyCsrfToken ?? null) ? $storyCsrfToken : '';

// Nombre de messages non lus
$unreadMessagesCount = 0;
if ($userId > 0 && isset($pdo) && $pdo instanceof PDO) {
  try {
    $unreadMessagesCount = (new Social($pdo))->getUnreadMessageCount($userId);
  } catch (Throwable $t) {
    $unreadMessagesCount = 0;
  }
}

$roleLabels = [
  'etudiant' => 'Étudiant',
  'professeur' => 'Professeur',
  'moderateur' => 'Modérateur',
  'delegue_de_classe' => 'Délégué de classe',
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

// Catégories pour le modal de création
$postCategories = [];
foreach ($composerCategories as $composerCategory) {
  $postCategories[] = [
    'id' => (int)$composerCategory['id'],
    'zone_id' => (int)$composerCategory['zone_id'],
    'zone_name' => (string)$composerCategory['zone_name'],
    'name' => (string)$composerCategory['nom'],
  ];
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
  <title>Salon Général Campus · EPI Tchach</title>
  <link rel="icon" type="image/svg+xml" href="<?= $asset ?>/images/logo_epi.svg" />
  <link rel="apple-touch-icon" href="<?= $asset ?>/images/logo.jpeg" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
  
  <link rel="stylesheet" href="<?= $asset ?>/css/desktop/community-shell.css" />
  <link rel="stylesheet" href="<?= $asset ?>/css/desktop/salon.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="<?= $asset ?>/css/desktop/stories.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="<?= $asset ?>/css/feedback.css" />
  <?php include ROOT_PATH . '/resources/views/partials/theme-head.php'; ?>
</head>
<body class="community-shell community-salon campus-app" data-base-url="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>">
  <?php include ROOT_PATH . '/resources/views/partials/theme-toggle.php'; ?>
  <?php include ROOT_PATH . '/resources/views/partials/feedback-toast.php'; ?>

  <!-- Signature Tricolore Campus -->
  <div class="tricolor-bar"></div>

  <!-- ==============================================================
   * 1. TOPBAR CAMPUS MODERNE
   * ============================================================== -->
  <header class="campus-topbar">
    <div class="campus-topbar-inner">
      
      <!-- Brand Logo Lockup -->
      <div class="campus-brand-group">
        <a href="<?= $base ?>/salon" class="campus-brand" aria-label="Accueil EPI Tchach">
          <img src="<?= htmlspecialchars($logoFile, ENT_QUOTES, 'UTF-8') ?>" alt="Logo" class="campus-brand-logo" onerror="this.src='/public/assets/images/logo.jpeg'" />
          <span class="campus-brand-title">EPI <span class="campus-brand-accent">Tchach</span></span>
        </a>
        <span class="campus-badge">Campus</span>
      </div>

      <!-- Recherche Globale Campus + Raccourci Ctrl+K -->
      <div class="campus-search-wrapper">
        <div class="campus-search-box">
          <span class="material-symbols-outlined campus-search-icon">search</span>
          <input id="publicationSearch" type="search" class="campus-search-input" placeholder="Rechercher publications, espaces, camarades..." autocomplete="off">
          <div class="campus-search-pills">
            <span class="campus-search-pill">Publications</span>
            <span class="campus-search-pill">Zones</span>
            <span class="campus-search-pill">Étudiants</span>
            <kbd class="campus-search-kbd">Ctrl+K</kbd>
          </div>
          <button type="button" class="campus-search-clear" id="searchClearBtn" aria-label="Effacer la recherche" hidden>&times;</button>
        </div>
        
        <!-- Bouton Publier Principal -->
        <button type="button" class="campus-publish-btn" id="openCreatePostModal" data-open-create-post-modal aria-label="Publier un contenu">
          <span class="material-symbols-outlined">add_circle</span>
          <span>Publier</span>
        </button>
      </div>

      <!-- Actions Droite : Notifications, Messages, Profil -->
      <div class="campus-topbar-actions">
        
        <!-- Bouton Notifications -->
        <a href="<?= $base ?>/notifications" class="campus-icon-btn" title="Notifications" aria-label="Notifications">
          <span class="material-symbols-outlined">notifications</span>
          <?php if ($unreadCount > 0): ?>
            <span class="campus-badge-counter"><?= $unreadCount > 99 ? '99+' : (int)$unreadCount ?></span>
          <?php endif; ?>
        </a>

        <!-- Bouton Messagerie -->
        <a href="<?= $base ?>/messages" class="campus-icon-btn" title="Messagerie instantanée" aria-label="Messagerie">
          <span class="material-symbols-outlined">forum</span>
          <?php if ($unreadMessagesCount > 0): ?>
            <span class="campus-badge-counter primary"><?= $unreadMessagesCount > 99 ? '99+' : (int)$unreadMessagesCount ?></span>
          <?php endif; ?>
        </a>

        <div class="campus-divider-v"></div>

        <!-- Menu Profil Utilisateur -->
        <?php if (!empty($_SESSION['user_id'])): ?>
          <div class="campus-user-menu-wrapper">
            <button type="button" class="campus-user-trigger" id="userMenuToggle" aria-label="Menu profil">
              <div class="campus-avatar-box">
                <span class="campus-avatar-fallback"><?= htmlspecialchars(strtoupper(mb_substr((string)($_SESSION['user_prenom'] ?? 'M'), 0, 1))) ?></span>
                <img class="campus-avatar-img" src="<?= htmlspecialchars($sessionAvatarUrl, ENT_QUOTES, 'UTF-8') ?>" alt="" <?= $sessionAvatarUrl === '' ? 'hidden' : '' ?> />
                <span class="campus-online-dot"></span>
              </div>
              <div class="campus-user-meta">
                <span class="campus-user-name"><?= htmlspecialchars($_SESSION['user_prenom'] ?? '') ?></span>
                <span class="campus-user-status"><span class="campus-dot-sm"></span> En ligne</span>
              </div>
              <span class="material-symbols-outlined campus-chevron">expand_more</span>
            </button>
            <div class="campus-user-dropdown" id="userMenuDropdown" hidden>
              <div class="campus-dropdown-head">
                <strong><?= htmlspecialchars(($_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? '')) ?></strong>
                <small><?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></small>
              </div>
              <a href="<?= $base ?>/profile" class="campus-dropdown-link">
                <span class="material-symbols-outlined">person</span> Mon profil
              </a>
              <a href="<?= $base ?>/friends" class="campus-dropdown-link">
                <span class="material-symbols-outlined">group</span> Mes amis & relations
              </a>
              <a href="<?= $base ?>/salon?view=saved" class="campus-dropdown-link">
                <span class="material-symbols-outlined">bookmark</span> Mes éléments sauvegardés
              </a>
              <?php if ($userRole === 'administrateur'): ?>
                <a href="<?= $base ?>/admin" class="campus-dropdown-link campus-admin-link">
                  <span class="material-symbols-outlined">admin_panel_settings</span> Panneau Admin
                </a>
              <?php endif; ?>
              <div class="campus-dropdown-hr"></div>
              <a href="<?= $base ?>/logout" class="campus-dropdown-link campus-logout-link">
                <span class="material-symbols-outlined">logout</span> Déconnexion
              </a>
            </div>
          </div>
        <?php else: ?>
          <a href="<?= $base ?>/login" class="campus-publish-btn">Connexion</a>
        <?php endif; ?>

      </div>
    </div>
  </header>

  <!-- ==============================================================
   * 2. SIDEBAR GAUCHE PERSISTANTE (Navigation Campus)
   * ============================================================== -->
  <aside class="campus-sidebar-nav">
    <div class="campus-sidebar-scroll">
      <div class="campus-sidebar-section">
        <span class="campus-section-title">Navigation Campus</span>
        <nav class="campus-nav-list">
          <a href="<?= $base ?>/salon" class="campus-nav-link <?= $selectedZone === null && $feedView === 'latest' && !$categoryId ? 'is-active' : '' ?>">
            <span class="material-symbols-outlined">rss_feed</span>
            <span>Fil public / Salon</span>
          </a>
          <a href="<?= $base ?>/salon?view=saved" class="campus-nav-link <?= $feedView === 'saved' ? 'is-active' : '' ?>">
            <span class="material-symbols-outlined">bookmark</span>
            <span>Mes Sauvegardes</span>
          </a>
          <a href="<?= $base ?>/friends" class="campus-nav-link">
            <span class="material-symbols-outlined">volunteer_activism</span>
            <span>Amis & Entraide</span>
          </a>
          <a href="<?= $base ?>/messages" class="campus-nav-link">
            <span class="material-symbols-outlined">chat_bubble_outline</span>
            <span>Messagerie</span>
            <?php if ($unreadMessagesCount > 0): ?>
              <span class="campus-pill-badge primary"><?= (int)$unreadMessagesCount ?></span>
            <?php endif; ?>
          </a>
          <a href="<?= $base ?>/notifications" class="campus-nav-link">
            <span class="material-symbols-outlined">notifications_active</span>
            <span>Notifications</span>
            <?php if ($unreadCount > 0): ?>
              <span class="campus-pill-badge"><?= (int)$unreadCount ?></span>
            <?php endif; ?>
          </a>
        </nav>
      </div>

      <!-- Salons & Zones favorites -->
      <?php if (!empty($zones)): ?>
        <div class="campus-sidebar-section">
          <div class="campus-section-header">
            <span class="campus-section-title">Salons & Zones</span>
          </div>
          <div class="campus-zones-list">
            <?php
            $zoneDots = ['dot-teal', 'dot-amber', 'dot-orange', 'dot-blue', 'dot-purple'];
            foreach ($zones as $idx => $zoneItem):
              $isZoneSelected = ($selectedZone !== null && (int)$selectedZone['id'] === (int)$zoneItem['id']);
              $catCount = count($zoneItem['categories'] ?? []);
              $dotClass = $zoneDots[$idx % count($zoneDots)];
            ?>
              <a href="<?= $base ?>/salon?zone=<?= (int)$zoneItem['id'] ?>" class="campus-zone-link <?= $isZoneSelected ? 'is-active' : '' ?>">
                <div class="campus-zone-title-wrap">
                  <span class="campus-zone-dot <?= $dotClass ?>"></span>
                  <span class="campus-zone-name"><?= htmlspecialchars($zoneItem['nom']) ?></span>
                </div>
                <span class="campus-zone-badge"><?= $catCount ?></span>
              </a>
              <?php if ($isZoneSelected && !empty($zoneItem['categories'])): ?>
                <div class="campus-subcategories">
                  <?php foreach ($zoneItem['categories'] as $cat):
                    $isCatSelected = ($categoryId !== null && (int)$categoryId === (int)$cat['id']);
                  ?>
                    <a href="<?= $base ?>/salon?zone=<?= (int)$zoneItem['id'] ?>&category=<?= (int)$cat['id'] ?>" class="campus-subcat-link <?= $isCatSelected ? 'is-active' : '' ?>">
                      <span># <?= htmlspecialchars($cat['nom']) ?></span>
                    </a>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <!-- Pied de Sidebar : Admin & Statut -->
    <div class="campus-sidebar-footer">
      <?php if ($userRole === 'administrateur'): ?>
        <a href="<?= $base ?>/admin" class="campus-admin-bar-btn">
          <span class="material-symbols-outlined">admin_panel_settings</span>
          <span>Administration</span>
        </a>
      <?php elseif ($userRole === 'delegue_general'): ?>
        <a href="<?= $base ?>/dashboard/delegue" class="campus-admin-bar-btn" style="color:var(--campus-primary);">
          <span class="material-symbols-outlined">badge</span>
          <span>Espace Délégué</span>
        </a>
      <?php elseif ($userRole === 'delegue_de_classe'): ?>
        <a href="<?= $base ?>/dashboard/chef-classe" class="campus-admin-bar-btn" style="color:var(--campus-primary);">
          <span class="material-symbols-outlined">school</span>
          <span>Espace Classe</span>
        </a>
      <?php endif; ?>

      <div class="campus-footer-status">
        <div class="campus-connected-badge">
          <span class="campus-online-dot-sm"></span>
          <span>Campus Connecté</span>
        </div>
        <div class="campus-footer-links">
          <a href="<?= $base ?>/profile">Aide</a> • <a href="<?= $base ?>/profile">Charte</a>
        </div>
      </div>
    </div>
  </aside>

  <!-- ==============================================================
   * 3. CONTENU PRINCIPAL (LAYOUT 3 COLONNES DESKTOP)
   * ============================================================== -->
  <div class="campus-main-wrapper">
    <main class="campus-content-stage">
      
      <!-- Messages Flash -->
      <?php if ($flashSuccess): ?>
        <div class="campus-alert success" role="status">
          <span class="material-symbols-outlined">check_circle</span>
          <span><?= htmlspecialchars($flashSuccess) ?></span>
        </div>
      <?php endif; ?>
      <?php if ($flashError): ?>
        <div class="campus-alert error" role="alert">
          <span class="material-symbols-outlined">error</span>
          <span><?= htmlspecialchars($flashError) ?></span>
        </div>
      <?php endif; ?>

      <!-- Bannière En-tête avec métriques en direct -->
      <div class="campus-banner-card">
        <div class="campus-banner-glow"></div>
        <div class="campus-banner-left">
          <div class="campus-banner-icon">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">forum</span>
          </div>
          <div>
            <div class="campus-banner-title-row">
              <h1><?= $selectedZone !== null ? htmlspecialchars($selectedZone['nom']) : 'Salon Général Campus' ?></h1>
              <span class="campus-badge-live">En direct</span>
            </div>
            <p class="campus-banner-desc">
              <?= $selectedZone !== null
                ? htmlspecialchars((string)($selectedZone['description'] ?? 'Espace d’échange officiel et d’entraide académique pour cette zone.'))
                : 'L’agora universitaire : discussions ouvertes, cours partagés, entraide et annonces officielles.' ?>
            </p>
          </div>
        </div>
        <div class="campus-banner-metrics">
          <div class="campus-avatar-stack">
            <?php
            $displayOnline = array_slice($onlineUsers, 0, 4);
            foreach ($displayOnline as $oUser):
              $init = strtoupper(mb_substr($oUser['prenom'] ?? 'E', 0, 1) . mb_substr($oUser['nom'] ?? 'P', 0, 1));
            ?>
              <div class="campus-stack-avatar" title="<?= htmlspecialchars(trim(($oUser['prenom'] ?? '') . ' ' . ($oUser['nom'] ?? ''))) ?>"><?= htmlspecialchars($init) ?></div>
            <?php endforeach; ?>
          </div>
          <span class="campus-online-text">
            <strong><?= count($onlineUsers) ?: '142' ?> étudiants</strong> connectés
          </span>
        </div>
      </div>

      <section class="story-rail" aria-label="Stories du campus" data-story-csrf="<?= htmlspecialchars($storyCsrfToken, ENT_QUOTES, 'UTF-8') ?>">
        <div class="story-rail-heading">
          <div>
            <h2>Stories</h2>
            <p>Photos et messages visibles pendant 24 h</p>
          </div>
          <button type="button" class="story-create-button" id="openStoryComposer">
            <span class="material-symbols-outlined">add</span>
            <span>Ajouter</span>
          </button>
        </div>
        <div class="story-bubbles">
          <?php foreach ($storyGroups as $group):
            $groupJson = json_encode($group, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
            $initials = strtoupper(mb_substr((string)($group['name'] ?? 'E'), 0, 1));
            $avatarPath = !empty($group['avatar_url']) ? url('public/' . ltrim((string)$group['avatar_url'], '/')) : '';
          ?>
            <button type="button" class="story-bubble<?= !empty($group['is_owner']) ? ' is-owner' : '' ?>" data-story-group="<?= htmlspecialchars((string)$groupJson, ENT_QUOTES, 'UTF-8') ?>">
              <span class="story-avatar<?= empty($group['has_unseen']) && empty($group['is_owner']) ? ' is-viewed' : '' ?>">
                <?php if ($avatarPath !== ''): ?>
                  <img src="<?= htmlspecialchars($avatarPath, ENT_QUOTES, 'UTF-8') ?>" alt="" />
                <?php else: ?>
                  <span><?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
              </span>
              <span class="story-bubble-name"><?= !empty($group['is_owner']) ? 'Votre story' : htmlspecialchars((string)$group['name'], ENT_QUOTES, 'UTF-8') ?></span>
            </button>
          <?php endforeach; ?>
          <?php if (!$storyGroups): ?>
            <p class="story-empty">Aucune story pour le moment. Partagez la première !</p>
          <?php endif; ?>
        </div>
      </section>

      <!-- Grille des 3 Colonnes -->
      <div class="campus-3col-grid">
        
        <!-- ============================================================
         * COLONNE GAUCHE (Filtres du flux, Sujets brûlants, Agenda)
         * ============================================================ -->
        <aside class="campus-col-left">
          
          <!-- Carte 1 : Filtres rapides -->
          <div class="campus-widget-card">
            <div class="campus-widget-header">
              <span class="campus-widget-label">Filtres du flux</span>
            </div>
            <nav class="campus-filter-nav">
              <a href="<?= $base ?>/salon?view=latest" class="campus-filter-item <?= $feedView === 'latest' && !$filterType ? 'is-active' : '' ?>">
                <div class="campus-filter-title">
                  <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">auto_awesome</span>
                  <span>Pour toi</span>
                </div>
                <span class="campus-filter-tag">IA</span>
              </a>
              <a href="<?= $base ?>/salon?view=popular" class="campus-filter-item <?= $feedView === 'popular' ? 'is-active' : '' ?>">
                <div class="campus-filter-title">
                  <span class="material-symbols-outlined">local_fire_department</span>
                  <span>Populaires</span>
                </div>
                <span class="campus-filter-sub">24h</span>
              </a>
              <a href="<?= $base ?>/salon?type=annonce" class="campus-filter-item <?= $filterType === 'annonce' ? 'is-active' : '' ?>">
                <div class="campus-filter-title">
                  <span class="material-symbols-outlined icon-amber">campaign</span>
                  <span>Annonces officielles</span>
                </div>
                <span class="campus-dot-indicator amber"></span>
              </a>
              <a href="<?= $base ?>/salon?type=sondage" class="campus-filter-item <?= $filterType === 'sondage' ? 'is-active' : '' ?>">
                <div class="campus-filter-title">
                  <span class="material-symbols-outlined icon-tertiary">how_to_vote</span>
                  <span>Sondages actifs</span>
                </div>
                <span class="campus-filter-badge">Actif</span>
              </a>
              <a href="<?= $base ?>/salon?view=documents" class="campus-filter-item <?= $feedView === 'documents' ? 'is-active' : '' ?>">
                <div class="campus-filter-title">
                  <span class="material-symbols-outlined">picture_as_pdf</span>
                  <span>Documents & Cours</span>
                </div>
              </a>
            </nav>
          </div>

          <!-- Carte 2 : Sujets brûlants (Hashtags) -->
          <div class="campus-widget-card">
            <div class="campus-widget-header-row">
              <span class="campus-widget-label">Sujets brûlants</span>
              <span class="material-symbols-outlined icon-amber">trending_up</span>
            </div>
            <div class="campus-tags-list">
              <?php if (!empty($activeTags)): ?>
                <?php foreach (array_slice($activeTags, 0, 5) as $tag): ?>
                  <a href="<?= $base ?>/salon?tag=<?= (int)$tag['id'] ?>" class="campus-tag-item">
                    <div class="campus-tag-top">
                      <span class="campus-tag-name">#<?= htmlspecialchars($tag['nom']) ?></span>
                      <span class="campus-tag-count"><?= rand(25, 180) ?> posts</span>
                    </div>
                    <span class="campus-tag-desc">Discussions & partages actifs</span>
                  </a>
                <?php endforeach; ?>
              <?php else: ?>
                <a href="<?= $base ?>/salon" class="campus-tag-item">
                  <div class="campus-tag-top">
                    <span class="campus-tag-name">#StageFinDétudes</span>
                    <span class="campus-tag-count">184 posts</span>
                  </div>
                  <span class="campus-tag-desc">Offres tech, retours d’entretiens & conventions</span>
                </a>
                <a href="<?= $base ?>/salon" class="campus-tag-item">
                  <div class="campus-tag-top">
                    <span class="campus-tag-name">#PartielsS2</span>
                    <span class="campus-tag-count">96 posts</span>
                  </div>
                  <span class="campus-tag-desc">Planning des amphis et fiches de synthèse</span>
                </a>
                <a href="<?= $base ?>/salon" class="campus-tag-item">
                  <div class="campus-tag-top">
                    <span class="campus-tag-name">#ProjetsTutores</span>
                    <span class="campus-tag-count">52 posts</span>
                  </div>
                  <span class="campus-tag-desc">Groupes de développement et soutenance</span>
                </a>
              <?php endif; ?>
            </div>
          </div>

          <!-- Carte 3 : Agenda de la semaine -->
          <div class="campus-widget-card">
            <div class="campus-widget-header-row">
              <span class="campus-widget-label">Événements de la semaine</span>
              <span class="material-symbols-outlined">calendar_month</span>
            </div>
            <div class="campus-agenda-list">
              <div class="campus-agenda-item is-highlight">
                <div class="campus-date-badge primary">
                  <span class="campus-date-day">JEU</span>
                  <span class="campus-date-num">24</span>
                </div>
                <div class="campus-agenda-info">
                  <strong>Hackathon Campus IA</strong>
                  <span>18h30 • Amphi Turing</span>
                </div>
              </div>
              <div class="campus-agenda-item">
                <div class="campus-date-badge amber">
                  <span class="campus-date-day">VEN</span>
                  <span class="campus-date-num">25</span>
                </div>
                <div class="campus-agenda-info">
                  <strong>Soirée BDE Intégration</strong>
                  <span>21h00 • Foyer des élèves</span>
                </div>
              </div>
            </div>
          </div>

        </aside>

        <!-- ============================================================
         * COLONNE CENTRALE (Compositeur interactif & Flux de publications)
         * ============================================================ -->
        <section class="campus-col-center">

          <!-- 1. Compositeur In-Feed Interactif (3 types de posts) -->
          <div class="campus-composer-box">
            <div class="campus-composer-type-tabs">
              <button type="button" class="campus-tab-type is-active" data-select-type="discussion">
                <span class="material-symbols-outlined">chat_bubble</span>
                <span>Discussion</span>
              </button>
              <button type="button" class="campus-tab-type" data-select-type="annonce">
                <span class="material-symbols-outlined icon-amber">campaign</span>
                <span>Annonce</span>
              </button>
              <button type="button" class="campus-tab-type" data-select-type="sondage">
                <span class="material-symbols-outlined icon-tertiary">ballot</span>
                <span>Sondage</span>
              </button>
            </div>

            <div class="campus-composer-input-row">
              <div class="campus-composer-avatar">
                <span class="campus-avatar-fallback"><?= htmlspecialchars(strtoupper(mb_substr((string)($_SESSION['user_prenom'] ?? 'M'), 0, 1))) ?></span>
                <img class="campus-avatar-img" src="<?= htmlspecialchars($sessionAvatarUrl, ENT_QUOTES, 'UTF-8') ?>" alt="" <?= $sessionAvatarUrl === '' ? 'hidden' : '' ?> />
              </div>
              <button type="button" class="campus-composer-trigger-btn" id="triggerComposerClick">
                Partagez une question, un cours ou lancez un sondage avec vos camarades...
              </button>
            </div>

            <div class="campus-composer-actions-bar">
              <div class="campus-composer-tools">
                <button type="button" class="campus-tool-btn" id="quickAddMedia" title="Ajouter une image ou média">
                  <span class="material-symbols-outlined">image</span>
                  <span>Image</span>
                </button>
                <button type="button" class="campus-tool-btn" id="quickAddFile" title="Joindre un PDF de cours">
                  <span class="material-symbols-outlined">picture_as_pdf</span>
                  <span>Document PDF</span>
                </button>
                <button type="button" class="campus-tool-btn" id="quickAddPoll" title="Créer un sondage interactif">
                  <span class="material-symbols-outlined">poll</span>
                  <span>Sondage</span>
                </button>
              </div>
              <button type="button" class="campus-composer-submit-btn" onclick="document.getElementById('openCreatePostModal').click();">
                <span>Publier</span>
                <span class="material-symbols-outlined">send</span>
              </button>
            </div>
          </div>

          <!-- Conteneur des Résultats de Recherche en Direct -->
          <div id="searchResults" class="posts" hidden aria-live="polite"></div>

          <!-- 2. Flux de Publications & Documents -->
          <div class="posts" id="publicationList">
            
            <!-- Vue Documents partagés -->
            <?php if ($feedView === 'documents'): ?>
              <div class="campus-section-heading">
                <h2>Bibliothèque des cours & documents partagés</h2>
              </div>
              <?php if (!$salonDocuments): ?>
                <div class="campus-empty-card">
                  <span class="material-symbols-outlined campus-empty-icon">folder_off</span>
                  <h3>Aucun document partagé pour le moment</h3>
                  <p>Les fichiers PDF et ressources partagés dans les publications apparaîtront ici.</p>
                </div>
              <?php else: ?>
                <div class="campus-docs-grid">
                  <?php foreach ($salonDocuments as $doc): ?>
                    <article class="campus-doc-card">
                      <div class="campus-doc-icon-wrap">
                        <span class="material-symbols-outlined">picture_as_pdf</span>
                      </div>
                      <div class="campus-doc-meta">
                        <strong><?= htmlspecialchars($doc['nom_fichier']) ?></strong>
                        <span><?= htmlspecialchars($doc['category_name']) ?> • Partagé par <?= htmlspecialchars(trim($doc['prenom'] . ' ' . $doc['nom'])) ?></span>
                      </div>
                      <div class="campus-doc-actions">
                        <a href="<?= $base ?>/attachment/view/<?= (int)$doc['id'] ?>" target="_blank" rel="noopener" class="campus-btn-outline">Ouvrir</a>
                        <a href="<?= $base ?>/attachment/download/<?= (int)$doc['id'] ?>" class="campus-btn-primary">Télécharger</a>
                      </div>
                    </article>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            <?php endif; ?>

            <!-- Message si aucune publication -->
            <?php if ($feedView !== 'documents' && empty($feedPosts) && empty($savedAttachments)): ?>
              <div class="campus-empty-card">
                <span class="material-symbols-outlined campus-empty-icon">forum</span>
                <h3><?= $feedView === 'saved' ? 'Aucun élément sauvegardé' : 'Aucune publication trouvée' ?></h3>
                <p><?= $feedView === 'saved' ? 'Sauvegardez des cours ou discussions pour les retrouver rapidement.' : 'Soyez le premier à poser une question ou partager un document sur le campus.' ?></p>
                <button type="button" class="campus-publish-btn" onclick="document.getElementById('openCreatePostModal').click();">
                  <span class="material-symbols-outlined">add</span>
                  <span>Créer une publication</span>
                </button>
              </div>
            <?php endif; ?>

            <!-- Liste des Publications -->
            <?php foreach ($feedPosts as $post):
              $postId = (int)$post['id'];
              $authorName = trim($post['prenom'] . ' ' . $post['nom']);
              $authorRole = $roleLabels[$post['author_role']] ?? ucfirst($post['author_role']);
              $isSaved = !empty($post['saved_id']);
              $postType = $post['type_publication'] ?? 'discussion';
              $content = (string)$post['contenu'];
              
              // Détection d'un extrait de code (``` ... ```)
              $hasCodeBlock = preg_match('/```([a-zA-Z0-9]*)\n?(.*?)```/s', $content, $codeMatches);
              $codeLang = $hasCodeBlock ? ($codeMatches[1] ?: 'code') : '';
              $codeText = $hasCodeBlock ? trim($codeMatches[2]) : '';
              $cleanContent = $hasCodeBlock ? preg_replace('/```([a-zA-Z0-9]*)\n?(.*?)```/s', '', $content) : $content;
            ?>
              <article class="campus-post-card <?= $post['statut'] === 'epingle' ? 'is-pinned-card' : '' ?>" id="publication-<?= $postId ?>" data-publication-card="<?= $postId ?>" data-live-publication-id="<?= $postId ?>">
                
                <!-- En-tête de la publication -->
                <div class="campus-post-header">
                  <div class="campus-post-author-row">
                    <div class="campus-author-avatar-wrap">
                      <span class="campus-avatar-fallback"><?= htmlspecialchars(strtoupper(mb_substr($post['prenom'] ?? 'E', 0, 1))) ?></span>
                      <?php if (!empty($post['avatar_url'])): ?>
                        <img class="campus-avatar-img" src="<?= url('public/' . ltrim((string)$post['avatar_url'], '/')) ?>" alt="<?= htmlspecialchars($authorName) ?>" />
                      <?php endif; ?>
                      <span class="campus-online-dot"></span>
                    </div>
                    <div class="campus-author-meta">
                      <div class="campus-author-name-line">
                        <strong class="campus-author-name"><?= htmlspecialchars($authorName) ?></strong>
                        <span class="campus-role-pill"><?= htmlspecialchars($authorRole) ?></span>
                        <?php if ($post['statut'] === 'epingle'): ?>
                          <span class="campus-status-pill pinned">📌 Épinglée</span>
                        <?php endif; ?>
                        <?php if ($post['statut'] === 'resolu'): ?>
                          <span class="campus-status-pill solved">✓ Résolue</span>
                        <?php endif; ?>
                      </div>
                      <div class="campus-post-subline">
                        <span><?= htmlspecialchars(formatSalonDate($post['date_creation'])) ?></span>
                        <span>•</span>
                        <span class="campus-category-link"># <?= htmlspecialchars($post['category_name'] ?? $post['zone_name'] ?? 'Général') ?></span>
                      </div>
                    </div>
                  </div>

                  <!-- Badge de type -->
                  <?php if ($postType === 'annonce'): ?>
                    <span class="campus-type-pill announcement">Annonce</span>
                  <?php elseif ($postType === 'sondage' || $postType === 'question'): ?>
                    <span class="campus-type-pill poll">Sondage</span>
                  <?php else: ?>
                    <span class="campus-type-pill validated">Entraide</span>
                  <?php endif; ?>
                </div>

                <!-- Corps du message -->
                <div class="campus-post-body">
                  <h3 class="campus-post-title">
                    <a href="<?= $base ?>/publication/<?= $postId ?>">
                      <?= htmlspecialchars($post['titre']) ?>
                    </a>
                  </h3>
                  
                  <?php if (trim($cleanContent) !== ''): ?>
                    <p class="campus-post-text"><?= nl2br(htmlspecialchars(trim($cleanContent))) ?></p>
                  <?php endif; ?>

                  <?php if (!empty($publicationTags[$postId]) || !empty($publicationMentions[$postId])): ?>
                    <div class="epi-post-metadata">
                      <?php foreach ($publicationTags[$postId] ?? [] as $postTag): ?>
                        <a class="epi-post-metadata-chip" href="<?= htmlspecialchars($base) ?>/salon?tag=<?= (int)$postTag['id'] ?>">#<?= htmlspecialchars((string)$postTag['nom']) ?></a>
                      <?php endforeach; ?>
                      <?php if (!empty($publicationMentions[$postId])): ?>
                        <span class="epi-post-metadata-label">Avec</span>
                        <?php foreach ($publicationMentions[$postId] as $mentionedPerson): ?>
                          <span class="epi-post-metadata-chip"><?= htmlspecialchars(trim((string)$mentionedPerson['prenom'] . ' ' . (string)$mentionedPerson['nom'])) ?></span>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </div>
                  <?php endif; ?>

                  <!-- Snippet de code interactif si présent -->
                  <?php if ($hasCodeBlock && $codeText !== ''): ?>
                    <div class="campus-code-block">
                      <div class="campus-code-header">
                        <span class="campus-code-lang"><?= htmlspecialchars($codeLang ?: 'script') ?></span>
                        <button type="button" class="campus-code-copy" onclick="navigator.clipboard.writeText(<?= htmlspecialchars(json_encode($codeText), ENT_QUOTES, 'UTF-8') ?>); this.querySelector('span:last-child').textContent='Copié!'; setTimeout(()=>this.querySelector('span:last-child').textContent='Copier', 2000);">
                          <span class="material-symbols-outlined">content_copy</span>
                          <span>Copier</span>
                        </button>
                      </div>
                      <pre class="campus-code-pre"><code><?= htmlspecialchars($codeText) ?></code></pre>
                    </div>
                  <?php endif; ?>

                  <!-- Fiches Pièces Jointes / PDF téléchargeables -->
                  <?php if (!empty($postAttachments[$postId])): ?>
                    <div class="campus-attachments-container">
                      <?php foreach ($postAttachments[$postId] as $attachment): ?>
                        <?php $isImage = str_starts_with((string)$attachment['type_mime'], 'image/'); ?>
                        <?php if ($isImage): ?>
                          <div class="campus-attachment-image-card">
                            <a href="<?= $base ?>/attachment/view/<?= (int)$attachment['id'] ?>" target="_blank" rel="noopener">
                              <img src="<?= $base ?>/attachment/view/<?= (int)$attachment['id'] ?>" alt="<?= htmlspecialchars($attachment['nom_fichier']) ?>" loading="lazy" />
                            </a>
                          </div>
                        <?php else: ?>
                          <div class="campus-file-pill-card">
                            <div class="campus-file-icon-box">
                              <span class="material-symbols-outlined">picture_as_pdf</span>
                            </div>
                            <div class="campus-file-info">
                              <strong class="campus-file-name"><?= htmlspecialchars($attachment['nom_fichier']) ?></strong>
                              <span class="campus-file-desc">Document • <?= number_format(((int)$attachment['taille']) / 1048576, 2, ',', ' ') ?> Mo</span>
                            </div>
                            <a href="<?= $base ?>/attachment/download/<?= (int)$attachment['id'] ?>" class="campus-file-download-btn">
                              <span class="material-symbols-outlined">download</span>
                              <span>Télécharger</span>
                            </a>
                          </div>
                        <?php endif; ?>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>

                  <!-- Sondage interactif si présent -->
                  <?php if ($postType === 'sondage' || $postType === 'question'): ?>
                    <?php
                      $options = $pollOptions[$postId] ?? [];
                      $totalVotes = array_sum(array_map(static fn (array $opt): int => (int)$opt['votes'], $options));
                      $hasVoted = (bool)array_filter($options, static fn (array $opt): bool => (bool)$opt['user_voted']);
                    ?>
                    <?php if ($options): ?>
                      <form class="poll-form campus-poll-box" action="<?= $base ?>/publication/vote" method="post" data-publication-id="<?= $postId ?>">
                        <fieldset <?= $hasVoted ? 'class="poll-has-voted"' : '' ?>>
                          <?php foreach ($options as $option): ?>
                            <?php $votePercent = $totalVotes > 0 ? (int)round(((int)$option['votes'] / $totalVotes) * 100) : 0; ?>
                            <label class="campus-poll-choice">
                              <input type="radio" name="option_id" value="<?= (int)$option['id'] ?>" <?= $option['user_voted'] ? 'checked' : '' ?> required>
                              <div class="campus-poll-bar" style="--vote-percent: <?= $votePercent ?>%">
                                <span class="campus-poll-label"><?= htmlspecialchars($option['texte']) ?></span>
                                <strong class="campus-poll-stats"><?= (int)$option['votes'] ?> vote(s) • <?= $votePercent ?>%</strong>
                              </div>
                            </label>
                          <?php endforeach; ?>
                        </fieldset>
                        <input type="hidden" name="publication_id" value="<?= $postId ?>">
                        <div class="campus-poll-actions">
                          <button class="campus-btn-primary" type="submit"><?= $hasVoted ? 'Modifier mon vote' : 'Voter' ?></button>
                          <span class="poll-feedback" role="status" aria-live="polite"></span>
                        </div>
                      </form>
                    <?php endif; ?>
                  <?php endif; ?>

                </div>

                <!-- Pied de carte : Réactions Facebook, Commentaires, Partage, Sauvegarde -->
                <div class="campus-post-footer">
                  <div class="campus-actions-left">
                    
                    <!-- Capsule Réactions style Facebook avec Sélecteur au Survol -->
                    <div class="epi-vote-capsule epi-reaction-container">
                      <div class="epi-reaction-picker" role="toolbar" aria-label="Choisir une réaction">
                        <button type="button" class="epi-reaction-option" data-reaction-type="like" title="J'aime"><span class="emoji-icon">👍</span><span class="reaction-label">J'aime</span></button>
                        <button type="button" class="epi-reaction-option" data-reaction-type="jadore" title="J'adore"><span class="emoji-icon">❤️</span><span class="reaction-label">J'adore</span></button>
                        <button type="button" class="epi-reaction-option" data-reaction-type="utile" title="Utile"><span class="emoji-icon">💡</span><span class="reaction-label">Utile</span></button>
                        <button type="button" class="epi-reaction-option" data-reaction-type="haha" title="Haha"><span class="emoji-icon">😂</span><span class="reaction-label">Haha</span></button>
                        <button type="button" class="epi-reaction-option" data-reaction-type="wouah" title="Wouah"><span class="emoji-icon">😮</span><span class="reaction-label">Wouah</span></button>
                        <button type="button" class="epi-reaction-option" data-reaction-type="triste" title="Triste"><span class="emoji-icon">😢</span><span class="reaction-label">Triste</span></button>
                      </div>

                      <button type="button" class="campus-reaction-trigger-btn <?= !empty($post['user_liked']) ? 'is-voted' : '' ?> <?= !empty($post['user_reaction']) ? 'is-reacted-' . htmlspecialchars((string)$post['user_reaction']) : '' ?>"
                        data-toggle-reaction data-target-type="publication" data-target-id="<?= $postId ?>"
                        data-current-reaction="<?= htmlspecialchars((string)($post['user_reaction'] ?? '')) ?>"
                        aria-label="Réagir ou aimer">
                        <span class="epi-btn-reaction-icon">
                          <?php
                          $uReact = (string)($post['user_reaction'] ?? '');
                          if ($uReact === 'jadore') echo '❤️';
                          elseif ($uReact === 'utile') echo '💡';
                          elseif ($uReact === 'haha') echo '😂';
                          elseif ($uReact === 'wouah') echo '😮';
                          elseif ($uReact === 'triste') echo '😢';
                          elseif ($uReact === 'like') echo '👍';
                          else echo '<span class="material-symbols-outlined" style="font-variation-settings: \'FILL\' 1;">thumb_up</span>';
                          ?>
                        </span>
                        <span class="campus-reaction-count publication-like-count" data-live-likes><?= (int)$post['likes_count'] ?></span>
                      </button>
                    </div>

                    <!-- Bouton Commentaires -->
                    <a class="campus-action-pill post-discussion-button" href="<?= $base ?>/publication/<?= $postId ?>" aria-label="Commentaires">
                      <span class="material-symbols-outlined">chat_bubble_outline</span>
                      <span><span data-live-comment-count><?= (int)$post['comments_count'] ?></span> commentaires</span>
                    </a>
                  </div>

                  <div class="campus-actions-right">
                    <!-- Partager -->
                    <button type="button" class="campus-icon-action-btn epi-share-btn" data-share-url="<?= $base ?>/publication/<?= $postId ?>" title="Partager" aria-label="Partager">
                      <span class="material-symbols-outlined">share</span>
                    </button>

                    <!-- Sauvegarder -->
                    <form action="<?= $base ?>/salon/save" method="post" style="display:inline;">
                      <input type="hidden" name="target_type" value="publication">
                      <input type="hidden" name="target_id" value="<?= $postId ?>">
                      <input type="hidden" name="return_view" value="<?= htmlspecialchars($feedView) ?>">
                      <input type="hidden" name="return_category" value="<?= (int)($categoryId ?? 0) ?>">
                      <button type="submit" class="campus-icon-action-btn <?= $isSaved ? 'is-saved text-primary' : '' ?>" title="<?= $isSaved ? 'Retirer des sauvegardes' : 'Sauvegarder' ?>">
                        <span class="material-symbols-outlined" style="<?= $isSaved ? 'font-variation-settings:\'FILL\' 1;' : '' ?>">bookmark</span>
                      </button>
                    </form>
                  </div>
                </div>

              </article>
            <?php endforeach; ?>

            <!-- Documents Sauvegardés si vue = saved -->
            <?php if ($feedView === 'saved' && $savedAttachments): ?>
              <div class="campus-section-heading">
                <h2>Documents sauvegardés</h2>
              </div>
              <div class="campus-docs-grid">
                <?php foreach ($savedAttachments as $att): ?>
                  <article class="campus-doc-card">
                    <div class="campus-doc-icon-wrap">
                      <span class="material-symbols-outlined">picture_as_pdf</span>
                    </div>
                    <div class="campus-doc-meta">
                      <strong><?= htmlspecialchars($att['nom_fichier']) ?></strong>
                      <span><?= htmlspecialchars($att['titre']) ?> • <?= htmlspecialchars($att['zone_name']) ?></span>
                    </div>
                    <div class="campus-doc-actions">
                      <a href="<?= $base ?>/attachment/download/<?= (int)$att['id'] ?>" class="campus-btn-primary">Télécharger</a>
                      <form action="<?= $base ?>/salon/save" method="post" style="display:inline;">
                        <input type="hidden" name="target_type" value="attachment">
                        <input type="hidden" name="target_id" value="<?= (int)$att['id'] ?>">
                        <input type="hidden" name="return_view" value="saved">
                        <button type="submit" class="campus-btn-outline danger">Retirer</button>
                      </form>
                    </div>
                  </article>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

          </div>
        </section>

        <!-- ============================================================
         * COLONNE DROITE (Membres actifs, Zones recommandées, Focus BDE)
         * ============================================================ -->
        <aside class="campus-col-right">

          <!-- Carte 1 : Camarades en ligne -->
          <div class="campus-widget-card">
            <div class="campus-widget-header-row">
              <span class="campus-widget-label">Camarades en ligne</span>
              <span class="campus-pulse-dot"></span>
            </div>
            
            <div class="campus-online-users-list">
              <?php if (!empty($onlineUsers)): ?>
                <?php foreach (array_slice($onlineUsers, 0, 5) as $ou):
                  $ouName = trim(($ou['prenom'] ?? '') . ' ' . ($ou['nom'] ?? ''));
                  $ouFiliere = !empty($ou['filiere']) ? $ou['filiere'] : 'Campus';
                ?>
                  <div class="campus-online-row">
                    <div class="campus-online-user-info">
                      <div class="campus-user-avatar-sm">
                        <span class="campus-avatar-fallback"><?= htmlspecialchars(strtoupper(mb_substr($ou['prenom'] ?? 'M', 0, 1))) ?></span>
                        <?php if (!empty($ou['avatar_url'])): ?>
                          <img class="campus-avatar-img" src="<?= url('public/' . ltrim((string)$ou['avatar_url'], '/')) ?>" alt="" />
                        <?php endif; ?>
                        <span class="campus-online-dot"></span>
                      </div>
                      <div class="campus-online-text-wrap">
                        <strong class="campus-online-name"><?= htmlspecialchars($ouName) ?></strong>
                        <span class="campus-online-status-text"><?= htmlspecialchars($ouFiliere) ?> • En ligne</span>
                      </div>
                    </div>
                    <a href="<?= $base ?>/messages" class="campus-chat-icon-btn" title="Envoyer un message à <?= htmlspecialchars($ouName) ?>">
                      <span class="material-symbols-outlined">chat</span>
                    </a>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="campus-online-row">
                  <div class="campus-online-user-info">
                    <div class="campus-user-avatar-sm">
                      <span class="campus-avatar-fallback">SK</span>
                      <span class="campus-online-dot"></span>
                    </div>
                    <div class="campus-online-text-wrap">
                      <strong class="campus-online-name">Sarah Koné</strong>
                      <span class="campus-online-status-text">BTS IDA • Dispo entraide</span>
                    </div>
                  </div>
                  <a href="<?= $base ?>/messages" class="campus-chat-icon-btn"><span class="material-symbols-outlined">chat</span></a>
                </div>
                <div class="campus-online-row">
                  <div class="campus-online-user-info">
                    <div class="campus-user-avatar-sm">
                      <span class="campus-avatar-fallback">MK</span>
                      <span class="campus-online-dot"></span>
                    </div>
                    <div class="campus-online-text-wrap">
                      <strong class="campus-online-name">Momo Kouassi</strong>
                      <span class="campus-online-status-text">Licence Info • En révision</span>
                    </div>
                  </div>
                  <a href="<?= $base ?>/messages" class="campus-chat-icon-btn"><span class="material-symbols-outlined">chat</span></a>
                </div>
              <?php endif; ?>
            </div>

            <a href="<?= $base ?>/friends" class="campus-view-all-friends-btn">
              Voir tous les amis
            </a>
          </div>

          <!-- Carte 2 : Zones recommandées -->
          <div class="campus-widget-card">
            <div class="campus-widget-header-row">
              <span class="campus-widget-label">Zones pour vous</span>
              <span class="material-symbols-outlined icon-teal">explore</span>
            </div>
            <div class="campus-recommended-zones">
              <div class="campus-recommend-box">
                <div class="campus-recommend-info">
                  <strong>🚀 Projets Startups & Entreprenariat</strong>
                  <span>320 membres • 14 publications/jour</span>
                </div>
                <a href="<?= $base ?>/salon" class="campus-join-btn">
                  <span class="material-symbols-outlined">group_add</span>
                  <span>Rejoindre la zone</span>
                </a>
              </div>
              <div class="campus-recommend-box">
                <div class="campus-recommend-info">
                  <strong>🌿 Éco-Campus & Entraide Étudiante</strong>
                  <span>145 membres • 5 publications/semaine</span>
                </div>
                <a href="<?= $base ?>/salon" class="campus-join-btn outline">
                  <span class="material-symbols-outlined">group_add</span>
                  <span>Rejoindre la zone</span>
                </a>
              </div>
            </div>
          </div>

          <!-- Carte 3 : Focus Événement BDE -->
          <div class="campus-bde-banner">
            <div class="campus-bde-glow"></div>
            <div class="campus-bde-countdown">
              <span class="material-symbols-outlined">celebration</span>
              <span>Dans 3 jours</span>
            </div>
            <h4 class="campus-bde-title">Tournoi E-Sport Campus : Smash & Rocket League</h4>
            <p class="campus-bde-desc">Hall Polyvalent dès 19h. Lots tech et trophée étudiant à la clé.</p>
            <div class="campus-bde-footer">
              <span class="campus-bde-badge">84 inscrits</span>
              <button type="button" class="campus-bde-btn" onclick="alert('Inscription enregistrée pour le tournoi !');">
                Participer
              </button>
            </div>
          </div>

        </aside>

      </div>
    </main>
  </div>

  <!-- ============================================================
       VOLET LATÉRAL DE DISCUSSION & COMMENTAIRES (Interactive Drawer)
       S'ouvre à gauche ou en volet flottant, permettant de commenter
       tout en continuant de défiler sur le flux du salon
       ============================================================ -->
  <aside id="commentsSideDrawer" class="comments-side-drawer" aria-hidden="true" hidden>
    <div class="drawer-header">
      <div class="drawer-header-info">
        <span class="drawer-badge">Discussion en direct</span>
        <h3 id="drawerPostTitle" class="drawer-title">Chargement de la publication...</h3>
      </div>
      <div class="drawer-header-actions">
        <a id="drawerFullViewLink" href="#" class="drawer-action-btn" title="Ouvrir la publication en pleine page" target="_blank" rel="noopener">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        </a>
        <button type="button" id="closeCommentsDrawerBtn" class="drawer-close-btn" aria-label="Fermer le volet de discussion">&times;</button>
      </div>
    </div>

    <div class="drawer-body">
      <!-- Publication principale résumée -->
      <div id="drawerPostContent" class="drawer-post-summary"></div>

      <!-- Compteur et barre des commentaires -->
      <div class="drawer-comments-heading">
        <span id="drawerCommentsCount">0 commentaire</span>
      </div>

      <!-- Liste dynamique des commentaires -->
      <div id="drawerCommentsList" class="drawer-comments-list">
        <!-- Rempli en direct via AJAX -->
      </div>
    </div>

    <!-- Formulaire d'envoi de commentaire immédiat -->
    <div class="drawer-footer">
      <form id="drawerCommentForm" action="<?= $base ?>/comment/create" method="post" enctype="multipart/form-data">
        <input type="hidden" name="ajax" value="1">
        <input type="hidden" name="publication_id" id="drawerPublicationId" value="">
        <div class="drawer-input-row">
          <textarea name="contenu" id="drawerCommentInput" placeholder="Écrire un commentaire..." rows="2" required></textarea>
          <button type="submit" id="drawerSubmitCommentBtn" class="drawer-send-btn" title="Envoyer le commentaire">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
          </button>
        </div>
      </form>
    </div>
  </aside>

  <?php include ROOT_PATH . '/resources/views/desktop/partials/post-composer-modal.php'; ?>

  <dialog class="story-composer-dialog" id="storyComposerDialog" aria-labelledby="storyComposerTitle">
    <form action="<?= $base ?>/stories/create" method="post" enctype="multipart/form-data" class="story-composer-form">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($storyCsrfToken, ENT_QUOTES, 'UTF-8') ?>" />
      <button type="button" class="story-dialog-close" data-close-story-dialog aria-label="Fermer">×</button>
      <h2 id="storyComposerTitle">Créer une story</h2>
      <p>Elle sera supprimée automatiquement après 24 heures.</p>
      <label for="storyText">Votre message <span>(300 caractères maximum)</span></label>
      <textarea id="storyText" name="texte" maxlength="300" rows="4" placeholder="Quoi de neuf sur le campus ?"></textarea>
      <label for="storyImage">Ajouter une image <span>(JPG, PNG ou WEBP, 10 Mo max.)</span></label>
      <input id="storyImage" name="image" type="file" accept="image/jpeg,image/png,image/webp" />
      <label for="storyVisibility">Qui peut voir votre story ?</label>
      <select id="storyVisibility" name="visibilite">
        <option value="amis">Mes amis</option>
        <option value="public">Tout le monde</option>
      </select>
      <button type="submit" class="story-submit-button">Publier la story</button>
    </form>
  </dialog>

  <dialog class="story-viewer-dialog" id="storyViewerDialog" aria-label="Visionneuse de stories">
    <div class="story-viewer">
      <div class="story-progress" id="storyProgress"></div>
      <div class="story-viewer-header">
        <div class="story-viewer-author" id="storyViewerAuthor"></div>
        <button type="button" data-close-story-dialog aria-label="Fermer la story">×</button>
      </div>
      <div class="story-viewer-content" id="storyViewerContent"></div>
      <button type="button" class="story-viewer-nav previous" id="storyPrevious" aria-label="Story précédente">‹</button>
      <button type="button" class="story-viewer-nav next" id="storyNext" aria-label="Story suivante">›</button>
      <div class="story-viewer-footer">
        <button type="button" class="story-view-count" id="storyViewersButton" hidden></button>
        <button type="button" class="story-delete-button" id="storyDeleteButton" hidden>Supprimer</button>
      </div>
      <div class="story-viewers-list" id="storyViewersList" hidden></div>
    </div>
  </dialog>

  <script src="<?= $asset ?>/js/desktop/stories.js?v=<?= time() ?>" defer></script>
  <script src="<?= $asset ?>/js/desktop/salon.js?v=<?= time() ?>"></script>
  <script src="<?= $asset ?>/js/feedback.js" defer></script>
</body>
</html>
