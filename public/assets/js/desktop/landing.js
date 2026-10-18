/**
 * ============================================================
 * EPI TCHACH – Script JavaScript Desktop Landing (landing.js)
 * Interactions, animations au scroll & parallaxe bulles
 * ============================================================
 */

document.addEventListener("DOMContentLoaded", () => {
  // 1. Apparition progressive des éléments au défilement (Intersection Observer)
  const revealElements = document.querySelectorAll(".reveal");

  if ("IntersectionObserver" in window) {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add("visible");
            observer.unobserve(entry.target);
          }
        });
      },
      {
        threshold: 0.15,
        rootMargin: "0px 0px -40px 0px",
      }
    );

    revealElements.forEach((el) => observer.observe(el));
  } else {
    // Fallback pour anciens navigateurs
    revealElements.forEach((el) => el.classList.add("visible"));
  }

  // 2. En-tête avec ombre dynamique au scroll
  const header = document.querySelector(".header-wrapper");
  if (header) {
    window.addEventListener(
      "scroll",
      () => {
        if (window.scrollY > 20) {
          header.classList.add("scrolled");
        } else {
          header.classList.remove("scrolled");
        }
      },
      { passive: true }
    );
  }

  // 3. Défilement doux vers les ancres internes
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      const targetId = this.getAttribute("href");
      if (targetId && targetId !== "#") {
        const targetElement = document.querySelector(targetId);
        if (targetElement) {
          e.preventDefault();
          targetElement.scrollIntoView({
            behavior: "smooth",
            block: "start",
          });
        }
      }
    });
  });

  // 4. Parallaxe interactif sur les bulles du héros (Desktop)
  const heroBubbles = document.querySelector(".hero-bubbles");
  if (heroBubbles && window.matchMedia("(min-width: 992px)").matches) {
    const bubbles = heroBubbles.querySelectorAll(".bubble");
    window.addEventListener(
      "mousemove",
      (e) => {
        const { clientX, clientY } = e;
        const centerX = window.innerWidth / 2;
        const centerY = window.innerHeight / 2;
        const deltaX = (clientX - centerX) / 60;
        const deltaY = (clientY - centerY) / 60;

        bubbles.forEach((bubble, index) => {
          const depth = (index + 1) * 0.4;
          bubble.style.transform = `translate(${deltaX * depth}px, ${deltaY * depth}px)`;
        });
      },
      { passive: true }
    );

    heroBubbles.addEventListener("mouseleave", () => {
      bubbles.forEach((bubble) => {
        bubble.style.transform = "";
      });
    });
  }
});
