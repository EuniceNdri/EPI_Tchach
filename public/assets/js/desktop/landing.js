/**
 * ============================================================
 * EPI TCHACH – Script JavaScript Desktop Landing (landing.js)
 * Interactions riches : FAQ accordéon, compteurs animés & animations
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

  // 5. Accordéon FAQ interactif
  const faqItems = document.querySelectorAll(".faq-item");
  faqItems.forEach((item) => {
    const question = item.querySelector(".faq-question");
    if (question) {
      question.addEventListener("click", () => {
        const isActive = item.classList.contains("active");

        // Fermer les autres questions
        faqItems.forEach((other) => other.classList.remove("active"));

        // Basculer l'état actuel
        if (!isActive) {
          item.classList.add("active");
        }
      });
    }
  });

  // 6. Animation des compteurs de statistiques
  const statNumbers = document.querySelectorAll(".stat-number[data-target]");
  if (statNumbers.length > 0 && "IntersectionObserver" in window) {
    let animated = false;
    const statsObserver = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting && !animated) {
            animated = true;
            statNumbers.forEach((counter) => {
              const target = parseInt(counter.getAttribute("data-target"), 10);
              const prefix = counter.getAttribute("data-prefix") || "";
              const suffix = counter.getAttribute("data-suffix") || "";
              let count = 0;
              const duration = 1800; // ms
              const stepTime = 20;
              const increment = target / (duration / stepTime);

              const timer = setInterval(() => {
                count += increment;
                if (count >= target) {
                  counter.textContent = prefix + target.toLocaleString("fr-FR") + suffix;
                  clearInterval(timer);
                } else {
                  counter.textContent = prefix + Math.floor(count).toLocaleString("fr-FR") + suffix;
                }
              }, stepTime);
            });
            statsObserver.disconnect();
          }
        });
      },
      { threshold: 0.3 }
    );

    const statsSection = document.querySelector(".stats-banner");
    if (statsSection) {
      statsObserver.observe(statsSection);
    }
  }
});
