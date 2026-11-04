/**
 * ============================================================
 * EPI TCHACH – Script JavaScript Desktop Landing (landing.js)
 * Interactions riches : Animations, Carrousel, Accordéon FAQ,
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
    const updateHeader = () => {
      header.classList.toggle("scrolled", window.scrollY > 20);
    };
    const navToggle = header.querySelector(".nav-toggle");
    const navLinks = header.querySelector(".nav-links");

    updateHeader();
    window.addEventListener("scroll", updateHeader, { passive: true });

    if (navToggle && navLinks) {
      const closeNavigation = () => {
        navLinks.classList.remove("is-open");
        navToggle.setAttribute("aria-expanded", "false");
        navToggle.setAttribute("aria-label", "Ouvrir la navigation");
      };

      navToggle.addEventListener("click", () => {
        const isOpen = navToggle.getAttribute("aria-expanded") === "true";
        navToggle.setAttribute("aria-expanded", String(!isOpen));
        navToggle.setAttribute("aria-label", isOpen ? "Ouvrir la navigation" : "Fermer la navigation");
        navLinks.classList.toggle("is-open", !isOpen);
      });

      navLinks.addEventListener("click", (event) => {
        if (event.target.closest("a")) closeNavigation();
      });

      document.addEventListener("click", (event) => {
        if (!header.contains(event.target)) closeNavigation();
      });

      document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && navToggle.getAttribute("aria-expanded") === "true") {
          closeNavigation();
          navToggle.focus();
        }
      });
    }
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

  // 4. Carrousel photo du héros
  const heroCarousel = document.querySelector(".hero-photo-carousel");
  if (heroCarousel) {
    const cards = Array.from(heroCarousel.querySelectorAll("[data-hero-card]"));
    const dots = Array.from(heroCarousel.querySelectorAll("[data-hero-dot]"));
    const previousButton = heroCarousel.querySelector("[data-hero-prev]");
    const nextButton = heroCarousel.querySelector("[data-hero-next]");
    const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    let activeIndex = 0;
    let autoplayId = null;
    let isVisible = !("IntersectionObserver" in window);

    if (cards.length) {
      const showCard = (index) => {
        activeIndex = (index + cards.length) % cards.length;
        cards.forEach((card, cardIndex) => {
          const offset = (cardIndex - activeIndex + cards.length) % cards.length;
          const position = offset > Math.floor(cards.length / 2) ? offset - cards.length : offset;
          let positionClass = "is-hidden";
          if (position === 0) positionClass = "is-active";
          else if (position === 1) positionClass = "is-next";
          else if (position === -1) positionClass = "is-prev";
          else if (position > 0) positionClass = "is-next-2";
          else if (position < 0) positionClass = "is-prev-2";

          card.classList.remove("is-active", "is-next", "is-prev", "is-next-2", "is-prev-2", "is-hidden");
          card.classList.add(positionClass);
          card.setAttribute("aria-hidden", String(position !== 0));
          card.tabIndex = position === 0 ? 0 : -1;
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

      const stopAutoplay = () => {
        window.clearInterval(autoplayId);
        autoplayId = null;
      };
      const startAutoplay = () => {
        if (cards.length > 1 && !prefersReducedMotion && isVisible && autoplayId === null && !document.hidden) {
          autoplayId = window.setInterval(() => showCard(activeIndex + 1), 5000);
        }
      };

      cards.forEach((card, index) => {
        card.addEventListener("click", () => showCard(index));
      });
      dots.forEach((dot) => {
        dot.addEventListener("click", () => showCard(Number(dot.dataset.heroDot)));
      });
      if (previousButton) {
        previousButton.addEventListener("click", () => showCard(activeIndex - 1));
      }
      if (nextButton) {
        nextButton.addEventListener("click", () => showCard(activeIndex + 1));
      }

      heroCarousel.addEventListener("mouseenter", stopAutoplay);
      heroCarousel.addEventListener("mouseleave", startAutoplay);
      heroCarousel.addEventListener("focusin", stopAutoplay);
      heroCarousel.addEventListener("focusout", (event) => {
        if (!heroCarousel.contains(event.relatedTarget)) startAutoplay();
      });
      document.addEventListener("visibilitychange", () => {
        if (document.hidden) {
          stopAutoplay();
        } else {
          startAutoplay();
        }
      });

      showCard(activeIndex);
      if ("IntersectionObserver" in window) {
        const heroCarouselObserver = new IntersectionObserver(
          ([entry]) => {
            isVisible = entry.isIntersecting;
            if (isVisible) {
              startAutoplay();
            } else {
              stopAutoplay();
            }
          },
          { threshold: 0.25 }
        );
        heroCarouselObserver.observe(heroCarousel);
      } else {
        startAutoplay();
      }
    }
  }

  // 5. Carrousel des filières
  const filiereCarousel = document.querySelector(".filiere-carousel");
  if (filiereCarousel) {
    const cards = Array.from(filiereCarousel.querySelectorAll("[data-filiere-card]"));
    const dots = Array.from(filiereCarousel.querySelectorAll("[data-carousel-dot]"));
    const track = filiereCarousel.querySelector(".filiere-track");
    const previousButton = filiereCarousel.querySelector("[data-carousel-prev]");
    const nextButton = filiereCarousel.querySelector("[data-carousel-next]");
    const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    let activeIndex = Math.max(0, cards.findIndex((card) => card.classList.contains("is-active")));
    let autoplayId = null;
    let isVisible = !("IntersectionObserver" in window);

    const showCard = (index, shouldScroll = false) => {
      activeIndex = (index + cards.length) % cards.length;
      cards.forEach((card, cardIndex) => {
        const isActive = cardIndex === activeIndex;
        card.classList.toggle("is-active", isActive);
        card.setAttribute("aria-pressed", String(isActive));
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

      if (shouldScroll && track && window.matchMedia("(max-width: 850px)").matches) {
        const card = cards[activeIndex];
        const trackBounds = track.getBoundingClientRect();
        const cardBounds = card.getBoundingClientRect();
        const cardLeft = track.scrollLeft + cardBounds.left - trackBounds.left;
        const targetLeft = cardLeft - (track.clientWidth - card.clientWidth) / 2;
        track.scrollTo({
          left: targetLeft,
          behavior: prefersReducedMotion ? "auto" : "smooth",
        });
      }
    };

    const stopAutoplay = () => {
      window.clearInterval(autoplayId);
      autoplayId = null;
    };
    const startAutoplay = () => {
      if (cards.length > 1 && !prefersReducedMotion && isVisible && autoplayId === null && !document.hidden) {
        autoplayId = window.setInterval(() => showCard(activeIndex + 1, true), 6000);
      }
    };

    if (!cards.length) return;

    cards.forEach((card, index) => {
      card.addEventListener("click", () => showCard(index, true));
      card.addEventListener("keydown", (event) => {
        if (event.key === "Enter" || event.key === " ") {
          event.preventDefault();
          showCard(index, true);
        }
      });
    });
    dots.forEach((dot) => {
      dot.addEventListener("click", () => showCard(Number(dot.dataset.carouselDot), true));
    });
    if (previousButton) {
      previousButton.addEventListener("click", () => showCard(activeIndex - 1, true));
    }
    if (nextButton) {
      nextButton.addEventListener("click", () => showCard(activeIndex + 1, true));
    }

    filiereCarousel.addEventListener("mouseenter", stopAutoplay);
    filiereCarousel.addEventListener("mouseleave", startAutoplay);
    filiereCarousel.addEventListener("focusin", stopAutoplay);
    filiereCarousel.addEventListener("focusout", (event) => {
      if (!filiereCarousel.contains(event.relatedTarget)) startAutoplay();
    });
    document.addEventListener("visibilitychange", () => {
      if (document.hidden) {
        stopAutoplay();
      } else {
        startAutoplay();
      }
    });

    showCard(activeIndex);
    filiereCarousel.classList.add("is-ready");
    if ("IntersectionObserver" in window) {
      const carouselObserver = new IntersectionObserver(
        ([entry]) => {
          isVisible = entry.isIntersecting;
          if (isVisible) {
            startAutoplay();
          } else {
            stopAutoplay();
          }
        },
        { threshold: 0.25 }
      );
      carouselObserver.observe(filiereCarousel);
    } else {
      startAutoplay();
    }
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
    'a[href]:not([href^="#"]):not([href=""]):not([target="_blank"]):not([href^="javascript:"]):not([href^="mailto:"]):not([href^="tel:"]):not(.open-policy-modal):not([data-open-modal])'
  );

  internalLinks.forEach((link) => {
    link.addEventListener("click", (e) => {
      const destination = link.getAttribute("href");
      // Ignorer si clic molette, touche Ctrl/Cmd, ancre ou vide
      if (e.metaKey || e.ctrlKey || !destination || destination.startsWith("#")) return;

      // Ignorer les liens externes absolus vers d'autres domaines
      if (destination.startsWith("http://") || destination.startsWith("https://")) {
        try {
          const urlObj = new URL(destination);
          if (urlObj.origin !== window.location.origin) return;
        } catch (_) {
          return;
        }
      }

      e.preventDefault();
      document.body.classList.add("page-transitioning");

      setTimeout(() => {
        window.location.href = destination;
      }, 180);
    });
  });

  // 9. Gestion de la Modale Popup (Charte d'Utilisation & Confidentialité)
  window.openPolicyModal = function (e) {
    if (e) {
      if (typeof e.preventDefault === "function") e.preventDefault();
      if (typeof e.stopPropagation === "function") e.stopPropagation();
    }
    const modal = document.getElementById("policyModal");
    if (!modal) return;
    modal.classList.add("show");
    modal.style.display = "flex";
    modal.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";
  };

  window.closePolicyModal = function () {
    const modal = document.getElementById("policyModal");
    if (!modal) return;
    modal.classList.remove("show");
    modal.style.display = "none";
    modal.setAttribute("aria-hidden", "true");
    document.body.style.overflow = "";
  };

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

    // Écouteur global délégué pour ouvrir la modale
    document.addEventListener("click", (e) => {
      const trigger = e.target.closest(".open-policy-modal, a[href='#policyModal'], [data-open-modal='policyModal']");
      if (trigger) {
        window.openPolicyModal(e);
      }
    });

    // Déclencheurs de fermeture
    if (closeBtn) closeBtn.addEventListener("click", window.closePolicyModal);
    if (cancelBtn) cancelBtn.addEventListener("click", window.closePolicyModal);

    // Clic sur le fond sombre pour fermer
    policyModal.addEventListener("click", (e) => {
      if (e.target === policyModal) {
        window.closePolicyModal();
      }
    });

    // Touche Echap pour fermer
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && policyModal.classList.contains("show")) {
        window.closePolicyModal();
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
          window.closePolicyModal();
        }
      });
    }
  }
});
