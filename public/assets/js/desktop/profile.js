/**
 * ============================================================
 * EPI TCHACH – Script JavaScript Desktop Profil (profile.js)
 * Gestion des onglets, interactions & simulations
 * ============================================================
 */

document.addEventListener("DOMContentLoaded", () => {
  // 1. Gestion des onglets du profil
  const tabButtons = document.querySelectorAll(".tab-btn");
  const tabContents = document.querySelectorAll(".tab-content");

  tabButtons.forEach((btn) => {
    btn.addEventListener("click", () => {
      const targetId = btn.getAttribute("data-tab");
      if (!targetId) return;

      // Désactiver tous les onglets
      tabButtons.forEach((b) => b.classList.remove("active"));
      tabContents.forEach((c) => c.classList.remove("active"));

      // Activer l'onglet sélectionné
      btn.classList.add("active");
      const targetContent = document.getElementById(targetId);
      if (targetContent) {
        targetContent.classList.add("active");
      }
    });
  });

  // 2. Simulation de sauvegarde du profil
  const profileForm = document.getElementById("profileEditForm");
  if (profileForm) {
    profileForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const saveBtn = profileForm.querySelector("button[type='submit']");
      const originalText = saveBtn ? saveBtn.innerHTML : "Enregistrer";

      if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.innerHTML = "<span>Enregistrement...</span>";
      }

      setTimeout(() => {
        if (saveBtn) {
          saveBtn.disabled = false;
          saveBtn.innerHTML = "✓ Modifications enregistrées !";
          setTimeout(() => {
            saveBtn.innerHTML = originalText;
          }, 2500);
        }

        // Notification visuelle
        alert("Vos informations de profil ont été mises à jour avec succès sur EPI Tchach !");
      }, 600);
    });
  }

  // 3. Prévisualisation d'avatar si upload
  const avatarInput = document.getElementById("avatarUpload");
  const avatarPreview = document.getElementById("avatarPreviewImg");
  if (avatarInput && avatarPreview) {
    avatarInput.addEventListener("change", function () {
      const file = this.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
          avatarPreview.src = e.target.result;
          avatarPreview.style.display = "block";
          const initials = document.getElementById("avatarInitials");
          if (initials) initials.style.display = "none";
        };
        reader.readAsDataURL(file);
      }
    });
  }
});

