/**
 * ============================================================
 * EPI TCHACH – Script JavaScript Authentification Desktop (auth.js)
 * Affichage/masquage mot de passe, transitions & modale Charte/CGU
 * ============================================================
 */

document.addEventListener("DOMContentLoaded", () => {
  const avatarInput = document.querySelector("[data-avatar-input]");
  const avatarPreview = document.querySelector("[data-avatar-preview]");
  const avatarPreviewImage = document.querySelector("[data-avatar-preview-image]");
  let avatarPreviewUrl = null;
  avatarInput?.addEventListener("change", () => {
    if (avatarPreviewUrl) URL.revokeObjectURL(avatarPreviewUrl);
    const file = avatarInput.files?.[0];
    if (!file || !avatarPreview || !avatarPreviewImage) {
      if (avatarPreview) avatarPreview.hidden = true;
      return;
    }
    avatarPreviewUrl = URL.createObjectURL(file);
    avatarPreviewImage.src = avatarPreviewUrl;
    avatarPreview.hidden = false;
  });

  const registerForm = document.querySelector("[data-register-form]");
  if (registerForm) {
    const panels = Array.from(registerForm.querySelectorAll("[data-register-step]"));
    const progressSteps = Array.from(registerForm.querySelectorAll("[data-register-progress]"));
    const nextButton = registerForm.querySelector("[data-register-next]");
    const backButton = registerForm.querySelector("[data-register-back]");
    const submitButton = registerForm.querySelector("[data-register-submit]");

    if (panels.length && nextButton && backButton && submitButton) {
      registerForm.classList.add("is-register-staged");
      registerForm.noValidate = true;

      const getInvalidField = (panel) => Array.from(
        panel.querySelectorAll("input, select, textarea")
      ).find((field) => !field.disabled && field.willValidate && !field.validity.valid);

      const showStep = (step, shouldFocus = false) => {
        registerForm.dataset.currentStep = String(step);
        panels.forEach((panel) => {
          panel.hidden = Number(panel.dataset.registerStep) !== step;
        });
        progressSteps.forEach((progressStep) => {
          const current = Number(progressStep.dataset.registerProgress) === step;
          progressStep.classList.toggle("is-current", current);
          progressStep.classList.toggle("is-complete", Number(progressStep.dataset.registerProgress) < step);
          if (current) progressStep.setAttribute("aria-current", "step");
          else progressStep.removeAttribute("aria-current");
        });
        backButton.hidden = step === 1;
        nextButton.hidden = step === panels.length;
        submitButton.hidden = step !== panels.length;

        if (shouldFocus) {
          const firstField = panels[step - 1].querySelector("input, select, textarea");
          if (firstField) firstField.focus();
        }
      };

      nextButton.addEventListener("click", () => {
        const step = Number(registerForm.dataset.currentStep);
        const invalidField = getInvalidField(panels[step - 1]);
        if (invalidField) {
          invalidField.reportValidity();
          return;
        }
        showStep(step + 1, true);
      });

      backButton.addEventListener("click", () => {
        showStep(Number(registerForm.dataset.currentStep) - 1, true);
      });

      registerForm.addEventListener("submit", (event) => {
        for (let index = 0; index < panels.length; index += 1) {
          const invalidField = getInvalidField(panels[index]);
          if (invalidField) {
            event.preventDefault();
            showStep(index + 1, true);
            invalidField.reportValidity();
            return;
          }
        }
      });

      const initialStep = Number(registerForm.dataset.initialStep);
      showStep(Number.isInteger(initialStep) && initialStep >= 1 && initialStep <= panels.length ? initialStep : 1);
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
