document.addEventListener("DOMContentLoaded", () => {
  const header = document.querySelector(".phone-header");
  const menuToggle = document.querySelector(".phone-menu-toggle");
  const navigation = document.querySelector(".phone-navigation");

  if (header && menuToggle && navigation) {
    const closeNavigation = () => {
      menuToggle.setAttribute("aria-expanded", "false");
      menuToggle.setAttribute("aria-label", "Ouvrir la navigation");
      navigation.classList.remove("is-open");
    };

    menuToggle.addEventListener("click", () => {
      const isOpen = menuToggle.getAttribute("aria-expanded") === "true";
      menuToggle.setAttribute("aria-expanded", String(!isOpen));
      menuToggle.setAttribute("aria-label", isOpen ? "Ouvrir la navigation" : "Fermer la navigation");
      navigation.classList.toggle("is-open", !isOpen);
    });

    navigation.addEventListener("click", (event) => {
      if (event.target.closest("a")) closeNavigation();
    });

    document.addEventListener("click", (event) => {
      if (!header.contains(event.target)) closeNavigation();
    });

    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape" && menuToggle.getAttribute("aria-expanded") === "true") {
        closeNavigation();
        menuToggle.focus();
      }
    });
  }

  const slides = Array.from(document.querySelectorAll("[data-phone-slide]"));
  const dots = Array.from(document.querySelectorAll("[data-phone-dot]"));
  let activeIndex = 0;

  const showSlide = (index) => {
    if (!slides.length) return;
    activeIndex = (index + slides.length) % slides.length;
    slides.forEach((slide, slideIndex) => {
      const isActive = slideIndex === activeIndex;
      slide.classList.toggle("is-active", isActive);
      slide.setAttribute("aria-hidden", String(!isActive));
    });
    dots.forEach((dot, dotIndex) => {
      const isActive = dotIndex === activeIndex;
      dot.classList.toggle("is-active", isActive);
      if (isActive) {
        dot.setAttribute("aria-current", "true");
      } else {
        dot.removeAttribute("aria-current");
      }
    });
  };

  document.querySelector("[data-phone-prev]")?.addEventListener("click", () => showSlide(activeIndex - 1));
  document.querySelector("[data-phone-next]")?.addEventListener("click", () => showSlide(activeIndex + 1));
  dots.forEach((dot) => {
    dot.addEventListener("click", () => showSlide(Number(dot.dataset.phoneDot)));
  });
  showSlide(activeIndex);
});
