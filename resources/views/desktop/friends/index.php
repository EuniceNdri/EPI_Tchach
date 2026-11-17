<?php
/**
 * ==============================================================
 * resources/views/desktop/friends/index.php
 * Camarades & Réseau d'Entraide Universitaire · EPI Tchach
 * Design Campus Hub (Teal #00685f, Amber #fea619)
 * ==============================================================
 *
 * @var string|null $baseUrl
 * @var string|null $assetBase
 * @var array|null $targetUser
 * @var array|null $friends
 * @var array|null $pendingRequests
 * @var array|null $suggestions
 * @var bool|null $isOwnProfile
 * @var int|null $unreadMessagesCount
 * @var string|null $csrfToken
 */

$base = is_string($baseUrl ?? null) ? $baseUrl : '';
$asset = is_string($assetBase ?? null)
  ? $assetBase
  : ($base !== '' ? $base . '/public/assets' : '/public/assets');

$targetUser = is_array($targetUser ?? null) ? $targetUser : [];
$friends = is_array($friends ?? null) ? $friends : [];
$pendingRequests = is_array($pendingRequests ?? null) ? $pendingRequests : [];
$suggestions = is_array($suggestions ?? null) ? $suggestions : [];
$isOwnProfile = (bool)($isOwnProfile ?? false);
$currentUserId = (int)($_SESSION['user_id'] ?? 0);
$unreadMessagesCount = max(0, (int)($unreadMessagesCount ?? 0));
$csrfToken = is_string($csrfToken ?? null) ? $csrfToken : ($_SESSION['csrf_token'] ?? '');

$targetName = trim(($targetUser['prenom'] ?? '') . ' ' . ($targetUser['nom'] ?? ''));
$targetAvatar = !empty($targetUser['avatar_url'])
  ? url('public/' . ltrim((string)$targetUser['avatar_url'], '/'))
  : '';
$targetInitial = strtoupper(mb_substr((string)($targetUser['prenom'] ?? 'M'), 0, 1) . mb_substr((string)($targetUser['nom'] ?? ''), 0, 1));
$currentUserName = trim(($_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? ''));

