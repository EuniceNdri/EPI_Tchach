<?php
/**
 * =============================================================
 * resources/views/desktop/notifications.php
 * Centre de Notifications – EPI Tchach (École Polytechnique Internationale)
 * =============================================================
 *
 * @var string|null $baseUrl
 * @var string|null $assetBase
 * @var array|null  $notifications
 * @var int|null    $unreadCount
 * @var bool|null   $canModerateReports
 */

$base = is_string($baseUrl ?? null) ? $baseUrl : '';
$asset = is_string($assetBase ?? null)
  ? $assetBase
  : ($base !== '' ? $base . '/public/assets' : '/public/assets');
$logoFile = $asset . '/images/logo.jpeg';

$notifications = is_array($notifications ?? null) ? $notifications : [];
$unreadCount = max(0, (int)($unreadCount ?? 0));
$canModerateReports = !empty($canModerateReports);

if (!isset($_SESSION) || !is_array($_SESSION)) {
  $_SESSION = [];
}
$currentUserRole = strtolower(trim((string)($_SESSION['user_role'] ?? 'etudiant')));

if (!function_exists('formatNotificationDate')) {
  function formatNotificationDate(string $dateStr): string {
    try {
      $date = new DateTimeImmutable($dateStr);
      $seconds = max(0, time() - $date->getTimestamp());
      if ($seconds < 60) return "À l'instant";
      if ($seconds < 3600) return 'Il y a ' . (int)floor($seconds / 60) . ' min';
      if ($seconds < 86400) return 'Il y a ' . (int)floor($seconds / 3600) . ' h';
      if ($seconds < 604800) return 'Il y a ' . (int)floor($seconds / 86400) . ' j';
      return $date->format('d/m/Y à H:i');
    } catch (Exception $e) {
      return $dateStr;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Notifications <?= $unreadCount > 0 ? '(' . $unreadCount . ')' : '' ?> · EPI Tchach</title>
  <link rel="icon" type="image/svg+xml" href="<?= $asset ?>/images/logo_epi.svg" />
  <link rel="apple-touch-icon" href="<?= $asset ?>/images/logo.jpeg" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="<?= $asset ?>/css/desktop/notifications.css" />
  <link rel="stylesheet" href="<?= $asset ?>/css/feedback.css" />
</head>
<body data-base-url="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>">
  <?php include ROOT_PATH . '/resources/views/partials/feedback-toast.php'; ?>

  <!-- Barre tricolore officielle Yamoussoukro -->
  <div class="tricolor-bar" aria-hidden="true"></div>

  <!-- Barre de navigation supérieure -->
  <header class="notif-topbar">
    <div class="topbar-container">
      <a href="<?= $base ?>/salon" class="topbar-brand" title="Retourner au Salon">
        <img src="<?= htmlspecialchars($logoFile) ?>" alt="EPI Logo" class="topbar-logo" onerror="this.style.display='none'" />
        <div class="topbar-text">
          <span class="topbar-title">EPI <span class="brand-accent">Tchach</span></span>
          <span class="topbar-subtitle">Centre de Notifications</span>
        </div>
      </a>

      <nav class="topbar-nav" aria-label="Liens rapides">
        <a href="<?= $base ?>/salon" class="btn-topbar-link">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          <span>Salon Général</span>
        </a>
        <a href="<?= $base ?>/profile" class="btn-topbar-link">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          <span>Mon Profil</span>
        </a>
        <?php if ($canModerateReports): ?>
          <a href="<?= $base ?>/moderation" class="btn-topbar-link is-moderation-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            <span>Modération</span>
          </a>
        <?php endif; ?>
        <?php if (in_array($currentUserRole, ['administrateur', 'admin'], true)): ?>
          <a href="<?= $base ?>/admin" class="btn-topbar-link is-admin-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            <span>Admin</span>
          </a>
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <!-- Contenu Principal sous forme de Fenêtre Popup / Dialogue Élégant -->
  <main class="notif-wrapper">

    <div class="notif-popup-window">
      <!-- En-tête de la fenêtre popup -->
      <div class="popup-window-header">
        <div class="popup-header-title-box">
          <div class="popup-header-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
          </div>
          <div>
            <h1 class="popup-title">
              <span>Notifications</span>
              <span class="notif-unread-counter" id="badgeTotalUnread" style="<?= $unreadCount > 0 ? '' : 'display:none;' ?>">
                <?= (int)$unreadCount ?>
              </span>
            </h1>
            <p class="popup-subtitle">Vos alertes, mentions et interactions de l'EPI en temps réel</p>
          </div>
        </div>

        <div class="popup-header-actions">
          <?php if (!empty($notifications) && $unreadCount > 0): ?>
            <button type="button" class="btn-mark-all-read" id="btnMarkAllRead" title="Marquer tout comme lu">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
              <span>Tout marquer lu</span>
            </button>
          <?php endif; ?>
          <a href="<?= $base ?>/salon" class="btn-popup-close-window" title="Retourner au Salon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
          </a>
        </div>
      </div>

      <!-- Barre d'onglets de filtrage -->
      <div class="notif-filter-bar" role="tablist" aria-label="Filtrer les notifications">
        <div class="filter-tabs-scroll">
          <button type="button" class="filter-tab is-active" data-filter="all" role="tab" aria-selected="true">
            <span>Toutes</span>
            <span class="tab-badge-count" id="countAll"><?= count($notifications) ?></span>
          </button>
          <button type="button" class="filter-tab" data-filter="unread" role="tab" aria-selected="false">
            <span>Non lues</span>
            <span class="tab-badge-count is-unread-badge" id="countUnreadTab" style="<?= $unreadCount > 0 ? '' : 'display:none;' ?>">
              <?= (int)$unreadCount ?>
            </span>
          </button>
          <button type="button" class="filter-tab" data-filter="reponse" role="tab" aria-selected="false">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            <span>Commentaires</span>
          </button>
          <button type="button" class="filter-tab" data-filter="reaction" role="tab" aria-selected="false">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            <span>J’aime</span>
          </button>
          <button type="button" class="filter-tab" data-filter="mention" role="tab" aria-selected="false">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 1 0-3.92 7.94"/></svg>
            <span>Mentions</span>
          </button>
          <button type="button" class="filter-tab" data-filter="moderation" role="tab" aria-selected="false">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            <span>Modération</span>
          </button>
        </div>
      </div>

      <!-- Corps défilable de la popup -->
      <div class="popup-window-scroll-body">
        <div class="notif-cards-stack" id="notifListContainer">
      <?php if (empty($notifications)): ?>
        <div class="notif-empty-state-box">
          <div class="empty-icon-circle">
            <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="1.75">
              <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
          </div>
          <h2>Vous êtes parfaitement à jour !</h2>
          <p>Aucune notification pour l’instant. Vous recevrez une alerte dès qu’un camarade réagit ou répond à l’un de vos sujets.</p>
          <a href="<?= $base ?>/salon" class="btn-empty-return">Explorer le Salon Général</a>
        </div>
      <?php else: ?>
        <?php foreach ($notifications as $n):
          $isUnread = empty($n['lu']) || $n['lu'] == 0;
          $type = htmlspecialchars($n['type_notification'] ?? 'systeme');
          $notifId = (int)$n['id'];

          // Catégorisation pour filtrage
          $filterGroup = 'systeme';
          if ($type === 'reaction') {
            $filterGroup = 'reaction';
          } elseif ($type === 'reponse' || $type === 'comment') {
            $filterGroup = 'reponse';
          } elseif ($type === 'mention') {
            $filterGroup = 'mention';
          } elseif (in_array($type, ['moderation', 'avertissement', 'blocage'], true)) {
            $filterGroup = 'moderation';
          }

          // Libellé en français
          $labelMap = [
            'reaction'        => 'J’aime',
            'reponse'         => 'Commentaire',
            'comment'         => 'Commentaire',
            'publication'     => 'Publication',
            'mention'         => 'Mention',
            'solution'        => 'Solution validée',
            'avertissement'   => 'Avertissement',
            'moderation'      => 'Modération',
            'friend_request'  => 'Demande d’ami',
            'friend_accepted' => 'Ami accepté',
            'private_message' => 'Message privé',
            'systeme'         => 'Système',
          ];
          $typeLabel = $labelMap[$type] ?? ucfirst($type);
        ?>
          <article class="notif-card <?= $isUnread ? 'is-unread' : 'is-read' ?> notif-type-<?= $type ?>"
                   data-notif-id="<?= $notifId ?>"
                   data-type="<?= $type ?>"
                   data-group="<?= $filterGroup ?>"
                   data-unread="<?= $isUnread ? '1' : '0' ?>">

            <!-- Icône vectorielle SVG selon le type -->
            <div class="notif-icon-circle icon-<?= $type ?>" aria-hidden="true">
              <?php if ($type === 'reaction'): ?>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
              <?php elseif ($type === 'reponse' || $type === 'comment'): ?>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
              <?php elseif ($type === 'mention'): ?>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 1 0-3.92 7.94"/></svg>
              <?php elseif ($type === 'solution'): ?>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
              <?php elseif (in_array($type, ['avertissement', 'moderation', 'blocage'], true)): ?>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
              <?php elseif ($type === 'friend_request' || $type === 'friend_accepted'): ?>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
              <?php elseif ($type === 'private_message'): ?>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
              <?php else: ?>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
              <?php endif; ?>
            </div>

            <!-- Contenu textuel et détails -->
            <div class="notif-body">
              <div class="notif-meta-row">
                <span class="notif-badge badge-<?= $type ?>"><?= htmlspecialchars($typeLabel) ?></span>
                <time class="notif-date" datetime="<?= htmlspecialchars($n['date_creation'] ?? '') ?>">
                  <?= formatNotificationDate($n['date_creation'] ?? 'now') ?>
                </time>
              </div>

              <div class="notif-message">
                <?= htmlspecialchars($n['contenu'] ?? '') ?>
              </div>

              <div class="notif-actions-row">
                <?php if (!empty($n['lien'])): ?>
                  <a href="<?= htmlspecialchars($n['lien']) ?>" class="btn-notif-action is-link">
                    <span>Accéder au contenu</span>
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                  </a>
                <?php endif; ?>

                <?php if ($isUnread): ?>
                  <button type="button" class="btn-notif-action btn-mark-one-read" data-id="<?= $notifId ?>">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>Marquer comme lu</span>
                  </button>
                <?php endif; ?>

                <button type="button" class="btn-notif-delete btn-delete-notif" data-id="<?= $notifId ?>" title="Supprimer cette alerte">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                </button>
              </div>
            </div>

            <!-- Point indicateur non-lu -->
            <?php if ($isUnread): ?>
              <span class="notif-unread-glow" title="Non lue" aria-hidden="true"></span>
            <?php endif; ?>

          </article>
        <?php endforeach; ?>
      <?php endif; ?>
        </div>
      </div>
    </div>

  </main>

  <script src="<?= $asset ?>/js/desktop/notification-badge.js"></script>
  <script src="<?= $asset ?>/js/desktop/notifications.js"></script>
  <script src="<?= $asset ?>/js/feedback.js" defer></script>
</body>
</html>
