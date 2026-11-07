<?php
/**
 * =============================================================
 * views/desktop/notifications.php – Centre de Notifications
 * =============================================================
 * Vue élégante et dynamique affichant toutes les alertes de l'utilisateur :
 * réactions (likes, j'adore), avertissements, modération, réponses et système.
 * =============================================================
 */

$base = isset($baseUrl) ? $baseUrl : '';
$asset = isset($assetBase) ? $assetBase : (isset($baseUrl) ? $baseUrl . '/public/assets' : '/public/assets');
$logoFile = $asset . '/images/logo.jpeg';

$notifications = $notifications ?? [];
$unreadCount = $unreadCount ?? 0;
$userRole = strtolower($_SESSION['user_role'] ?? 'etudiant');

// Fonction helper pour afficher une date relative conviviale
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
  <link rel="stylesheet" href="<?= $asset ?>/css/desktop/notifications.css" />
</head>
<body class="notifications-body">
  <!-- Bande tricolore décorative officielle -->
  <div class="notif-tricolor-bar"></div>

  <!-- Barre de navigation supérieure -->
  <header class="notif-header">
    <div class="notif-nav-container">
      <a href="<?= $base ?>/salon" class="notif-brand">
        <img src="<?= htmlspecialchars($logoFile) ?>" alt="Logo EPI" style="width:34px; height:34px; border-radius:8px; object-fit:cover;" onerror="this.style.display='none'" />
        <div>
          <div class="notif-brand-title">EPI <span>Tchach</span></div>
          <div class="notif-brand-sub">Centre de Notifications</div>
        </div>
      </a>

      <div class="notif-header-actions">
        <a href="<?= $base ?>/salon" class="notif-btn-nav">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          <span>Salon Général</span>
        </a>
        <a href="<?= $base ?>/profile" class="notif-btn-nav">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          <span>Mon Profil</span>
        </a>
        <?php if ($userRole === 'administrateur' || $userRole === 'moderateur'): ?>
          <a href="<?= $base ?>/admin/moderation" class="notif-btn-nav" style="color:var(--notif-accent); border-color:rgba(234,88,12,0.3);">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            <span>Modération</span>
          </a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <!-- Zone de Contenu Principal -->
  <main class="notif-main-container">

    <!-- En-tête de page -->
    <div class="notif-page-heading">
      <div class="notif-title-area">
        <h1>
          <span>Mes Notifications</span>
          <span class="notif-unread-badge" id="badgeTotalUnread" style="<?= $unreadCount > 0 ? '' : 'display:none;' ?>">
            <?= (int)$unreadCount ?>
          </span>
        </h1>
        <p>Restez informé de vos interactions, des réponses à vos questions et des actualités de l'école.</p>
      </div>

      <?php if (!empty($notifications) && $unreadCount > 0): ?>
        <button type="button" class="btn-mark-all-read" id="btnMarkAllRead">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          <span>Tout marquer comme lu</span>
        </button>
      <?php endif; ?>
    </div>

    <!-- Barre d'outils et de filtres -->
    <div class="notif-toolbar">
      <div class="notif-filter-tabs">
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

      <div style="font-size:0.8rem; color:var(--notif-muted); font-weight:600;">
        Actualisation en temps réel
      </div>
    </div>

    <!-- Liste des Cartes de Notification -->
    <div class="notif-list" id="notifListContainer">
      <?php if (empty($notifications)): ?>
        <div class="notif-empty-state">
          <div class="notif-empty-icon">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          </div>
          <h3>Vous êtes parfaitement à jour !</h3>
          <p>Aucune notification pour le moment. Vous recevrez une alerte dès qu'un camarade réagit ou répond à l'une de vos publications.</p>
          <a href="<?= $base ?>/salon" class="notif-btn-nav primary">Explorer le Salon Général</a>
        </div>
      <?php else: ?>
        <?php foreach ($notifications as $n): 
          $isUnread = empty($n['lu']) || $n['lu'] == 0;
          $type = htmlspecialchars($n['type_notification'] ?? 'systeme');
          $notifId = (int)$n['id'];

          // Configuration iconographique selon le type
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

          // Classe de filtrage groupé
          $categoryFilter = 'systeme';
          if ($type === 'reaction') {
              $categoryFilter = 'reaction';
          } elseif (in_array($type, ['moderation', 'avertissement', 'blocage'], true)) {
              $categoryFilter = 'moderation';
          }
        ?>
          <div class="notif-card <?= $isUnread ? 'unread' : 'read' ?>" 
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

                <button type="button" class="btn-delete-notif" data-id="<?= $notifId ?>" style="background:none; border:none; padding:0; font-size:0.78rem; font-weight:600; color:#ef4444; cursor:pointer;" title="Supprimer cette notification">
                  Supprimer
                </button>
              </div>
            </div>

            <?php if ($isUnread): ?>
              <div class="notif-unread-dot" title="Non lu"></div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

  </main>

  <!-- Système de Toast dynamique -->
  <div class="notif-toast-center" id="toastCenter"></div>

  <!-- Script d'interaction AJAX -->
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const baseUrl = "<?= $base ?>";

      const showToast = (message) => {
        const center = document.getElementById("toastCenter");
        if (!center) return;
        const toast = document.createElement("div");
        toast.className = "notif-toast";
        toast.innerHTML = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg><span>${message}</span>`;
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
          badge.textContent = newCount;
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
</body>
</html>