$roleLabels = [
    'etudiant' => 'Étudiant',
    'professeur' => 'Professeur',
    'moderateur' => 'Modérateur',
    'administrateur' => 'Administrateur',
    'delegue_general' => 'Délégué Général',
    'delegue_de_classe' => 'Délégué de Classe',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $isOwnProfile ? 'Camarades & Réseau d\'Entraide' : 'Amis de ' . htmlspecialchars($targetName) ?> · EPI Tchach</title>
  <link rel="icon" type="image/svg+xml" href="<?= htmlspecialchars($asset) ?>/images/logo_epi.svg" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="<?= htmlspecialchars($asset) ?>/css/desktop/community-shell.css" />
  <link rel="stylesheet" href="<?= htmlspecialchars($asset) ?>/css/desktop/salon.css?v=<?= (int)filemtime(ROOT_PATH . '/public/assets/css/desktop/salon.css') ?>" />
  <link rel="stylesheet" href="<?= htmlspecialchars($asset) ?>/css/desktop/friends.css?v=<?= (int)filemtime(ROOT_PATH . '/public/assets/css/desktop/friends.css') ?>" />
  <link rel="stylesheet" href="<?= htmlspecialchars($asset) ?>/css/feedback.css" />
  <?php include ROOT_PATH . '/resources/views/partials/theme-head.php'; ?>
</head>
<body class="community-shell community-salon campus-friends-page" data-base-url="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>">
  <?php include ROOT_PATH . '/resources/views/partials/theme-toggle.php'; ?>
  <?php include ROOT_PATH . '/resources/views/partials/feedback-toast.php'; ?>

  <!-- Signature tricolore EPI -->
  <div class="tricolor-bar"></div>

  <!-- Topbar Header -->
  <header class="epi-topbar">
    <div class="epi-topbar-inner">
      <div class="epi-topbar-left">
        <a class="epi-brand" href="<?= htmlspecialchars($base) ?>/salon" aria-label="Accueil EPI Tchach">
          <div class="epi-brand-icon">
            <img src="<?= htmlspecialchars($asset) ?>/images/logo.jpeg" alt="EPI" />
          </div>
          <span class="epi-brand-title">EPI <span class="accent">Tchach</span></span>
        </a>
        <a class="epi-community-pill" href="<?= htmlspecialchars($base) ?>/profile">
          <span>u/<?= htmlspecialchars(strtolower(preg_replace('/[^a-zA-Z0-9_.]/', '', str_replace(' ', '.', $currentUserName)))) ?></span>
        </a>
      </div>

      <div class="epi-topbar-center">
        <span class="epi-topbar-brand-title">Réseau Universitaire</span>
      </div>

      <div class="epi-topbar-right">
        <a class="epi-icon-action" href="<?= htmlspecialchars($base) ?>/messages" aria-label="Messagerie" title="Messagerie">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
          <?php if ($unreadMessagesCount > 0): ?><span class="epi-badge-count"><?= $unreadMessagesCount > 99 ? '99+' : (int)$unreadMessagesCount ?></span><?php endif; ?>
        </a>
        <a class="epi-icon-action" href="<?= htmlspecialchars($base) ?>/salon" aria-label="Fil d'actualité" title="Fil d'actualité">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>
        </a>
        <a class="epi-icon-action" href="<?= htmlspecialchars($base) ?>/notifications" aria-label="Notifications" title="Notifications">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        </a>
      </div>
    </div>
  </header>

  <!-- 3-Column Layout -->
  <div class="epi-salon-grid">
    <!-- LEFT SIDEBAR -->
    <aside class="epi-sidebar-left">
      <div class="epi-sidebar-box">
        <div class="epi-nav-heading">Navigation</div>
        <a href="<?= htmlspecialchars($base) ?>/salon" class="epi-nav-item">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          <span>Fil Campus</span>
        </a>
        <a href="<?= htmlspecialchars($base) ?>/salon?view=popular" class="epi-nav-item">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>
          <span>Populaires</span>
        </a>
        <a href="<?= htmlspecialchars($base) ?>/friends" class="epi-nav-item is-active">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          <span>Mes Amis</span>
        </a>
        <a href="<?= htmlspecialchars($base) ?>/messages" class="epi-nav-item">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
          <span>Messagerie</span>
        </a>
        <a href="<?= htmlspecialchars($base) ?>/profile" class="epi-nav-item">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          <span>Mon profil</span>
        </a>
        <a href="<?= htmlspecialchars($base) ?>/notifications" class="epi-nav-item">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <span>Notifications</span>
        </a>
      </div>
    </aside>

    <!-- CENTER FEED / MAIN -->
    <main class="epi-main-feed">
      <!-- Flash alerts -->
      <?php if (!empty($flashSuccess)): ?>
        <div class="campus-alert is-success" role="status">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          <span><?= htmlspecialchars($flashSuccess) ?></span>
        </div>
      <?php endif; ?>
      <?php if (!empty($flashError)): ?>
        <div class="campus-alert is-error" role="alert">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <span><?= htmlspecialchars($flashError) ?></span>
        </div>
      <?php endif; ?>

      <!-- 1. Hero Campus Header Card -->
      <section class="campus-network-hero">
        <div class="campus-hero-content">
          <div class="campus-hero-badge">
            <span class="campus-badge-dot"></span>
            <span>Réseau d'Entraide EPI</span>
          </div>
          <h1 class="campus-hero-title"><?= $isOwnProfile ? 'Camarades & Réseau d\'Entraide' : 'Amis de ' . htmlspecialchars($targetName) ?></h1>
          <p class="campus-hero-subtitle">
            <?= $isOwnProfile ? 'Gérez vos connexions universitaires, traitez vos demandes de binômes et restez connecté.' : 'Découvrez les contacts et camarades de ce profil.' ?>
          </p>
        </div>
        <div class="campus-hero-actions">
          <a href="<?= htmlspecialchars($base) ?>/messages" class="campus-hero-btn secondary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
            <span>Ouvrir la messagerie</span>
          </a>
        </div>
      </section>

      <!-- 2. Segmented Navigation Bar -->
      <div class="campus-nav-toolbar">
        <div class="campus-tabs-segment" role="tablist">
          <button type="button" class="campus-tab is-active" data-tab-target="tab-friends" role="tab" aria-selected="true">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <span>Mes Camarades</span>
            <span class="campus-tab-badge"><?= count($friends) ?></span>
          </button>
          <?php if ($isOwnProfile): ?>
            <button type="button" class="campus-tab" data-tab-target="tab-requests" role="tab" aria-selected="false">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/></svg>
              <span>Demandes</span>
              <?php if (count($pendingRequests) > 0): ?>
                <span class="campus-tab-badge is-urgent"><?= count($pendingRequests) ?></span>
              <?php endif; ?>
            </button>
            <button type="button" class="campus-tab" data-tab-target="tab-suggestions" role="tab" aria-selected="false">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
              <span>Suggestions Binômes</span>
              <span class="campus-tab-badge"><?= count($suggestions) ?></span>
            </button>
          <?php endif; ?>
        </div>

        <div class="campus-search-filter">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="search" id="friendsSearchInput" placeholder="Rechercher par nom, filière..." autocomplete="off">
        </div>
      </div>

      <!-- TAB 1 : MES CAMARADES -->
      <section class="campus-tab-pane is-active" id="tab-friends">
        <?php if (!$friends): ?>
          <div class="campus-empty-box">
            <div class="campus-empty-icon">🤝</div>
            <h3>Aucun camarade dans cette liste</h3>
            <p>Découvrez d'autres étudiants de l'École Polytechnique et formez vos premiers binômes de révision.</p>
          </div>
        <?php else: ?>
          <div class="campus-friends-grid" id="friendsGrid">
            <?php foreach ($friends as $friend):
              $fId = (int)$friend['id'];
              $fName = trim($friend['prenom'] . ' ' . $friend['nom']);
              $fAvatar = !empty($friend['avatar_url'])
                ? url('public/' . ltrim((string)$friend['avatar_url'], '/'))
                : '';
              $fRole = $roleLabels[$friend['role']] ?? ucfirst((string)$friend['role']);
              $fInitials = mb_substr((string)$friend['prenom'], 0, 1) . mb_substr((string)$friend['nom'], 0, 1);
            ?>
              <div class="campus-friend-card" data-search-text="<?= htmlspecialchars(mb_strtolower($fName . ' ' . $fRole)) ?>">
                <div class="campus-friend-card-top">
                  <div class="campus-friend-avatar">
                    <span><?= htmlspecialchars($fInitials) ?></span>
                    <?php if ($fAvatar !== ''): ?><img src="<?= htmlspecialchars($fAvatar, ENT_QUOTES, 'UTF-8') ?>" alt="" loading="lazy"><?php endif; ?>
                    <span class="campus-online-dot" title="Actif"></span>
                  </div>
                  <div class="campus-friend-meta">
                    <a href="<?= htmlspecialchars($base) ?>/profile/<?= $fId ?>" class="campus-friend-name"><?= htmlspecialchars($fName) ?></a>
                    <span class="campus-friend-role"><?= htmlspecialchars($fRole) ?></span>
                  </div>
                </div>

                <div class="campus-friend-actions">
                  <a href="<?= htmlspecialchars($base) ?>/messages?user=<?= $fId ?>" class="campus-action-btn primary" title="Envoyer un message direct">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                    <span>Discuter</span>
                  </a>
                  <a href="<?= htmlspecialchars($base) ?>/profile/<?= $fId ?>" class="campus-action-btn secondary" title="Voir le profil">
                    <span>Profil</span>
                  </a>
                  <?php if ($isOwnProfile): ?>
                    <form method="post" action="<?= htmlspecialchars($base) ?>/friends/action" onsubmit="return confirm('Retirer ce membre de vos contacts ?')">
                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                      <input type="hidden" name="target_user_id" value="<?= $fId ?>">
                      <input type="hidden" name="action" value="remove">
                      <input type="hidden" name="return_url" value="friends">
                      <button type="submit" class="campus-action-btn danger" title="Retirer des amis">
                        ✕
                      </button>
                    </form>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>

      <!-- TAB 2 : DEMANDES EN ATTENTE -->
      <?php if ($isOwnProfile): ?>
        <section class="campus-tab-pane" id="tab-requests" hidden>
          <?php if (!$pendingRequests): ?>
            <div class="campus-empty-box">
              <div class="campus-empty-icon">📬</div>
              <h3>Aucune demande d'ami en attente</h3>
              <p>Vous êtes à jour ! Vos futures invitations reçues s'afficheront ici.</p>
            </div>
          <?php else: ?>
            <div class="campus-requests-list">
              <?php foreach ($pendingRequests as $req):
                $rId = (int)$req['id'];
                $rName = trim($req['prenom'] . ' ' . $req['nom']);
                $rAvatar = !empty($req['avatar_url'])
                  ? url('public/' . ltrim((string)$req['avatar_url'], '/'))
                  : '';
                $rRole = $roleLabels[$req['role']] ?? ucfirst((string)$req['role']);
                $rInitials = mb_substr((string)$req['prenom'], 0, 1) . mb_substr((string)$req['nom'], 0, 1);
              ?>
                <div class="campus-request-card">
                  <div class="campus-request-user">
                    <div class="campus-friend-avatar">
                      <span><?= htmlspecialchars($rInitials) ?></span>
                      <?php if ($rAvatar !== ''): ?><img src="<?= htmlspecialchars($rAvatar, ENT_QUOTES, 'UTF-8') ?>" alt="" loading="lazy"><?php endif; ?>
                    </div>
                    <div class="campus-request-info">
                      <strong><?= htmlspecialchars($rName) ?></strong>
                      <span><?= htmlspecialchars($rRole) ?></span>
                      <small>Souhaite vous ajouter à ses camarades de révision</small>
                    </div>
                  </div>
                  <div class="campus-request-buttons">
                    <form method="post" action="<?= htmlspecialchars($base) ?>/friends/action">
                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                      <input type="hidden" name="target_user_id" value="<?= $rId ?>">
                      <input type="hidden" name="action" value="accept">
                      <input type="hidden" name="return_url" value="friends">
                      <button type="submit" class="campus-btn-accept">Accepter</button>
                    </form>
                    <form method="post" action="<?= htmlspecialchars($base) ?>/friends/action">
                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                      <input type="hidden" name="target_user_id" value="<?= $rId ?>">
                      <input type="hidden" name="action" value="decline">
                      <input type="hidden" name="return_url" value="friends">
                      <button type="submit" class="campus-btn-decline">Refuser</button>
                    </form>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </section>

        <!-- TAB 3 : SUGGESTIONS BINÔMES -->
        <section class="campus-tab-pane" id="tab-suggestions" hidden>
          <?php if (!$suggestions): ?>
            <div class="campus-empty-box">
              <div class="campus-empty-icon">⚡</div>
              <h3>Aucune suggestion disponible</h3>
              <p>Rejoignez des salons de filières pour recevoir des recommandations personnalisées.</p>
            </div>
          <?php else: ?>
            <div class="campus-friends-grid">
              <?php foreach ($suggestions as $sug):
                $sId = (int)$sug['id'];
                $sName = trim($sug['prenom'] . ' ' . $sug['nom']);
                $sAvatar = !empty($sug['avatar_url'])
                  ? url('public/' . ltrim((string)$sug['avatar_url'], '/'))
                  : '';
                $sRole = $roleLabels[$sug['role']] ?? ucfirst((string)$sug['role']);
                $sInitials = mb_substr((string)$sug['prenom'], 0, 1) . mb_substr((string)$sug['nom'], 0, 1);
              ?>
                <div class="campus-friend-card">
                  <div class="campus-friend-card-top">
                    <div class="campus-friend-avatar">
                      <span><?= htmlspecialchars($sInitials) ?></span>
                      <?php if ($sAvatar !== ''): ?><img src="<?= htmlspecialchars($sAvatar, ENT_QUOTES, 'UTF-8') ?>" alt="" loading="lazy"><?php endif; ?>
                    </div>
                    <div class="campus-friend-meta">
                      <a href="<?= htmlspecialchars($base) ?>/profile/<?= $sId ?>" class="campus-friend-name"><?= htmlspecialchars($sName) ?></a>
                      <span class="campus-friend-role"><?= htmlspecialchars($sRole) ?></span>
                      <small class="campus-compatibility">⚡ 95% compatible promo</small>
                    </div>
                  </div>
                  <div class="campus-friend-actions">
                    <form method="post" action="<?= htmlspecialchars($base) ?>/friends/action" style="width:100%;">
                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                      <input type="hidden" name="target_user_id" value="<?= $sId ?>">
                      <input type="hidden" name="action" value="request">
                      <input type="hidden" name="return_url" value="friends">
                      <button type="submit" class="campus-action-btn primary w-full">
                        + Inviter en binôme
                      </button>
                    </form>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </section>
      <?php endif; ?>
    </main>

    <!-- RIGHT SIDEBAR -->
    <aside class="epi-sidebar-right">
      <div class="campus-sidebar-card">
        <div class="campus-card-header">
          <span class="campus-card-icon">⚡</span>
          <h4>Algorithme Binômes EPI</h4>
        </div>
        <p class="campus-card-body">
          Le matching universitaire analyse vos filières et vos participations aux salons pour vous proposer des binômes de révision performants.
        </p>
      </div>

      <div class="campus-sidebar-card">
        <div class="campus-card-header">
          <span class="campus-card-icon">📜</span>
          <h4>Charte d'Entraide</h4>
        </div>
        <ul class="campus-card-list">
          <li>Partage solidaire des fiches de TD et cours.</li>
          <li>Respect mutuel et bienveillance sur le campus.</li>
          <li>Confidentialité des échanges directs.</li>
        </ul>
      </div>
    </aside>
  </div>

  <script>
    // Tab switching behavior
    const tabs = document.querySelectorAll('[data-tab-target]');
    const panes = document.querySelectorAll('.campus-tab-pane');

    tabs.forEach(tab => {
      tab.addEventListener('click', () => {
        tabs.forEach(t => {
          t.classList.remove('is-active');
          t.setAttribute('aria-selected', 'false');
        });
        panes.forEach(p => p.hidden = true);

        tab.classList.add('is-active');
        tab.setAttribute('aria-selected', 'true');
        const targetPane = document.getElementById(tab.dataset.tabTarget);
        if (targetPane) {
          targetPane.hidden = false;
        }
      });
    });

    // Real-time search filter in friends list
    const searchInput = document.getElementById('friendsSearchInput');
    const cards = document.querySelectorAll('.campus-friend-card[data-search-text]');
    if (searchInput) {
      searchInput.addEventListener('input', () => {
        const query = searchInput.value.trim().toLowerCase();
        cards.forEach(card => {
          const text = card.dataset.searchText || '';
          card.style.display = text.includes(query) ? '' : 'none';
        });
      });
    }
  </script>
</body>
</html>
