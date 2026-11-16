/**
 * =============================================================
 * public/assets/js/desktop/notifications.js
 * Centre de Notifications – EPI Tchach
 * Zero-reload AJAX, filtres instantanés, suppression douce & feedback
 * =============================================================
 */

document.addEventListener("DOMContentLoaded", () => {
  const baseUrl = document.body.dataset.baseUrl || "";
  const listContainer = document.getElementById("notifListContainer");
  const filterTabs = Array.from(document.querySelectorAll(".filter-tab"));
  let activeFilter = "all";

  // Helper Toast Feedback
  const showToast = (message, type = "success") => {
    if (typeof window.showFeedbackToast === "function") {
      window.showFeedbackToast(message, type);
    } else {
      console.log(`[Toast ${type}] ${message}`);
    }
  };

  const getAllCards = () => Array.from(document.querySelectorAll(".notif-card"));

  // Mise à jour de tous les badges de compteur
  const updateBadges = (newUnreadCount) => {
    const unread = Math.max(0, Number(newUnreadCount) || 0);
    const badgeTotal = document.getElementById("badgeTotalUnread");
    const countUnreadTab = document.getElementById("countUnreadTab");
    const btnMarkAll = document.getElementById("btnMarkAllRead");
    const navNotificationCount = document.querySelector(".nav-notification-count");

    if (badgeTotal) {
      badgeTotal.textContent = String(unread);
      badgeTotal.style.display = unread > 0 ? "inline-flex" : "none";
    }

    if (countUnreadTab) {
      countUnreadTab.textContent = String(unread);
      countUnreadTab.style.display = unread > 0 ? "inline-flex" : "none";
    }

    if (btnMarkAll) {
      btnMarkAll.style.display = unread > 0 ? "inline-flex" : "none";
    }

    if (navNotificationCount) {
      navNotificationCount.textContent = String(unread);
      navNotificationCount.style.display = unread > 0 ? "inline-flex" : "none";
    }

    // Compteur total
    const countAll = document.getElementById("countAll");
    if (countAll) {
      countAll.textContent = String(getAllCards().length);
    }
  };

  // Filtrage des notifications
  const applyFilter = () => {
    const cards = getAllCards();
    let visibleCount = 0;

    cards.forEach((card) => {
      const isUnread = card.dataset.unread === "1";
      const cardType = card.dataset.type || "";
      const cardGroup = card.dataset.group || "";

      let matches = false;
      if (activeFilter === "all") {
        matches = true;
      } else if (activeFilter === "unread") {
        matches = isUnread;
      } else if (activeFilter === "reaction") {
        matches = (cardType === "reaction");
      } else if (activeFilter === "reponse") {
        matches = (cardType === "reponse" || cardType === "comment");
      } else if (activeFilter === "mention") {
        matches = (cardType === "mention");
      } else if (activeFilter === "moderation") {
        matches = (cardGroup === "moderation");
      }

      card.hidden = !matches;
      if (matches) visibleCount++;
    });

    // État vide spécifique au filtre si aucune carte visible
    let filterEmpty = document.getElementById("filterEmptyNotice");
    if (visibleCount === 0 && cards.length > 0) {
      if (!filterEmpty) {
        filterEmpty = document.createElement("div");
        filterEmpty.id = "filterEmptyNotice";
        filterEmpty.className = "notif-empty-state-box";
        filterEmpty.style.padding = "2.5rem 1.5rem";
        filterEmpty.innerHTML = `
          <h3>Aucune notification dans cette catégorie</h3>
          <p>Consultez l’onglet "Toutes" pour voir l’ensemble de vos alertes.</p>
        `;
        listContainer?.appendChild(filterEmpty);
      }
      filterEmpty.hidden = false;
    } else if (filterEmpty) {
      filterEmpty.hidden = true;
    }
  };

  // Envoi d'une action notification en AJAX
  const postNotificationAction = async (action, notificationId) => {
    const body = new FormData();
    if (notificationId) {
      body.append("notification_id", String(notificationId));
    }

    const res = await fetch(`${baseUrl}/notifications/${action}`, {
      method: "POST",
      headers: {
        "X-Requested-With": "XMLHttpRequest",
        Accept: "application/json",
      },
      body,
    });

    const data = await res.json();
    if (!res.ok || !data.success) {
      throw new Error(data.message || "L'action sur la notification a échoué.");
    }
    return data;
  };

  // Clics sur les actions
  document.addEventListener("click", async (e) => {
    const readBtn = e.target.closest(".btn-mark-one-read");
    const deleteBtn = e.target.closest(".btn-delete-notif");
    const markAllBtn = e.target.closest("#btnMarkAllRead");

    if (!readBtn && !deleteBtn && !markAllBtn) return;
    e.preventDefault();

    const card = (readBtn || deleteBtn)?.closest(".notif-card");
    const notifId = card?.dataset.notifId || (readBtn || deleteBtn)?.dataset.id;
    const button = readBtn || deleteBtn || markAllBtn;
    button.disabled = true;

    try {
      // 1. Tout marquer comme lu
      if (markAllBtn) {
        const result = await postNotificationAction("mark-all-read");

        getAllCards().forEach((c) => {
          c.classList.remove("is-unread");
          c.classList.add("is-read");
          c.dataset.unread = "0";
          c.querySelector(".notif-unread-glow")?.remove();
          c.querySelector(".btn-mark-one-read")?.remove();
        });

        updateBadges(result.unread_count || 0);
        applyFilter();
        showToast("Toutes vos notifications sont marquées comme lues.", "success");
      }
      // 2. Marquer une notification comme lue
      else if (readBtn && card) {
        const result = await postNotificationAction("mark-read", notifId);

        card.classList.remove("is-unread");
        card.classList.add("is-read");
        card.dataset.unread = "0";
        card.querySelector(".notif-unread-glow")?.remove();
        readBtn.remove();

        updateBadges(result.unread_count || 0);
        applyFilter();
        showToast("Notification marquée comme lue.", "success");
      }
      // 3. Supprimer une notification
      else if (deleteBtn && card) {
        const result = await postNotificationAction("delete", notifId);

        card.style.transition = "all 0.25s ease-out";
        card.style.opacity = "0";
        card.style.transform = "translateX(20px)";

        setTimeout(() => {
          card.remove();
          updateBadges(result.unread_count || 0);
          applyFilter();

          // Si plus aucune carte
          if (getAllCards().length === 0 && listContainer) {
            listContainer.innerHTML = `
              <div class="notif-empty-state-box">
                <div class="empty-icon-circle">
                  <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="1.75">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                  </svg>
                </div>
                <h2>Vous êtes parfaitement à jour !</h2>
                <p>Aucune notification pour l’instant. Vous recevrez une alerte dès qu’un camarade réagit ou répond à l’un de vos sujets.</p>
                <a href="${baseUrl}/salon" class="btn-empty-return">Explorer le Salon Général</a>
              </div>
            `;
          }
        }, 250);

        showToast("Notification supprimée.", "info");
      }
    } catch (err) {
      showToast(err.message || "Impossible d'effectuer l'action.", "error");
    } finally {
      if (button.isConnected) button.disabled = false;
    }
  });

  // Changement d'onglet de filtre
  filterTabs.forEach((tab) => {
    tab.addEventListener("click", () => {
      activeFilter = tab.dataset.filter || "all";
      filterTabs.forEach((t) => {
        const isActive = (t === tab);
        t.classList.toggle("is-active", isActive);
        t.setAttribute("aria-selected", isActive ? "true" : "false");
      });
      applyFilter();
    });
  });
});
