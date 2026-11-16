document.addEventListener("DOMContentLoaded", () => {
  const baseUrl = document.body.dataset.baseUrl || "";
  const toastCenter = document.getElementById("toastCenter");
  const list = document.getElementById("notifListContainer");
  const allCards = () => Array.from(document.querySelectorAll(".notif-card"));
  const filterTabs = Array.from(document.querySelectorAll(".notif-tab"));
  let activeFilter = "all";

  const showToast = (message, isError = false) => {
    if (!toastCenter) return;

    const toast = document.createElement("div");
    toast.className = "notif-toast";
    toast.setAttribute("role", "status");
    toast.textContent = message;
    if (isError) toast.classList.add("notif-toast-error");
    toastCenter.appendChild(toast);
    window.setTimeout(() => {
      toast.classList.add("is-leaving");
      window.setTimeout(() => toast.remove(), 300);
    }, 3000);
  };

  const updateBadges = (newCount) => {
    const count = Math.max(0, Number(newCount) || 0);
    const badge = document.getElementById("badgeTotalUnread");
    const countTab = document.getElementById("countUnreadTab");
    const navCount = document.querySelector(".notif-nav-count");
    const markAllButton = document.getElementById("btnMarkAllRead");

    if (badge) {
      badge.textContent = String(count);
      badge.style.display = count > 0 ? "inline-flex" : "none";
    }
    if (countTab) countTab.textContent = String(count);
    if (navCount) {
      navCount.textContent = String(count);
      navCount.hidden = count === 0;
    }
    if (markAllButton) markAllButton.hidden = count === 0;
  };

  const applyFilter = () => {
    allCards().forEach((card) => {
      const isUnread = card.dataset.unread === "1";
      const matches = activeFilter === "all"
        || (activeFilter === "unread" && isUnread)
        || (activeFilter === "reaction" && card.dataset.type === "reaction")
        || (activeFilter === "moderation" && card.dataset.group === "moderation");
      card.hidden = !matches;
    });
  };

  const updateTotalCount = () => {
    const countAll = document.getElementById("countAll");
    if (countAll) countAll.textContent = String(allCards().length);
  };

  const postNotificationAction = async (action, notificationId) => {
    const body = new FormData();
    if (notificationId) body.append("notification_id", notificationId);

    const response = await fetch(`${baseUrl}/notifications/${action}`, {
      method: "POST",
      body,
      headers: { "X-Requested-With": "XMLHttpRequest", Accept: "application/json" },
    });
    const result = await response.json();
    if (!response.ok || !result.success) {
      throw new Error(result.message || "L'action sur la notification a échoué.");
    }
    return result;
  };

  document.addEventListener("click", async (event) => {
    const readButton = event.target.closest(".btn-mark-one-read");
    const deleteButton = event.target.closest(".btn-delete-notif");
    const markAllButton = event.target.closest("#btnMarkAllRead");

    if (!readButton && !deleteButton && !markAllButton) return;
    event.preventDefault();

    const card = (readButton || deleteButton)?.closest(".notif-card");
    const notificationId = card?.dataset.notifId
      || (readButton || deleteButton)?.dataset.id;
    const button = readButton || deleteButton || markAllButton;
    button.disabled = true;

    try {
      if (markAllButton) {
        const result = await postNotificationAction("mark-all-read");
        allCards().forEach((notificationCard) => {
          notificationCard.classList.remove("unread");
          notificationCard.classList.add("read");
          notificationCard.dataset.unread = "0";
          notificationCard.querySelector(".notif-unread-dot")?.remove();
          notificationCard.querySelector(".btn-mark-one-read")?.remove();
        });
        updateBadges(result.unread_count);
        applyFilter();
        showToast("Toutes les notifications sont marquées comme lues.");
      } else if (readButton && card) {
        const result = await postNotificationAction("mark-read", notificationId);
        card.classList.remove("unread");
        card.classList.add("read");
        card.dataset.unread = "0";
        card.querySelector(".notif-unread-dot")?.remove();
        readButton.remove();
        updateBadges(result.unread_count);
        applyFilter();
        showToast("Notification marquée comme lue.");
      } else if (deleteButton && card) {
        const result = await postNotificationAction("delete", notificationId);
        card.remove();
        updateBadges(result.unread_count);
        updateTotalCount();
        applyFilter();
        if (allCards().length === 0 && list) {
          const emptyState = document.createElement("div");
          emptyState.className = "notif-empty-state";
          const title = document.createElement("h3");
          title.textContent = "Vous êtes parfaitement à jour !";
          const text = document.createElement("p");
          text.textContent = "Aucune notification pour le moment.";
          emptyState.append(title, text);
          list.replaceChildren(emptyState);
        }
        showToast("Notification supprimée.");
      }
    } catch (error) {
      console.error("Erreur lors de la gestion des notifications :", error);
      showToast(error.message || "Impossible de traiter cette action.", true);
    } finally {
      if (button.isConnected) button.disabled = false;
    }
  });

  filterTabs.forEach((tab) => {
    tab.addEventListener("click", () => {
      activeFilter = tab.dataset.filter || "all";
      filterTabs.forEach((filterTab) => {
        const isActive = filterTab === tab;
        filterTab.classList.toggle("active", isActive);
        filterTab.setAttribute("aria-pressed", String(isActive));
      });
      applyFilter();
    });
  });
});
