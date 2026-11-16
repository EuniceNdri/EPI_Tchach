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
 * @var array<int, array<string, mixed>> $profilePostCategories
 * @var array|null $profileDocuments
 * @var array<int, array<string, mixed>> $profileCategoryIndex
 * @var array<int, array<string, mixed>> $savedPublications
 * @var array<int, array<string, mixed>> $savedAttachments
 * @var array|null $profileStats
 * @var string $activeProfileTab
 * @var int|null $unreadCount
 * @var bool|null $isOwnProfile
 * @var bool|null $isUserAdmin
 * @var bool|null $isUserGlobalDelegate
 * @var bool|null $isUserClassDelegate
 */
$base = is_string($baseUrl ?? null) ? $baseUrl : '';
$asset = is_string($assetBase ?? null)
  ? $assetBase
  : ($base !== '' ? $base . '/public/assets' : '/public/assets');
$logoFile = $asset . '/images/logo.jpeg';

// Récupération des informations du profil (utilisateur consulté ou session courante)
$user = is_array($user ?? null) ? $user : [];
$profilePosts = is_array($profilePosts ?? null) ? $profilePosts : [];
$profileZones = is_array($profileZones ?? null) ? $profileZones : [];
$profileCategories = is_array($profileCategories ?? null) ? $profileCategories : [];
$profileDocuments = is_array($profileDocuments ?? null) ? $profileDocuments : [];
$savedPublications = is_array($savedPublications ?? null) ? $savedPublications : [];
$savedAttachments = is_array($savedAttachments ?? null) ? $savedAttachments : [];
$profilePostCategories = is_array($profilePostCategories ?? null) ? $profilePostCategories : [];
$profileCategoryIndex = is_array($profileCategoryIndex ?? null) ? $profileCategoryIndex : [];
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
$sessionRole = strtolower(trim((string)($user['role'] ?? 'etudiant')));
$sessionBio = $user['biographie'] ?? '';
$sessionFiliere = $user['filiere'] ?? '';
$sessionModules = $user['modules_enseignes'] ?? '';
$initials = strtoupper(mb_substr($sessionPrenom, 0, 1) . mb_substr($sessionNom, 0, 1));
$isOwnProfile = isset($isOwnProfile)
  ? (bool)$isOwnProfile
  : (isset($_SESSION['user_id'], $user['id']) && (int)$_SESSION['user_id'] === (int)$user['id']);
$roleLabels = [
  'etudiant' => 'Étudiant',
  'professeur' => 'Professeur',
  'moderateur' => 'Délégué / Modérateur',
  'administrateur' => 'Administrateur',
];
$userIdParam = !empty($_GET['id']) ? '&id=' . (int)$_GET['id'] : (!empty($_GET['user_id']) ? '&user_id=' . (int)$_GET['user_id'] : '');

// Vérification des privilèges spécifiques pour les onglets
global $pdo;
require_once ROOT_PATH . '/app/Models/Moderation/RoleAccess.php';
$targetId = (int)($user['id'] ?? 0);
$isUserAdmin = $isUserAdmin ?? ($pdo instanceof PDO ? RoleAccess::isAdministrator($pdo, $targetId) : false);
$isUserGlobalDelegate = $isUserGlobalDelegate ?? ($pdo instanceof PDO ? RoleAccess::isGlobalDelegate($pdo, $targetId) : false);
$isUserClassDelegate = $isUserClassDelegate ?? ($pdo instanceof PDO ? RoleAccess::isClassDelegate($pdo, $targetId) : false);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Profil — <?= htmlspecialchars($sessionFullName) ?> · EPI Tchach</title>
  <meta name="description" content="Espace profil membre et gestion du compte de l'École Polytechnique Internationale de Yamoussoukro" />
  <link rel="icon" type="image/svg+xml" href="<?= $asset ?>/images/logo_epi.svg" />
  <link rel="apple-touch-icon" href="<?= $asset ?>/images/logo.jpeg" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <?php include ROOT_PATH . '/resources/views/partials/theme-head.php'; ?>
  <link rel="stylesheet" href="<?= htmlspecialchars($asset) ?>/css/desktop/community-shell.css?v=<?= (int)filemtime(ROOT_PATH . '/public/assets/css/desktop/community-shell.css') ?>" />
  <link rel="stylesheet" href="<?= htmlspecialchars($asset) ?>/css/desktop/profile.css?v=<?= (int)filemtime(ROOT_PATH . '/public/assets/css/desktop/profile.css') ?>" />
