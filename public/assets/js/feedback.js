/**
 * =============================================================
 * public/assets/js/feedback.js – Système de notifications Toast & Snackbar
 * =============================================================
 */
(() => {
  const initToast = (toast) => {
    let timeoutId;
    const close = () => {
      window.clearTimeout(timeoutId);
      toast.classList.add("is-closing");
      toast.addEventListener("animationend", () => toast.remove(), { once: true });
      window.setTimeout(() => toast.remove(), 250);
    };

    toast.querySelector(".feedback-toast-close")?.addEventListener("click", close);
    const dismissAfter = Number(toast.dataset.dismissAfter);
    if (Number.isFinite(dismissAfter) && dismissAfter > 0) {
      timeoutId = window.setTimeout(close, dismissAfter);
    }
  };

  document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll("[data-feedback-toast]").forEach(initToast);
  });

  /**
   * Affichage programmatique d'un toast feedback réutilisable (zéro code spaghetti)
   * @param {string} message 
   * @param {'success'|'error'} type 
   * @param {number} duration 
   */
  window.showFeedbackToast = (message, type = "success", duration = 5000) => {
    const isError = type === "error";
    const toast = document.createElement("div");
    toast.className = `feedback-toast feedback-toast-${isError ? "error" : "success"}`;
    toast.setAttribute("data-feedback-toast", "");
    toast.setAttribute("role", isError ? "alert" : "status");
    toast.setAttribute("aria-live", isError ? "assertive" : "polite");
    toast.dataset.dismissAfter = String(duration);

    const iconSvg = isError
      ? '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>'
      : '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>';

    toast.innerHTML = `
      <div class="feedback-toast-icon" aria-hidden="true">${iconSvg}</div>
      <div class="feedback-toast-body">
        <span class="feedback-toast-message">${message}</span>
      </div>
      <button class="feedback-toast-close" type="button" aria-label="Fermer la notification">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    `;

    document.body.appendChild(toast);
    initToast(toast);
  };
})();
