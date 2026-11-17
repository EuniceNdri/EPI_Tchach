<?php
$base = is_string($baseUrl ?? null) ? $baseUrl : '';
$asset = is_string($assetBase ?? null)
  ? $assetBase
  : ($base !== '' ? $base . '/public/assets' : '/public/assets');
$friends = is_array($friends ?? null) ? $friends : [];
$groups = is_array($groups ?? null) ? $groups : [];
$requests = is_array($requests ?? null) ? $requests : [];
$messages = is_array($messages ?? null) ? $messages : [];
$groupMessages = is_array($groupMessages ?? null) ? $groupMessages : [];
$groupMembers = is_array($groupMembers ?? null) ? $groupMembers : [];
$currentUserId = (int)($_SESSION['user_id'] ?? 0);
$currentUserName = trim((string)($_SESSION['user_prenom'] ?? '') . ' ' . (string)($_SESSION['user_nom'] ?? ''));
$currentUserAvatar = !empty($_SESSION['user_avatar'])
  ? url('public/' . ltrim((string)$_SESSION['user_avatar'], '/'))
  : '';
$csrfToken = is_string($csrfToken ?? null) ? $csrfToken : '';
$flashSuccess = is_string($flashSuccess ?? null) ? $flashSuccess : '';
$flashError = is_string($flashError ?? null) ? $flashError : '';
$selectedId = isset($selectedFriend['id']) ? (int)$selectedFriend['id'] : null;
$selectedGroupId = isset($selectedGroup['id']) ? (int)$selectedGroup['id'] : null;
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
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Messagerie Instantanée · EPI Tchach</title>
  <link rel="icon" type="image/svg+xml" href="<?= htmlspecialchars($asset) ?>/images/logo_epi.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= htmlspecialchars($asset) ?>/css/desktop/community-shell.css">
  <link rel="stylesheet" href="<?= htmlspecialchars($asset) ?>/css/desktop/salon.css">
  <link rel="stylesheet" href="<?= htmlspecialchars($asset) ?>/css/desktop/messages.css?v=<?= (int)filemtime(ROOT_PATH . '/public/assets/css/desktop/messages.css') ?>">
  <?php include ROOT_PATH . '/resources/views/partials/theme-head.php'; ?>
