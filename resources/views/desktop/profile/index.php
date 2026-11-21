<?php
/**
 * ==============================================================
 * resources/views/desktop/profile/index.php
 * Profil Membre · Design System Campus Hub (Teal #00685f, Amber #fea619)
 * ==============================================================
 *
 * @var string|null $baseUrl
 * @var string|null $assetBase
 * @var array|null $user
 * @var array|null $profilePosts
 * @var array|null $profileZones
 * @var array|null $profileCategories
 * @var array|null $profilePostCategories
 * @var array|null $profileDocuments
 * @var array|null $savedPublications
 * @var array|null $profileStats
 * @var int|null $unreadCount
 * @var int|null $unreadMessagesCount
 * @var bool|null $isOwnProfile
 * @var string|null $socialCsrfToken
 * @var array|null $loggedInUser
 * @var array|null $friendshipStatus
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
$profilePostCategories = is_array($profilePostCategories ?? null) ? $profilePostCategories : [];
$profileDocuments = is_array($profileDocuments ?? null) ? $profileDocuments : [];
$savedPublications = is_array($savedPublications ?? null) ? $savedPublications : [];
$profileStats = array_merge(
  ['posts' => 0, 'comments' => 0, 'documents' => 0, 'zones' => 0],
  is_array($profileStats ?? null) ? $profileStats : []
);
$unreadCount = max(0, (int)($unreadCount ?? 0));
$unreadMessagesCount = max(0, (int)($unreadMessagesCount ?? 0));

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
$sessionAnnee = (int)($user['annee'] ?? $_SESSION['user_annee'] ?? 0);
$sessionAnneeScolaireLibelle = $user['annee_scolaire_libelle'] ?? ($_SESSION['user_annee_scolaire_libelle'] ?? '');
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
  'delegue_general' => 'Délégué Général',
  'delegue_de_classe' => 'Délégué de Classe',
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

$socialCsrfToken = $socialCsrfToken ?? ($_SESSION['csrf_token'] ?? '');
$activeProfileTab = isset($activeProfileTab) ? $activeProfileTab : 'posts';
$userHandle = strtolower(preg_replace('/[^a-zA-Z0-9_.]/', '', ($sessionPrenom ? $sessionPrenom . '.' : '') . $sessionNom));

// Matières et spécialités réelles de l'utilisateur
$moduleList = array_filter(array_map('trim', explode(',', (string)$sessionModules)));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Profil Membre — <?= htmlspecialchars($sessionFullName) ?> · EPI Tchach</title>
  <link rel="icon" type="image/svg+xml" href="<?= htmlspecialchars($asset) ?>/images/logo_epi.svg" />
  <link rel="apple-touch-icon" href="<?= htmlspecialchars($asset) ?>/images/logo.jpeg" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="<?= htmlspecialchars($asset) ?>/css/desktop/community-shell.css" />
  <link rel="stylesheet" href="<?= htmlspecialchars($asset) ?>/css/desktop/salon.css?v=<?= (int)filemtime(ROOT_PATH . '/public/assets/css/desktop/salon.css') ?>" />
  <link rel="stylesheet" href="<?= htmlspecialchars($asset) ?>/css/desktop/profile.css?v=<?= (int)filemtime(ROOT_PATH . '/public/assets/css/desktop/profile.css') ?>" />
  <link rel="stylesheet" href="<?= htmlspecialchars($asset) ?>/css/feedback.css" />
  <?php include ROOT_PATH . '/resources/views/partials/theme-head.php'; ?>
</head>
<body class="community-shell community-salon campus-profile-page" data-base-url="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>">
  <?php include ROOT_PATH . '/resources/views/partials/theme-toggle.php'; ?>
  <?php include ROOT_PATH . '/resources/views/partials/feedback-toast.php'; ?>
  
  <!-- Bande tricolore signature EPI -->
  <div class="tricolor-bar"></div>

  <!-- ==============================================================
   * BARRE SUPÉRIEURE (TOPBAR)
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
      </div>

      <!-- CENTRE : Barre de Recherche -->
      <div class="epi-topbar-center">
        <div class="epi-search-box">
          <span class="epi-search-icon" aria-hidden="true">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          </span>
          <input type="search" class="epi-search-input" placeholder="Rechercher publications, camarades, fiches..." autocomplete="off">
        </div>
      </div>

      <!-- DROITE : Actions & Menu Utilisateur -->
      <div class="epi-topbar-right">
        <a href="<?= $base ?>/messages" class="epi-icon-action" title="Messagerie" aria-label="Messagerie">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
          </svg>
          <?php if ($unreadMessagesCount > 0): ?>
            <span class="epi-badge-count"><?= $unreadMessagesCount > 99 ? '99+' : (int)$unreadMessagesCount ?></span>
          <?php endif; ?>
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
            <?= renderAvatar((string)($loggedInUser['prenom'] ?? 'M'), (string)($loggedInUser['nom'] ?? ''), (string)($loggedInUser['avatar_url'] ?? null), 36, true) ?>
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
            <a href="<?= $base ?>/friends" class="epi-dropdown-item">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
              <span>Mes camarades</span>
            </a>
            <a href="<?= $base ?>/salon?view=saved" class="epi-dropdown-item">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
              <span>Mes favoris</span>
            </a>
            <?php if ($loggedInRole === 'administrateur'): ?>
              <a href="<?= $base ?>/admin" class="epi-dropdown-item" style="color:var(--profile-secondary);">
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
   * LAYOUT PRINCIPAL AVEC NAVIGATION LATÉRALE
   * ============================================================== -->
  <div class="epi-salon-grid">

    <!-- ========== SIDEBAR GAUCHE ========== -->
    <aside class="epi-sidebar-left">
      <div class="epi-sidebar-box">
        <div class="epi-nav-heading">Navigation Campus</div>
        <a href="<?= $base ?>/salon" class="epi-nav-item">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          <span>Fil public / Salon</span>
        </a>
        <a href="<?= $base ?>/zones" class="epi-nav-item">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v18M3 12h18"/><circle cx="12" cy="12" r="9"/></svg>
          <span>Mes Zones</span>
        </a>
        <a href="<?= $base ?>/friends" class="epi-nav-item">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          <span>Camarades & Réseau</span>
        </a>
        <a href="<?= $base ?>/messages" class="epi-nav-item">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
          <span>Messagerie</span>
          <?php if ($unreadMessagesCount > 0): ?><small class="epi-sidebar-count"><?= $unreadMessagesCount > 99 ? '99+' : $unreadMessagesCount ?></small><?php endif; ?>
        </a>
        <a href="<?= $base ?>/notifications" class="epi-nav-item">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <span>Notifications</span>
          <?php if ($unreadCount > 0): ?><small class="epi-sidebar-count is-notification"><?= $unreadCount > 99 ? '99+' : $unreadCount ?></small><?php endif; ?>
        </a>
        <a href="<?= $base ?>/profile" class="epi-nav-item <?= $isOwnProfile ? 'is-active' : '' ?>" <?= $isOwnProfile ? 'aria-current="page"' : '' ?>>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          <span>Mon Profil</span>
        </a>

        <?php if ($isOwnProfile && $profileZones): ?>
          <div class="epi-zone-group">
            <div class="epi-nav-heading">Mes zones</div>
            <?php foreach (array_slice($profileZones, 0, 5) as $profileZone): ?>
              <a href="<?= $base ?>/salon?zone=<?= (int)$profileZone['id'] ?>" class="epi-nav-item epi-nav-zone-item">
                <span class="epi-nav-zone-dot" aria-hidden="true"></span>
                <span><?= htmlspecialchars($profileZone['nom'] ?? 'Zone') ?></span>
                <small><?= (int)($profileZone['membres_count'] ?? 0) ?></small>
              </a>
            <?php endforeach; ?>
            <a href="<?= $base ?>/zones" class="epi-nav-more">Voir toutes les zones</a>
          </div>
        <?php endif; ?>

        <?php if ($loggedInRole === 'administrateur'): ?>
          <div class="epi-zone-group">
            <div class="epi-nav-heading">Supervision</div>
            <a href="<?= $base ?>/admin" class="epi-nav-item" style="color:var(--profile-secondary); font-weight:700;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
              <span>Administration</span>
            </a>
          </div>
        <?php endif; ?>
      </div>
    </aside>

    <!-- ========== ZONE CENTRALE PROFIL (CAMPUS HUB) ========== -->
    <main class="epi-main-feed">

      <div class="campus-profile-container">

        <!-- 1. HERO COVER & IDENTITÉ ACADÉMIQUE -->
        <section class="campus-profile-hero">
          <div class="campus-profile-banner">
            <svg class="campus-profile-banner-svg" viewBox="0 0 1000 300" preserveAspectRatio="none" fill="none">
              <path d="M0,0 L350,300 L600,0 L900,300 L1200,0 V300 H0 Z" fill="#ffffff"></path>
              <circle cx="850" cy="80" r="140" fill="#fea619" opacity="0.35"></circle>
              <circle cx="200" cy="220" r="90" fill="#6bd8cb" opacity="0.45"></circle>
            </svg>
            <div class="campus-banner-badge">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
              <span>Campus Yamoussoukro • INP-HB / EPI</span>
            </div>
          </div>

          <div class="campus-profile-shelf">
            <div class="campus-profile-identity">
              <!-- Avatar avec bague et micro-dot actif -->
              <div class="campus-profile-avatar-wrap">
                <div class="campus-profile-avatar-box">
                  <span class="profile-dynamic-initials"><?= htmlspecialchars($initials) ?></span>
                  <?php if (!empty($user['avatar_url'])): ?>
                    <img class="campus-profile-avatar-img profile-avatar-image" src="<?= htmlspecialchars(url('public/' . ltrim($user['avatar_url'], '/')), ENT_QUOTES, 'UTF-8') ?>" alt="Photo de <?= htmlspecialchars($sessionFullName) ?>" />
                  <?php endif; ?>
                </div>
                <span class="campus-status-dot-active" title="Membre actif sur le campus"></span>
              </div>

              <!-- Informations textuelles & Badges -->
              <div class="campus-profile-info">
                <div class="campus-profile-name-row">
                  <h1 class="campus-profile-name profile-dynamic-name"><?= htmlspecialchars($sessionFullName) ?></h1>
                  <span class="campus-verified-icon" title="Profil vérifié par l'école">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                  </span>
                  <span class="campus-role-badge <?= htmlspecialchars($sessionRole) ?> profile-dynamic-role">
                    <?= htmlspecialchars($roleLabels[$sessionRole] ?? ucfirst($sessionRole)) ?>
                  </span>
                </div>

                <div class="campus-profile-meta-row">
                  <span class="campus-profile-handle">@<?= htmlspecialchars($userHandle) ?></span>
                  <span>•</span>
                  <span class="profile-dynamic-filiere"><?= htmlspecialchars($sessionFiliere ?: 'Filière non spécifiée') ?></span>
                  <?php if ($sessionAnnee > 0): ?>
                    <span>•</span>
                    <span class="profile-dynamic-annee" style="color:#0d9488; font-weight:700;"><?= $sessionAnnee === 1 ? '1ère année' : $sessionAnnee . 'ème année' ?></span>
                  <?php endif; ?>
                  <?php if (!empty($sessionAnneeScolaireLibelle)): ?>
                    <span>•</span>
                    <span class="profile-dynamic-promo" style="color:#6366f1; font-weight:700;">Promo <?= htmlspecialchars($sessionAnneeScolaireLibelle) ?></span>
                  <?php endif; ?>
                  <span class="campus-cas-pill">
                    <span class="campus-cas-dot"></span>
                    <span>CAS Vérifié</span>
                  </span>
                </div>
              </div>
            </div>

            <!-- Actions du profil -->
            <div class="campus-profile-actions">
              <button type="button" class="campus-profile-btn secondary" onclick="navigator.clipboard.writeText(window.location.href); window.showToast('Lien du profil copié dans le presse-papier !', 'success');">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                <span>Partager</span>
              </button>

              <?php if ($isOwnProfile): ?>
                <button type="button" class="campus-profile-btn primary open-profile-edit-modal">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  <span>Modifier mon profil</span>
                </button>
                <a class="campus-profile-btn secondary" href="<?= $base ?>/binomage">
                  <span>🎓 Espace Binômage</span>
                </a>
              <?php else: ?>
                <!-- 1. ACTIONS SOCIALES (AMIS) -->
                <?php if ($friendshipState === 'friends'): ?>
                  <a class="campus-profile-btn primary" href="<?= $base ?>/messages?user=<?= (int)$user['id'] ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                    <span>Message privé</span>
                  </a>
                  <form method="post" action="<?= $base ?>/friends/action" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($socialCsrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                    <input type="hidden" name="action" value="remove">
                    <input type="hidden" name="return_url" value="profile?id=<?= (int)$user['id'] ?>">
                    <button class="campus-profile-btn secondary" type="submit" onclick="return confirm('Retirer ce camarade de votre réseau ?');">Ami ✓ (Retirer)</button>
                  </form>
                <?php elseif ($friendshipState === 'incoming'): ?>
                  <form method="post" action="<?= $base ?>/friends/action" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($socialCsrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                    <input type="hidden" name="action" value="accept">
                    <input type="hidden" name="return_url" value="profile?id=<?= (int)$user['id'] ?>">
                    <button class="campus-profile-btn primary" type="submit">Accepter l'ami</button>
                  </form>
                  <form method="post" action="<?= $base ?>/friends/action" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($socialCsrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                    <input type="hidden" name="action" value="decline">
                    <input type="hidden" name="return_url" value="profile?id=<?= (int)$user['id'] ?>">
                    <button class="campus-profile-btn secondary" type="submit">Décliner</button>
                  </form>
                <?php elseif ($friendshipState === 'outgoing'): ?>
                  <button class="campus-profile-btn secondary" type="button" disabled>Demande d'ami envoyée</button>
                  <form method="post" action="<?= $base ?>/friends/action" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($socialCsrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                    <input type="hidden" name="action" value="cancel">
                    <input type="hidden" name="return_url" value="profile?id=<?= (int)$user['id'] ?>">
                    <button class="campus-profile-btn secondary" type="submit">Annuler</button>
                  </form>
                <?php else: ?>
                  <form method="post" action="<?= $base ?>/friends/action" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($socialCsrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                    <input type="hidden" name="action" value="request">
                    <input type="hidden" name="return_url" value="profile?id=<?= (int)$user['id'] ?>">
                    <button class="campus-profile-btn primary" type="submit">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6m-3-3h6"/></svg>
                      <span>Ajouter en ami</span>
                    </button>
                  </form>
                <?php endif; ?>

                <!-- 2. ACTIONS BINÔMAGE (MENTORAT ACADÉMIQUE SÉPARÉ) -->
                <?php if (!empty($binomageRelation) && $binomageRelation['statut'] === 'accepte'): ?>
                  <?php if ((int)$binomageRelation['mentor_id'] === (int)$user['id']): ?>
                    <span class="campus-profile-btn" style="background:#0d9488; color:#fff; font-weight:700; cursor:default;">
                      🎓 Mon Mentor
                    </span>
                  <?php else: ?>
                    <span class="campus-profile-btn" style="background:#0284c7; color:#fff; font-weight:700; cursor:default;">
                      🌱 Mon Mentoré
                    </span>
                  <?php endif; ?>
                  <form method="post" action="<?= $base ?>/binomage/action" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['binomage_csrf'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="action" value="terminate">
                    <input type="hidden" name="relation_id" value="<?= (int)$binomageRelation['id'] ?>">
                    <input type="hidden" name="return_url" value="profile?id=<?= (int)$user['id'] ?>">
                    <button class="campus-profile-btn secondary" type="submit" onclick="return confirm('Voulez-vous vraiment rompre ce binômage de mentorat ?');" title="Mettre fin au mentorat">
                      Rompre le binômage
                    </button>
                  </form>
                <?php elseif (!empty($binomageRelation) && $binomageRelation['statut'] === 'en_attente'): ?>
                  <?php if ((int)$binomageRelation['mentore_id'] === (int)$currentUserId): ?>
                    <button class="campus-profile-btn secondary" type="button" disabled style="opacity:0.85;">
                      ⏳ Demande de mentor envoyée
                    </button>
                    <form method="post" action="<?= $base ?>/binomage/action" style="display:inline;">
                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['binomage_csrf'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                      <input type="hidden" name="action" value="cancel">
                      <input type="hidden" name="relation_id" value="<?= (int)$binomageRelation['id'] ?>">
                      <input type="hidden" name="return_url" value="profile?id=<?= (int)$user['id'] ?>">
                      <button class="campus-profile-btn secondary" type="submit">Annuler la demande</button>
                    </form>
                  <?php else: ?>
                    <form method="post" action="<?= $base ?>/binomage/action" style="display:inline;">
                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['binomage_csrf'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                      <input type="hidden" name="action" value="accept">
                      <input type="hidden" name="relation_id" value="<?= (int)$binomageRelation['id'] ?>">
                      <input type="hidden" name="return_url" value="profile?id=<?= (int)$user['id'] ?>">
                      <button class="campus-profile-btn primary" type="submit" style="background:#0d9488; border-color:#0d9488;">
                        Accepter comme mentoré
                      </button>
                    </form>
                    <form method="post" action="<?= $base ?>/binomage/action" style="display:inline;">
                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['binomage_csrf'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                      <input type="hidden" name="action" value="refuse">
                      <input type="hidden" name="relation_id" value="<?= (int)$binomageRelation['id'] ?>">
                      <input type="hidden" name="return_url" value="profile?id=<?= (int)$user['id'] ?>">
                      <button class="campus-profile-btn secondary" type="submit">Refuser</button>
                    </form>
                  <?php endif; ?>
                <?php elseif (!empty($canRequestMentor)): ?>
                  <button type="button" class="campus-profile-btn primary" onclick="document.getElementById('modalRequestMentor').style.display='flex';" style="background:#0d9488; border-color:#0d9488;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                    <span>Demander comme mentor</span>
                  </button>
                <?php endif; ?>

                <!-- Bouton Signaler le profil -->
                <button type="button" class="campus-profile-btn secondary btn-report-trigger" 
                  data-report-type="utilisateur" 
                  data-report-id="<?= (int)$user['id'] ?>" 
                  data-report-title="<?= htmlspecialchars($user['prenom'] . ' ' . $user['nom'], ENT_QUOTES, 'UTF-8') ?>"
                  title="Signaler ce compte" style="color:#ef4444; border-color:rgba(239, 68, 68, 0.35);">
                  <span class="material-symbols-outlined" style="font-size:1.1rem; vertical-align:middle; margin-right:4px;">flag</span>
                  Signaler ce profil
                </button>
              <?php endif; ?>
            </div>
          </div>
        </section>

        <!-- 2. CARTES D'IMPACT ACADÉMIQUE DYNAMIQUES -->
        <section class="campus-impact-grid">
          <div class="campus-impact-card">
            <div class="campus-impact-card-top">
              <div>
                <span class="campus-impact-label">Publications</span>
                <div class="campus-impact-value" id="statSubjectsCount"><?= (int)$profileStats['posts'] ?></div>
              </div>
              <div class="campus-impact-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
              </div>
            </div>
            <div class="campus-impact-sub">
              <span><?= (int)$profileStats['posts'] ?> sujet(s) et question(s) sur le campus</span>
            </div>
          </div>

          <div class="campus-impact-card">
            <div class="campus-impact-card-top">
              <div>
                <span class="campus-impact-label">Réponses & Discussions</span>
                <div class="campus-impact-value"><?= (int)$profileStats['comments'] ?></div>
              </div>
              <div class="campus-impact-icon" style="background:var(--profile-secondary-container); color:var(--profile-secondary);">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
              </div>
            </div>
            <div class="campus-impact-sub">
              <span>💬 <?= (int)$profileStats['comments'] ?> réponse(s) et contribution(s)</span>
            </div>
          </div>

          <div class="campus-impact-card">
            <div class="campus-impact-card-top">
              <div>
                <span class="campus-impact-label">Documents & Fiches</span>
                <div class="campus-impact-value" id="statDocumentsCount"><?= (int)$profileStats['documents'] ?></div>
              </div>
              <div class="campus-impact-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
              </div>
            </div>
            <div class="campus-impact-sub">
              <span>📎 <?= (int)$profileStats['documents'] ?> ressource(s) partagée(s)</span>
            </div>
          </div>
        </section>

        <!-- 3. GRILLE CENTRALE (2 COLONNES : SIDEBAR 320px + FEED PRINCIPAL) -->
        <section class="campus-profile-grid">

          <!-- COLONNE GAUCHE : INFOS ACADÉMIQUES & COMPÉTENCES -->
          <div class="campus-profile-sidebar">
            <!-- Carte Cursus & Présentation -->
            <div class="campus-sidebar-card">
              <h2 class="campus-sidebar-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span>Identité & Cursus</span>
              </h2>

              <div class="campus-info-list">
                <div class="campus-info-item">
                  <span class="campus-info-label">Établissement</span>
                  <span class="campus-info-val">INP-HB Yamoussoukro — EPI</span>
                </div>
                <div class="campus-info-item">
                  <span class="campus-info-label">Filière d'Excellence</span>
                  <span class="campus-info-val profile-dynamic-filiere"><?= htmlspecialchars($sessionFiliere ?: 'Filière non renseignée') ?></span>
                </div>
                <div class="campus-info-item">
                  <span class="campus-info-label">Statut & Rôle</span>
                  <span class="campus-info-val"><?= htmlspecialchars($roleLabels[$sessionRole] ?? ucfirst($sessionRole)) ?></span>
                </div>
                <div class="campus-info-item">
                  <span class="campus-info-label">E-mail Académique</span>
                  <span class="campus-info-val"><?= htmlspecialchars($sessionEmail) ?></span>
                </div>
              </div>

              <!-- Biographie -->
              <div style="margin-top:0.25rem;">
                <span class="campus-info-label" style="display:block; margin-bottom:0.35rem;">Présentation</span>
                <p class="profile-dynamic-bio" style="font-size:0.875rem; color:var(--profile-text-muted); line-height:1.55; margin:0;">
                  <?= htmlspecialchars($sessionBio ?: 'Aucune biographie académique renseignée pour le moment.') ?>
                </p>
              </div>

              <!-- Bouton Accès Annuaire Camarades -->
              <a href="<?= $base ?>/friends?user=<?= (int)$user['id'] ?>" class="campus-profile-btn secondary" style="justify-content:center; margin-top:0.5rem; text-decoration:none;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span><?= $isOwnProfile ? 'Consulter mes camarades' : 'Voir ses camarades' ?></span>
              </a>
            </div>

            <!-- Carte Matières & Compétences -->
            <div class="campus-sidebar-card">
              <h2 class="campus-sidebar-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                <span>Matières & Spécialités</span>
              </h2>
              <div class="campus-chips-wrap">
                <?php if (empty($moduleList)): ?>
                  <span style="font-size:0.8rem; color:var(--profile-text-muted); font-style:italic;">
                    <?= $isOwnProfile ? 'Aucune matière spécifiée. Cliquez sur "Modifier mon profil" pour en ajouter.' : 'Aucune matière spécifiée pour ce profil.' ?>
                  </span>
                <?php else: ?>
                  <?php foreach ($moduleList as $mod): ?>
                    <span class="campus-chip"><?= htmlspecialchars($mod) ?></span>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>

            <!-- Carte Salons Fréquentés -->
            <div class="campus-sidebar-card">
              <h2 class="campus-sidebar-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                <span>Espaces Rejoints (<?= count($profileZones) ?>)</span>
              </h2>
              <div style="display:flex; flex-direction:column; gap:0.4rem;">
                <?php if (empty($profileZones)): ?>
                  <span style="font-size:0.8rem; color:var(--profile-text-muted);">Aucun salon spécifique rattaché.</span>
                <?php else: ?>
                  <?php foreach (array_slice($profileZones, 0, 5) as $pz): ?>
                    <a href="<?= $base ?>/salon?zone=<?= (int)($pz['id'] ?? 0) ?>" class="campus-zone-mini-link">
                      <span class="campus-zone-mini-name">
                        <span style="width:7px; height:7px; border-radius:50%; background:var(--profile-primary); display:inline-block;"></span>
                        <span><?= htmlspecialchars($pz['nom'] ?? 'Salon') ?></span>
                      </span>
                      <span class="campus-zone-count"><?= (int)($pz['membres_count'] ?? 1) ?></span>
                    </a>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- COLONNE DROITE : TABS & FLUX D'ACTIVITÉ -->
          <div class="campus-profile-main">

            <!-- BARRE DES ONGLETS SEGMENTÉS -->
            <div class="campus-tabs-toolbar">
              <div class="campus-profile-tabs" role="tablist">
                <button type="button" class="tab-btn <?= $activeProfileTab === 'posts' ? 'is-active active' : '' ?>" data-tab="tab-posts" role="tab">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                  <span>Publications</span>
                  <span class="tab-counter"><?= count($profilePosts) ?></span>
                </button>
                <button type="button" class="tab-btn <?= $activeProfileTab === 'docs' ? 'is-active active' : '' ?>" data-tab="tab-docs" role="tab">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                  <span>Documents</span>
                  <span class="tab-counter"><?= count($profileDocuments) ?></span>
                </button>
                <button type="button" class="tab-btn <?= $activeProfileTab === 'channels' ? 'is-active active' : '' ?>" data-tab="tab-channels" role="tab">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                  <span>Espaces</span>
                  <span class="tab-counter"><?= count($profileZones) ?></span>
                </button>
                <?php if ($isOwnProfile): ?>
                  <button type="button" class="tab-btn <?= $activeProfileTab === 'saved' ? 'is-active active' : '' ?>" data-tab="tab-saved" role="tab">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m19 21-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                    <span>Favoris</span>
                    <span class="tab-counter"><?= count($savedPublications) ?></span>
                  </button>
                <?php endif; ?>
                <button type="button" class="tab-btn <?= $activeProfileTab === 'about' ? 'is-active active' : '' ?>" data-tab="tab-about" role="tab">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                  <span>À propos</span>
                </button>
              </div>

              <!-- Filtres interactifs par catégorie et statut -->
              <div class="campus-filter-toolbar">
                <select id="profilePostCategoryFilter" class="campus-select-filter">
                  <option value="all">Toutes catégories</option>
                  <?php
                  $displayCats = !empty($profilePostCategories) ? $profilePostCategories : $profileCategories;
                  foreach ($displayCats as $cat):
                    $catId = (int)($cat['id'] ?? 0);
                    $catTitle = htmlspecialchars($cat['nom'] ?? $cat['name'] ?? 'Catégorie');
                  ?>
                    <option value="<?= $catId ?>"><?= $catTitle ?></option>
                  <?php endforeach; ?>
                </select>
                <select id="profilePostStatusFilter" class="campus-select-filter">
                  <option value="all">Tous statuts</option>
                  <option value="visible">Visibles</option>
                  <option value="reported">En modération</option>
                </select>
              </div>
            </div>

            <!-- CONTENU DE L'ONGLET 1 : PUBLICATIONS -->
            <div class="tab-content <?= $activeProfileTab === 'posts' ? 'active is-active' : '' ?>" id="tab-posts">
              <?php if ($isOwnProfile): ?>
                <div style="background:var(--profile-card-bg); border:1px solid var(--profile-border); border-radius:var(--profile-radius-lg); padding:1rem 1.25rem; display:flex; align-items:center; gap:0.75rem;">
                  <div style="width:38px; height:38px; border-radius:var(--profile-radius-md); background:var(--profile-primary-container); color:var(--profile-primary); display:flex; align-items:center; justify-content:center; font-weight:800;">
                    <?= htmlspecialchars(mb_substr($initials, 0, 1)) ?>
                  </div>
                  <button type="button" class="form-control" data-open-create-post-modal style="text-align:left; color:var(--profile-text-muted); cursor:pointer;">
                    Publier une question, fiche ou ressource de cours...
                  </button>
                </div>
              <?php endif; ?>

              <div id="postsFeedList" style="display:flex; flex-direction:column; gap:1rem;">
                <?php if (empty($profilePosts)): ?>
                  <div class="campus-empty-box">
                    <div class="campus-empty-icon">📝</div>
                    <h3><?= $isOwnProfile ? "Aucune publication pour le moment" : "Cet utilisateur n'a pas encore publié" ?></h3>
                    <p><?= $isOwnProfile ? "Partagez des fiches de cours, posez des questions de révision ou lancez des débats dans vos salons." : "Les publications académiques de cet utilisateur apparaîtront ici." ?></p>
                    <?php if ($isOwnProfile): ?>
                      <button type="button" class="campus-profile-btn primary" data-open-create-post-modal style="margin-top:0.5rem;">
                        + Créer ma première publication
                      </button>
                    <?php endif; ?>
                  </div>
                <?php else: ?>
                  <?php foreach ($profilePosts as $post): ?>
                    <article class="campus-post-card" data-profile-post
                             data-live-publication-id="<?= (int)$post['id'] ?>"
                             data-category-id="<?= (int)$post['category_id'] ?>"
                             data-post-status="<?= !empty($post['est_signale']) ? 'reported' : (in_array($post['statut'], ['ferme', 'resolu'], true) ? 'closed' : 'visible') ?>">
                      
                      <div class="campus-post-header">
                        <div class="campus-post-author" style="display:flex; align-items:center; gap:0.6rem;">
                          <?= renderAvatar((string)($user['prenom'] ?? 'M'), (string)($user['nom'] ?? ''), (string)($user['avatar_url'] ?? null), 32) ?>
                          <div>
                            <div class="campus-post-author-name"><?= htmlspecialchars($sessionFullName) ?></div>
                            <div class="campus-post-time"><?= htmlspecialchars(date('d/m/Y à H:i', strtotime($post['date_creation']))) ?></div>
                          </div>
                        </div>

                        <div class="campus-post-badges">
                          <span class="campus-post-tag"><?= htmlspecialchars($post['category_name']) ?></span>
                          <?php if (!empty($post['est_signale'])): ?>
                            <span class="campus-post-status is-warning">En modération</span>
                          <?php else: ?>
                            <span class="campus-post-status"><?= htmlspecialchars(ucfirst($post['type_publication'])) ?></span>
                          <?php endif; ?>
                        </div>
                      </div>

                      <h3 class="campus-post-title">
                        <a href="<?= $base ?>/publication/<?= (int)$post['id'] ?>" style="color:inherit; text-decoration:none;">
                          <?= htmlspecialchars($post['titre']) ?>
                        </a>
                      </h3>

                      <p class="campus-post-excerpt">
                        <?= nl2br(htmlspecialchars(mb_strimwidth(strip_tags($post['contenu']), 0, 280, '…'))) ?>
                      </p>

                      <div class="campus-post-footer">
                        <div class="campus-post-stats-row">
                          <span class="campus-post-stat-item">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" style="color:#ef4444;"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                            <span><?= (int)$post['likes_count'] ?></span>
                          </span>
                          <span class="campus-post-stat-item">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            <span><?= (int)$post['comments_count'] ?> réponses</span>
                          </span>
                        </div>

                        <div style="display:flex; align-items:center; gap:0.4rem;">
                          <button type="button" class="campus-profile-btn secondary" style="padding:0.35rem 0.65rem; font-size:0.75rem;" onclick="navigator.clipboard.writeText(window.location.origin + '<?= $base ?>/publication/<?= (int)$post['id'] ?>'); window.showToast('Lien copié dans le presse-papier !', 'success');">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                            <span>Partager</span>
                          </button>
                          <?php if ($isOwnProfile): ?>
                            <form action="<?= $base ?>/salon/save" method="post" style="display:inline;">
                              <input type="hidden" name="target_type" value="publication">
                              <input type="hidden" name="target_id" value="<?= (int)$post['id'] ?>">
                              <input type="hidden" name="return_page" value="profile">
                              <input type="hidden" name="return_tab" value="posts">
                              <button class="campus-profile-btn secondary" type="submit" style="padding:0.35rem 0.65rem; font-size:0.75rem;">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="<?= !empty($post['saved_id']) ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                                <span><?= !empty($post['saved_id']) ? 'Enregistré' : 'Favori' ?></span>
                              </button>
                            </form>
                          <?php endif; ?>
                          <a href="<?= $base ?>/publication/<?= (int)$post['id'] ?>" class="campus-profile-btn secondary" style="padding:0.35rem 0.75rem; font-size:0.75rem;">
                            <span>Discussion →</span>
                          </a>
                        </div>
                      </div>
                    </article>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>

            <!-- CONTENU DE L'ONGLET 2 : DOCUMENTS & FICHES -->
            <div class="tab-content <?= $activeProfileTab === 'docs' ? 'active is-active' : '' ?>" id="tab-docs">
              <?php if (empty($profileDocuments)): ?>
                <div class="campus-empty-box">
                  <div class="campus-empty-icon">📁</div>
                  <h3>Aucun document ou fiche partagée</h3>
                  <p>Partagez vos polycopiés, corrigés d'examens ou fiches de révision pour aider la communauté.</p>
                </div>
              <?php else: ?>
                <div class="campus-docs-grid">
                  <?php foreach ($profileDocuments as $document):
                    $ext = strtoupper(pathinfo($document['nom_fichier'], PATHINFO_EXTENSION) ?: 'DOC');
                  ?>
                    <div class="campus-doc-card" data-profile-document data-category-id="<?= (int)($document['category_id'] ?? 0) ?>">
                      <div class="campus-doc-top">
                        <div class="campus-doc-icon">
                          <span style="font-size:0.8rem; font-weight:800;"><?= htmlspecialchars($ext) ?></span>
                        </div>
                        <div class="campus-doc-info">
                          <span class="campus-doc-name" title="<?= htmlspecialchars($document['nom_fichier']) ?>">
                            <?= htmlspecialchars($document['nom_fichier']) ?>
                          </span>
                          <span class="campus-doc-meta">
                            <?= htmlspecialchars($document['publication_title'] ?? 'Fiche') ?> · <?= number_format(((int)$document['taille']) / 1048576, 2, ',', ' ') ?> Mo
                          </span>
                        </div>
                      </div>
                      <a class="campus-doc-btn" href="<?= $base ?>/attachment/download/<?= (int)$document['id'] ?>">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        <span>Télécharger</span>
                      </a>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>

            <!-- CONTENU DE L'ONGLET 3 : SALONS & ZONES -->
            <div class="tab-content <?= $activeProfileTab === 'channels' ? 'active is-active' : '' ?>" id="tab-channels">
              <?php if (empty($profileZones)): ?>
                <div class="campus-empty-box">
                  <div class="campus-empty-icon">🏛️</div>
                  <h3>Aucun salon rattaché</h3>
                  <p>Explorez les espaces de filières et groupes d'entraide pour rejoindre vos camarades.</p>
                </div>
              <?php else: ?>
                <div class="campus-zones-grid">
                  <?php foreach ($profileZones as $zone): ?>
                    <a href="<?= $base ?>/salon?category=<?= (int)($zone['id'] ?? 0) ?>" class="campus-zone-card">
                      <div class="campus-zone-card-header">
                        <span class="campus-zone-dot"></span>
                        <h4 class="campus-zone-card-title"><?= htmlspecialchars($zone['nom']) ?></h4>
                      </div>
                      <p class="campus-zone-card-desc">
                        <?= htmlspecialchars(ucfirst($zone['type_zone'] ?? 'Salon')) ?> · Espace d'échanges académiques.
                      </p>
                      <div style="font-size:0.775rem; color:var(--profile-primary); font-weight:700;">
                        <?= (int)($zone['membres_count'] ?? 1) ?> membres connectés →
                      </div>
                    </a>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>

            <!-- CONTENU DE L'ONGLET 4 : FAVORIS & SAUVEGARDES -->
            <?php if ($isOwnProfile): ?>
              <div class="tab-content <?= $activeProfileTab === 'saved' ? 'active is-active' : '' ?>" id="tab-saved">
                <?php if (empty($savedPublications)): ?>
                  <div class="campus-empty-box">
                    <div class="campus-empty-icon">🔖</div>
                    <h3>Aucune publication enregistrée</h3>
                    <p>Enregistrez vos publications et fiches favorites pour les retrouver rapidement ici.</p>
                    <a href="<?= $base ?>/salon" class="campus-profile-btn secondary" style="margin-top:0.5rem; text-decoration:none;">
                      Explorer le fil du salon →
                    </a>
                  </div>
                <?php else: ?>
                  <div style="display:flex; flex-direction:column; gap:1rem;">
                    <?php foreach ($savedPublications as $savedPost): ?>
                      <article class="campus-post-card">
                        <div class="campus-post-header">
                          <div class="campus-post-badges">
                            <span class="campus-post-tag"><?= htmlspecialchars($savedPost['category_name'] ?? 'Salon') ?></span>
                            <span class="campus-post-status">Sauvegardé</span>
                          </div>
                        </div>

                        <h3 class="campus-post-title">
                          <a href="<?= $base ?>/publication/<?= (int)$savedPost['id'] ?>" style="color:inherit; text-decoration:none;">
                            <?= htmlspecialchars($savedPost['titre']) ?>
                          </a>
                        </h3>

                        <p class="campus-post-excerpt">
                          <?= nl2br(htmlspecialchars(mb_strimwidth(strip_tags($savedPost['contenu']), 0, 240, '…'))) ?>
                        </p>

                        <div class="campus-post-footer">
                          <span class="campus-post-stat-item">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" style="color:#ef4444;"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                            <span><?= (int)($savedPost['likes_count'] ?? 0) ?></span>
                          </span>

                          <div style="display:flex; align-items:center; gap:0.5rem;">
                            <form action="<?= $base ?>/salon/save" method="post" style="display:inline;">
                              <input type="hidden" name="target_type" value="publication">
                              <input type="hidden" name="target_id" value="<?= (int)$savedPost['id'] ?>">
                              <input type="hidden" name="return_page" value="profile">
                              <input type="hidden" name="return_tab" value="saved">
                              <button class="campus-profile-btn secondary" type="submit" style="padding:0.35rem 0.65rem; font-size:0.75rem;">
                                Retirer
                              </button>
                            </form>
                            <a href="<?= $base ?>/publication/<?= (int)$savedPost['id'] ?>" class="campus-profile-btn primary" style="padding:0.35rem 0.75rem; font-size:0.75rem;">
                              Consulter →
                            </a>
                          </div>
                        </div>
                      </article>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>
            <?php endif; ?>

            <!-- CONTENU DE L'ONGLET 5 : À PROPOS -->
            <div class="tab-content <?= $activeProfileTab === 'about' ? 'active is-active' : '' ?>" id="tab-about">
              <div class="campus-sidebar-card">
                <h3 class="campus-sidebar-title">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                  <span>Charte & Engagement d'Intégrité Polytech</span>
                </h3>
                <p style="font-size:0.875rem; color:var(--profile-text-muted); line-height:1.6; margin:0;">
                  Ce compte est rattaché au Système Centralisé d'Authentification (CAS) de l'Institut National Polytechnique Félix Houphouët-Boigny. 
                  Toutes les contributions partagées sont régies par le code d'honneur académique.
                </p>
                <div style="display:flex; flex-direction:column; gap:0.5rem; margin-top:0.5rem; font-size:0.825rem; color:var(--profile-text);">
                  <div style="display:flex; align-items:center; gap:0.5rem;">
                    <span style="color:#10b981; font-weight:800;">✓</span>
                    <span>Profil académique vérifié par la scolarité.</span>
                  </div>
                  <div style="display:flex; align-items:center; gap:0.5rem;">
                    <span style="color:#10b981; font-weight:800;">✓</span>
                    <span>Accès certifié aux ressources pédagogiques partagées.</span>
                  </div>
                  <div style="display:flex; align-items:center; gap:0.5rem;">
                    <span style="color:#10b981; font-weight:800;">✓</span>
                    <span>Respect de l'entraide solidaire et bienveillante.</span>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </section>

      </div>

    </main>

  </div>

  <!-- ============================================================
       MODALE D'ÉDITION DU PROFIL (AJAX)
       ============================================================ -->
  <?php if ($isOwnProfile): ?>
  <div id="profileEditModal" class="modal-backdrop-profile" aria-hidden="true" role="dialog" aria-labelledby="modalEditTitle" hidden style="display:none;">
    <div class="modal-dialog-profile">
      
      <div class="modal-header-profile">
        <div>
          <h3 id="modalEditTitle">Modifier mon profil académique</h3>
          <div style="font-size:0.78rem; color:var(--profile-text-muted);">Mettez à jour vos informations et votre photo</div>
        </div>
        <button type="button" class="epi-modal-close" id="closeEditModalBtn" aria-label="Fermer">&times;</button>
      </div>

      <form id="ajaxProfileEditForm" action="<?= $base ?>/profile/update" method="POST" enctype="multipart/form-data">
        <div class="modal-body-profile">

          <div class="avatar-edit-preview-row">
            <div class="avatar-edit-circle">
              <span class="profile-dynamic-initials"><?= htmlspecialchars($initials) ?></span>
              <img id="editAvatarPreview" class="profile-avatar-image" src="<?= !empty($user['avatar_url']) ? htmlspecialchars(url('public/' . ltrim($user['avatar_url'], '/')), ENT_QUOTES, 'UTF-8') : '' ?>" alt="Aperçu" <?= empty($user['avatar_url']) ? 'hidden' : '' ?> />
            </div>
            <div>
              <button type="button" class="campus-profile-btn secondary" id="triggerAvatarUploadBtn">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                <span>Changer la photo</span>
              </button>
              <input id="editAvatarUpload" type="file" name="avatar" accept="image/jpeg,image/png,image/webp" hidden data-profile-avatar-input />
              <div style="font-size:0.75rem; color:var(--profile-text-muted); margin-top:0.35rem;">Formats JPG, PNG ou WEBP (max 5 Mo)</div>
            </div>
          </div>

          <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
            <div class="form-group-profile">
              <label for="editNom">Nom</label>
              <input type="text" id="editNom" name="nom" class="form-control" value="<?= htmlspecialchars($sessionNom) ?>" required />
            </div>
            <div class="form-group-profile">
              <label for="editPrenom">Prénom</label>
              <input type="text" id="editPrenom" name="prenom" class="form-control" value="<?= htmlspecialchars($sessionPrenom) ?>" required />
            </div>
          </div>

          <div style="display:grid; grid-template-columns:1.2fr 1fr 1fr 1fr; gap:0.75rem;">
            <div class="form-group-profile">
              <label for="editFiliere">Filière d'études</label>
              <input type="text" id="editFiliere" name="filiere" class="form-control" value="<?= htmlspecialchars($sessionFiliere) ?>" placeholder="Ex: Informatique, BTP..." />
            </div>
            <div class="form-group-profile">
              <label for="editAnnee">Niveau d'études</label>
              <select id="editAnnee" name="annee" class="form-control">
                <option value="">Non spécifié</option>
                <option value="1" <?= $sessionAnnee === 1 ? 'selected' : '' ?>>1ère année (Mentoré)</option>
                <option value="2" <?= $sessionAnnee === 2 ? 'selected' : '' ?>>2ème année (Mentor)</option>
                <option value="3" <?= $sessionAnnee === 3 ? 'selected' : '' ?>>3ème année (Mentor)</option>
                <option value="4" <?= $sessionAnnee === 4 ? 'selected' : '' ?>>4ème année (Mentor)</option>
                <option value="5" <?= $sessionAnnee === 5 ? 'selected' : '' ?>>5ème année (Mentor)</option>
              </select>
            </div>
            <div class="form-group-profile">
              <label for="editAnneeScolaire">Année scolaire (Promo)</label>
              <select id="editAnneeScolaire" name="annee_scolaire_id" class="form-control">
                <?php foreach ($schoolYears ?? [] as $sy): ?>
                  <option value="<?= (int)$sy['id'] ?>" <?= ((int)($user['annee_scolaire_id'] ?? 0) === (int)$sy['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($sy['libelle']) ?><?= $sy['est_active'] ? ' (En cours)' : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group-profile">
              <label for="editModules">Matières clés</label>
              <input type="text" id="editModules" name="modules_enseignes" class="form-control" value="<?= htmlspecialchars($sessionModules) ?>" placeholder="Ex: Algorithmique, BDD..." />
            </div>
          </div>

          <div class="form-group-profile">
            <label for="editBio">Présentation / Biographie</label>
            <textarea id="editBio" name="biographie" class="form-control" placeholder="Parlez de vos passions, projets d'études, binômes recherchés..."><?= htmlspecialchars($sessionBio) ?></textarea>
          </div>

          <div class="form-group-profile">
            <label for="editPassword">Nouveau mot de passe <small style="text-transform:none; color:var(--profile-text-muted);">(laisser vide pour ne pas changer)</small></label>
            <input type="password" id="editPassword" name="nouveau_mot_de_passe" class="form-control" placeholder="Minimum 8 caractères" minlength="8" autocomplete="new-password" />
          </div>

        </div>

        <div class="modal-footer-profile">
          <button type="button" class="campus-profile-btn secondary" id="cancelEditModalBtn">Annuler</button>
          <button type="submit" class="campus-profile-btn primary" id="saveEditModalBtn">Enregistrer les modifications</button>
        </div>
      </form>

    </div>
  </div>

  <!-- MODALE CROPPING AVATAR -->
  <div id="avatarCropModal" class="modal-backdrop-profile profile-avatar-crop-modal" role="dialog" aria-modal="true" aria-labelledby="avatarCropTitle" aria-hidden="true" hidden>
    <div class="modal-dialog-profile">
      <div class="modal-header-profile">
        <div>
          <h3 id="avatarCropTitle">Recadrer votre photo</h3>
          <div style="font-size:0.78rem; color:var(--profile-text-muted);">Ajustez le zoom et la position</div>
        </div>
        <button type="button" class="epi-modal-close" id="closeAvatarCropBtn" aria-label="Annuler le recadrage">&times;</button>
      </div>
      <div class="modal-body-profile">
        <div class="profile-avatar-crop-viewport" id="avatarCropViewport">
          <img id="avatarCropImage" alt="Aperçu du recadrage" draggable="false" />
        </div>
        <input id="avatarCropZoom" class="profile-avatar-zoom" type="range" min="1" max="3" step="0.01" value="1" />
      </div>
      <div class="modal-footer-profile">
        <button type="button" class="campus-profile-btn secondary" id="cancelAvatarCropBtn">Annuler</button>
        <button type="button" class="campus-profile-btn primary" id="applyAvatarCropBtn">Valider le recadrage</button>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <?php if (!$isOwnProfile && !empty($canRequestMentor)): ?>
  <div id="modalRequestMentor" class="modal-backdrop-profile" role="dialog" aria-modal="true" aria-labelledby="mentorModalTitle" style="display:none; align-items:center; justify-content:center;">
    <div class="modal-dialog-profile" style="max-width:520px; width:92%; margin:auto;">
      <div class="modal-header-profile">
        <div>
          <h3 id="mentorModalTitle" style="margin:0 0 4px; font-size:1.15rem; color:var(--profile-text);">Demander comme Mentor</h3>
          <div style="font-size:0.78rem; color:var(--profile-text-muted);">
            Mentorat académique · <?= htmlspecialchars($sessionFullName) ?> (<?= htmlspecialchars($sessionFiliere) ?>)
          </div>
        </div>
        <button type="button" class="epi-modal-close" onclick="document.getElementById('modalRequestMentor').style.display='none';">&times;</button>
      </div>
      <form method="post" action="<?= $base ?>/binomage/action">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['binomage_csrf'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="action" value="send">
        <input type="hidden" name="mentor_id" value="<?= (int)$user['id'] ?>">
        <input type="hidden" name="return_url" value="profile?id=<?= (int)$user['id'] ?>">
        
        <div class="modal-body-profile">
          <div style="background:var(--profile-surface, rgba(13,148,136,0.08)); border:1px solid rgba(13,148,136,0.2); padding:0.85rem; border-radius:var(--profile-radius-md); font-size:0.835rem; color:var(--profile-text-muted); line-height:1.5; margin-bottom:0.85rem;">
            💡 <strong>Règles de binômage Polytech :</strong>
            <br>• En tant qu'étudiant de 1ère année, vous choisissez un aîné de même filière.
            <br>• Votre mentor pourra vous guider dans vos cours, projets et révisions.
          </div>
          <div class="form-group-profile">
            <label for="mentorMessage">Message pour votre futur mentor (recommandé) :</label>
            <textarea id="mentorMessage" name="message" class="form-control" rows="4" placeholder="Bonjour <?= htmlspecialchars($user['prenom'] ?? '') ?>, je suis en 1ère année <?= htmlspecialchars($sessionFiliere) ?> et j'aimerais beaucoup bénéficier de ton mentorat cette année..."></textarea>
          </div>
        </div>
        
        <div class="modal-footer-profile" style="display:flex; justify-content:flex-end; gap:0.5rem; padding:0.75rem 1rem;">
          <button type="button" class="campus-profile-btn secondary" onclick="document.getElementById('modalRequestMentor').style.display='none';">Annuler</button>
          <button type="submit" class="campus-profile-btn primary" style="background:#0d9488; border-color:#0d9488;">Envoyer la demande</button>
        </div>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <!-- Modale globale de création de publication -->
  <?php include ROOT_PATH . '/resources/views/desktop/partials/post-composer-modal.php'; ?>

  <!-- Scripts -->
  <script src="<?= htmlspecialchars($asset) ?>/js/theme.js?v=<?= (int)filemtime(ROOT_PATH . '/public/assets/js/theme.js') ?>"></script>
  <script src="<?= htmlspecialchars($asset) ?>/js/feedback.js"></script>
  <script src="<?= htmlspecialchars($asset) ?>/js/desktop/profile.js?v=<?= (int)filemtime(ROOT_PATH . '/public/assets/js/desktop/profile.js') ?>"></script>
  <script>
    // Menu Utilisateur Toggle
    const userToggle = document.getElementById('userMenuToggle');
    const userDropdown = document.getElementById('userMenuDropdown');
    if (userToggle && userDropdown) {
      userToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        const isExpanded = userDropdown.hidden === false;
        userDropdown.hidden = isExpanded;
        userToggle.setAttribute('aria-expanded', !isExpanded);
      });
      document.addEventListener('click', (e) => {
        if (!userDropdown.contains(e.target) && !userToggle.contains(e.target)) {
          userDropdown.hidden = true;
          userToggle.setAttribute('aria-expanded', 'false');
        }
      });
    }
  </script>
  <?php include ROOT_PATH . '/resources/views/desktop/partials/report-modal.php'; ?>
</body>
</html>
