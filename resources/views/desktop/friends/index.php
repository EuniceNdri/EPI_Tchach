<?php
/**
 * ==============================================================
 * resources/views/desktop/friends/index.php
 * Vue Liste des Amis d'un Utilisateur · EPI Tchach
 * ==============================================================
 *
 * @var string|null $baseUrl
 * @var string|null $assetBase
 * @var array $targetUser
 * @var array $friends
 * @var array $pendingRequests
 * @var array $suggestions
 * @var int $unreadMessagesCount
 * @var bool $isOwnProfile
 * @var string $csrfToken
 * @var string $flashSuccess
 * @var string $flashError
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

$targetName = trim(($targetUser['prenom'] ?? '') . ' ' . ($targetUser['nom'] ?? ''));
$targetAvatar = !empty($targetUser['avatar_url'])
  ? url('public/' . ltrim((string)$targetUser['avatar_url'], '/'))
  : '';
$targetInitial = strtoupper(mb_substr((string)($targetUser['prenom'] ?? 'M'), 0, 1) . mb_substr((string)($targetUser['nom'] ?? ''), 0, 1));

$currentUserName = trim(($_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? ''));
$currentUserRole = $_SESSION['user_role'] ?? 'etudiant';

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
  <title><?= $isOwnProfile ? 'Mes Amis' : 'Amis de ' . htmlspecialchars($targetName) ?> · EPI Tchach</title>
  <link rel="icon" type="image/svg+xml" href="<?= $asset ?>/images/logo_epi.svg" />
  <link rel="apple-touch-icon" href="<?= $asset ?>/images/logo.jpeg" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="<?= $asset ?>/css/desktop/community-shell.css" />
  <link rel="stylesheet" href="<?= $asset ?>/css/desktop/salon.css" />
  <link rel="stylesheet" href="<?= $asset ?>/css/desktop/friends.css" />
  <link rel="stylesheet" href="<?= $asset ?>/css/feedback.css" />
  <?php include ROOT_PATH . '/resources/views/partials/theme-head.php'; ?>
</head>
<body class="community-shell community-salon" data-base-url="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>">
  <?php include ROOT_PATH . '/resources/views/partials/theme-toggle.php'; ?>
  <?php include ROOT_PATH . '/resources/views/partials/feedback-toast.php'; ?>

  <!-- Bande tricolore signature EPI -->
  <div class="tricolor-bar"></div>

  <!-- ==============================================================
   * TOPBAR DE NAVIGATION (Style EPI Tchach)
   * ============================================================== -->
  <header class="epi-topbar">
    <div class="epi-topbar-inner">
      <div class="epi-topbar-left">
        <a class="epi-brand" href="<?= $base ?>/salon" aria-label="Accueil EPI Tchach">
          <div class="epi-brand-icon">
            <img src="<?= $asset ?>/images/logo.jpeg" alt="Logo EPI" />
          </div>
          <span class="epi-brand-title">EPI <span class="accent">Tchach</span></span>
        </a>
        <a class="epi-community-pill" href="<?= $base ?>/profile">
          <span>u/<?= htmlspecialchars(strtolower(preg_replace('/[^a-zA-Z0-9_.]/', '', str_replace(' ', '.', $currentUserName)))) ?></span>
        </a>
      </div>

      <div class="epi-topbar-center">
        <span class="epi-topbar-brand-title"><?= $isOwnProfile ? 'Mes Amis' : 'Amis de ' . htmlspecialchars($targetUser['prenom'] ?? '') ?></span>
      </div>

      <div class="epi-topbar-right">
        <a class="epi-icon-action" href="<?= $base ?>/messages" aria-label="Messagerie" title="Messagerie">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
          <?php if ($unreadMessagesCount > 0): ?><span class="epi-badge-count"><?= $unreadMessagesCount > 99 ? '99+' : (int)$unreadMessagesCount ?></span><?php endif; ?>
        </a>
        <a class="epi-icon-action" href="<?= $base ?>/salon" aria-label="Fil d'actualité" title="Fil d'actualité">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>
        </a>
        <a class="epi-icon-action" href="<?= $base ?>/notifications" aria-label="Notifications" title="Notifications">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        </a>
      </div>
    </div>
  </header>

  <!-- ==============================================================
   * LAYOUT PRINCIPAL
   * ============================================================== -->
  <div class="epi-salon-grid">

    <!-- ========== SIDEBAR GAUCHE (grid-area: sidebar) ========== -->
    <aside class="epi-sidebar-left">
      <div class="epi-sidebar-box">
        <div class="epi-nav-heading">Navigation</div>
        <a href="<?= $base ?>/salon" class="epi-nav-item">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          <span>Fil public</span>
        </a>
        <a href="<?= $base ?>/salon?view=popular" class="epi-nav-item">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>
          <span>Populaires</span>
        </a>
        <a href="<?= $base ?>/friends" class="epi-nav-item is-active">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          <span>Mes amis</span>
        </a>
        <a href="<?= $base ?>/messages" class="epi-nav-item">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
          <span>Messagerie</span>
        </a>
        <a href="<?= $base ?>/profile" class="epi-nav-item">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          <span>Mon profil</span>
        </a>
        <a href="<?= $base ?>/notifications" class="epi-nav-item">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <span>Notifications</span>
        </a>

        <?php if ($currentUserRole === 'administrateur'): ?>
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

      <!-- Messages Flash -->
      <?php if ($flashSuccess): ?>
        <div style="background:rgba(16,185,129,0.12); border:1px solid #10b981; color:#34d399; padding:0.75rem 1rem; border-radius:var(--community-radius-md); font-size:0.9rem; margin-bottom:1rem;" role="status">
          <?= htmlspecialchars($flashSuccess) ?>
        </div>
      <?php endif; ?>
      <?php if ($flashError): ?>
        <div style="background:rgba(239,68,68,0.12); border:1px solid #ef4444; color:#f87171; padding:0.75rem 1rem; border-radius:var(--community-radius-md); font-size:0.9rem; margin-bottom:1rem;" role="alert">
          <?= htmlspecialchars($flashError) ?>
        </div>
      <?php endif; ?>

      <!-- 1. En-tête de la page d'amis -->
      <section class="epi-friends-header-card">
        <div class="epi-friends-header-info">
          <div class="epi-friends-header-avatar">
            <?php if ($targetAvatar !== ''): ?>
              <img src="<?= htmlspecialchars($targetAvatar, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($targetName) ?>" />
            <?php else: ?>
              <span><?= htmlspecialchars($targetInitial) ?></span>
            <?php endif; ?>
          </div>
          <div class="epi-friends-header-text">
            <h1><?= $isOwnProfile ? 'Mes Amis' : 'Amis de ' . htmlspecialchars($targetName) ?></h1>
            <p><?= $isOwnProfile ? 'Gérez vos contacts, discutez en direct et découvrez d’autres étudiants.' : 'Consultez les relations de ce membre.' ?></p>
          </div>
        </div>
        <div class="epi-friends-badge-count">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          <span id="friendsCountDisplay"><?= count($friends) ?> ami<?= count($friends) > 1 ? 's' : '' ?></span>
        </div>
      </section>

      <!-- 2. Barre d'outils : Onglets & Recherche instantanée -->
      <div class="epi-friends-toolbar">
        <div class="epi-friends-tabs" role="tablist">
          <button type="button" class="epi-friends-tab-btn is-active" data-friends-tab="all" role="tab" aria-selected="true">
            <span>Tous les amis</span>
            <span class="epi-friends-tab-count"><?= count($friends) ?></span>
          </button>
          <?php if ($isOwnProfile && count($pendingRequests) > 0): ?>
            <button type="button" class="epi-friends-tab-btn" data-friends-tab="requests" role="tab" aria-selected="false">
              <span>Demandes reçues</span>
              <span class="epi-friends-tab-count" style="background:#ef4444; color:#fff;"><?= count($pendingRequests) ?></span>
            </button>
          <?php endif; ?>
          <?php if ($isOwnProfile && count($suggestions) > 0): ?>
            <button type="button" class="epi-friends-tab-btn" data-friends-tab="suggestions" role="tab" aria-selected="false">
              <span>Suggestions</span>
              <span class="epi-friends-tab-count"><?= count($suggestions) ?></span>
            </button>
          <?php endif; ?>
        </div>

        <div class="epi-friends-search">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="search" id="friendsSearchInput" placeholder="Rechercher par nom ou filière..." aria-label="Rechercher parmi les amis" autocomplete="off" />
        </div>
      </div>

      <!-- 3. Section : Grille des Amis -->
      <div id="tabContentAll" class="epi-friends-tab-content">
        <?php if (empty($friends)): ?>
          <div class="epi-friends-empty">
            <div class="epi-friends-empty-icon">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <h2><?= $isOwnProfile ? 'Vous n’avez pas encore d’amis' : 'Cet utilisateur n’a pas encore d’amis' ?></h2>
            <p><?= $isOwnProfile ? 'Explorez les publications du Salon ou découvrez les suggestions pour agrandir votre réseau à l’EPI !' : '' ?></p>
            <?php if ($isOwnProfile): ?>
              <a href="<?= $base ?>/salon" class="epi-btn-primary" style="display:inline-flex; padding:0.6rem 1.25rem; text-decoration:none; border-radius:var(--community-radius-md);">
                Découvrir le salon
              </a>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <div class="epi-friends-grid" id="friendsCardsGrid">
            <?php foreach ($friends as $friend):
              $fName = trim(($friend['prenom'] ?? '') . ' ' . ($friend['nom'] ?? ''));
              $fAvatar = !empty($friend['avatar_url'])
                ? url('public/' . ltrim((string)$friend['avatar_url'], '/'))
                : '';
              $fInitials = strtoupper(mb_substr((string)($friend['prenom'] ?? 'M'), 0, 1) . mb_substr((string)($friend['nom'] ?? ''), 0, 1));
              $fRole = $roleLabels[$friend['role'] ?? 'etudiant'] ?? ucfirst((string)$friend['role']);
              $fFiliere = (string)($friend['filiere'] ?? '');
            ?>
              <article class="epi-friend-card" data-friend-card data-friend-name="<?= htmlspecialchars(mb_strtolower($fName), ENT_QUOTES, 'UTF-8') ?>" data-friend-filiere="<?= htmlspecialchars(mb_strtolower($fFiliere), ENT_QUOTES, 'UTF-8') ?>">
                <div class="epi-friend-card-avatar">
                  <?php if ($fAvatar !== ''): ?>
                    <img src="<?= htmlspecialchars($fAvatar, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($fName) ?>" loading="lazy" />
                  <?php else: ?>
                    <span><?= htmlspecialchars($fInitials) ?></span>
                  <?php endif; ?>
                </div>

                <a class="epi-friend-card-name" href="<?= $base ?>/profile?id=<?= (int)$friend['id'] ?>">
                  <?= htmlspecialchars($fName) ?>
                </a>

                <div class="epi-friend-card-badges">
                  <span class="epi-friend-badge role"><?= htmlspecialchars($fRole) ?></span>
                  <?php if ($fFiliere !== ''): ?>
                    <span class="epi-friend-badge"><?= htmlspecialchars($fFiliere) ?></span>
                  <?php endif; ?>
                </div>

                <div class="epi-friend-card-actions">
                  <a class="epi-btn-chat" href="<?= $base ?>/messages?user=<?= (int)$friend['id'] ?>" title="Discuter sur WhatsApp Web">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                    <span>Discuter</span>
                  </a>
                  <a class="epi-btn-profile" href="<?= $base ?>/profile?id=<?= (int)$friend['id'] ?>" title="Voir le profil">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                  </a>
                  <?php if ($isOwnProfile): ?>
                    <form method="post" action="<?= $base ?>/friends/action" onsubmit="return confirm('Retirer <?= htmlspecialchars(addslashes($fName)) ?> de vos amis ?');">
                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>" />
                      <input type="hidden" name="target_user_id" value="<?= (int)$friend['id'] ?>" />
                      <input type="hidden" name="action" value="remove" />
                      <input type="hidden" name="return_url" value="friends" />
                      <button type="submit" class="epi-btn-remove" title="Retirer des amis" aria-label="Retirer des amis">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                      </button>
                    </form>
                  <?php endif; ?>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
          <p id="noFriendsSearchMatch" style="text-align:center; color:var(--community-muted); padding:2rem 1rem;" hidden>
            Aucun ami ne correspond à cette recherche.
          </p>
        <?php endif; ?>
      </div>

      <!-- 4. Section : Demandes reçues (si propre profil) -->
      <?php if ($isOwnProfile): ?>
        <div id="tabContentRequests" class="epi-friends-tab-content" hidden>
          <?php if (empty($pendingRequests)): ?>
            <div class="epi-friends-empty">
              <div class="epi-friends-empty-icon">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
              </div>
              <h2>Aucune demande d’ami en attente</h2>
              <p>Lorsque d'autres étudiants vous envoient une invitation, elle apparaîtra ici.</p>
            </div>
          <?php else: ?>
            <div class="epi-requests-list">
              <?php foreach ($pendingRequests as $req):
                $rName = trim(($req['prenom'] ?? '') . ' ' . ($req['nom'] ?? ''));
                $rAvatar = !empty($req['avatar_url'])
                  ? url('public/' . ltrim((string)$req['avatar_url'], '/'))
                  : '';
                $rInitial = strtoupper(mb_substr((string)($req['prenom'] ?? 'M'), 0, 1) . mb_substr((string)($req['nom'] ?? ''), 0, 1));
                $rRole = $roleLabels[$req['role'] ?? 'etudiant'] ?? ucfirst((string)$req['role']);
              ?>
                <article class="epi-request-card">
                  <div class="epi-request-user">
                    <div class="epi-request-avatar">
                      <?php if ($rAvatar !== ''): ?>
                        <img src="<?= htmlspecialchars($rAvatar, ENT_QUOTES, 'UTF-8') ?>" alt="" />
                      <?php else: ?>
                        <span><?= htmlspecialchars($rInitial) ?></span>
                      <?php endif; ?>
                    </div>
                    <div>
                      <a href="<?= $base ?>/profile?id=<?= (int)$req['id'] ?>" style="font-weight:700; color:var(--community-text); text-decoration:none;">
                        <?= htmlspecialchars($rName) ?>
                      </a>
                      <div style="font-size:0.8rem; color:var(--community-muted);"><?= htmlspecialchars($rRole) ?><?= !empty($req['filiere']) ? ' · ' . htmlspecialchars($req['filiere']) : '' ?></div>
                    </div>
                  </div>

                  <div class="epi-request-actions">
                    <form method="post" action="<?= $base ?>/friends/action">
                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>" />
                      <input type="hidden" name="target_user_id" value="<?= (int)$req['id'] ?>" />
                      <input type="hidden" name="action" value="accept" />
                      <input type="hidden" name="return_url" value="friends" />
                      <button type="submit" class="epi-btn-accept">Accepter</button>
                    </form>
                    <form method="post" action="<?= $base ?>/friends/action">
                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>" />
                      <input type="hidden" name="target_user_id" value="<?= (int)$req['id'] ?>" />
                      <input type="hidden" name="action" value="decline" />
                      <input type="hidden" name="return_url" value="friends" />
                      <button type="submit" class="epi-btn-decline">Refuser</button>
                    </form>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- 5. Section : Suggestions de membres -->
        <div id="tabContentSuggestions" class="epi-friends-tab-content" hidden>
          <div class="epi-friends-grid">
            <?php foreach ($suggestions as $sug):
              $sName = trim(($sug['prenom'] ?? '') . ' ' . ($sug['nom'] ?? ''));
              $sAvatar = !empty($sug['avatar_url'])
                ? url('public/' . ltrim((string)$sug['avatar_url'], '/'))
                : '';
              $sInitial = strtoupper(mb_substr((string)($sug['prenom'] ?? 'M'), 0, 1) . mb_substr((string)($sug['nom'] ?? ''), 0, 1));
              $sRole = $roleLabels[$sug['role'] ?? 'etudiant'] ?? ucfirst((string)$sug['role']);
              $sFiliere = (string)($sug['filiere'] ?? '');
            ?>
              <article class="epi-friend-card">
                <div class="epi-friend-card-avatar" style="background:linear-gradient(135deg, #6366f1, #8b5cf6);">
                  <?php if ($sAvatar !== ''): ?>
                    <img src="<?= htmlspecialchars($sAvatar, ENT_QUOTES, 'UTF-8') ?>" alt="" />
                  <?php else: ?>
                    <span><?= htmlspecialchars($sInitial) ?></span>
                  <?php endif; ?>
                </div>

                <a class="epi-friend-card-name" href="<?= $base ?>/profile?id=<?= (int)$sug['id'] ?>">
                  <?= htmlspecialchars($sName) ?>
                </a>

                <div class="epi-friend-card-badges">
                  <span class="epi-friend-badge role"><?= htmlspecialchars($sRole) ?></span>
                  <?php if ($sFiliere !== ''): ?>
                    <span class="epi-friend-badge"><?= htmlspecialchars($sFiliere) ?></span>
                  <?php endif; ?>
                </div>

                <div class="epi-friend-card-actions">
                  <form method="post" action="<?= $base ?>/friends/action" style="width:100%;">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>" />
                    <input type="hidden" name="target_user_id" value="<?= (int)$sug['id'] ?>" />
                    <input type="hidden" name="action" value="request" />
                    <input type="hidden" name="return_url" value="friends" />
                    <button type="submit" class="epi-btn-chat" style="width:100%; background:var(--community-green);">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                      <span>Ajouter en ami</span>
                    </button>
                  </form>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

    </main>

    <!-- ========== SIDEBAR DROITE (grid-area: widgets) ========== -->
    <aside class="epi-sidebar-right">
      <div class="epi-widget-box">
        <h3 class="epi-widget-title">Communauté EPI</h3>
        <p style="font-size:0.86rem; color:var(--community-muted); line-height:1.5; margin-bottom:1rem;">
          Connectez-vous avec vos camarades de classe et professeurs pour partager des cours, des questions et collaborer.
        </p>
        <a href="<?= $base ?>/messages" class="epi-btn-chat" style="width:100%; display:inline-flex; justify-content:center; padding:0.6rem; border-radius:var(--community-radius-md); text-decoration:none;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
          <span>Ouvrir WhatsApp Web</span>
        </a>
      </div>

      <div class="epi-widget-box" style="margin-top:1rem;">
        <h3 class="epi-widget-title">Raccourcis rapides</h3>
        <div style="display:flex; flex-direction:column; gap:0.5rem;">
          <a href="<?= $base ?>/profile" class="epi-nav-item" style="padding:0.45rem 0.5rem; font-size:0.84rem;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <span>Modifier mon profil</span>
          </a>
          <a href="<?= $base ?>/salon?view=documents" class="epi-nav-item" style="padding:0.45rem 0.5rem; font-size:0.84rem;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            <span>Bibliothèque partagée</span>
          </a>
        </div>
      </div>
    </aside>

  </div>

  <script>
    document.addEventListener("DOMContentLoaded", () => {
      // 1. Gestion des onglets
      const tabButtons = document.querySelectorAll("[data-friends-tab]");
      const tabContents = {
        all: document.getElementById("tabContentAll"),
        requests: document.getElementById("tabContentRequests"),
        suggestions: document.getElementById("tabContentSuggestions")
      };

      tabButtons.forEach(btn => {
        btn.addEventListener("click", () => {
          const tabName = btn.dataset.friendsTab;
          tabButtons.forEach(b => {
            b.classList.remove("is-active");
            b.setAttribute("aria-selected", "false");
          });
          btn.classList.add("is-active");
          btn.setAttribute("aria-selected", "true");

          Object.keys(tabContents).forEach(key => {
            if (tabContents[key]) {
              tabContents[key].hidden = key !== tabName;
            }
          });
        });
      });

      // 2. Recherche instantanée en direct
      const searchInput = document.getElementById("friendsSearchInput");
      const friendCards = document.querySelectorAll("[data-friend-card]");
      const noMatchMsg = document.getElementById("noFriendsSearchMatch");

      if (searchInput) {
        searchInput.addEventListener("input", (e) => {
          const term = e.target.value.toLowerCase().trim();
          let visibleCount = 0;

          // Si l'utilisateur tape quelque chose, basculer sur l'onglet 'all'
          if (term !== "") {
            const allBtn = document.querySelector('[data-friends-tab="all"]');
            if (allBtn && !allBtn.classList.contains("is-active")) {
              allBtn.click();
            }
          }

          friendCards.forEach(card => {
            const name = card.dataset.friendName || "";
            const filiere = card.dataset.friendFiliere || "";
            const matches = name.includes(term) || filiere.includes(term);

            card.style.display = matches ? "" : "none";
            if (matches) visibleCount++;
          });

          if (noMatchMsg) {
            noMatchMsg.hidden = visibleCount > 0 || friendCards.length === 0;
          }
        });
      }
    });
  </script>
</body>
</html>
