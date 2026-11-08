<?php
/**
 * Variables supplied by ProfileController::index() before this view is included.
 *
 * @var string|null $baseUrl
 * @var string|null $assetBase
 * @var array|null $user
 * @var array|null $profilePosts
 * @var array|null $profileZones
 * @var array|null $profileCategories
 * @var array|null $profileDocuments
 * @var array|null $profileStats
 * @var int|null $unreadCount
 * @var bool|null $isOwnProfile
 */
$base = is_string($baseUrl ?? null) ? $baseUrl : '';
$asset = is_string($assetBase ?? null)
  ? $assetBase
  : ($base !== '' ? $base . '/public/assets' : '/public/assets');
$logoFile = $asset . '/images/logo.jpeg';

$user = is_array($user ?? null) ? $user : [];
$profilePosts = is_array($profilePosts ?? null) ? $profilePosts : [];
$profileZones = is_array($profileZones ?? null) ? $profileZones : [];
$profileCategories = is_array($profileCategories ?? null) ? $profileCategories : [];
$profileDocuments = is_array($profileDocuments ?? null) ? $profileDocuments : [];
$profileStats = array_merge(
  ['posts' => 0, 'comments' => 0, 'documents' => 0, 'zones' => 0],
  is_array($profileStats ?? null) ? $profileStats : []
);
$unreadCount = max(0, (int)($unreadCount ?? 0));
if (!isset($_SESSION) || !is_array($_SESSION)) {
  $_SESSION = [];
}
$sessionNom = $user['nom'] ?? '';
$sessionPrenom = $user['prenom'] ?? '';
$sessionFullName = trim($sessionPrenom . ' ' . $sessionNom);
$sessionEmail = $user['email'] ?? '';
$sessionRole = $user['role'] ?? 'etudiant';
$sessionBio = $user['biographie'] ?? '';
$sessionFiliere = $user['filiere'] ?? '';
$sessionModules = $user['modules_enseignes'] ?? '';
$initials = strtoupper(mb_substr($sessionPrenom, 0, 1) . mb_substr($sessionNom, 0, 1));
$loggedInUser = is_array($loggedInUser ?? null) ? $loggedInUser : [];
$loggedInName = trim(($loggedInUser['prenom'] ?? $_SESSION['user_prenom'] ?? '') . ' ' . ($loggedInUser['nom'] ?? $_SESSION['user_nom'] ?? ''));
$loggedInRole = $loggedInUser['role'] ?? $_SESSION['user_role'] ?? 'etudiant';
$loggedInAvatarUrl = !empty($loggedInUser['avatar_url'])
  ? url('public/' . ltrim((string)$loggedInUser['avatar_url'], '/'))
  : '';
$loggedInInitial = mb_substr((string)($loggedInUser['prenom'] ?? $_SESSION['user_prenom'] ?? ''), 0, 1);
$loggedInHandle = strtolower(preg_replace(
  '/[^a-zA-Z0-9_.]/',
  '',
  (($loggedInUser['prenom'] ?? $_SESSION['user_prenom'] ?? '') ? ($loggedInUser['prenom'] ?? $_SESSION['user_prenom']) . '.' : '')
    . ($loggedInUser['nom'] ?? $_SESSION['user_nom'] ?? '')
));
$isOwnProfile = isset($isOwnProfile)
  ? (bool)$isOwnProfile
  : (isset($_SESSION['user_id'], $user['id']) && (int)$_SESSION['user_id'] === (int)$user['id']);
$roleLabels = [
  'etudiant' => 'Étudiant',
  'professeur' => 'Professeur',
  'moderateur' => 'Modérateur',
  'administrateur' => 'Administrateur',
];
$friendshipState = 'none';
if (is_array($friendshipStatus ?? null)) {
  if (($friendshipStatus['status'] ?? '') === 'accepted') {
    $friendshipState = 'friends';
  } elseif (($friendshipStatus['status'] ?? '') === 'pending') {
    $friendshipState = (int)$friendshipStatus['requested_by'] === (int)($_SESSION['user_id'] ?? 0)
      ? 'outgoing'
      : 'incoming';
  }
}
$activeProfileTab = isset($activeProfileTab) ? $activeProfileTab : 'posts';
$userHandle = strtolower(preg_replace('/[^a-zA-Z0-9_.]/', '', ($sessionPrenom ? $sessionPrenom . '.' : '') . $sessionNom));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Profil Membre — <?= htmlspecialchars($sessionFullName) ?> · EPI Tchach</title>
  <link rel="icon" type="image/svg+xml" href="<?= $asset ?>/images/logo_epi.svg" />
  <link rel="apple-touch-icon" href="<?= $asset ?>/images/logo.jpeg" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="<?= $asset ?>/css/desktop/community-shell.css" />
  <link rel="stylesheet" href="<?= $asset ?>/css/desktop/salon.css?v=<?= (int)filemtime(ROOT_PATH . '/public/assets/css/desktop/salon.css') ?>" />
  <link rel="stylesheet" href="<?= $asset ?>/css/desktop/profile.css?v=<?= (int)filemtime(ROOT_PATH . '/public/assets/css/desktop/profile.css') ?>" />
  <link rel="stylesheet" href="<?= $asset ?>/css/feedback.css" />
  <?php include ROOT_PATH . '/views/partials/theme-head.php'; ?>
