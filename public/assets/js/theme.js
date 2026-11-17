(() => {
  const storageKey = "epi-theme";
  let theme = "dark";
  try {
    const savedTheme = window.localStorage.getItem(storageKey);
    const pathSegments = window.location.pathname.split("/").filter(Boolean);
    const communityRoutes = [
      "salon", "salons", "zones", "profile", "profil", "friends", "amis",
      "messages", "messagerie", "notifications", "admin", "dashboard",
      "login", "register", "forgot-password", "reset-password", "publication",
      "search", "moderation", "signalement", "logout", "attachment", "api",
    ];
    const isLandingPage = pathSegments.length === 0
      || pathSegments.includes("landing")
      || pathSegments.includes("home")
      || !pathSegments.some((segment) => communityRoutes.includes(segment));
    theme = savedTheme === "light"
      ? "light"
      : savedTheme === "dark" || isLandingPage
        ? "dark"
        : "light";
  } catch (error) {
    console.warn("Le thème ne peut pas être mémorisé dans ce navigateur.", error);
  }
  document.documentElement.dataset.theme = theme;

  document.addEventListener("DOMContentLoaded", () => {
    const toggle = document.querySelector("[data-theme-toggle]");
    if (!toggle) return;

    const label = toggle.querySelector("[data-theme-label]");
    const updateToggle = () => {
      const isLight = document.documentElement.dataset.theme === "light";
      toggle.setAttribute("aria-label", isLight ? "Activer le mode sombre" : "Activer le mode clair");
      toggle.setAttribute("title", isLight ? "Activer le mode sombre" : "Activer le mode clair");
      if (label) label.textContent = isLight ? "Mode sombre" : "Mode clair";
      toggle.querySelector("[data-theme-sun]")?.toggleAttribute("hidden", !isLight);
      toggle.querySelector("[data-theme-moon]")?.toggleAttribute("hidden", isLight);
    };

    toggle.addEventListener("click", () => {
      const nextTheme = document.documentElement.dataset.theme === "light" ? "dark" : "light";
      document.documentElement.dataset.theme = nextTheme;
      try {
        window.localStorage.setItem(storageKey, nextTheme);
      } catch (error) {
        console.warn("Le choix du thème ne peut pas être mémorisé dans ce navigateur.", error);
      }
      updateToggle();
    });

    updateToggle();
  });
})();
