/**
 * ============================================================
 * EPI TCHACH – Script JavaScript Desktop Landing (landing.js)
 * Interactions riches : Animations, Parallaxe, Accordéon FAQ, 
 * Compteurs animés, Transitions de page & Modale Charte/Politique
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

  // 3. Défilement doux vers les ancres internes (excluant les modales)
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      const targetId = this.getAttribute("href");
      if (targetId && targetId !== "#" && targetId !== "#policyModal") {
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

  // 7. Bouton Retour en Haut (Back-to-top)
  const backToTopBtn = document.getElementById("backToTop");
  if (backToTopBtn) {
    window.addEventListener(
      "scroll",
      () => {
        if (window.scrollY > 400) {
          backToTopBtn.classList.add("show");
        } else {
          backToTopBtn.classList.remove("show");
        }
      },
      { passive: true }
    );

    backToTopBtn.addEventListener("click", () => {
      window.scrollTo({
        top: 0,
        behavior: "smooth",
      });
    });
  }

  // 8. Transitions douces entre les pages
  const internalLinks = document.querySelectorAll(
    'a[href]:not([href^="#"]):not([target="_blank"]):not([href^="javascript:"]):not([href^="mailto:"]):not([href^="tel:"])'
  );

  internalLinks.forEach((link) => {
    link.addEventListener("click", (e) => {
      const destination = link.getAttribute("href");
      // Ignorer si clic molette ou avec touche Ctrl/Cmd
      if (e.metaKey || e.ctrlKey || !destination) return;

      e.preventDefault();
      document.body.classList.add("page-transitioning");

      setTimeout(() => {
        window.location.href = destination;
      }, 180);
    });
  });

  // 9. Gestion de la Modale Popup (Charte d'Utilisation & Confidentialité)
  const policyModal = document.getElementById("policyModal");
  if (policyModal) {
    const modalCheckbox = document.getElementById("policyAgreeCheckbox");
    const confirmBtn = document.getElementById("confirmPolicyAgree");
    const closeBtn = document.getElementById("closePolicyModal");
    const cancelBtn = document.getElementById("cancelPolicyModal");

    // Vérifier si l'utilisateur a déjà accepté
    const isAlreadyAccepted = localStorage.getItem("epi_policy_accepted") === "true";
    if (isAlreadyAccepted && modalCheckbox) {
      modalCheckbox.checked = true;
      if (confirmBtn) confirmBtn.disabled = false;
    }

    const openModal = (e) => {
      if (e) e.preventDefault();
      policyModal.classList.add("show");
      policyModal.setAttribute("aria-hidden", "false");
      document.body.style.overflow = "hidden";
    };

    const closeModal = () => {
      policyModal.classList.remove("show");
      policyModal.setAttribute("aria-hidden", "true");
      document.body.style.overflow = "";
    };

    // Déclencheurs d'ouverture
    document.querySelectorAll(".open-policy-modal, a[href='#policyModal']").forEach((trigger) => {
      trigger.addEventListener("click", openModal);
    });

    // Déclencheurs de fermeture
    if (closeBtn) closeBtn.addEventListener("click", closeModal);
    if (cancelBtn) cancelBtn.addEventListener("click", closeModal);

    // Clic sur le fond sombre pour fermer
    policyModal.addEventListener("click", (e) => {
      if (e.target === policyModal) {
        closeModal();
      }
    });

    // Touche Echap pour fermer
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && policyModal.classList.contains("show")) {
        closeModal();
      }
    });

    // Case à cocher d'accord
    if (modalCheckbox && confirmBtn) {
      modalCheckbox.addEventListener("change", () => {
        confirmBtn.disabled = !modalCheckbox.checked;
      });

      // Bouton Valider l'accord
      confirmBtn.addEventListener("click", () => {
        if (modalCheckbox.checked) {
          localStorage.setItem("epi_policy_accepted", "true");
          closeModal();
        }
      });
    }
  }
});
