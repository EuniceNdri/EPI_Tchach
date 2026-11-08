<?php
/**
 * =============================================================
 * views/desktop/notifications.php – Centre de Notifications
 * =============================================================
 * Style unifié EPI Tchach · Design inspiré de Reddit
 * =============================================================
 */

$base = isset($baseUrl) ? $baseUrl : '';
$asset = isset($assetBase) ? $assetBase : (isset($baseUrl) ? $baseUrl . '/public/assets' : '/public/assets');
$logoFile = $asset . '/images/logo.jpeg';

$notifications = $notifications ?? [];
$unreadCount = max(0, (int)($unreadCount ?? 0));
$userRole = strtolower($_SESSION['user_role'] ?? 'etudiant');
$sessionAvatarUrl = !empty($_SESSION['user_avatar'])
    ? url('public/' . ltrim((string)$_SESSION['user_avatar'], '/'))
    : '';

if (!function_exists('formatDateNotif')) {
    function formatDateNotif(string $dateStr): string {
        try {
            $date = new DateTime($dateStr);
            $now = new DateTime();
            $diff = $now->diff($date);

            if ($diff->y > 0) return "Il y a " . $diff->y . " an" . ($diff->y > 1 ? 's' : '');
            if ($diff->m > 0) return "Il y a " . $diff->m . " mois";
            if ($diff->d > 0) {
                if ($diff->d === 1) return "Hier à " . $date->format('H:i');
                return "Il y a " . $diff->d . " jours";
            }
            if ($diff->h > 0) return "Il y a " . $diff->h . " h";
            if ($diff->i > 0) return "Il y a " . $diff->i . " min";
            return "À l'instant";
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
  <title>Notifications (<?= (int)$unreadCount ?>) · EPI Tchach</title>
  <link rel="icon" type="image/svg+xml" href="<?= $asset ?>/images/logo_epi.svg" />
  <link rel="apple-touch-icon" href="<?= $asset ?>/images/logo.jpeg" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="<?= $asset ?>/css/desktop/community-shell.css" />
  <link rel="stylesheet" href="<?= $asset ?>/css/desktop/salon.css?v=<?= (int)filemtime(ROOT_PATH . '/public/assets/css/desktop/salon.css') ?>" />
  <link rel="stylesheet" href="<?= $asset ?>/css/desktop/notifications.css" />
  <link rel="stylesheet" href="<?= $asset ?>/css/feedback.css" />
  <?php include ROOT_PATH . '/views/partials/theme-head.php'; ?>
</head>
<body class="community-shell community-salon community-notifications" data-base-url="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>">
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

        <a class="epi-community-pill" href="<?= $base ?>/notifications">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <span>Notifications</span>
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
        <button type="button" class="epi-create-post-btn" data-open-create-post-modal aria-label="Créer une publication">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          <span>Créer</span>
        </button>

        <!-- Notifications (actif) -->
        <a href="<?= $base ?>/notifications" class="epi-icon-action" style="color:var(--community-green); background:var(--community-raised);" title="Notifications" aria-label="Notifications">
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
            <button type="button" class="epi-profile-pill" id="userMenuToggle" aria-label="Menu du profil utilisateur" aria-haspopup="true" aria-expanded="false">
              <div class="epi-avatar-bubble">
                <span class="epi-avatar-fallback"><?= htmlspecialchars(strtoupper(mb_substr((string)($_SESSION['user_prenom'] ?? 'M'), 0, 1))) ?></span>
                <img class="epi-avatar-image" src="<?= htmlspecialchars($sessionAvatarUrl, ENT_QUOTES, 'UTF-8') ?>" alt="" <?= $sessionAvatarUrl === '' ? 'hidden' : '' ?> />
                <span class="epi-status-dot" aria-label="En ligne"></span>
              </div>
              <div class="epi-profile-info">
                <span class="epi-profile-name"><?= htmlspecialchars($_SESSION['user_prenom'] ?? 'Membre') ?></span>
                <span class="epi-profile-role"><?= htmlspecialchars(ucfirst((string)($_SESSION['user_role'] ?? 'etudiant'))) ?></span>
              </div>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
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
              <?php if ($userRole === 'administrateur'): ?>
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
   * LAYOUT PRINCIPAL (GRID POINT TEMPLATE SCOPÉ SALON & NOTIFICATIONS)
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
        <a href="<?= $base ?>/profile" class="epi-nav-item">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          <span>Mon profil</span>
        </a>
        <a href="<?= $base ?>/notifications" class="epi-nav-item is-active">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <span>Notifications</span>
        </a>

        <?php if ($userRole === 'administrateur' || $userRole === 'moderateur'): ?>
          <div class="epi-zone-group">
            <div class="epi-nav-heading">Gestion</div>
            <a href="<?= $base ?>/admin/moderation" class="epi-nav-item" style="color:var(--community-orange);">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
              <span>Modération</span>
            </a>
          </div>
        <?php endif; ?>
      </div>
    </aside>

    <!-- ========== CONTENU PRINCIPAL (grid-area: main) ========== -->
    <main class="epi-main-feed">

      <!-- En-tête de la page Notifications -->
      <div class="notif-heading-row">
        <div class="notif-heading-title">
          <h1>Notifications</h1>
          <span class="notif-heading-badge" id="badgeTotalUnread" style="<?= $unreadCount > 0 ? '' : 'display:none;' ?>">
            <?= (int)$unreadCount ?> non lue<?= $unreadCount > 1 ? 's' : '' ?>
          </span>
        </div>

        <?php if (!empty($notifications) && $unreadCount > 0): ?>
          <button type="button" class="btn-mark-all-read" id="btnMarkAllRead">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <span>Tout marquer comme lu</span>
          </button>
        <?php endif; ?>
      </div>

      <!-- Onglets de filtrage -->
      <div class="notif-tabs-bar">
        <button type="button" class="notif-tab active" data-filter="all">
          <span>Toutes</span>
          <span class="notif-tab-count" id="countAll"><?= count($notifications) ?></span>
        </button>
        <button type="button" class="notif-tab" data-filter="unread">
          <span>Non lues</span>
          <span class="notif-tab-count" id="countUnreadTab"><?= (int)$unreadCount ?></span>
        </button>
        <button type="button" class="notif-tab" data-filter="reaction">
          <span>Réactions</span>
        </button>
        <button type="button" class="notif-tab" data-filter="moderation">
          <span>Modération & Alertes</span>
        </button>
      </div>

      <!-- Liste des Cartes de Notification -->
      <div class="notif-list" id="notifListContainer">
        <?php if (empty($notifications)): ?>
          <div class="notif-empty-state">
            <div class="notif-empty-mascot">
              <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
              </svg>
            </div>
            <h3>Aucune activité pour l'instant</h3>
            <p>Ce n'est pas grave, vous recevrez une alerte dès qu'un membre réagit ou répond à l'une de vos publications dans les salons.</p>
            <a href="<?= $base ?>/salon" class="notif-empty-btn">Explorer les salons</a>
          </div>
        <?php else: ?>
          <?php foreach ($notifications as $n): 
            $isUnread = empty($n['lu']) || $n['lu'] == 0;
            $type = htmlspecialchars($n['type_notification'] ?? 'systeme');
            $notifId = (int)$n['id'];

            $iconMap = [
              'reaction'      => '❤️',
              'avertissement' => '⚠️',
              'moderation'    => '🛑',
              'reponse'       => '💬',
              'solution'      => '✅',
              'systeme'       => 'ℹ️',
              'mention'       => '@'
            ];
            $iconChar = $iconMap[$type] ?? '🔔';

            $categoryFilter = 'systeme';
            if ($type === 'reaction') {
                $categoryFilter = 'reaction';
            } elseif (in_array($type, ['moderation', 'avertissement', 'blocage'], true)) {
                $categoryFilter = 'moderation';
            }
          ?>
            <article class="notif-card <?= $isUnread ? 'unread' : 'read' ?>" 
                 data-notif-id="<?= $notifId ?>" 
                 data-type="<?= $type ?>" 
                 data-group="<?= $categoryFilter ?>" 
                 data-unread="<?= $isUnread ? '1' : '0' ?>">
              
              <div class="notif-type-icon <?= $type ?>">
                <?= $iconChar ?>
              </div>

              <div class="notif-content-area">
                <div class="notif-card-header">
                  <span class="notif-type-badge <?= $type ?>"><?= ucfirst($type) ?></span>
                  <span class="notif-time"><?= formatDateNotif($n['date_creation'] ?? 'now') ?></span>
                </div>

                <div class="notif-message-text">
                  <?= htmlspecialchars($n['contenu'] ?? '') ?>
                </div>

                <div class="notif-card-footer">
                  <?php if (!empty($n['lien'])): ?>
                    <a href="<?= htmlspecialchars($n['lien']) ?>" class="notif-action-link">
                      <span>Accéder au contenu</span>
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                  <?php endif; ?>

                  <?php if ($isUnread): ?>
                    <button type="button" class="btn-mark-one-read" data-id="<?= $notifId ?>">
                      Marquer comme lu
                    </button>
                  <?php endif; ?>

                  <button type="button" class="btn-delete-notif" data-id="<?= $notifId ?>" title="Supprimer cette notification">
                    Supprimer
                  </button>
                </div>
              </div>

              <?php if ($isUnread): ?>
                <div class="notif-unread-dot" title="Non lu"></div>
              <?php endif; ?>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

    </main>

    <!-- ========== SIDEBAR DROITE (grid-area: rightbar) ========== -->
    <aside class="epi-sidebar-right">
      <div class="epi-widget-card">
        <div class="epi-widget-banner"></div>
        <div class="epi-widget-title">
          <span>Centre d'activité</span>
        </div>
        <p class="epi-about-text">
          Restez informé de toutes vos interactions, questions résolues et actualités de la communauté de l'École Polytechnique Internationale.
        </p>
        <div class="epi-widget-stats">
          <div class="epi-stat-block">
            <span class="epi-stat-value"><?= count($notifications) ?></span>
            <span class="epi-stat-label">Total reçues</span>
          </div>
          <div class="epi-stat-block">
            <span class="epi-stat-value"><?= (int)$unreadCount ?></span>
            <span class="epi-stat-label">Non lues</span>
          </div>
        </div>
        <a href="<?= $base ?>/salon" class="epi-btn-submit" style="display:block; text-align:center; text-decoration:none;">
          Explorer les salons
        </a>
      </div>

      <div class="epi-widget-card">
        <div class="epi-widget-title">
          <span>Règles d'EPI Tchach</span>
        </div>
        <ol style="padding-left:1.2rem; font-size:0.82rem; color:var(--community-muted); line-height:1.6; margin:0;">
          <li>Notifications générées en temps réel.</li>
          <li>Les alertes de modération doivent être consultées en priorité.</li>
          <li>Nettoyez régulièrement vos notifications archivées.</li>
        </ol>
      </div>
    </aside>

  </div>

  <?php include ROOT_PATH . '/views/desktop/partials/post-composer-modal.php'; ?>

  <!-- Centre de toast dynamique -->
  <div class="notif-toast-center" id="toastCenter"></div>

  <!-- Script interactif AJAX -->
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const baseUrl = "<?= $base ?>";

      const showToast = (message) => {
        const center = document.getElementById("toastCenter");
        if (!center) return;
        const toast = document.createElement("div");
        toast.className = "notif-toast";
        toast.innerHTML = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#34d399" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg><span>${message}</span>`;
        center.appendChild(toast);
        setTimeout(() => {
          toast.style.opacity = "0";
          toast.style.transform = "translateY(10px)";
          setTimeout(() => toast.remove(), 300);
        }, 3000);
      };

      const updateBadges = (newCount) => {
        const badge = document.getElementById("badgeTotalUnread");
        const countTab = document.getElementById("countUnreadTab");
        if (badge) {
          badge.textContent = `${newCount} non lue${newCount > 1 ? 's' : ''}`;
          badge.style.display = newCount > 0 ? "inline-flex" : "none";
        }
        if (countTab) {
          countTab.textContent = newCount;
        }
        const btnAll = document.getElementById("btnMarkAllRead");
        if (btnAll && newCount <= 0) {
          btnAll.style.display = "none";
        }
      };

      // 1. Marquer une notification comme lue
      document.querySelectorAll(".btn-mark-one-read").forEach(btn => {
        btn.addEventListener("click", async function(e) {
          e.preventDefault();
          const card = this.closest(".notif-card");
          const notifId = this.getAttribute("data-id") || card?.getAttribute("data-notif-id");
          if (!notifId) return;

          try {
            const formData = new FormData();
            formData.append("notification_id", notifId);

            const res = await fetch(`${baseUrl}/notifications/mark-read`, {
              method: "POST",
              body: formData,
              headers: { "X-Requested-With": "XMLHttpRequest" }
            });
            const data = await res.json();

            if (data.success) {
              card.classList.remove("unread");
              card.classList.add("read");
              card.setAttribute("data-unread", "0");
              const dot = card.querySelector(".notif-unread-dot");
              if (dot) dot.remove();
              this.remove();

              updateBadges(data.unread_count ?? 0);
              showToast("Notification marquée comme lue.");
            }
          } catch (err) {
            console.error("Erreur mark-read:", err);
          }
        });
      });

      // 2. Tout marquer comme lu
      const btnAll = document.getElementById("btnMarkAllRead");
      if (btnAll) {
        btnAll.addEventListener("click", async function() {
          try {
            const res = await fetch(`${baseUrl}/notifications/mark-all-read`, {
              method: "POST",
              headers: { "X-Requested-With": "XMLHttpRequest" }
            });
            const data = await res.json();

            if (data.success) {
              document.querySelectorAll(".notif-card.unread").forEach(card => {
                card.classList.remove("unread");
                card.classList.add("read");
                card.setAttribute("data-unread", "0");
                const dot = card.querySelector(".notif-unread-dot");
                if (dot) dot.remove();
                const readBtn = card.querySelector(".btn-mark-one-read");
                if (readBtn) readBtn.remove();
              });

              updateBadges(0);
              showToast("Toutes les notifications sont marquées comme lues.");
            }
          } catch (err) {
            console.error("Erreur mark-all-read:", err);
          }
        });
      }

      // 3. Supprimer une notification
      document.querySelectorAll(".btn-delete-notif").forEach(btn => {
        btn.addEventListener("click", async function(e) {
          e.preventDefault();
          const card = this.closest(".notif-card");
          const notifId = this.getAttribute("data-id") || card?.getAttribute("data-notif-id");
          if (!notifId) return;

          try {
            const formData = new FormData();
            formData.append("notification_id", notifId);

            const res = await fetch(`${baseUrl}/notifications/delete`, {
              method: "POST",
              body: formData,
              headers: { "X-Requested-With": "XMLHttpRequest" }
            });
            const data = await res.json();

            if (data.success) {
              card.style.transition = "all 0.3s ease";
              card.style.opacity = "0";
              card.style.transform = "scale(0.96)";
              setTimeout(() => card.remove(), 300);

              updateBadges(data.unread_count ?? 0);
              showToast("Notification supprimée.");
            }
          } catch (err) {
            console.error("Erreur delete notif:", err);
          }
        });
      });

      // 4. Filtrage dynamique par onglet
      const filterTabs = document.querySelectorAll(".notif-tab");
      const cards = document.querySelectorAll(".notif-card");

      filterTabs.forEach(tab => {
        tab.addEventListener("click", function() {
          filterTabs.forEach(t => t.classList.remove("active"));
          this.classList.add("active");

          const filter = this.getAttribute("data-filter");

          cards.forEach(card => {
            const isUnread = card.getAttribute("data-unread") === "1";
            const group = card.getAttribute("data-group");
            const type = card.getAttribute("data-type");

            let visible = true;
            if (filter === "unread") {
              visible = isUnread;
            } else if (filter === "reaction") {
              visible = (type === "reaction");
            } else if (filter === "moderation") {
              visible = (group === "moderation");
            }

            card.style.display = visible ? "flex" : "none";
          });
        });
      });
    });
  </script>
  <script src="<?= $asset ?>/js/desktop/salon.js?v=<?= (int)filemtime(ROOT_PATH . '/public/assets/js/desktop/salon.js') ?>"></script>
</body>
</html>
