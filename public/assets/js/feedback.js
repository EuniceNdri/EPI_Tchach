document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll("[data-feedback-toast]").forEach((toast) => {
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
  });
});
