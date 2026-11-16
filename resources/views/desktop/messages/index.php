<?php
/**
 * @var string|null $baseUrl
 * @var string|null $assetBase
 * @var array<int, array<string, mixed>>|null $friends
 * @var array<int, array<string, mixed>>|null $requests
 * @var array<int, array<string, mixed>>|null $messages
 * @var array<string, mixed>|null $selectedFriend
 * @var string|null $csrfToken
 * @var string|null $flashSuccess
 * @var string|null $flashError
 * @var int|null $unreadMessagesCount
 */
$base = is_string($baseUrl ?? null) ? $baseUrl : '';
$asset = is_string($assetBase ?? null)
  ? $assetBase
  : ($base !== '' ? $base . '/public/assets' : '/public/assets');
$friends = is_array($friends ?? null) ? $friends : [];
$requests = is_array($requests ?? null) ? $requests : [];
$messages = is_array($messages ?? null) ? $messages : [];
$selectedFriend = is_array($selectedFriend ?? null) ? $selectedFriend : null;
$unreadMessagesCount = (int)($unreadMessagesCount ?? 0);
$currentUserId = (int)($_SESSION['user_id'] ?? 0);
$currentUserName = trim((string)($_SESSION['user_prenom'] ?? '') . ' ' . (string)($_SESSION['user_nom'] ?? ''));
$currentUserAvatar = !empty($_SESSION['user_avatar'])
  ? url('public/' . ltrim((string)$_SESSION['user_avatar'], '/'))
  : '';