</head>
<body class="community-shell community-profile" data-base-url="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>">
  <?php include ROOT_PATH . '/resources/views/partials/theme-toggle.php'; ?>

  <div class="profile-page-wrapper">
    <div class="profile-layout-grid">

      <!-- ========================================================
           1. SIDEBAR DU PROFIL (Fixée à gauche, moderne et épurée)
           ======================================================== -->
      <aside class="profile-sidebar-card">
        <a href="<?= $base ?>/salon" class="profile-sidebar-brand">
          <img src="<?= htmlspecialchars($logoFile, ENT_QUOTES, 'UTF-8') ?>" alt="" class="brand-logo-img" />
          <span>EPI <strong>Tchach</strong></span>
        </a>

        <div class="sidebar-profile-hero">
          <div class="sidebar-avatar-wrapper">
            <div class="sidebar-avatar profile-dynamic-initials"><?= htmlspecialchars($initials) ?></div>
            <img class="sidebar-avatar-image profile-avatar-image" src="<?= !empty($user['avatar_url']) ? htmlspecialchars(url($user['avatar_url']), ENT_QUOTES, 'UTF-8') : '' ?>" alt="" <?= empty($user['avatar_url']) ? 'hidden' : '' ?> />
            <?php if ($isOwnProfile): ?>
              <div class="sidebar-online-dot" title="En ligne sur EPI Tchach"></div>
            <?php endif; ?>
          </div>

          <h2 class="sidebar-user-name profile-dynamic-name"><?= htmlspecialchars($sessionFullName) ?></h2>

          <div class="sidebar-roles-container">
            <span class="sidebar-role-badge role-<?= htmlspecialchars($sessionRole) ?>">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
              <span><?= htmlspecialchars($roleLabels[$sessionRole] ?? ucfirst($sessionRole)) ?></span>
            </span>

            <?php if ($isUserGlobalDelegate): ?>
              <span class="sidebar-role-badge role-delegue-general" title="Délégué Général des étudiants">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                <span>Délégué Général</span>
              </span>
            <?php elseif ($isUserClassDelegate): ?>
              <span class="sidebar-role-badge role-delegue-classe" title="Délégué de classe">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span>Délégué de classe</span>
              </span>
            <?php endif; ?>

            <?php if (!empty($sessionFiliere)): ?>
              <span class="sidebar-filiere-badge">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/></svg>
                <span><?= htmlspecialchars($sessionFiliere) ?></span>
              </span>
            <?php endif; ?>
          </div>

          <p class="sidebar-bio-text profile-dynamic-bio"><?= htmlspecialchars($sessionBio ?: 'Aucune biographie renseignée pour l\'instant.') ?></p>

          <?php if ($isOwnProfile): ?>
            <button type="button" class="btn-sidebar-edit open-profile-edit-modal">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
              <span>Modifier mon profil</span>
            </button>
          <?php endif; ?>
        </div>

        <!-- Coordonnées académiques rapides -->
        <ul class="sidebar-meta-list">
          <li class="sidebar-meta-item">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
            <span class="profile-dynamic-email"><?= htmlspecialchars($sessionEmail) ?></span>
          </li>
          <?php if (!empty($sessionFiliere)): ?>
            <li class="sidebar-meta-item">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6"/><path d="M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
              <span>Filière : <?= htmlspecialchars($sessionFiliere) ?></span>
            </li>
          <?php endif; ?>
          <?php if ($sessionRole === 'professeur' && !empty($sessionModules)): ?>
            <li class="sidebar-meta-item">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
              <span title="<?= htmlspecialchars($sessionModules) ?>"><?= htmlspecialchars(mb_strimwidth($sessionModules, 0, 36, '…')) ?></span>
            </li>
          <?php endif; ?>
        </ul>

        <nav class="profile-account-menu" aria-label="Compte">
          <a href="<?= $base ?>/notifications">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            <span>Notifications</span>
            <span class="profile-notification-count" data-notification-count<?= $unreadCount > 0 ? '' : ' hidden' ?> style="<?= $unreadCount > 0 ? '' : 'display:none;' ?>"><?= (int)$unreadCount ?></span>
          </a>
          <?php if ($sessionRole === 'administrateur'): ?>
            <a href="<?= $base ?>/admin">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
              <span>Administration</span>
            </a>
          <?php endif; ?>
          <a class="profile-logout-link" href="<?= $base ?>/logout">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            <span>Déconnexion</span>
          </a>
        </nav>
      </aside>

      <!-- ========================================================
           2. ZONE CENTRALE (Bannière Hero + Onglets + Contenu)
           ======================================================== -->
      <main class="profile-main-area">

        <!-- Bannière d'accueil & Titre du profil -->
        <div class="profile-banner-card">
          <div class="profile-cover">
            <div class="profile-cover-pattern"></div>
            <div class="profile-cover-gradient"></div>
          </div>
          <div class="profile-banner-content">
            <div class="profile-banner-info">
              <h1><?= $isOwnProfile ? 'Mon espace membre' : 'Profil de ' . htmlspecialchars($sessionFullName) ?></h1>
              <p>Consultez vos échanges académiques, accédez à vos salons et participez aux discussions de l'École Polytechnique Internationale de Yamoussoukro.</p>
            </div>
          </div>
        </div>

        <!-- Grille des statistiques d'activité -->
        <div class="profile-stats-grid" aria-label="Activité du membre">
          <div class="stat-box">
            <div class="stat-icon-wrapper icon-blue" aria-hidden="true">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            </div>
            <div>
              <div class="stat-val" id="statSubjectsCount"><?= (int)$profileStats['posts'] ?></div>
              <div class="stat-lbl">Publications</div>
            </div>
          </div>
          <div class="stat-box">
            <div class="stat-icon-wrapper icon-green" aria-hidden="true">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 17 4 12 9 7"/><path d="M20 18v-2a4 4 0 0 0-4-4H4"/></svg>
            </div>
            <div>
              <div class="stat-val"><?= (int)$profileStats['comments'] ?></div>
              <div class="stat-lbl">Réponses publiées</div>
            </div>
          </div>
          <div class="stat-box">
            <div class="stat-icon-wrapper icon-amber" aria-hidden="true">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
            </div>
            <div>
              <div class="stat-val" id="statDocumentsCount"><?= (int)$profileStats['documents'] ?></div>
              <div class="stat-lbl">Documents partagés</div>
            </div>
          </div>
          <div class="stat-box">
            <div class="stat-icon-wrapper icon-purple" aria-hidden="true">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
            <div>
              <div class="stat-val"><?= (int)$profileStats['zones'] ?></div>
              <div class="stat-lbl">Salons rejoints</div>
            </div>
          </div>
        </div>

        <!-- ========================================================
             BARRE D'ONGLETS HORIZONTALE UNIFIÉE (Générale & Rôles)
             ======================================================== -->
        <nav class="profile-tabs-nav" aria-label="Navigation du profil">
          <!-- 1. Onglet Général : Flux & Publications -->
          <button type="button" class="profile-nav-tab <?= $activeProfileTab === 'posts' ? 'active' : '' ?>" data-tab="tab-posts">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            <span>Publications</span>
            <span class="tab-badge-count"><?= count($profilePosts) ?></span>
          </button>

          <!-- 2. Onglet Général : Salons & Filières -->
          <button type="button" class="profile-nav-tab <?= $activeProfileTab === 'channels' ? 'active' : '' ?>" data-tab="tab-channels">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            <span>Salons</span>
            <span class="tab-badge-count"><?= count($profileZones) ?></span>
          </button>

          <!-- 3. Onglet Général : Documents partagés -->
          <button type="button" class="profile-nav-tab <?= $activeProfileTab === 'docs' ? 'active' : '' ?>" data-tab="tab-docs">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            <span>Documents</span>
            <span class="tab-badge-count"><?= count($profileDocuments) ?></span>
          </button>

          <!-- 4. Onglet Spécifique : Favoris (Seulement pour son propre profil) -->
          <?php if ($isOwnProfile): ?>
            <button type="button" class="profile-nav-tab <?= $activeProfileTab === 'saved' ? 'active' : '' ?>" data-tab="tab-saved">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m19 21-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
              <span>Favoris</span>
              <span class="tab-badge-count"><?= count($savedPublications) + count($savedAttachments) ?></span>
            </button>
          <?php endif; ?>

          <!-- 5. Onglet Spécifique Rôle : Enseignement & Modules (Professeur) -->
          <?php if ($sessionRole === 'professeur'): ?>
            <button type="button" class="profile-nav-tab role-tab-prof <?= $activeProfileTab === 'teaching' ? 'active' : '' ?>" data-tab="tab-teaching">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
              <span>Enseignement</span>
            </button>
          <?php endif; ?>

          <!-- 6. Onglet Spécifique Rôle : Délégation Étudiante (Délégué Général / Délégué de classe) -->
          <?php if ($isUserGlobalDelegate || $isUserClassDelegate): ?>
            <button type="button" class="profile-nav-tab role-tab-delegue <?= $activeProfileTab === 'delegation' ? 'active' : '' ?>" data-tab="tab-delegation">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
              <span>Délégation</span>
            </button>
          <?php endif; ?>

          <!-- 7. Onglet Spécifique Rôle : Espace Admin (Administrateur) -->
          <?php if ($isUserAdmin): ?>
            <button type="button" class="profile-nav-tab role-tab-admin <?= $activeProfileTab === 'admin_info' ? 'active' : '' ?>" data-tab="tab-admin_info">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
              <span>Administration</span>
            </button>
          <?php endif; ?>

          <!-- 8. Onglet Général : À propos & Infos -->
          <button type="button" class="profile-nav-tab <?= $activeProfileTab === 'about' ? 'active' : '' ?>" data-tab="tab-about">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            <span>À propos</span>
          </button>
        </nav>

        <!-- Formulaire de Publication Rapide (AJAX sans rechargement) -->
        <?php if ($isOwnProfile): ?>
          <section class="quick-post-card">
            <form id="quickPostForm" action="<?= htmlspecialchars($base) ?>/publication/create" method="post" enctype="multipart/form-data">
              <input type="hidden" name="ajax" value="1" />
              <input type="hidden" name="type_publication" value="discussion" />
              <div class="quick-post-header">
                <div class="post-avatar-mini profile-dynamic-initials"><?= htmlspecialchars($initials) ?></div>
                <div>
                  <strong>Poser une question ou lancer une discussion</strong>
                  <span class="quick-post-sub">Partagez vos réflexions ou demandez de l'aide sur le forum</span>
                </div>
              </div>
              <input type="text" name="titre" class="quick-post-title-input" placeholder="Titre clair de votre question ou sujet…" required />
              <textarea name="contenu" class="quick-post-textarea" placeholder="Expliquez en détail votre demande ou votre partage pour la communauté…" required></textarea>
              
              <div class="quick-post-file-row">
                <label for="postAttachments" class="quick-post-file-label">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.49-8.48a4 4 0 0 1 5.66 5.66l-8.48 8.49a2 2 0 0 1-2.83-2.83l7.78-7.78"/></svg>
                  <span>Joindre des documents (PDF, cours, images, ZIP — 10 Mo max)</span>
                </label>
                <input id="postAttachments" type="file" name="pieces_jointes[]" multiple
                  accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.png,.jpg,.jpeg,.gif,.webp"
                  style="display:none;" />
                <span id="selectedFilesBadge" class="selected-files-indicator" hidden>0 fichier(s)</span>
              </div>

              <div class="quick-post-footer">
                <select name="categorie_id" class="post-tag-select" required <?= empty($profileCategories) ? 'disabled' : '' ?>>
                  <?php if (!$profileCategories): ?>
                    <option value="">Aucun salon accessible</option>
                  <?php else: ?>
                    <?php foreach ($profileCategories as $category): ?>
                      <option value="<?= (int)$category['id'] ?>"><?= htmlspecialchars($category['zone_name'] . ' · ' . $category['name']) ?></option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
                <button type="submit" class="btn btn-primary" <?= empty($profileCategories) ? 'disabled' : '' ?>>
                  <span>Publier le sujet</span>
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                </button>
              </div>
              <p id="quickPostFeedback" role="status" aria-live="polite"></p>
            </form>
          </section>
        <?php endif; ?>

        <!-- ========================================================
             CONTENUS DES ONGLETS
             ======================================================== -->
        <div class="profile-tabs-card">

          <!-- ONGLET 1 : PUBLICATIONS -->
          <div class="tab-content <?= $activeProfileTab === 'posts' ? 'active' : '' ?>" id="tab-posts">
            <div class="tab-section-header">
              <div>
                <h3><?= $isOwnProfile ? 'Mes publications récentes' : 'Publications de ' . htmlspecialchars($sessionFullName) ?></h3>
                <p>Retrouvez tous les sujets et discussions lancés sur le forum</p>
              </div>
              <span class="tab-count-badge"><?= count($profilePosts) ?> publication(s)</span>
            </div>

            <?php if ($isOwnProfile && $profilePosts): ?>
              <div class="profile-library-filters">
                <div class="filter-group">
                  <label for="profilePostCategoryFilter">Rubrique</label>
                  <select id="profilePostCategoryFilter">
                    <option value="all">Toutes les rubriques</option>
                    <?php foreach ($profilePostCategories as $category): ?>
                      <option value="<?= (int)$category['id'] ?>"><?= htmlspecialchars($category['zone_name'] . ' · ' . ($category['name'] ?? $category['nom'] ?? '')) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="filter-group">
                  <label for="profilePostStatusFilter">État</label>
                  <select id="profilePostStatusFilter">
                    <option value="all">Tous les états</option>
                    <option value="visible">Actives</option>
                    <option value="reported">Signalées / en modération</option>
                    <option value="closed">Fermées ou résolues</option>
                  </select>
                </div>
              </div>
            <?php endif; ?>

            <div class="posts-feed" id="postsFeedList">
              <?php if (!$profilePosts): ?>
                <div class="profile-empty-state">
                  <div class="empty-icon-circle">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                  </div>
                  <h4>Aucune publication pour le moment</h4>
                  <p>Les sujets et questions lancés par ce membre apparaîtront ici.</p>
                </div>
              <?php else: ?>
                <?php foreach ($profilePosts as $post): ?>
                  <article class="post-card" data-profile-post
                    data-category-id="<?= (int)$post['category_id'] ?>"
                    data-post-status="<?= !empty($post['est_signale']) ? 'reported' : (in_array($post['statut'], ['ferme', 'resolu'], true) ? 'closed' : 'visible') ?>">
                    <div class="post-header-flex">
                      <div class="post-author-box">
                        <div class="post-avatar-mini profile-dynamic-initials"><?= htmlspecialchars($initials) ?></div>
                        <div>
                          <div class="post-author-name profile-dynamic-name"><?= htmlspecialchars($sessionFullName) ?></div>
                          <div class="post-time"><?= htmlspecialchars($post['zone_name'] . ' · ' . $post['category_name'] . ' · ' . date('d/m/Y H:i', strtotime($post['date_creation']))) ?></div>
                        </div>
                      </div>
                      <div class="profile-post-badges">
                        <span class="post-tag-pill"><?= htmlspecialchars(ucfirst($post['type_publication'])) ?></span>
                        <?php if (!empty($post['est_signale'])): ?>
                          <span class="profile-status-badge is-warning">En modération</span>
                        <?php else: ?>
                          <span class="profile-status-badge is-active">Visible</span>
                        <?php endif; ?>
                        <span class="profile-status-badge"><?= htmlspecialchars(ucfirst($post['statut'])) ?></span>
                      </div>
                      <?php if ($isOwnProfile): ?>
                        <form action="<?= $base ?>/salon/save" method="post" style="margin:0;">
                          <input type="hidden" name="target_type" value="publication">
                          <input type="hidden" name="target_id" value="<?= (int)$post['id'] ?>">
                          <input type="hidden" name="return_page" value="profile">
                          <input type="hidden" name="return_tab" value="posts">
                          <button class="btn btn-outline btn-sm" type="submit"><?= !empty($post['saved_id']) ? '★ En favori' : '☆ Favori' ?></button>
                        </form>
                      <?php endif; ?>
                    </div>
                    <h4 class="post-title">
                      <a href="<?= $base ?>/publication/<?= (int)$post['id'] ?>" class="profile-post-title-link"><?= htmlspecialchars($post['titre']) ?></a>
                    </h4>
                    <p class="post-excerpt"><?= nl2br(htmlspecialchars(mb_strimwidth(strip_tags($post['contenu']), 0, 360, '…'))) ?></p>
                    <?php if ($isOwnProfile && !empty($post['est_signale'])): ?>
                      <p class="profile-moderation-note">Cette publication est en cours d’examen par la modération.</p>
                    <?php endif; ?>
                    <div class="post-footer-actions">
                      <span class="profile-post-stat">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                        <span><?= (int)$post['likes_count'] ?> likes</span>
                      </span>
                      <span class="profile-post-stat">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        <span><?= (int)$post['comments_count'] ?> réponses</span>
                      </span>
                    </div>
                  </article>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>

          <!-- ONGLET 2 : SALONS & FILIÈRES -->
          <div class="tab-content <?= $activeProfileTab === 'channels' ? 'active' : '' ?>" id="tab-channels">
            <div class="tab-section-header">
              <div>
                <h3>Salons & Espaces rejoints</h3>
                <p>Espaces de travail, filières et groupes de discussion auxquels ce membre a accès</p>
              </div>
              <span class="tab-count-badge"><?= count($profileZones) ?> salon(s)</span>
            </div>

            <?php if (!$profileZones): ?>
              <div class="profile-empty-state">
                <div class="empty-icon-circle">
                  <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </div>
                <h4>Aucun salon associé</h4>
                <p>Ce compte n'est pour le moment inscrit dans aucun salon spécifique.</p>
              </div>
            <?php else: ?>
              <div class="profile-zone-grid">
                <?php foreach ($profileZones as $zone): ?>
                  <a class="profile-zone-card" href="<?= $base ?>/salon?zone=<?= (int)$zone['id'] ?>">
                    <div class="zone-card-top">
                      <span class="post-tag-pill zone-type-<?= htmlspecialchars($zone['type_zone']) ?>"><?= htmlspecialchars(ucfirst($zone['type_zone'])) ?></span>
                      <small class="zone-role-tag"><?= htmlspecialchars(str_replace('_', ' ', $zone['role_dans_zone'])) ?></small>
                    </div>
                    <strong><?= htmlspecialchars($zone['nom']) ?></strong>
                    <?php if (!empty($zone['description'])): ?>
                      <p class="zone-card-desc"><?= htmlspecialchars($zone['description']) ?></p>
                    <?php endif; ?>
                    <div class="zone-card-footer">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                      <span><?= (int)$zone['membres_count'] ?> membre(s)</span>
                    </div>
                  </a>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- ONGLET 3 : DOCUMENTS PARTAGÉS -->
          <div class="tab-content <?= $activeProfileTab === 'docs' ? 'active' : '' ?>" id="tab-docs">
            <div class="tab-section-header">
              <div>
                <h3><?= $isOwnProfile ? 'Mes documents & fichiers partagés' : 'Documents partagés' ?></h3>
                <p>Retrouvez tous les supports de cours, TD, synthèses et pièces jointes mis à disposition</p>
              </div>
              <span class="tab-count-badge"><?= count($profileDocuments) ?> document(s)</span>
            </div>

            <?php if ($isOwnProfile && $profileCategoryIndex): ?>
              <div class="profile-library-filters">
                <div class="filter-group">
                  <label for="profileDocumentCategoryFilter">Filtrer par salon</label>
                  <select id="profileDocumentCategoryFilter">
                    <option value="all">Tous les salons</option>
                    <?php foreach ($profileCategoryIndex as $category): ?>
                      <option value="<?= (int)$category['id'] ?>"><?= htmlspecialchars($category['zone_name'] . ' · ' . ($category['name'] ?? $category['nom'] ?? '')) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            <?php endif; ?>

            <?php if (!$profileDocuments): ?>
              <div class="profile-empty-state">
                <div class="empty-icon-circle">
                  <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
                <h4>Aucun document partagé</h4>
                <p>Les fichiers ajoutés aux publications ou commentaires seront listés ici.</p>
              </div>
            <?php else: ?>
              <div class="profile-documents-list">
                <?php foreach ($profileDocuments as $document): ?>
                  <div class="profile-document-row" data-profile-document data-category-id="<?= (int)$document['category_id'] ?>">
                    <div class="doc-icon-badge">
                      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    </div>
                    <div class="doc-info-block">
                      <strong><?= htmlspecialchars($document['nom_fichier']) ?></strong>
                      <span><?= htmlspecialchars($document['publication_title'] . ' · ' . $document['zone_name'] . ' · ' . $document['category_name']) ?> · <?= number_format(((int)$document['taille']) / 1048576, 2, ',', ' ') ?> Mo</span>
                      <?php if (!empty($document['est_signale'])): ?>
                        <small class="profile-status-badge is-warning">Publication en modération</small>
                      <?php endif; ?>
                    </div>
                    <div class="profile-file-actions">
                      <?php if (str_starts_with((string)$document['type_mime'], 'image/')): ?>
                        <a class="btn btn-outline btn-sm" href="<?= $base ?>/attachment/view/<?= (int)$document['id'] ?>" target="_blank" rel="noopener">Aperçu</a>
                      <?php endif; ?>
                      <a class="btn btn-outline btn-sm" href="<?= $base ?>/attachment/download/<?= (int)$document['id'] ?>">Télécharger</a>
                      <?php if ($isOwnProfile): ?>
                        <form action="<?= $base ?>/salon/save" method="post" style="margin:0;">
                          <input type="hidden" name="target_type" value="attachment">
                          <input type="hidden" name="target_id" value="<?= (int)$document['id'] ?>">
                          <input type="hidden" name="return_page" value="profile">
                          <input type="hidden" name="return_tab" value="docs">
                          <button class="btn btn-outline btn-sm" type="submit"><?= !empty($document['saved_id']) ? '★ En favori' : '☆ Favori' ?></button>
                        </form>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- ONGLET 4 : FAVORIS (Seulement pour son propre profil) -->
          <?php if ($isOwnProfile): ?>
            <div class="tab-content <?= $activeProfileTab === 'saved' ? 'active' : '' ?>" id="tab-saved">
              <div class="tab-section-header">
                <div>
                  <h3>Mes favoris & sauvegardes</h3>
                  <p>Accédez rapidement à vos publications et documents sauvegardés pour consultation ultérieure</p>
                </div>
                <span class="tab-count-badge"><?= count($savedPublications) + count($savedAttachments) ?> élément(s)</span>
              </div>

              <h4 class="profile-library-subtitle">Publications enregistrées (<?= count($savedPublications) ?>)</h4>
              <?php if (!$savedPublications): ?>
                <p class="profile-empty-sub">Aucune publication en favori. Cliquez sur « Favori » dans un sujet pour l'épingler ici.</p>
              <?php else: ?>
                <div class="posts-feed" style="margin-bottom:1.5rem;">
                  <?php foreach ($savedPublications as $savedPost): ?>
                    <article class="post-card">
                      <div class="post-header-flex">
                        <div>
                          <span class="post-time"><?= htmlspecialchars($savedPost['zone_name'] . ' · ' . $savedPost['category_name']) ?></span>
                          <h4 class="post-title">
                            <a href="<?= $base ?>/publication/<?= (int)$savedPost['id'] ?>" class="profile-post-title-link"><?= htmlspecialchars($savedPost['titre']) ?></a>
                          </h4>
                        </div>
                        <form action="<?= $base ?>/salon/save" method="post" style="margin:0;">
                          <input type="hidden" name="target_type" value="publication">
                          <input type="hidden" name="target_id" value="<?= (int)$savedPost['id'] ?>">
                          <input type="hidden" name="return_page" value="profile">
                          <input type="hidden" name="return_tab" value="saved">
                          <button class="btn btn-outline btn-sm" type="submit">Retirer</button>
                        </form>
                      </div>
                      <p class="post-excerpt"><?= nl2br(htmlspecialchars(mb_strimwidth(strip_tags($savedPost['contenu']), 0, 320, '…'))) ?></p>
                      <div class="post-footer-actions">
                        <span class="profile-post-stat">♥ <?= (int)$savedPost['likes_count'] ?> likes</span>
                        <span class="profile-post-stat">💬 <?= (int)$savedPost['comments_count'] ?> réponses</span>
                        <span class="profile-post-stat">Sauvegardé le <?= htmlspecialchars(date('d/m/Y', strtotime($savedPost['date_sauvegarde']))) ?></span>
                      </div>
                    </article>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>

              <h4 class="profile-library-subtitle">Fichiers & supports sauvegardés (<?= count($savedAttachments) ?>)</h4>
              <?php if (!$savedAttachments): ?>
                <p class="profile-empty-sub">Aucun fichier favori.</p>
              <?php else: ?>
                <div class="profile-documents-list">
                  <?php foreach ($savedAttachments as $attachment): ?>
                    <div class="profile-document-row">
                      <div class="doc-icon-badge">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                      </div>
                      <div class="doc-info-block">
                        <strong><?= htmlspecialchars($attachment['nom_fichier']) ?></strong>
                        <span><?= htmlspecialchars($attachment['publication_title'] . ' · ' . $attachment['zone_name'] . ' · ' . $attachment['category_name']) ?> · <?= number_format(((int)$attachment['taille']) / 1048576, 2, ',', ' ') ?> Mo</span>
                      </div>
                      <div class="profile-file-actions">
                        <?php if (str_starts_with((string)$attachment['type_mime'], 'image/')): ?>
                          <a class="btn btn-outline btn-sm" href="<?= $base ?>/attachment/view/<?= (int)$attachment['id'] ?>" target="_blank" rel="noopener">Aperçu</a>
                        <?php endif; ?>
                        <a class="btn btn-outline btn-sm" href="<?= $base ?>/attachment/download/<?= (int)$attachment['id'] ?>">Télécharger</a>
                        <form action="<?= $base ?>/salon/save" method="post" style="margin:0;">
                          <input type="hidden" name="target_type" value="attachment">
                          <input type="hidden" name="target_id" value="<?= (int)$attachment['id'] ?>">
                          <input type="hidden" name="return_page" value="profile">
                          <input type="hidden" name="return_tab" value="saved">
                          <button class="btn btn-outline btn-sm" type="submit">Retirer</button>
                        </form>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          <?php endif; ?>

          <!-- ONGLET SPÉCIFIQUE 5 : ENSEIGNEMENT & MODULES (Professeur) -->
          <?php if ($sessionRole === 'professeur'): ?>
            <div class="tab-content <?= $activeProfileTab === 'teaching' ? 'active' : '' ?>" id="tab-teaching">
              <div class="tab-section-header">
                <div>
                  <h3>Modules & Enseignements</h3>
                  <p>Détail des cours dispensés et des départements rattachés</p>
                </div>
                <span class="tab-count-badge role-badge-prof">Corps Enseignant</span>
              </div>

              <div class="profile-role-content-box">
                <div class="role-feature-card">
                  <div class="feature-icon icon-orange">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                  </div>
                  <div>
                    <h4>Modules et Matières enseignées</h4>
                    <p class="role-desc-highlight"><?= nl2br(htmlspecialchars($sessionModules ?: 'Aucun module spécifié pour le moment.')) ?></p>
                  </div>
                </div>

                <div class="role-feature-card">
                  <div class="feature-icon icon-blue">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/></svg>
                  </div>
                  <div>
                    <h4>Filière principale de rattachement</h4>
                    <p><?= htmlspecialchars($sessionFiliere ?: 'Toutes les filières de l\'EPI') ?></p>
                  </div>
                </div>

                <div class="role-feature-card">
                  <div class="feature-icon icon-green">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                  </div>
                  <div>
                    <h4>Salon des Professeurs</h4>
                    <p>Accès au salon d'échange pédagogique et de concertation des enseignants de l'école.</p>
                    <a href="<?= $base ?>/salon?zone=professeurs" class="btn btn-outline btn-sm" style="margin-top:0.4rem;">Accéder au salon enseignants</a>
                  </div>
                </div>
              </div>
            </div>
          <?php endif; ?>

          <!-- ONGLET SPÉCIFIQUE 6 : DÉLÉGATION ÉTUDIANTE (Délégués) -->
          <?php if ($isUserGlobalDelegate || $isUserClassDelegate): ?>
            <div class="tab-content <?= $activeProfileTab === 'delegation' ? 'active' : '' ?>" id="tab-delegation">
              <div class="tab-section-header">
                <div>
                  <h3>Responsabilités au sein de la Délégation</h3>
                  <p>Mandats, filières et prérogatives étudiantes</p>
                </div>
                <span class="tab-count-badge role-badge-delegue">Délégation</span>
              </div>

              <div class="profile-role-content-box">
                <?php if ($isUserGlobalDelegate): ?>
                  <div class="role-feature-card featured-gold">
                    <div class="feature-icon icon-gold">
                      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
                    <div>
                      <h4>Délégué Général de l'École</h4>
                      <p>Représentation globale de l'ensemble des étudiants auprès de la Direction Générale et de l'Administration.</p>
                      <small style="color:var(--community-muted);">Accès étendu aux salons de concertation générale et à la modération de premier niveau.</small>
                    </div>
                  </div>
                <?php endif; ?>

                <?php if ($isUserClassDelegate): ?>
                  <div class="role-feature-card">
                    <div class="feature-icon icon-blue">
                      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <div>
                      <h4>Délégué de classe / Filière</h4>
                      <p>Interlocuteur privilégié pour la promotion <strong><?= htmlspecialchars($sessionFiliere) ?></strong>.</p>
                      <small style="color:var(--community-muted);">Gestion des questions relatives à la promotion, emploi du temps et vie de classe.</small>
                    </div>
                  </div>
                <?php endif; ?>

                <div class="role-feature-card">
                  <div class="feature-icon icon-green">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                  </div>
                  <div>
                    <h4>Salon de la Délégation</h4>
                    <p>Espace privé pour coordonner les actions et requêtes des délégués de l'EPI.</p>
                    <a href="<?= $base ?>/salon?zone=delegation" class="btn btn-outline btn-sm" style="margin-top:0.4rem;">Ouvrir le salon délégation</a>
                  </div>
                </div>
              </div>
            </div>
          <?php endif; ?>

          <!-- ONGLET SPÉCIFIQUE 7 : ADMINISTRATION (Administrateurs) -->
          <?php if ($isUserAdmin): ?>
            <div class="tab-content <?= $activeProfileTab === 'admin_info' ? 'active' : '' ?>" id="tab-admin_info">
              <div class="tab-section-header">
                <div>
                  <h3>Administration Système & Sécurité</h3>
                  <p>Contrôles globaux de la plateforme EPI Tchach</p>
                </div>
                <span class="tab-count-badge role-badge-admin">Super Admin</span>
              </div>

              <div class="profile-role-content-box">
                <div class="role-feature-card featured-admin">
                  <div class="feature-icon icon-red">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                  </div>
                  <div>
                    <h4>Droits d'administration complets</h4>
                    <p>Ce compte dispose des prérogatives de gestion des utilisateurs, des salons académiques, de la modération centrale et des configurations de sécurité.</p>
                    <div style="display:flex; gap:0.5rem; margin-top:0.75rem;">
                      <a href="<?= $base ?>/admin" class="btn btn-primary btn-sm">Panneau d'administration</a>
                      <a href="<?= $base ?>/admin/users" class="btn btn-outline btn-sm">Gérer les comptes</a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          <?php endif; ?>

          <!-- ONGLET 8 : À PROPOS & INFOS -->
          <div class="tab-content <?= $activeProfileTab === 'about' ? 'active' : '' ?>" id="tab-about">
            <div class="tab-section-header">
              <div>
                <h3>Informations académiques & profil</h3>
                <p>Données officielles enregistrées sur l'annuaire de l'école</p>
              </div>
            </div>

            <div class="profile-about-grid">
              <div class="about-card-box full-width">
                <dt>Biographie & Présentation</dt>
                <dd class="profile-dynamic-bio"><?= nl2br(htmlspecialchars($sessionBio ?: 'Aucune biographie renseignée.')) ?></dd>
              </div>
              <div class="about-card-box">
                <dt>Nom complet</dt>
                <dd class="profile-dynamic-name"><?= htmlspecialchars($sessionFullName) ?></dd>
              </div>
              <div class="about-card-box">
                <dt>Adresse e-mail institutionnelle</dt>
                <dd class="profile-dynamic-email"><?= htmlspecialchars($sessionEmail) ?></dd>
              </div>
              <div class="about-card-box">
                <dt>Rôle institutionnel</dt>
                <dd><?= htmlspecialchars($roleLabels[$sessionRole] ?? ucfirst($sessionRole)) ?></dd>
              </div>
              <?php if ($sessionFiliere): ?>
                <div class="about-card-box">
                  <dt>Filière & Promotion</dt>
                  <dd><?= htmlspecialchars($sessionFiliere) ?></dd>
                </div>
              <?php endif; ?>
              <?php if ($sessionRole === 'professeur' && $sessionModules): ?>
                <div class="about-card-box full-width">
                  <dt>Modules & Matières enseignées</dt>
                  <dd><?= nl2br(htmlspecialchars($sessionModules)) ?></dd>
                </div>
              <?php endif; ?>
            </div>
          </div>

        </div>
      </main>

    </div>
  </div>

  <!-- ============================================================
       3. MODALE POPUP D'ÉDITION DU PROFIL (Fluide, AJAX sans rechargement)
       ============================================================ -->
  <?php if ($isOwnProfile): ?>
  <div id="profileEditModal" class="modal-backdrop-profile" aria-hidden="true" role="dialog" aria-labelledby="modalEditTitle">
    <div class="modal-dialog-profile">
      
      <!-- En-tête de la popup -->
      <div class="modal-header-profile">
        <div>
          <h3 id="modalEditTitle" class="modal-title-profile">Modifier mon profil</h3>
          <p class="modal-subtitle-profile">Mettez à jour votre photo, votre biographie et votre mot de passe</p>
        </div>
        <button type="button" class="btn-close-modal" id="closeEditModalBtn" aria-label="Fermer">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>

      <!-- Formulaire AJAX -->
      <form id="ajaxProfileEditForm" action="<?= $base ?>/profile/update" method="POST" enctype="multipart/form-data">
        <div class="modal-body-profile">

          <div class="profile-avatar-edit-section">
            <div class="profile-avatar-edit-preview">
              <span class="profile-avatar-edit-initials"><?= htmlspecialchars($initials) ?></span>
              <img id="editAvatarPreview" class="profile-avatar-image" src="<?= !empty($user['avatar_url']) ? htmlspecialchars(url($user['avatar_url']), ENT_QUOTES, 'UTF-8') : '' ?>" alt="Aperçu" <?= empty($user['avatar_url']) ? 'hidden' : '' ?> />
            </div>
            <div class="profile-avatar-edit-controls">
              <label class="btn btn-outline btn-sm" for="editAvatarUpload">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                <span>Changer la photo</span>
              </label>
              <input id="editAvatarUpload" type="file" name="avatar" accept="image/jpeg,image/png,image/webp" hidden data-profile-avatar-input />
              <input type="hidden" name="remove_avatar" value="0" data-remove-profile-avatar />
              <button type="button" class="btn btn-outline btn-sm btn-danger-soft" data-clear-profile-avatar>Supprimer</button>
              <span class="profile-photo-note">JPG, PNG ou WEBP jusqu’à 5 Mo.</span>
            </div>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label class="form-label" for="editNom">Nom de famille</label>
              <input type="text" id="editNom" name="nom" class="form-control" value="<?= htmlspecialchars($sessionNom) ?>" required />
              <input type="text" id="editNom" name="nom" class="form-control" value="<?= htmlspecialchars($sessionNom) ?>" autocomplete="family-name" required />
            </div>
            <div class="form-group">
              <label class="form-label" for="editPrenom">Prénom</label>
              <input type="text" id="editPrenom" name="prenom" class="form-control" value="<?= htmlspecialchars($sessionPrenom) ?>" required />
              <input type="text" id="editPrenom" name="prenom" class="form-control" value="<?= htmlspecialchars($sessionPrenom) ?>" autocomplete="given-name" required />
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="editBio">Présentation & Biographie</label>
            <textarea id="editBio" name="biographie" class="form-control" placeholder="Quelques mots sur votre parcours académique, vos projets..."><?= htmlspecialchars($sessionBio) ?></textarea>
          </div>

          <div class="form-group">
            <label class="form-label" for="editPassword">Nouveau mot de passe <small>(laisser vide pour conserver l'actuel)</small></label>
            <input type="password" id="editPassword" name="nouveau_mot_de_passe" class="form-control" placeholder="Minimum 8 caractères" minlength="8" autocomplete="new-password" />
          </div>

        </div>

        <div class="modal-footer-profile">
          <p data-profile-upload-feedback role="status" aria-live="polite"></p>
          <button type="button" class="btn btn-outline" id="cancelEditModalBtn">Annuler</button>
          <button type="submit" class="btn btn-primary">
            <span>Enregistrer les modifications</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          </button>
        </div>
      </form>

    </div>
  </div>
  <?php endif; ?>

  <footer class="profile-footer">
    <p>© 2026 École Polytechnique Internationale de Yamoussoukro — EPI Tchach</p>
  </footer>

  <script src="<?= $asset ?>/js/desktop/notification-badge.js"></script>
  <script src="<?= $asset ?>/js/desktop/profile.js"></script>
</body>
</html>
