(() => {
  const storageKey = "epi-theme";
  let theme = "dark";
  try {
    theme = window.localStorage.getItem(storageKey) === "light" ? "light" : "dark";
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