$csrfToken = is_string($csrfToken ?? null) ? $csrfToken : '';
$flashSuccess = is_string($flashSuccess ?? null) ? $flashSuccess : '';
$flashError = is_string($flashError ?? null) ? $flashError : '';
$selectedId = isset($selectedFriend['id']) ? (int)$selectedFriend['id'] : null;
$roleLabels = [
  'etudiant' => 'Étudiant',
  'professeur' => 'Professeur',
  'moderateur' => 'Modérateur',
  'administrateur' => 'Administrateur',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Messagerie · EPI Tchach</title>
  <link rel="icon" type="image/svg+xml" href="<?= htmlspecialchars($asset) ?>/images/logo_epi.svg">
  <?php
  $frontendStyles = ['desktop/community-shell.css', 'desktop/salon.css', 'desktop/messages.css'];
  $frontendScripts = ['desktop/messages.js', 'feedback.js'];
  include ROOT_PATH . '/resources/views/partials/frontend-assets.php';
  ?>
</head>
<body class="community-shell community-salon epi-messages-page" data-base-url="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>">
  <?php include ROOT_PATH . '/resources/views/partials/theme-toggle.php'; ?>
  <?php include ROOT_PATH . '/resources/views/partials/feedback-toast.php'; ?>
  <div class="tricolor-bar"></div>
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
      <div class="epi-topbar-center"><strong>Messagéries</strong></div>
      <div class="epi-topbar-right">
        <a class="epi-icon-action" href="<?= htmlspecialchars($base) ?>/messages" aria-label="Messagerie" title="Messagerie">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
          <?php if ($unreadMessagesCount > 0): ?><span class="epi-badge-count"><?= $unreadMessagesCount > 99 ? '99+' : (int)$unreadMessagesCount ?></span><?php endif; ?>
        </a>
        <a class="epi-icon-action" href="<?= htmlspecialchars($base) ?>/salon" aria-label="Retour aux salons" title="Retour aux salons">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>
        </a>
        <a class="epi-icon-action" href="<?= htmlspecialchars($base) ?>/notifications" aria-label="Notifications" title="Notifications">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        </a>
      </div>
    </div>
  </header>

  <main class="epi-messages-layout<?= $selectedId !== null ? ' is-conversation-open' : '' ?>">
    <aside class="epi-messages-sidebar">
      <section class="epi-messages-panel">
        <div class="epi-messages-panel-heading">
          <h1>Mes amis</h1>
          <span><?= count($friends) ?></span>
        </div>
        <?php if (!$friends): ?>
          <p class="epi-messages-muted">Ajoute des personnes depuis leur profil pour commencer à discuter.</p>
        <?php else: ?>
          <nav class="epi-friend-list" aria-label="Conversations">
            <?php foreach ($friends as $friend):
              $friendId = (int)$friend['id'];
              $friendName = trim($friend['prenom'] . ' ' . $friend['nom']);
              $friendAvatar = !empty($friend['avatar_url'])
                ? url('public/' . ltrim((string)$friend['avatar_url'], '/'))
                : '';
            ?>
              <a class="epi-friend-link <?= $selectedId === $friendId ? 'is-selected' : '' ?>" href="<?= htmlspecialchars($base) ?>/messages?user=<?= $friendId ?>">
                <span class="epi-message-avatar">
                  <span><?= htmlspecialchars(mb_substr((string)$friend['prenom'], 0, 1) . mb_substr((string)$friend['nom'], 0, 1)) ?></span>
                  <?php if ($friendAvatar !== ''): ?><img src="<?= htmlspecialchars($friendAvatar, ENT_QUOTES, 'UTF-8') ?>" alt="" loading="lazy"><?php endif; ?>
                </span>
                <span class="epi-friend-link-content">
                  <strong><?= htmlspecialchars($friendName) ?></strong>
                  <small><?= !empty($friend['last_message']) ? htmlspecialchars(mb_strimwidth((string)$friend['last_message'], 0, 54, '…')) : 'Aucun message pour le moment' ?></small>
                </span>
                <?php if ((int)($friend['unread_count'] ?? 0) > 0): ?><span class="epi-message-unread"><?= (int)$friend['unread_count'] ?></span><?php endif; ?>
              </a>
            <?php endforeach; ?>
          </nav>
        <?php endif; ?>
      </section>

      <section class="epi-messages-panel">
        <div class="epi-messages-panel-heading">
          <h2>Demandes reçues</h2>
          <span><?= count($requests) ?></span>
        </div>
        <?php if (!$requests): ?>
          <p class="epi-messages-muted">Aucune demande en attente.</p>
        <?php else: ?>
          <div class="epi-request-list">
            <?php foreach ($requests as $request):
              $requestId = (int)$request['id'];
              $requestName = trim($request['prenom'] . ' ' . $request['nom']);
              $requestAvatar = !empty($request['avatar_url'])
                ? url('public/' . ltrim((string)$request['avatar_url'], '/'))
                : '';
            ?>
              <article class="epi-friend-request">
                <a class="epi-friend-link" href="<?= htmlspecialchars($base) ?>/profile?id=<?= $requestId ?>">
                  <span class="epi-message-avatar">
                    <span><?= htmlspecialchars(mb_substr((string)$request['prenom'], 0, 1) . mb_substr((string)$request['nom'], 0, 1)) ?></span>
                    <?php if ($requestAvatar !== ''): ?><img src="<?= htmlspecialchars($requestAvatar, ENT_QUOTES, 'UTF-8') ?>" alt="" loading="lazy"><?php endif; ?>
                  </span>
                  <span class="epi-friend-link-content">
                    <strong><?= htmlspecialchars($requestName) ?></strong>
                    <small><?= htmlspecialchars($roleLabels[$request['role']] ?? ucfirst((string)$request['role'])) ?></small>
                  </span>
                </a>
                <div class="epi-request-actions">
                  <form method="post" action="<?= htmlspecialchars($base) ?>/friends/action">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="user_id" value="<?= $requestId ?>">
                    <input type="hidden" name="action" value="accept">
                    <button class="epi-message-accept" type="submit">Accepter</button>
                  </form>
                  <form method="post" action="<?= htmlspecialchars($base) ?>/friends/action">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="user_id" value="<?= $requestId ?>">
                    <input type="hidden" name="action" value="decline">
                    <button class="epi-message-decline" type="submit">Refuser</button>
                  </form>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>
    </aside>

    <section class="epi-conversation-panel" aria-label="Conversation privée">
      <?php if ($selectedFriend):
        $friendName = trim($selectedFriend['prenom'] . ' ' . $selectedFriend['nom']);
        $selectedAvatar = !empty($selectedFriend['avatar_url'])
          ? url('public/' . ltrim((string)$selectedFriend['avatar_url'], '/'))
          : '';
      ?>
        <header class="epi-conversation-header">
          <a class="epi-conversation-back" href="<?= htmlspecialchars($base) ?>/messages" aria-label="Retour aux amis" title="Retour aux amis">
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/><path d="M9 12h12"/></svg>
          </a>
          <a class="epi-message-avatar" href="<?= htmlspecialchars($base) ?>/profile?id=<?= (int)$selectedFriend['id'] ?>">
            <span><?= htmlspecialchars(mb_substr((string)$selectedFriend['prenom'], 0, 1) . mb_substr((string)$selectedFriend['nom'], 0, 1)) ?></span>
            <?php if ($selectedAvatar !== ''): ?><img src="<?= htmlspecialchars($selectedAvatar, ENT_QUOTES, 'UTF-8') ?>" alt=""><?php endif; ?>
          </a>
          <div>
            <a href="<?= htmlspecialchars($base) ?>/profile?id=<?= (int)$selectedFriend['id'] ?>"><?= htmlspecialchars($friendName) ?></a>
            <span><?= htmlspecialchars($roleLabels[$selectedFriend['role']] ?? ucfirst((string)$selectedFriend['role'])) ?></span>
          </div>
        </header>
        <div class="epi-message-history" id="messageHistory" aria-live="polite">
          <?php if (!$messages): ?>
            <div class="epi-conversation-empty">
              <h2>Votre conversation commence ici</h2>
              <p>Envoyez un message à <?= htmlspecialchars($selectedFriend['prenom']) ?>.</p>
            </div>
          <?php else: ?>
            <?php foreach ($messages as $message): ?>
              <?php
                $isMine = (int)$message['sender_id'] === $currentUserId;
                $isDeletedForEveryone = !empty($message['deleted_for_everyone_at']);
              ?>
              <article class="epi-message-row <?= $isMine ? 'is-mine' : 'is-theirs' ?>">
                <div class="epi-message-bubble <?= $isMine ? 'is-mine' : 'is-theirs' ?><?= $isDeletedForEveryone ? ' is-deleted' : '' ?>">
                <?php if ($isDeletedForEveryone): ?>
                  <p class="epi-message-deleted-text"><em>Ce message a été supprimé.</em></p>
                <?php elseif ((string)$message['contenu'] !== ''): ?>
                  <p><?= nl2br(htmlspecialchars((string)$message['contenu'], ENT_QUOTES, 'UTF-8')) ?></p>
                <?php endif; ?>
                <?php foreach ($isDeletedForEveryone ? [] : ($message['attachments'] ?? []) as $attachment):
                  $attachmentUrl = htmlspecialchars($base . '/messages/attachment/' . (int)$attachment['id'], ENT_QUOTES, 'UTF-8');
                  $attachmentName = (string)$attachment['nom_fichier'];
                  $attachmentMime = (string)$attachment['type_mime'];
                  $isImage = str_starts_with($attachmentMime, 'image/');
                  $isAudio = str_starts_with($attachmentMime, 'audio/')
                    || in_array($attachmentMime, ['application/ogg', 'video/webm'], true);
                ?>
                  <div class="epi-message-attachment">
                    <?php if ($isImage): ?>
                      <a href="<?= $attachmentUrl ?>" target="_blank" rel="noopener">
                        <img src="<?= $attachmentUrl ?>" alt="<?= htmlspecialchars($attachmentName, ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
                      </a>
                    <?php elseif ($isAudio): ?>
                      <audio controls preload="none">
                        <source src="<?= $attachmentUrl ?>" type="<?= htmlspecialchars($attachmentMime, ENT_QUOTES, 'UTF-8') ?>">
                        Votre navigateur ne peut pas lire cet audio.
                      </audio>
                    <?php endif; ?>
                    <a class="epi-message-attachment-link" href="<?= $attachmentUrl ?>?download=1">
                      <?= htmlspecialchars($attachmentName, ENT_QUOTES, 'UTF-8') ?>
                      <span>(<?= number_format((int)$attachment['taille'] / 1048576, 2, ',', ' ') ?> Mo)</span>
                    </a>
                  </div>
                <?php endforeach; ?>
                <time datetime="<?= htmlspecialchars((string)$message['date_creation'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(date('d/m/Y H:i', strtotime((string)$message['date_creation']))) ?></time>
                </div>
                <?php if (!$isDeletedForEveryone): ?>
                  <details class="epi-message-actions">
                    <summary aria-label="Actions sur le message" title="Actions sur le message">⋯</summary>
                    <div class="epi-message-actions-menu">
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
                          <button type="submit" data-confirm-delete="everyone">Supprimer pour tout le monde</button>
                        </form>
                      <?php endif; ?>
                    </div>
                  </details>
                <?php endif; ?>
              </article>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
        <form id="privateMessageForm" class="epi-message-composer" method="post" enctype="multipart/form-data" action="<?= htmlspecialchars($base) ?>/messages/send">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="user_id" value="<?= (int)$selectedFriend['id'] ?>">
          <div class="epi-message-composer-input">
            <div class="epi-message-attachment-picker">
            <label class="epi-message-attach-button" for="messageAttachments" aria-label="Joindre des photos ou fichiers" title="Joindre des photos ou fichiers">
              <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l9.2-9.19a4 4 0 0 1 5.65 5.65l-9.2 9.19a2 2 0 0 1-2.82-2.82l8.48-8.49"/></svg>
            </label>
            <input id="messageAttachments" class="sr-only" type="file" name="pieces_jointes[]" multiple
              accept="image/png,image/jpeg,image/gif,image/webp,audio/mpeg,audio/mp4,audio/wav,audio/ogg,audio/webm,.mp3,.m4a,.wav,.ogg,.oga,.webm,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip"
              aria-describedby="messageAttachmentHelp">
            </div>
            <label class="sr-only" for="privateMessage">Votre message</label>
            <textarea id="privateMessage" name="contenu" maxlength="2000" rows="1" placeholder="Écrire un message..." aria-label="Votre message"></textarea>
            <button id="recordMessageAudio" class="epi-message-record" type="button" aria-label="Enregistrer un message vocal" title="Enregistrer un message vocal">
              <svg class="epi-microphone-icon" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="2" width="6" height="12" rx="3"/><path d="M5 10v1a7 7 0 0 0 14 0v-1M12 18v4m-4 0h8"/></svg>
              <svg class="epi-record-stop-icon" aria-hidden="true" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="6" width="12" height="12" rx="2"/></svg>
            </button>
            <button type="submit" class="epi-message-send" aria-label="Envoyer le message" title="Envoyer">
              <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
              <span>Envoyer</span>
            </button>
          </div>
          <div class="epi-message-composer-meta">
            <span id="selectedMessageFiles" class="epi-message-selected-files" aria-live="polite"></span>
            <span id="audioRecordingStatus" class="epi-message-recording-status" role="status" aria-live="polite"></span>
            <small id="messageAttachmentHelp" class="epi-message-attachment-help">Photos, audio et fichiers · 10 Mo par fichier</small>
          </div>
        </form>
      <?php else: ?>
        <div class="epi-conversation-empty epi-conversation-welcome">
          <div class="epi-conversation-icon">✉</div>
          <h1>Ta messagerie privée</h1>
          <p>Sélectionne un ami pour ouvrir une conversation. Les messages sont disponibles uniquement entre amis acceptés.</p>
        </div>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>
