/**
 * ============================================================
 * EPI TCHACH – Script JavaScript Authentification Desktop (auth.js)
 * Affichage/masquage mot de passe, transitions & modale Charte/CGU
 * ============================================================
 */

document.addEventListener("DOMContentLoaded", () => {
  // 1. Bascule afficher / masquer le mot de passe
  document.querySelectorAll(".toggle-password").forEach((button) => {
    button.addEventListener("click", () => {
      const targetId = button.getAttribute("data-target");
      const input = document.getElementById(targetId);

      if (input) {
        const isPassword = input.getAttribute("type") === "password";
        input.setAttribute("type", isPassword ? "text" : "password");

        // Changer l'icône
        const eyeIcon = button.querySelector(".icon-eye");
        const eyeOffIcon = button.querySelector(".icon-eye-off");

        if (eyeIcon && eyeOffIcon) {
          eyeIcon.style.display = isPassword ? "none" : "block";
          eyeOffIcon.style.display = isPassword ? "block" : "none";
        }
      }
    });
  });

  // 2. Transitions douces entre les pages d'authentification
  const internalLinks = document.querySelectorAll(
    'a[href]:not([href^="#"]):not([target="_blank"]):not([href^="javascript:"]):not([href^="mailto:"])'
  );

  internalLinks.forEach((link) => {
    link.addEventListener("click", (e) => {
      const destination = link.getAttribute("href");
      if (e.metaKey || e.ctrlKey || !destination) return;

      e.preventDefault();
      document.body.classList.add("page-transitioning");

      setTimeout(() => {
        window.location.href = destination;
      }, 180);
    });
  });

  // 3. Gestion de la Modale Popup (Charte & Confidentialité dans l'inscription)
  const policyModal = document.getElementById("policyModal");
  if (policyModal) {
    const modalCheckbox = document.getElementById("policyAgreeCheckbox");
    const confirmBtn = document.getElementById("confirmPolicyAgree");
    const closeBtn = document.getElementById("closePolicyModal");
    const cancelBtn = document.getElementById("cancelPolicyModal");
    const formCguCheckbox = document.getElementById("cguCheckbox") || document.querySelector("input[name='cgu']");

    // Si déjà accepté dans localStorage, cocher automatiquement
    const isAlreadyAccepted = localStorage.getItem("epi_policy_accepted") === "true";
    if (isAlreadyAccepted) {
      if (modalCheckbox) modalCheckbox.checked = true;
      if (confirmBtn) confirmBtn.disabled = false;
      if (formCguCheckbox) formCguCheckbox.checked = true;
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

    // Clic sur le fond
    policyModal.addEventListener("click", (e) => {
      if (e.target === policyModal) {
        closeModal();
      }
    });

    // Échap
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && policyModal.classList.contains("show")) {
        closeModal();
      }
    });

    // Case à cocher de la modale
    if (modalCheckbox && confirmBtn) {
      modalCheckbox.addEventListener("change", () => {
        confirmBtn.disabled = !modalCheckbox.checked;
      });

      // Valider l'accord depuis la modale
      confirmBtn.addEventListener("click", () => {
        if (modalCheckbox.checked) {
          localStorage.setItem("epi_policy_accepted", "true");
          if (formCguCheckbox) {
            formCguCheckbox.checked = true;
            formCguCheckbox.dispatchEvent(new Event("change"));
          }
          closeModal();
        }
      });
    }

    // Si l'utilisateur coche la case directement dans le formulaire
    if (formCguCheckbox && modalCheckbox) {
      formCguCheckbox.addEventListener("change", () => {
        if (formCguCheckbox.checked) {
          localStorage.setItem("epi_policy_accepted", "true");
          modalCheckbox.checked = true;
          if (confirmBtn) confirmBtn.disabled = false;
        }
      });
    }
  }
});