</head>
<body class="community-shell community-salon epi-messages-page" data-base-url="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>">
  <?php include ROOT_PATH . '/resources/views/partials/feedback-toast.php'; ?>
  <div class="tricolor-bar"></div>

  <!-- Topbar Header -->
  <header class="epi-topbar">
    <div class="epi-topbar-inner">
      <div class="epi-topbar-left">
        <a class="epi-brand" href="<?= htmlspecialchars($base) ?>/salon" aria-label="Accueil EPI Tchach">
          <div class="epi-brand-icon"><img src="<?= htmlspecialchars($asset) ?>/images/logo.jpeg" alt="EPI"></div>
          <span class="epi-brand-title">EPI <span class="accent">Tchach</span></span>
        </a>
        <a class="epi-community-pill" href="<?= htmlspecialchars($base) ?>/profile">
          <span>u/<?= htmlspecialchars(strtolower(preg_replace('/[^a-zA-Z0-9_.]/', '', str_replace(' ', '.', $currentUserName)))) ?></span>
        </a>
      </div>
      <div class="epi-topbar-center">
        <span class="epi-topbar-brand-title">Messagerie Campus</span>
      </div>
      <div class="epi-topbar-right">
        <a class="epi-icon-action" href="<?= htmlspecialchars($base) ?>/salon" aria-label="Retour au fil campus" title="Retour au fil campus">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>
        </a>
        <a class="epi-icon-action" href="<?= htmlspecialchars($base) ?>/friends" aria-label="Gérer mes amis" title="Mes camarades">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </a>
        <a class="epi-icon-action" href="<?= htmlspecialchars($base) ?>/notifications" aria-label="Notifications" title="Notifications">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        </a>
        <?php include ROOT_PATH . '/resources/views/partials/theme-toggle.php'; ?>
      </div>
    </div>
  </header>

  <main class="epi-messages-layout<?= ($selectedId !== null || $selectedGroupId !== null) ? ' is-conversation-open' : '' ?>">
    <!-- ==================== LEFT COLUMN: CONVERSATION LIST ==================== -->
    <aside class="epi-messages-sidebar">
      <header class="epi-messages-sidebar-header">
        <div class="epi-messages-sidebar-title">
          <div>
            <span class="epi-messages-eyebrow">Direct Campus</span>
            <h1>Discussions</h1>
          </div>
          <div class="epi-messages-header-actions">
            <a href="<?= htmlspecialchars($base) ?>/friends" class="epi-messages-action-btn" title="Nouvelle discussion / Amis" aria-label="Nouvelle discussion">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
            </a>
          </div>
        </div>

        <!-- Search Bar -->
        <label class="epi-messages-search">
          <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
          <input id="conversationSearch" type="search" placeholder="Rechercher une discussion..." autocomplete="off">
        </label>

        <!-- Segmented Filter Pills -->
        <div class="epi-messages-filters" role="group" aria-label="Filtrer les conversations">
          <button type="button" class="is-active" data-conversation-filter="all" aria-pressed="true">Toutes</button>
          <button type="button" data-conversation-filter="groups" aria-pressed="false">Groupes (<?= count($groups) ?>)</button>
          <button type="button" data-conversation-filter="private" aria-pressed="false">Privées (<?= count($friends) ?>)</button>
          <button type="button" data-conversation-filter="requests" aria-pressed="false">
            Demandes<?php if (count($requests) > 0): ?> <span class="epi-message-unread-pill"><?= count($requests) ?></span><?php endif; ?>
          </button>
        </div>
      </header>

      <p class="epi-messages-search-empty" id="conversationSearchEmpty" role="status" hidden>Aucune conversation trouvée.</p>

      <div class="epi-messages-sidebar-scroll">
        <!-- GROUPS SECTION -->
        <section class="epi-messages-panel-section" data-conversation-section="groups">
          <div class="epi-messages-section-title">
            <span>Groupes de filière & promo</span>
            <span class="epi-section-count"><?= count($groups) ?></span>
          </div>
          <?php if (!$groups): ?>
            <p class="epi-messages-muted">Aucun groupe de filière disponible.</p>
          <?php else: ?>
            <nav class="epi-conv-list" aria-label="Groupes de filière">
              <?php foreach ($groups as $group): ?>
                <a class="epi-conv-card <?= $selectedGroupId === (int)$group['id'] ? 'is-selected' : '' ?>"
                   data-conversation-item="groups"
                   href="<?= htmlspecialchars($base) ?>/messages?group=<?= (int)$group['id'] ?>">
                  <div class="epi-conv-avatar is-group">
                    <span>#</span>
                  </div>
                  <div class="epi-conv-info">
                    <div class="epi-conv-top">
                      <strong class="epi-conv-name"><?= htmlspecialchars($group['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                      <span class="epi-conv-badge-tag">Promo</span>
                    </div>
                    <p class="epi-conv-snippet">Groupe officiel de filière · Tous niveaux</p>
                  </div>
                  <?php if ((int)$group['unread_count'] > 0): ?>
                    <span class="epi-conv-unread"><?= (int)$group['unread_count'] ?></span>
                  <?php endif; ?>
                </a>
              <?php endforeach; ?>
            </nav>
          <?php endif; ?>
        </section>

        <!-- PRIVATE CONVERSATIONS SECTION -->
        <section class="epi-messages-panel-section" data-conversation-section="private">
          <div class="epi-messages-section-title">
            <span>Amis & Camarades</span>
            <span class="epi-section-count"><?= count($friends) ?></span>
          </div>
          <?php if (!$friends): ?>
            <div class="epi-conv-empty-callout">
              <p>Aucun ami pour le moment.</p>
              <a href="<?= htmlspecialchars($base) ?>/friends" class="epi-btn-link">+ Trouver des camarades</a>
            </div>
          <?php else: ?>
            <nav class="epi-conv-list" aria-label="Conversations privées">
              <?php foreach ($friends as $friend):
                $friendId = (int)$friend['id'];
                $friendName = trim($friend['prenom'] . ' ' . $friend['nom']);
                $friendAvatar = !empty($friend['avatar_url'])
                  ? url('public/' . ltrim((string)$friend['avatar_url'], '/'))
                  : '';
                $initials = mb_substr((string)$friend['prenom'], 0, 1) . mb_substr((string)$friend['nom'], 0, 1);
                $conversationUrl = !empty($friend['conversation_id'])
                  ? $base . '/messages/' . (int)$friend['conversation_id']
                  : $base . '/messages?user=' . $friendId;
              ?>
                <a class="epi-conv-card <?= $selectedId === $friendId ? 'is-selected' : '' ?>"
                   data-conversation-item="private"
                   href="<?= htmlspecialchars($conversationUrl, ENT_QUOTES, 'UTF-8') ?>">
                  <div class="epi-conv-avatar">
                    <span><?= htmlspecialchars($initials) ?></span>
                    <?php if ($friendAvatar !== ''): ?><img src="<?= htmlspecialchars($friendAvatar, ENT_QUOTES, 'UTF-8') ?>" alt="" loading="lazy"><?php endif; ?>
                    <span class="epi-online-dot" title="En ligne"></span>
                  </div>
                  <div class="epi-conv-info">
                    <div class="epi-conv-top">
                      <strong class="epi-conv-name"><?= htmlspecialchars($friendName) ?></strong>
                      <span class="epi-conv-time"><?= !empty($friend['last_activity']) ? htmlspecialchars(date('H:i', strtotime((string)$friend['last_activity']))) : '' ?></span>
                    </div>
                    <p class="epi-conv-snippet"><?= !empty($friend['last_message']) ? htmlspecialchars(mb_strimwidth((string)$friend['last_message'], 0, 48, '…')) : 'Aucun message pour le moment' ?></p>
                  </div>
                  <?php if ((int)($friend['unread_count'] ?? 0) > 0): ?>
                    <span class="epi-conv-unread"><?= (int)$friend['unread_count'] ?></span>
                  <?php endif; ?>
                </a>
              <?php endforeach; ?>
            </nav>
          <?php endif; ?>
        </section>

        <!-- REQUESTS SECTION -->
        <section class="epi-messages-panel-section" data-conversation-section="requests">
          <div class="epi-messages-section-title">
            <span>Demandes en attente</span>
            <span class="epi-section-count"><?= count($requests) ?></span>
          </div>
          <?php if (!$requests): ?>
            <p class="epi-messages-muted">Aucune demande en attente.</p>
          <?php else: ?>
            <div class="epi-request-cards">
              <?php foreach ($requests as $request):
                $requestId = (int)$request['id'];
                $requestName = trim($request['prenom'] . ' ' . $request['nom']);
                $requestAvatar = !empty($request['avatar_url'])
                  ? url('public/' . ltrim((string)$request['avatar_url'], '/'))
                  : '';
              ?>
                <article class="epi-request-item" data-conversation-item="requests">
                  <div class="epi-request-user">
                    <div class="epi-conv-avatar">
                      <span><?= htmlspecialchars(mb_substr((string)$request['prenom'], 0, 1) . mb_substr((string)$request['nom'], 0, 1)) ?></span>
                      <?php if ($requestAvatar !== ''): ?><img src="<?= htmlspecialchars($requestAvatar, ENT_QUOTES, 'UTF-8') ?>" alt="" loading="lazy"><?php endif; ?>
                    </div>
                    <div class="epi-request-info">
                      <strong><?= htmlspecialchars($requestName) ?></strong>
                      <small><?= htmlspecialchars($roleLabels[$request['role']] ?? ucfirst((string)$request['role'])) ?></small>
                    </div>
                  </div>
                  <div class="epi-request-actions">
                    <form method="post" action="<?= htmlspecialchars($base) ?>/friends/action">
                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                      <input type="hidden" name="target_user_id" value="<?= $requestId ?>">
                      <input type="hidden" name="user_id" value="<?= $requestId ?>">
                      <input type="hidden" name="action" value="accept">
                      <input type="hidden" name="return_url" value="messages">
                      <button class="epi-btn-accept" type="submit">Accepter</button>
                    </form>
                    <form method="post" action="<?= htmlspecialchars($base) ?>/friends/action">
                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                      <input type="hidden" name="target_user_id" value="<?= $requestId ?>">
                      <input type="hidden" name="user_id" value="<?= $requestId ?>">
                      <input type="hidden" name="action" value="decline">
                      <input type="hidden" name="return_url" value="messages">
                      <button class="epi-btn-decline" type="submit">Refuser</button>
                    </form>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </section>
      </div>
    </aside>

    <!-- ==================== CENTER COLUMN: ACTIVE CHAT CONVERSATION ==================== -->
    <section class="epi-conversation-panel" aria-label="Conversation active">
      <?php if ($selectedGroup): ?>
        <!-- GROUP CHAT HEADER -->
        <header class="epi-chat-header">
          <div class="epi-chat-header-left">
            <a class="epi-chat-back-btn" href="<?= htmlspecialchars($base) ?>/messages" aria-label="Retour aux discussions" title="Retour">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m15 18-6-6 6-6"/></svg>
            </a>
            <div class="epi-conv-avatar is-group">
              <span>#</span>
            </div>
            <div class="epi-chat-contact-info">
              <h2 class="epi-chat-contact-name"><?= htmlspecialchars($selectedGroup['name'], ENT_QUOTES, 'UTF-8') ?></h2>
              <span class="epi-chat-contact-status">Groupe de filière · <?= count($groupMembers) ?> membre<?= count($groupMembers) > 1 ? 's' : '' ?></span>
            </div>
          </div>
          <div class="epi-chat-header-actions">
            <button type="button" class="epi-chat-tool-btn" id="groupMembersToggle" aria-label="Voir la liste des membres" title="Membres du groupe">
              <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M20 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </button>
          </div>
        </header>

        <!-- GROUP MEMBERS DRAWER / MODAL -->
        <div class="epi-group-members-drawer" id="groupMembersPanel" hidden>
          <div class="epi-drawer-header">
            <h3>Membres de la promotion</h3>
            <button type="button" class="epi-drawer-close" id="closeGroupMembersBtn" aria-label="Fermer">✕</button>
          </div>
          <ul class="epi-drawer-list">
            <?php foreach ($groupMembers as $member):
              $mName = trim((string)$member['prenom'] . ' ' . (string)$member['nom']);
              $mInitial = mb_substr((string)$member['prenom'], 0, 1) . mb_substr((string)$member['nom'], 0, 1);
            ?>
              <li>
                <a href="<?= htmlspecialchars($base) ?>/profile/<?= (int)$member['id'] ?>" class="epi-drawer-user-link">
                  <span class="epi-drawer-avatar"><?= htmlspecialchars($mInitial) ?></span>
                  <div>
                    <strong><?= htmlspecialchars($mName) ?></strong>
                    <small><?= htmlspecialchars($roleLabels[$member['role']] ?? ucfirst((string)$member['role'])) ?></small>
                  </div>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <!-- GROUP MESSAGES HISTORY -->
        <div class="epi-message-history" id="messageHistory" aria-live="polite">
          <div class="epi-chat-encryption-banner">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <span>Canal officiel de promotion EPI Tchach. Les messages sont partagés avec tous les étudiants de la filière.</span>
          </div>

          <?php if (!$groupMessages): ?>
            <div class="epi-conversation-empty">
              <div class="epi-empty-chat-icon">💬</div>
              <h2>Bienvenue dans le canal <?= htmlspecialchars($selectedGroup['name'], ENT_QUOTES, 'UTF-8') ?></h2>
              <p>Posez vos questions sur les cours, TD, examens ou organisez vos révisions !</p>
            </div>
          <?php else: ?>
            <?php foreach ($groupMessages as $message):
              $isMine = (int)$message['sender_id'] === $currentUserId;
              $senderName = trim((string)$message['prenom'] . ' ' . (string)$message['nom']);
            ?>
              <article class="epi-msg-bubble-row <?= $isMine ? 'is-sent' : 'is-received' ?>">
                <div class="epi-msg-bubble">
                  <?php if (!$isMine): ?>
                    <span class="epi-msg-author-name"><?= htmlspecialchars($senderName, ENT_QUOTES, 'UTF-8') ?></span>
                  <?php endif; ?>
                  <div class="epi-msg-text"><?= nl2br(htmlspecialchars((string)$message['contenu'], ENT_QUOTES, 'UTF-8')) ?></div>
                  <div class="epi-msg-meta">
                    <time datetime="<?= htmlspecialchars((string)$message['date_creation'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(date('H:i', strtotime((string)$message['date_creation'])), ENT_QUOTES, 'UTF-8') ?></time>
                    <?php if ($isMine): ?><span class="epi-msg-checks">✓✓</span><?php endif; ?>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- GROUP COMPOSER -->
        <form class="epi-chat-composer" method="post" action="<?= htmlspecialchars($base) ?>/messages/groups/send">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="group_id" value="<?= $selectedGroupId ?>">
          <div class="epi-composer-inner">
            <textarea id="groupMessage" name="contenu" maxlength="2000" rows="1" placeholder="Écrire au groupe <?= htmlspecialchars($selectedGroup['name']) ?>..." required></textarea>
            <button type="submit" class="epi-composer-send-btn" aria-label="Envoyer" title="Envoyer">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
            </button>
          </div>
        </form>

      <?php elseif ($selectedFriend):
        $friendName = trim($selectedFriend['prenom'] . ' ' . $selectedFriend['nom']);
        $selectedAvatar = !empty($selectedFriend['avatar_url'])
          ? url('public/' . ltrim((string)$selectedFriend['avatar_url'], '/'))
          : '';
        $friendInitials = mb_substr((string)$selectedFriend['prenom'], 0, 1) . mb_substr((string)$selectedFriend['nom'], 0, 1);
        $friendRole = $roleLabels[$selectedFriend['role']] ?? ucfirst((string)$selectedFriend['role']);
      ?>
        <!-- PRIVATE CHAT HEADER -->
        <header class="epi-chat-header">
          <div class="epi-chat-header-left">
            <a class="epi-chat-back-btn" href="<?= htmlspecialchars($base) ?>/messages" aria-label="Retour aux discussions" title="Retour">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m15 18-6-6 6-6"/></svg>
            </a>
            <a class="epi-conv-avatar" href="<?= htmlspecialchars($base) ?>/profile/<?= (int)$selectedFriend['id'] ?>">
              <span><?= htmlspecialchars($friendInitials) ?></span>
              <?php if ($selectedAvatar !== ''): ?><img src="<?= htmlspecialchars($selectedAvatar, ENT_QUOTES, 'UTF-8') ?>" alt=""><?php endif; ?>
              <span class="epi-online-dot"></span>
            </a>
            <div class="epi-chat-contact-info">
              <h2 class="epi-chat-contact-name">
                <a href="<?= htmlspecialchars($base) ?>/profile/<?= (int)$selectedFriend['id'] ?>"><?= htmlspecialchars($friendName) ?></a>
              </h2>
              <span class="epi-chat-contact-status"><span class="epi-status-green">● En ligne</span> • <?= htmlspecialchars($friendRole) ?></span>
            </div>
          </div>
          <div class="epi-chat-header-actions">
            <button type="button" class="epi-chat-tool-btn" id="toggleContactInfoBtn" aria-label="Détails du camarade" title="Informations & Fichiers">
              <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
            </button>
          </div>
        </header>

        <!-- CHAT MESSAGES SCROLL AREA -->
        <div class="epi-message-history" id="messageHistory" aria-live="polite">
          <div class="epi-chat-encryption-banner">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <span>Les messages échangés sont chiffrés et confidentiels entre vous et <?= htmlspecialchars($selectedFriend['prenom']) ?>.</span>
          </div>

          <?php if (!$messages): ?>
            <div class="epi-conversation-empty">
              <div class="epi-empty-chat-icon">👋</div>
              <h2>Votre discussion avec <?= htmlspecialchars($selectedFriend['prenom']) ?></h2>
              <p>Dites bonjour ou partagez des documents de révision pour lancer la discussion !</p>
              <div class="epi-quick-suggestions">
                <button type="button" class="epi-quick-chip" onclick="insertQuickSuggestion('Salut <?= htmlspecialchars($selectedFriend['prenom']) ?>, tu es dispo pour réviser ?')">Salut, tu es dispo pour réviser ?</button>
                <button type="button" class="epi-quick-chip" onclick="insertQuickSuggestion('Est-ce que tu as le support du cours d\'aujourd\'hui ?')">Tu as le support du cours ?</button>
                <button type="button" class="epi-quick-chip" onclick="insertQuickSuggestion('Merci beaucoup pour ton aide !')">Merci beaucoup !</button>
              </div>
            </div>
          <?php else: ?>
            <?php foreach ($messages as $message): ?>
              <?php
                $isMine = (int)$message['sender_id'] === $currentUserId;
                $isDeletedForEveryone = !empty($message['deleted_for_everyone_at']);
                $attachments = $isDeletedForEveryone ? [] : ($message['attachments'] ?? []);
              ?>
              <article class="epi-msg-bubble-row <?= $isMine ? 'is-sent' : 'is-received' ?>">
                <div class="epi-msg-bubble <?= $isDeletedForEveryone ? 'is-deleted' : '' ?>">
                  <?php if ($isDeletedForEveryone): ?>
                    <p class="epi-msg-deleted-text"><em>Ce message a été supprimé.</em></p>
                  <?php elseif ((string)$message['contenu'] !== ''): ?>
                    <p class="epi-msg-text"><?= nl2br(htmlspecialchars((string)$message['contenu'], ENT_QUOTES, 'UTF-8')) ?></p>
                  <?php endif; ?>

                  <!-- Attachments preview -->
                  <?php foreach ($attachments as $attachment):
                    $attUrl = htmlspecialchars($base . '/messages/attachment/' . (int)$attachment['id'], ENT_QUOTES, 'UTF-8');
                    $attName = (string)$attachment['nom_fichier'];
                    $attMime = (string)$attachment['type_mime'];
                    $isImg = str_starts_with($attMime, 'image/');
                    $isAudio = str_starts_with($attMime, 'audio/') || in_array($attMime, ['application/ogg', 'video/webm'], true);
                  ?>
                    <div class="epi-msg-attachment-card">
                      <?php if ($isImg): ?>
                        <a href="<?= $attUrl ?>" target="_blank" rel="noopener" class="epi-msg-img-wrap">
                          <img src="<?= $attUrl ?>" alt="<?= htmlspecialchars($attName) ?>" loading="lazy">
                        </a>
                      <?php elseif ($isAudio): ?>
                        <div class="epi-msg-audio-player">
                          <span class="epi-audio-icon">🎙️</span>
                          <audio controls preload="none">
                            <source src="<?= $attUrl ?>" type="<?= htmlspecialchars($attMime) ?>">
                          </audio>
                        </div>
                      <?php else: ?>
                        <a href="<?= $attUrl ?>?download=1" class="epi-msg-file-download">
                          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                          <div class="epi-msg-file-meta">
                            <strong><?= htmlspecialchars($attName) ?></strong>
                            <small><?= number_format((int)$attachment['taille'] / 1048576, 2, ',', ' ') ?> Mo</small>
                          </div>
                          <svg class="epi-file-dl-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        </a>
                      <?php endif; ?>
                    </div>
                  <?php endforeach; ?>

                  <div class="epi-msg-meta">
                    <time datetime="<?= htmlspecialchars((string)$message['date_creation'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(date('H:i', strtotime((string)$message['date_creation']))) ?></time>
                    <?php if ($isMine): ?><span class="epi-msg-checks" title="Distribué">✓✓</span><?php endif; ?>
                  </div>
                </div>

                <!-- Message Dropdown Menu -->
                <?php if (!$isDeletedForEveryone): ?>
                  <details class="epi-msg-options">
                    <summary aria-label="Options" title="Options">⋯</summary>
                    <div class="epi-msg-menu">
                      <form method="post" action="<?= htmlspecialchars($base) ?>/messages/delete">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="user_id" value="<?= (int)$selectedFriend['id'] ?>">
                        <input type="hidden" name="message_id" value="<?= (int)$message['id'] ?>">
                        <input type="hidden" name="delete_action" value="me">
                        <button type="submit">Supprimer pour moi</button>
                      </form>
                      <?php if ($isMine): ?>
                        <form method="post" action="<?= htmlspecialchars($base) ?>/messages/delete">
                          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                          <input type="hidden" name="user_id" value="<?= (int)$selectedFriend['id'] ?>">
                          <input type="hidden" name="message_id" value="<?= (int)$message['id'] ?>">
                          <input type="hidden" name="delete_action" value="everyone">
                          <button type="submit" data-confirm-delete="everyone" class="text-danger">Supprimer pour tout le monde</button>
                        </form>
                      <?php endif; ?>
                    </div>
                  </details>
                <?php endif; ?>
              </article>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- PRIVATE COMPOSER FOOTER -->
        <form id="privateMessageForm" class="epi-chat-composer" method="post" enctype="multipart/form-data" action="<?= htmlspecialchars($base) ?>/messages/send">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="recipient_id" value="<?= (int)$selectedFriend['id'] ?>">
          <input type="hidden" name="user_id" value="<?= (int)$selectedFriend['id'] ?>">

          <div class="epi-composer-inner">
            <!-- Attachment Button -->
            <label class="epi-composer-btn" for="messageAttachments" aria-label="Joindre un fichier" title="Joindre un document ou une photo">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l9.2-9.19a4 4 0 0 1 5.65 5.65l-9.2 9.19a2 2 0 0 1-2.82-2.82l8.48-8.49"/></svg>
            </label>
            <input id="messageAttachments" class="sr-only" type="file" name="pieces_jointes[]" multiple
              accept="image/png,image/jpeg,image/gif,image/webp,audio/mpeg,audio/mp4,audio/wav,audio/ogg,audio/webm,.mp3,.m4a,.wav,.ogg,.oga,.webm,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip">

            <!-- Audio Recording Button -->
            <button id="recordMessageAudio" class="epi-composer-btn epi-mic-btn" type="button" aria-label="Enregistrer un message vocal" title="Enregistrer une note vocale">
              <svg class="epi-microphone-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="2" width="6" height="12" rx="3"/><path d="M5 10v1a7 7 0 0 0 14 0v-1M12 18v4m-4 0h8"/></svg>
              <svg class="epi-record-stop-icon" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="6" width="12" height="12" rx="2"/></svg>
            </button>

            <!-- Text Input -->
            <textarea id="privateMessage" name="contenu" maxlength="2000" rows="1" placeholder="Écrire un message à <?= htmlspecialchars($selectedFriend['prenom']) ?>..." aria-label="Message"></textarea>

            <!-- Submit Send Button -->
            <button type="submit" class="epi-composer-send-btn" aria-label="Envoyer" title="Envoyer le message">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
            </button>
          </div>

          <!-- Attachment & Audio Feedback -->
          <div class="epi-composer-feedback">
            <span id="selectedMessageFiles" class="epi-selected-files" aria-live="polite"></span>
            <span id="audioRecordingStatus" class="epi-recording-badge" role="status" aria-live="polite"></span>
          </div>
        </form>

        <!-- RIGHT COLLAPSIBLE DRAWER: CONTACT INFO & SHARED MEDIA -->
        <aside class="epi-contact-drawer" id="contactInfoDrawer" hidden>
          <div class="epi-drawer-header">
            <h3>Détails du camarade</h3>
            <button type="button" class="epi-drawer-close" id="closeContactDrawerBtn" aria-label="Fermer">✕</button>
          </div>
          <div class="epi-drawer-body">
            <div class="epi-drawer-profile-card">
              <div class="epi-drawer-avatar-lg">
                <span><?= htmlspecialchars($friendInitials) ?></span>
                <?php if ($selectedAvatar !== ''): ?><img src="<?= htmlspecialchars($selectedAvatar, ENT_QUOTES, 'UTF-8') ?>" alt=""><?php endif; ?>
              </div>
              <h4><?= htmlspecialchars($friendName) ?></h4>
              <span class="epi-drawer-badge"><?= htmlspecialchars($friendRole) ?></span>
              <a href="<?= htmlspecialchars($base) ?>/profile/<?= (int)$selectedFriend['id'] ?>" class="epi-drawer-link-btn">Voir le profil complet</a>
            </div>

            <div class="epi-drawer-section">
              <h5>Statut universitaire</h5>
              <div class="epi-drawer-status-pill">
                <span class="epi-status-dot"></span>
                <span>Connecté sur le campus</span>
              </div>
            </div>

            <div class="epi-drawer-section">
              <h5>Actions directes</h5>
              <a href="<?= htmlspecialchars($base) ?>/friends?user=<?= (int)$selectedFriend['id'] ?>" class="epi-drawer-action-row">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                <span>Voir ses amis & réseau</span>
              </a>
              <form method="post" action="<?= htmlspecialchars($base) ?>/friends/action" onsubmit="return confirm('Retirer ce camarade de votre liste d\'amis ?')">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="target_user_id" value="<?= (int)$selectedFriend['id'] ?>">
                <input type="hidden" name="action" value="remove">
                <input type="hidden" name="return_url" value="messages">
                <button type="submit" class="epi-drawer-danger-row">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="18" y1="8" x2="23" y2="13"/><line x1="23" y1="8" x2="18" y2="13"/></svg>
                  <span>Retirer des amis</span>
                </button>
              </form>
            </div>
          </div>
        </aside>

      <?php else: ?>
        <!-- EMPTY STATE (NO CHAT SELECTED) -->
        <div class="epi-conversation-empty epi-chat-welcome">
          <div class="epi-welcome-icon-wrap">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#00685f" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
          </div>
          <h1>EPI Tchach · Messagerie Campus</h1>
          <p>Discutez en temps réel avec vos binômes, partagez vos supports de cours et gardez le contact avec votre filière.</p>
          <div class="epi-welcome-features">
            <div class="epi-feature-pill">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 12 2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg>
              <span>Notes vocales instantanées</span>
            </div>
            <div class="epi-feature-pill">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>
              <span>Partage PDF & TDs</span>
            </div>
            <div class="epi-feature-pill">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              <span>Échanges privés & sécurisés</span>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </section>
  </main>

  <script src="<?= htmlspecialchars($asset) ?>/js/desktop/messages.js"></script>
  <script>
    // Quick suggestion chip inserter
    function insertQuickSuggestion(text) {
      const textarea = document.getElementById('privateMessage');
      if (textarea) {
        textarea.value = text;
        textarea.focus();
      }
    }

    // Toggle Contact Info Drawer
    const toggleContactInfoBtn = document.getElementById('toggleContactInfoBtn');
    const contactInfoDrawer = document.getElementById('contactInfoDrawer');
    const closeContactDrawerBtn = document.getElementById('closeContactDrawerBtn');
    if (toggleContactInfoBtn && contactInfoDrawer) {
      toggleContactInfoBtn.addEventListener('click', () => {
        contactInfoDrawer.hidden = !contactInfoDrawer.hidden;
      });
      closeContactDrawerBtn?.addEventListener('click', () => {
        contactInfoDrawer.hidden = true;
      });
    }

    // Toggle Group Members Drawer
    const groupMembersToggle = document.getElementById('groupMembersToggle');
    const groupMembersPanel = document.getElementById('groupMembersPanel');
    const closeGroupMembersBtn = document.getElementById('closeGroupMembersBtn');
    if (groupMembersToggle && groupMembersPanel) {
      groupMembersToggle.addEventListener('click', () => {
        groupMembersPanel.hidden = !groupMembersPanel.hidden;
      });
      closeGroupMembersBtn?.addEventListener('click', () => {
        groupMembersPanel.hidden = true;
      });
    }

    // Search filter for sidebar
    const conversationSearch = document.getElementById('conversationSearch');
    const conversationFilters = [...document.querySelectorAll('[data-conversation-filter]')];
    const conversationSections = [...document.querySelectorAll('[data-conversation-section]')];
    const emptyConversationSearch = document.getElementById('conversationSearchEmpty');
    let activeConversationFilter = 'all';

    const filterConversations = () => {
      const query = conversationSearch?.value.trim().toLocaleLowerCase('fr') || '';
      let visibleConversations = 0;
      conversationSections.forEach(section => {
        const sectionType = section.dataset.conversationSection;
        const matchesFilter = activeConversationFilter === 'all'
          || sectionType === activeConversationFilter
          || (activeConversationFilter === 'private' && sectionType === 'requests');
        const items = [...section.querySelectorAll('[data-conversation-item]')];
        let visibleInSection = 0;
        items.forEach(item => {
          const matchesQuery = !query || item.textContent.toLocaleLowerCase('fr').includes(query);
          item.hidden = !matchesQuery;
          if (matchesQuery) visibleInSection += 1;
        });
        const hasSearchMatch = !query || visibleInSection > 0;
        section.hidden = !matchesFilter || !hasSearchMatch;
        if (matchesFilter) visibleConversations += visibleInSection;
      });
      if (emptyConversationSearch) {
        emptyConversationSearch.hidden = !query || visibleConversations > 0;
      }
    };

    conversationSearch?.addEventListener('input', filterConversations);
    conversationFilters.forEach(button => button.addEventListener('click', () => {
      activeConversationFilter = button.dataset.conversationFilter || 'all';
      conversationFilters.forEach(filter => {
        filter.classList.toggle('is-active', filter === button);
        filter.setAttribute('aria-pressed', String(filter === button));
      });
      filterConversations();
    }));

    // Auto-grow textarea
    const autoGrow = (el) => {
      el.style.height = 'auto';
      el.style.height = Math.min(el.scrollHeight, 120) + 'px';
    };
    const privateMsgInput = document.getElementById('privateMessage');
    if (privateMsgInput) {
      privateMsgInput.addEventListener('input', () => autoGrow(privateMsgInput));
      privateMsgInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
          e.preventDefault();
          document.getElementById('privateMessageForm')?.requestSubmit();
        }
      });
    }
  </script>
</body>
</html>
