/**
 * ============================================================
 * EPI TCHACH – Script JavaScript Authentification Desktop (auth.js)
 * Affichage/masquage mot de passe, transitions & modale Charte/CGU
 * ============================================================
 */

document.addEventListener("DOMContentLoaded", () => {
  const loginForm = document.querySelector("[data-login-form]");
  if (loginForm) {
    const emailInput = document.getElementById("email");
    const passwordInput = document.getElementById("mot_de_passe");
    const nextButton = loginForm.querySelector("[data-login-next]");
    const backButton = loginForm.querySelector("[data-login-back]");
    const submitButton = loginForm.querySelector("[data-login-submit]");
    const progressSteps = document.querySelectorAll("[data-progress-step]");

    if (emailInput && passwordInput && nextButton && backButton && submitButton) {
      loginForm.classList.add("is-staged");

      const showStep = (step) => {
        loginForm.dataset.currentStep = step;
        loginForm.classList.toggle("is-password-step", step === "password");
        loginForm.querySelectorAll("[data-login-step]").forEach((section) => {
          const visible = section.dataset.loginStep === step;
          section.hidden = !visible;
        });
        passwordInput.disabled = step !== "password";
        nextButton.hidden = step !== "email";
        backButton.hidden = step !== "password";
        submitButton.hidden = step !== "password";
        progressSteps.forEach((progressStep) => {
          progressStep.classList.toggle("is-current", progressStep.dataset.progressStep === step);
          progressStep.classList.toggle("is-complete", step === "password" && progressStep.dataset.progressStep === "email");
        });
        (step === "email" ? emailInput : passwordInput).focus();
      };

      nextButton.addEventListener("click", () => {
        if (emailInput.reportValidity()) showStep("password");
      });
      backButton.addEventListener("click", () => showStep("email"));
      showStep(loginForm.dataset.initialStep === "password" ? "password" : "email");
    }
  }

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
    'a[href]:not([href^="#"]):not([href=""]):not([target="_blank"]):not([href^="javascript:"]):not([href^="mailto:"]):not([href^="tel:"]):not(.open-policy-modal):not([data-open-modal])'
  );

  internalLinks.forEach((link) => {
    link.addEventListener("click", (e) => {
      const destination = link.getAttribute("href");
      if (e.metaKey || e.ctrlKey || !destination || destination.startsWith("#")) return;

      // Ignorer les liens externes absolus
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

  // 3. Gestion de la Modale Popup (Charte & Confidentialité dans l'inscription)
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
    const formCguCheckbox = document.getElementById("cguCheckbox") || document.querySelector("input[name='cgu']");

    // Si déjà accepté dans localStorage, cocher automatiquement
    const isAlreadyAccepted = localStorage.getItem("epi_policy_accepted") === "true";
    if (isAlreadyAccepted) {
      if (modalCheckbox) modalCheckbox.checked = true;
      if (confirmBtn) confirmBtn.disabled = false;
      if (formCguCheckbox) formCguCheckbox.checked = true;
    }

    // Déclencheurs d'ouverture délégués
    document.addEventListener("click", (e) => {
      const trigger = e.target.closest(".open-policy-modal, a[href='#policyModal'], [data-open-modal='policyModal']");
      if (trigger) {
        window.openPolicyModal(e);
      }
    });

    // Déclencheurs de fermeture
    if (closeBtn) closeBtn.addEventListener("click", window.closePolicyModal);
    if (cancelBtn) cancelBtn.addEventListener("click", window.closePolicyModal);

    // Clic sur le fond
    policyModal.addEventListener("click", (e) => {
      if (e.target === policyModal) {
        window.closePolicyModal();
      }
    });

    // Échap
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && policyModal.classList.contains("show")) {
        window.closePolicyModal();
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
          window.closePolicyModal();
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

  // 4. Gestion dynamique du formulaire d'inscription (Étudiant vs Professeur)
  const roleSelect = document.getElementById("role");
  const sectionEtudiant = document.getElementById("section-etudiant");
  const sectionProfesseur = document.getElementById("section-professeur");
  const filiereSelect = document.getElementById("filiere-select");
  const autreFiliereGroup = document.getElementById("autre-filiere-group");
  const autreFiliereInput = document.getElementById("autre_filiere");

  function updateRoleVisibility() {
    if (!roleSelect) return;
    const currentRole = roleSelect.value;

    if (currentRole === "professeur") {
      if (sectionProfesseur) sectionProfesseur.style.display = "block";
      if (sectionEtudiant) sectionEtudiant.style.display = "none";
      if (filiereSelect) filiereSelect.removeAttribute("required");
      if (autreFiliereInput) autreFiliereInput.removeAttribute("required");
    } else {
      // Étudiant (ou délégué)
      if (sectionEtudiant) sectionEtudiant.style.display = "block";
      if (sectionProfesseur) sectionProfesseur.style.display = "none";
      if (filiereSelect) {
        filiereSelect.setAttribute("required", "required");
      }
      updateAutreFiliereVisibility();
    }
  }

  function updateAutreFiliereVisibility() {
    if (!filiereSelect || !autreFiliereGroup) return;
    if (filiereSelect.value === "autre") {
      autreFiliereGroup.style.display = "block";
      if (autreFiliereInput) autreFiliereInput.setAttribute("required", "required");
    } else {
      autreFiliereGroup.style.display = "none";
      if (autreFiliereInput) {
        autreFiliereInput.removeAttribute("required");
      }
    }
  }

  if (roleSelect) {
    roleSelect.addEventListener("change", updateRoleVisibility);
  }

  if (filiereSelect) {
    filiereSelect.addEventListener("change", updateAutreFiliereVisibility);
  }

  // Initialisation à l'affichage
  updateRoleVisibility();
});