</head>
<body class="community-shell community-salon community-profile" data-base-url="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>">
  <?php include ROOT_PATH . '/views/partials/theme-toggle.php'; ?>
  <?php include ROOT_PATH . '/views/partials/feedback-toast.php'; ?>
  
  <!-- Bande tricolore signature EPI -->
  <div class="tricolor-bar"></div>

  <!-- ==============================================================
   * BARRE SUPÉRIEURE (EPI STYLE TOPBAR)
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

        <a class="epi-community-pill" href="<?= $base ?>/profile">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          <span>u/<?= htmlspecialchars($loggedInHandle) ?></span>
        </a>
        <a class="mobile-page-nav mobile-home-button" href="<?= $base ?>/salon" aria-label="Accueil" title="Accueil">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        </a>
      </div>

      <!-- CENTRE : Barre de Recherche -->
      <div class="epi-topbar-center">
        <div class="epi-search-box">
          <span class="epi-search-icon" aria-hidden="true">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          </span>
          <input type="search" class="epi-search-input" placeholder="Rechercher des publications ou des profils..." autocomplete="off">
        </div>
      </div>

      <!-- DROITE : Actions -->
      <div class="epi-topbar-right">
        <a href="<?= $base ?>/messages" class="epi-icon-action" title="Messagerie" aria-label="Messagerie">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
          </svg>
          <?php if ($unreadMessagesCount > 0): ?><span class="epi-badge-count"><?= $unreadMessagesCount > 99 ? '99+' : (int)$unreadMessagesCount ?></span><?php endif; ?>
        </a>

        <button type="button" class="epi-create-post-btn" data-open-create-post-modal aria-label="Créer une publication">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          <span>Créer</span>
        </button>

        <a href="<?= $base ?>/notifications" class="epi-icon-action" title="Notifications" aria-label="Notifications">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
          </svg>
          <?php if ($unreadCount > 0): ?>
            <span class="epi-badge-count"><?= $unreadCount ?></span>
          <?php endif; ?>
        </a>

        <div class="epi-user-menu-wrapper">
          <button type="button" class="epi-profile-pill" id="userMenuToggle" aria-label="Menu du profil utilisateur" aria-haspopup="true" aria-expanded="false">
            <div class="epi-avatar-bubble">
              <span class="epi-avatar-fallback"><?= htmlspecialchars($loggedInInitial) ?></span>
              <img class="epi-avatar-image" src="<?= htmlspecialchars($loggedInAvatarUrl, ENT_QUOTES, 'UTF-8') ?>" alt="" <?= $loggedInAvatarUrl === '' ? 'hidden' : '' ?> />
              <span class="epi-status-dot" aria-label="En ligne"></span>
            </div>
            <div class="epi-profile-info">
              <span class="epi-profile-name"><?= htmlspecialchars($loggedInName) ?></span>
              <span class="epi-profile-role"><?= htmlspecialchars($roleLabels[$loggedInRole] ?? ucfirst($loggedInRole)) ?></span>
            </div>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
          </button>
          <div class="epi-user-dropdown" id="userMenuDropdown" hidden>
            <div class="epi-dropdown-header">
              <strong><?= htmlspecialchars($loggedInName) ?></strong>
              <small><?= htmlspecialchars($loggedInUser['email'] ?? $_SESSION['user_email'] ?? '') ?></small>
            </div>
            <a href="<?= $base ?>/profile" class="epi-dropdown-item">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              <span>Mon profil</span>
            </a>
            <a href="<?= $base ?>/salon?view=saved" class="epi-dropdown-item">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
              <span>Mes sauvegardes</span>
            </a>
            <?php if ($loggedInRole === 'administrateur'): ?>
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
      </div>
    </div>
  </header>

  <!-- ==============================================================
   * LAYOUT PRINCIPAL (GRID POINT TEMPLATE SCOPÉ SALON & PROFIL)
   * ============================================================== -->
  <div class="epi-salon-grid">

    <!-- ========== SIDEBAR GAUCHE (grid-area: sidebar) ========== -->
    <aside class="epi-sidebar-left">
      <div class="epi-sidebar-box">
        <div class="epi-nav-heading">Navigation</div>
        <a href="<?= $base ?>/salon" class="epi-nav-item">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          <span>Tous les salons</span>
        </a>
        <a href="<?= $base ?>/salon?view=popular" class="epi-nav-item">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>
          <span>Populaires</span>
        </a>
        <a href="<?= $base ?>/profile" class="epi-nav-item is-active">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          <span>Mon profil</span>
        </a>
        <a href="<?= $base ?>/notifications" class="epi-nav-item">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <span>Notifications</span>
        </a>

        <?php if ($loggedInRole === 'administrateur'): ?>
          <div class="epi-zone-group">
            <div class="epi-nav-heading">Administration</div>
            <a href="<?= $base ?>/admin" class="epi-nav-item" style="color:var(--community-orange);">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
              <span>Panneau Admin</span>
            </a>
          </div>
        <?php endif; ?>
      </div>
    </aside>

    <!-- ========== CONTENU PRINCIPAL (grid-area: main) ========== -->
    <main class="epi-main-feed">

      <!-- 1. En-tête / Carte Bannière Profil Style Reddit -->
      <div class="epi-profile-header-card">
        <div class="epi-profile-cover"></div>
        <div class="epi-profile-header-body">
          <div class="epi-profile-identity">
            <div class="epi-profile-avatar-large">
              <span class="profile-dynamic-initials"><?= htmlspecialchars($initials) ?></span>
              <img class="profile-avatar-image" src="<?= !empty($user['avatar_url']) ? htmlspecialchars(url('public/' . ltrim($user['avatar_url'], '/')), ENT_QUOTES, 'UTF-8') : '' ?>" alt="Photo de profil de <?= htmlspecialchars($sessionFullName, ENT_QUOTES, 'UTF-8') ?>" <?= empty($user['avatar_url']) ? 'hidden' : '' ?> />
            </div>
            <div class="epi-profile-names">
              <h1 class="epi-profile-fullname profile-dynamic-name"><?= htmlspecialchars($sessionFullName) ?></h1>
              <div class="epi-profile-handle">
                <span>u/<?= htmlspecialchars($userHandle) ?></span>
                <span class="epi-profile-role-badge"><?= htmlspecialchars($roleLabels[$sessionRole] ?? ucfirst($sessionRole)) ?></span>
                <?php if ($sessionFiliere): ?>
                  <span class="epi-profile-filiere-badge"><?= htmlspecialchars($sessionFiliere) ?></span>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <?php if ($isOwnProfile): ?>
            <button type="button" class="epi-card-action-btn open-profile-edit-modal" style="width:auto; padding:0.45rem 1.15rem;">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
              <span>Modifier le profil</span>
            </button>
          <?php elseif ($friendshipState === 'friends'): ?>
            <div class="epi-profile-social-actions">
              <a class="epi-card-action-btn primary" href="<?= $base ?>/messages?user=<?= (int)$user['id'] ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                <span>Envoyer un message</span>
              </a>
              <form method="post" action="<?= $base ?>/friends/action">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($socialCsrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                <input type="hidden" name="action" value="remove">
                <button class="epi-card-action-btn" type="submit">Retirer des amis</button>
              </form>
            </div>
          <?php elseif ($friendshipState === 'incoming'): ?>
            <div class="epi-profile-social-actions">
              <form method="post" action="<?= $base ?>/friends/action">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($socialCsrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                <input type="hidden" name="action" value="accept">
                <button class="epi-card-action-btn primary" type="submit">Accepter la demande</button>
              </form>
              <form method="post" action="<?= $base ?>/friends/action">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($socialCsrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                <input type="hidden" name="action" value="decline">
                <button class="epi-card-action-btn" type="submit">Refuser</button>
              </form>
            </div>
          <?php elseif ($friendshipState === 'outgoing'): ?>
            <div class="epi-profile-social-actions">
              <button class="epi-card-action-btn" type="button" disabled>Demande envoyée</button>
              <form method="post" action="<?= $base ?>/friends/action">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($socialCsrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                <input type="hidden" name="action" value="cancel">
                <button class="epi-card-action-btn" type="submit">Annuler</button>
              </form>
            </div>
          <?php else: ?>
            <form class="epi-profile-social-actions" method="post" action="<?= $base ?>/friends/action">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($socialCsrfToken, ENT_QUOTES, 'UTF-8') ?>">
              <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
              <input type="hidden" name="action" value="request">
              <button class="epi-card-action-btn primary" type="submit">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6m-3-3h6"/></svg>
                <span>Ajouter en ami</span>
              </button>
            </form>
          <?php endif; ?>
        </div>
      </div>

      <!-- 2. Barre des 4 Statistiques -->
      <div class="epi-profile-stats-bar">
        <div class="epi-profile-stat-box">
          <div class="epi-stat-icon">▤</div>
          <div>
            <div class="epi-stat-num" id="statSubjectsCount"><?= (int)$profileStats['posts'] ?></div>
            <div class="epi-stat-title">Publications</div>
          </div>
        </div>
        <div class="epi-profile-stat-box">
          <div class="epi-stat-icon">💬</div>
          <div>
            <div class="epi-stat-num"><?= (int)$profileStats['comments'] ?></div>
            <div class="epi-stat-title">Réponses</div>
          </div>
        </div>
        <div class="epi-profile-stat-box">
          <div class="epi-stat-icon">📎</div>
          <div>
            <div class="epi-stat-num" id="statDocumentsCount"><?= (int)$profileStats['documents'] ?></div>
            <div class="epi-stat-title">Documents</div>
          </div>
        </div>
        <div class="epi-profile-stat-box">
          <div class="epi-stat-icon">⌂</div>
          <div>
            <div class="epi-stat-num"><?= (int)$profileStats['zones'] ?></div>
            <div class="epi-stat-title">Salons rejoints</div>
          </div>
        </div>
      </div>

      <!-- 3. Onglets de Navigation Profil Style Reddit (Vue d'ensemble, etc.) -->
      <div class="epi-profile-nav-tabs">
        <button type="button" class="epi-profile-tab-btn tab-btn <?= $activeProfileTab === 'posts' ? 'is-active active' : '' ?>" data-tab="tab-posts">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          <span>Vue d'ensemble</span>
        </button>
        <button type="button" class="epi-profile-tab-btn tab-btn <?= $activeProfileTab === 'channels' ? 'is-active active' : '' ?>" data-tab="tab-channels">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          <span>Salons & Filières</span>
        </button>
        <button type="button" class="epi-profile-tab-btn tab-btn <?= $activeProfileTab === 'docs' ? 'is-active active' : '' ?>" data-tab="tab-docs">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          <span>Documents</span>
        </button>
        <?php if ($isOwnProfile): ?>
          <button type="button" class="epi-profile-tab-btn tab-btn <?= $activeProfileTab === 'saved' ? 'is-active active' : '' ?>" data-tab="tab-saved">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m19 21-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
            <span>Enregistrées</span>
          </button>
        <?php endif; ?>
        <button type="button" class="epi-profile-tab-btn tab-btn <?= $activeProfileTab === 'about' ? 'is-active active' : '' ?>" data-tab="tab-about">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
          <span>À propos</span>
        </button>
      </div>

      <!-- 4. CONTENUS DES ONGLETS -->
      
      <!-- ONGLET 1 : PUBLICATIONS & FLUX -->
      <div class="tab-content epi-tab-panel <?= $activeProfileTab === 'posts' ? 'active is-active' : '' ?>" id="tab-posts">
        
        <?php if ($isOwnProfile): ?>
          <div class="epi-quick-composer" style="margin-bottom:0.5rem;">
            <div class="epi-composer-avatar">
              <span class="epi-avatar-fallback profile-dynamic-initials"><?= htmlspecialchars(mb_substr($initials, 0, 1)) ?></span>
              <img class="epi-avatar-image profile-avatar-image" src="<?= !empty($user['avatar_url']) ? htmlspecialchars(url('public/' . ltrim($user['avatar_url'], '/')), ENT_QUOTES, 'UTF-8') : '' ?>" alt="" <?= empty($user['avatar_url']) ? 'hidden' : '' ?> />
            </div>
            <button type="button" class="epi-composer-dummy-input" data-open-create-post-modal>
              Créer une nouvelle publication...
            </button>
          </div>
        <?php endif; ?>

        <div class="posts-feed" id="postsFeedList" style="display:flex; flex-direction:column; gap:0.85rem;">
          <?php if (!$profilePosts): ?>
            <div class="epi-empty-state-box">
              <div class="epi-empty-mascot-circle">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><circle cx="9" cy="9" r="1.5"/><circle cx="15" cy="9" r="1.5"/><path d="M8 15s1.5 2 4 2 4-2 4-2"/></svg>
              </div>
              <h3><?= $isOwnProfile ? "Tu n'as pas encore de publications" : "Cet utilisateur n'a pas encore de publications" ?></h3>
              <p><?= $isOwnProfile
                ? "Dès que tu commenceras à publier dans une communauté, le contenu s'affichera ici."
                : "Les publications de cet utilisateur apparaîtront ici." ?></p>
              <?php if ($isOwnProfile): ?>
                <button type="button" class="epi-btn-submit" data-open-create-post-modal style="margin-top:0.5rem;">
                  + Créer une publication
                </button>
              <?php endif; ?>
            </div>
          <?php else: ?>
            <?php foreach ($profilePosts as $post): ?>
              <article class="epi-post-card" data-profile-post data-live-publication-id="<?= (int)$post['id'] ?>"
                data-category-id="<?= (int)$post['category_id'] ?>"
                data-post-status="<?= !empty($post['est_signale']) ? 'reported' : (in_array($post['statut'], ['ferme', 'resolu'], true) ? 'closed' : 'visible') ?>">
                
                <div class="epi-card-header">
                  <div class="epi-card-meta">
                    <span class="epi-salon-tag">s/<?= htmlspecialchars($post['category_name']) ?></span>
                    <span>•</span>
                    <span class="epi-author-badge"><?= htmlspecialchars($sessionFullName) ?></span>
                    <span>•</span>
                    <span><?= htmlspecialchars(date('d/m/Y H:i', strtotime($post['date_creation']))) ?></span>
                  </div>
                  <div class="epi-post-badges">
                    <span class="epi-status-pill"><?= htmlspecialchars(ucfirst($post['type_publication'])) ?></span>
                    <?php if (!empty($post['est_signale'])): ?>
                      <span class="epi-status-pill is-pinned" style="background:rgba(239,68,68,0.18); color:#f87171;">En modération</span>
                    <?php endif; ?>
                  </div>
                </div>

                <h2 class="epi-post-title">
                  <a href="<?= $base ?>/publication/<?= (int)$post['id'] ?>"><?= htmlspecialchars($post['titre']) ?></a>
                </h2>

                <div class="epi-post-body">
                  <?= nl2br(htmlspecialchars(mb_strimwidth(strip_tags($post['contenu']), 0, 360, '…'))) ?>
                </div>

                <div class="epi-card-actions">
                  <div class="epi-vote-capsule">
                    <span class="epi-vote-count">♥ <span data-live-likes><?= (int)$post['likes_count'] ?></span></span>
                  </div>
                  <a class="epi-pill-btn post-discussion-button" href="<?= $base ?>/publication/<?= (int)$post['id'] ?>" aria-label="Commentaires" title="Commentaires">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <span><span data-live-comment-count><?= (int)$post['comments_count'] ?></span><span class="epi-action-label"> réponses</span></span>
                  </a>
                  <button type="button" class="epi-pill-btn epi-share-btn" data-share-url="<?= $base ?>/publication/<?= (int)$post['id'] ?>" aria-label="Partager" title="Partager">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                    <span>Partager</span>
                  </button>
                  <?php if ($isOwnProfile): ?>
                    <form action="<?= $base ?>/salon/save" method="post" style="display:inline;">
                      <input type="hidden" name="target_type" value="publication">
                      <input type="hidden" name="target_id" value="<?= (int)$post['id'] ?>">
                      <input type="hidden" name="return_page" value="profile">
                      <input type="hidden" name="return_tab" value="posts">
                      <button class="epi-pill-btn epi-save-post-btn <?= !empty($post['saved_id']) ? 'is-saved' : '' ?>" type="submit" aria-label="<?= !empty($post['saved_id']) ? 'Retirer des favoris' : 'Ajouter aux favoris' ?>" title="<?= !empty($post['saved_id']) ? 'Retirer des favoris' : 'Ajouter aux favoris' ?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="<?= !empty($post['saved_id']) ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                        <span><?= !empty($post['saved_id']) ? 'En favori' : 'Favori' ?></span>
                      </button>
                    </form>
                  <?php endif; ?>
                </div>
              </article>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- ONGLET 2 : SALONS & FILIÈRES -->
      <div class="tab-content epi-tab-panel <?= $activeProfileTab === 'channels' ? 'active is-active' : '' ?>" id="tab-channels">
        <h3 style="font-size:1.15rem; font-weight:800; margin:0 0 0.75rem;">Salons et Filières rejoints</h3>
        <?php if (!$profileZones): ?>
          <p style="color:var(--community-muted);">Aucun salon n'est rattaché pour le moment.</p>
        <?php else: ?>
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
            <?php foreach ($profileZones as $zone): ?>
              <div class="epi-file-pill" style="display:flex; flex-direction:column; align-items:flex-start; gap:0.35rem;">
                <span class="epi-file-badge"><?= htmlspecialchars(ucfirst($zone['type_zone'])) ?></span>
                <strong><?= htmlspecialchars($zone['nom']) ?></strong>
                <span style="font-size:0.75rem; color:var(--community-muted);"><?= (int)$zone['membres_count'] ?> membres</span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- ONGLET 3 : DOCUMENTS PARTAGÉS -->
      <div class="tab-content epi-tab-panel <?= $activeProfileTab === 'docs' ? 'active is-active' : '' ?>" id="tab-docs">
        <h3 style="font-size:1.15rem; font-weight:800; margin:0 0 0.75rem;">Documents et Médias</h3>
        <?php if (!$profileDocuments): ?>
          <p style="color:var(--community-muted);">Aucun fichier n'a été partagé.</p>
        <?php else: ?>
          <div style="display:flex; flex-direction:column; gap:0.6rem;">
            <?php foreach ($profileDocuments as $document): ?>
              <div class="epi-file-pill">
                <span class="epi-file-badge"><?= strtoupper(pathinfo($document['nom_fichier'], PATHINFO_EXTENSION) ?: 'DOC') ?></span>
                <div style="flex:1;">
                  <strong><?= htmlspecialchars($document['nom_fichier']) ?></strong>
                  <div style="font-size:0.75rem; color:var(--community-muted);"><?= htmlspecialchars($document['publication_title']) ?> · <?= number_format(((int)$document['taille']) / 1048576, 2, ',', ' ') ?> Mo</div>
                </div>
                <a class="epi-pill-btn" href="<?= $base ?>/attachment/download/<?= (int)$document['id'] ?>">Télécharger</a>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- ONGLET 4 : ENREGISTRÉES -->
      <?php if ($isOwnProfile): ?>
        <div class="tab-content epi-tab-panel <?= $activeProfileTab === 'saved' ? 'active is-active' : '' ?>" id="tab-saved">
          <h3 style="font-size:1.15rem; font-weight:800; margin:0 0 0.75rem;">Publications & Documents enregistrés</h3>
          <p style="color:var(--community-muted); font-size:0.9rem;">Retrouvez vos éléments sauvegardés sur l'EPI Tchach.</p>
          <a href="<?= $base ?>/salon?view=saved" class="epi-btn-submit" style="display:inline-block; margin-top:0.5rem; text-decoration:none;">
            Consulter mes favoris dans les salons
          </a>
        </div>
      <?php endif; ?>

      <!-- ONGLET 5 : À PROPOS -->
      <div class="tab-content epi-tab-panel <?= $activeProfileTab === 'about' ? 'active is-active' : '' ?>" id="tab-about">
        <div class="epi-widget-card">
          <div class="epi-widget-title"><span>Informations personnelles & académiques</span></div>
          <div style="display:flex; flex-direction:column; gap:0.65rem; font-size:0.9rem;">
            <div><strong style="color:var(--community-muted); display:block; font-size:0.75rem; text-transform:uppercase;">Nom complet</strong> <span class="profile-dynamic-name"><?= htmlspecialchars($sessionFullName) ?></span></div>
            <div><strong style="color:var(--community-muted); display:block; font-size:0.75rem; text-transform:uppercase;">Adresse Email</strong> <span class="profile-dynamic-email"><?= htmlspecialchars($sessionEmail) ?></span></div>
            <div><strong style="color:var(--community-muted); display:block; font-size:0.75rem; text-transform:uppercase;">Rôle</strong> <span><?= htmlspecialchars($roleLabels[$sessionRole] ?? ucfirst($sessionRole)) ?></span></div>
            <?php if ($sessionFiliere): ?>
              <div><strong style="color:var(--community-muted); display:block; font-size:0.75rem; text-transform:uppercase;">Filière d'études</strong> <span><?= htmlspecialchars($sessionFiliere) ?></span></div>
            <?php endif; ?>
            <div><strong style="color:var(--community-muted); display:block; font-size:0.75rem; text-transform:uppercase;">Biographie</strong> <p class="profile-dynamic-bio" style="margin:0.25rem 0 0;"><?= htmlspecialchars($sessionBio ?: 'Aucune biographie renseignée.') ?></p></div>
          </div>
        </div>
      </div>

    </main>

    <!-- ========== SIDEBAR DROITE : CARTE PROFIL STYLE REDDIT ========== -->
    <aside class="epi-sidebar-right">
      
      <!-- Carte Profil Style Reddit -->
      <div class="epi-profile-user-card">
        <div class="epi-card-cover-small"></div>
        <div class="epi-card-user-info">
          
          <div class="epi-user-avatar-mini-float">
            <span class="profile-dynamic-initials"><?= htmlspecialchars(mb_substr($initials, 0, 1)) ?></span>
            <img class="profile-avatar-image" src="<?= !empty($user['avatar_url']) ? htmlspecialchars(url('public/' . ltrim($user['avatar_url'], '/')), ENT_QUOTES, 'UTF-8') : '' ?>" alt="Photo de profil de <?= htmlspecialchars($sessionFullName, ENT_QUOTES, 'UTF-8') ?>" <?= empty($user['avatar_url']) ? 'hidden' : '' ?> />
          </div>

          <div class="epi-user-name-box">
            <strong class="profile-dynamic-name"><?= htmlspecialchars($sessionFullName) ?></strong>
            <span>u/<?= htmlspecialchars($userHandle) ?></span>
          </div>

          <button type="button" class="epi-card-action-btn" id="shareProfileBtn" onclick="navigator.clipboard.writeText(window.location.href); alert('Lien du profil copié !');">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
            <span>Partager</span>
          </button>

          <p class="epi-user-bio-text profile-dynamic-bio">
            <?= htmlspecialchars($sessionBio ?: 'Aucune présentation renseignée pour le moment.') ?>
          </p>

          <div class="epi-user-stats-grid">
            <div>
              <div style="font-size:1.1rem; font-weight:800; color:var(--community-text);"><?= (int)$profileStats['posts'] ?></div>
              <div style="font-size:0.72rem; color:var(--community-muted);">Publications</div>
            </div>
            <div>
              <div style="font-size:1.1rem; font-weight:800; color:var(--community-text);"><?= (int)$profileStats['comments'] ?></div>
              <div style="font-size:0.72rem; color:var(--community-muted);">Réponses</div>
            </div>
            <div>
              <div style="font-size:1.1rem; font-weight:800; color:var(--community-text);"><?= (int)$profileStats['documents'] ?></div>
              <div style="font-size:0.72rem; color:var(--community-muted);">Documents</div>
            </div>
            <div>
              <div style="font-size:1.1rem; font-weight:800; color:var(--community-text);"><?= (int)$profileStats['zones'] ?></div>
              <div style="font-size:0.72rem; color:var(--community-muted);">Salons</div>
            </div>
          </div>

          <?php if ($isOwnProfile): ?>
            <button type="button" class="epi-card-action-btn primary open-profile-edit-modal">
              Paramètres du profil
            </button>
          <?php endif; ?>

        </div>
      </div>

      <!-- Règles / À propos -->
      <div class="epi-widget-card">
        <div class="epi-widget-title">
          <span>Règles d'EPI Tchach</span>
        </div>
        <ol style="padding-left:1.2rem; font-size:0.82rem; color:var(--community-muted); line-height:1.6; margin:0;">
          <li>Profil vérifié de l'École Polytechnique.</li>
          <li>Partagez vos retours et compétences.</li>
          <li>Conservez un ton professionnel et solidaire.</li>
        </ol>
      </div>

    </aside>

  </div>

  <!-- ============================================================
       MODALE POPUP D'ÉDITION DU PROFIL
       ============================================================ -->
  <?php if ($isOwnProfile): ?>
  <div id="profileEditModal" class="modal-backdrop-profile" aria-hidden="true" role="dialog" aria-labelledby="modalEditTitle" hidden style="display:none;">
    <div class="modal-dialog-profile">
      
      <div class="modal-header-profile">
        <div>
          <h3 id="modalEditTitle">Modifier mon Profil Membre</h3>
          <div style="font-size:0.78rem; color:var(--community-muted);">Mettez à jour vos coordonnées et informations académiques</div>
        </div>
        <button type="button" class="epi-modal-close" id="closeEditModalBtn" aria-label="Fermer">&times;</button>
      </div>

      <form id="ajaxProfileEditForm" action="<?= $base ?>/profile/update" method="POST" enctype="multipart/form-data">
        <div class="modal-body-profile">

          <div style="display:flex; align-items:center; gap:1rem;">
            <div class="profile-avatar-edit-preview">
              <span class="profile-dynamic-initials"><?= htmlspecialchars($initials) ?></span>
              <img id="editAvatarPreview" class="profile-avatar-image" src="<?= !empty($user['avatar_url']) ? htmlspecialchars(url('public/' . ltrim($user['avatar_url'], '/')), ENT_QUOTES, 'UTF-8') : '' ?>" alt="Aperçu" <?= empty($user['avatar_url']) ? 'hidden' : '' ?> />
            </div>
            <div>
              <label class="epi-pill-btn" for="editAvatarUpload" style="cursor:pointer; display:inline-block;">Changer la photo</label>
              <input id="editAvatarUpload" type="file" name="avatar" accept="image/jpeg,image/png,image/webp" hidden data-profile-avatar-input />
              <div style="font-size:0.75rem; color:var(--community-muted); margin-top:0.35rem;">JPG, PNG ou WEBP, jusqu’à 5 Mo.</div>
            </div>
          </div>

          <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
            <div class="form-group">
              <label for="editNom">Nom</label>
              <input type="text" id="editNom" name="nom" class="form-control" value="<?= htmlspecialchars($sessionNom) ?>" required />
            </div>
            <div class="form-group">
              <label for="editPrenom">Prénom</label>
              <input type="text" id="editPrenom" name="prenom" class="form-control" value="<?= htmlspecialchars($sessionPrenom) ?>" required />
            </div>
          </div>

          <div class="form-group">
            <label for="editBio">Présentation & Biographie académique</label>
            <textarea id="editBio" name="biographie" class="form-control" placeholder="Parlez de vos passions, projets d'études..."><?= htmlspecialchars($sessionBio) ?></textarea>
          </div>

          <div class="form-group">
            <label for="editPassword">Nouveau mot de passe (laisser vide pour conserver)</label>
            <input type="password" id="editPassword" name="nouveau_mot_de_passe" class="form-control" placeholder="Minimum 8 caractères" minlength="8" />
          </div>

        </div>

        <div class="modal-footer-profile">
          <button type="button" class="epi-btn-cancel" id="cancelEditModalBtn">Annuler</button>
          <button type="submit" class="epi-btn-submit">Enregistrer les modifications</button>
        </div>
      </form>

    </div>
  </div>
  <?php endif; ?>

  <?php if ($isOwnProfile): ?>
  <div id="avatarCropModal" class="modal-backdrop-profile profile-avatar-crop-modal" role="dialog" aria-modal="true" aria-labelledby="avatarCropTitle" aria-hidden="true" hidden>
    <div class="profile-avatar-crop-dialog">
      <div class="modal-header-profile">
        <div>
          <h3 id="avatarCropTitle">Recadrer la photo</h3>
          <div class="profile-avatar-crop-hint">Déplacez l’image et ajustez le zoom dans le cercle.</div>
        </div>
        <button type="button" class="epi-modal-close" id="closeAvatarCropBtn" aria-label="Annuler le recadrage">&times;</button>
      </div>
      <div class="profile-avatar-crop-content">
        <div class="profile-avatar-crop-viewport" id="avatarCropViewport">
          <img id="avatarCropImage" alt="Aperçu du recadrage" draggable="false" />
        </div>
        <label class="profile-avatar-zoom-label" for="avatarCropZoom">Zoom</label>
        <input id="avatarCropZoom" class="profile-avatar-zoom" type="range" min="1" max="3" step="0.01" value="1" />
      </div>
      <div class="modal-footer-profile">
        <button type="button" class="epi-btn-cancel" id="cancelAvatarCropBtn">Annuler</button>
        <button type="button" class="epi-btn-submit" id="applyAvatarCropBtn">Utiliser cette photo</button>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <?php include ROOT_PATH . '/views/desktop/partials/post-composer-modal.php'; ?>
  <script src="<?= $asset ?>/js/desktop/salon.js?v=<?= (int)filemtime(ROOT_PATH . '/public/assets/js/desktop/salon.js') ?>"></script>
  <script src="<?= $asset ?>/js/desktop/profile.js?v=<?= (int)filemtime(ROOT_PATH . '/public/assets/js/desktop/profile.js') ?>"></script>
</body>
</html>
