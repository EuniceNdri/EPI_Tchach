document.addEventListener("DOMContentLoaded", () => {
  const baseUrl = document.body.dataset.baseUrl || "";
  const refreshBadges = async () => {
    try {
      const response = await fetch(`${baseUrl}/notifications/unread-count`, {
        cache: "no-store",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          Accept: "application/json",
        },
        credentials: "same-origin",
      });
      const result = await response.json();
      if (!response.ok || !result.success || !Number.isFinite(Number(result.count))) {
        throw new Error(result.message || "Impossible d'actualiser le compteur de notifications.");
      }

      const count = Math.max(0, Number(result.count));
      document.querySelectorAll("[data-notification-count], #badgeTotalUnread").forEach((badge) => {
        badge.textContent = String(count);
        badge.hidden = count === 0;
        badge.style.display = count > 0 ? "inline-flex" : "none";
      });

      const unreadTab = document.getElementById("countUnreadTab");
      if (unreadTab) unreadTab.textContent = String(count);
      const markAllButton = document.getElementById("btnMarkAllRead");
      if (markAllButton) markAllButton.hidden = count === 0;
    } catch (error) {
      console.error("Erreur lors de l'actualisation du compteur de notifications :", error);
    }
  };

  refreshBadges();
  window.setInterval(() => {
    if (!document.hidden) refreshBadges();
  }, 25000);
  document.addEventListener("visibilitychange", () => {
    if (!document.hidden) refreshBadges();
  });
});
