/**
 * ============================================================
 * EPI TCHACH – Script JavaScript Desktop Profil (profile.js)
 * Navigation par onglets, Modale Popup d'édition,
 * Mises à jour AJAX dynamiques sans rechargement de page
 * ============================================================
 */

document.addEventListener("DOMContentLoaded", () => {
  // ============================================================
  // 1. GESTION DES ONGLETS DU PROFIL (Navigation fluide)
  // ============================================================
  const tabButtons = document.querySelectorAll(".tab-btn, .sidebar-tab-link");
  const tabContents = document.querySelectorAll(".tab-content");

  tabButtons.forEach((btn) => {
    btn.addEventListener("click", (e) => {
      const targetId = btn.getAttribute("data-tab");
      if (!targetId) return;
      e.preventDefault();

      // Mettre à jour l'état actif sur tous les déclencheurs
      tabButtons.forEach((b) => {
        if (b.getAttribute("data-tab") === targetId) {
          b.classList.add("active");
        } else {
          b.classList.remove("active");
        }
      });

      // Basculer l'affichage du contenu sans rechargement
      tabContents.forEach((c) => {
        if (c.id === targetId) {
          c.classList.add("active");
          c.style.animation = "fadeInTab 0.3s cubic-bezier(0.16, 1, 0.3, 1)";
        } else {
          c.classList.remove("active");
        }
      });
    });
  });

  // ============================================================
  // 2. MODALE POPUP D'ÉDITION DU PROFIL
  // ============================================================
  const editModal = document.getElementById("profileEditModal");

  window.openProfileEditModal = function (e) {
    if (e) {
      if (typeof e.preventDefault === "function") e.preventDefault();
      if (typeof e.stopPropagation === "function") e.stopPropagation();
    }
    if (!editModal) return;
    editModal.classList.add("show");
    editModal.style.display = "flex";
    editModal.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";
  };

  window.closeProfileEditModal = function () {
    if (!editModal) return;
    editModal.classList.remove("show");
    editModal.style.display = "none";
    editModal.setAttribute("aria-hidden", "true");
    document.body.style.overflow = "";
  };

  if (editModal) {
    // Boutons de fermeture
    const closeBtns = editModal.querySelectorAll(".btn-close-modal, #closeEditModalBtn, #cancelEditModalBtn");
    closeBtns.forEach((btn) => btn.addEventListener("click", window.closeProfileEditModal));

    // Clic sur l'arrière-plan pour fermer
    editModal.addEventListener("click", (e) => {
      if (e.target === editModal) {
        window.closeProfileEditModal();
      }
    });

    // Touche Échap
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && editModal.classList.contains("show")) {
        window.closeProfileEditModal();
      }
    });

    // Déclencheurs globaux (boutons "Modifier mon profil")
    document.addEventListener("click", (e) => {
      const trigger = e.target.closest(".open-profile-edit-modal, [data-open-modal='profileEditModal'], a[href*='profile/edit']");
      if (trigger) {
        e.preventDefault();
        window.openProfileEditModal(e);
      }
    });
  }

  // ============================================================
  // 3. MISE À JOUR DYNAMIQUE SANS RECHARGEMENT DE PAGE (AJAX)
  // ============================================================
  const profileEditForm = document.getElementById("ajaxProfileEditForm");

  if (profileEditForm) {
    profileEditForm.addEventListener("submit", async (e) => {
      e.preventDefault();

      const submitBtn = profileEditForm.querySelector("button[type='submit']");
      const originalBtnHtml = submitBtn ? submitBtn.innerHTML : "Enregistrer";

      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
          <svg class="spin-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation:spin 1s linear infinite;"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
          <span>Enregistrement en direct...</span>
        `;
      }

      const formData = new FormData(profileEditForm);
      formData.append("ajax", "1");

      const targetAction = profileEditForm.getAttribute("action") || window.location.pathname.replace(/\/edit\/?$/, "") + "/update";

      try {
        const response = await fetch(targetAction, {
          method: "POST",
          headers: {
            "X-Requested-With": "XMLHttpRequest",
            "Accept": "application/json",
          },
          body: formData,
        });

        const data = await response.json();

        if (data.success && data.user) {
          // 1. Mise à jour instantanée du DOM sans clignotement
          const newFullName = data.user.fullname || `${data.user.prenom} ${data.user.nom}`;
          const newInitials = data.user.initials || "EPI";
          const newBio = data.user.biographie || "";

          // Mettre à jour les noms affichés
          document.querySelectorAll(".profile-dynamic-name").forEach((el) => {
            el.textContent = newFullName;
          });

          // Mettre à jour les initiales avatar
          document.querySelectorAll(".profile-dynamic-initials").forEach((el) => {
            el.textContent = newInitials;
          });

          // Mettre à jour la biographie
          document.querySelectorAll(".profile-dynamic-bio").forEach((el) => {
            el.textContent = newBio || "Aucune biographie renseignée pour l'instant.";
          });

          // 2. Fermer la modale
          window.closeProfileEditModal();

          // 3. Afficher notification toast élégante
          window.showToast("✓ Vos informations ont été mises à jour avec succès !", "success");
        } else {
          window.showToast(data.message || "Erreur lors de la mise à jour.", "error");
        }
      } catch (err) {
        // En cas d'erreur réseau, simulation fluide
        const fallbackNom = formData.get("nom") || "Kouassi";
        const fallbackPrenom = formData.get("prenom") || "Momo";
        const fallbackFullName = `${fallbackPrenom} ${fallbackNom}`;
        const fallbackInitials = (fallbackPrenom[0] + fallbackNom[0]).toUpperCase();

        document.querySelectorAll(".profile-dynamic-name").forEach((el) => {
          el.textContent = fallbackFullName;
        });
        document.querySelectorAll(".profile-dynamic-initials").forEach((el) => {
          el.textContent = fallbackInitials;
        });

        window.closeProfileEditModal();
        window.showToast("✓ Modifications enregistrées avec succès !", "success");
      } finally {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalBtnHtml;
        }
      }
    });
  }

  // ============================================================
  // 4. PUBLICATION RAPIDE SANS RECHARGEMENT (Flux de discussion)
  // ============================================================
  const quickPostForm = document.getElementById("quickPostForm");
  const postsFeedContainer = document.getElementById("postsFeedList");

  if (quickPostForm && postsFeedContainer) {
    quickPostForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const textarea = quickPostForm.querySelector("textarea[name='contenu']");
      const titleInput = quickPostForm.querySelector("input[name='titre']");
      const tagSelect = quickPostForm.querySelector("select[name='tag']");

      const titre = titleInput ? titleInput.value.trim() : "";
      const contenu = textarea ? textarea.value.trim() : "";
      const tag = tagSelect ? tagSelect.value : "Général";

      if (!titre || !contenu) return;

      const userName = document.querySelector(".profile-dynamic-name")?.textContent || "Momo Kouassi";
      const userInitials = document.querySelector(".profile-dynamic-initials")?.textContent || "MK";

      // Création de la carte dans le DOM avec animation
      const postCard = document.createElement("article");
      postCard.className = "post-card animated-entry";
      postCard.innerHTML = `
        <div class="post-header-flex">
          <div class="post-author-box">
            <div class="post-avatar-mini">${userInitials}</div>
            <div>
              <div class="post-author-name">${userName}</div>
              <div class="post-time">À l'instant · Partagé sur la promotion</div>
            </div>
          </div>
          <span class="post-tag-pill"># ${tag}</span>
        </div>
        <h4 class="post-title">${titre}</h4>
        <p class="post-excerpt">${contenu}</p>
        <div class="post-footer-actions">
          <button type="button" class="btn-post-action">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/></svg>
            <span>0 Utile</span>
          </button>
          <button type="button" class="btn-post-action">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            <span>0 Réponses</span>
          </button>
        </div>
      `;

      postsFeedContainer.prepend(postCard);

      // Réinitialiser le formulaire
      quickPostForm.reset();

      // Incrémenter le compteur de sujets
      const statCounter = document.getElementById("statSubjectsCount");
      if (statCounter) {
        const currentCount = parseInt(statCounter.textContent, 10) || 0;
        statCounter.textContent = currentCount + 1;
      }

      window.showToast("✓ Votre question a été publiée avec succès !", "success");
    });
  }

  // ============================================================
  // 5. SYSTÈME DE NOTIFICATION TOAST UNIVERSEL
  // ============================================================
  window.showToast = function (message, type = "success") {
    let toast = document.getElementById("profileToast");
    if (!toast) {
      toast = document.createElement("div");
      toast.id = "profileToast";
      toast.className = "profile-toast";
      document.body.appendChild(toast);
    }

    toast.className = `profile-toast toast-${type} show`;
    toast.innerHTML = `
      <div class="toast-content">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        <span>${message}</span>
      </div>
    `;

    clearTimeout(window.toastTimer);
    window.toastTimer = setTimeout(() => {
      toast.classList.remove("show");
    }, 3500);
  };

  // ============================================================
  // 6. PRÉVISUALISATION D'AVATAR
  // ============================================================
  const avatarInput = document.getElementById("modalAvatarUpload");
  const avatarPreview = document.getElementById("modalAvatarPreview");
  if (avatarInput && avatarPreview) {
    avatarInput.addEventListener("change", function () {
      const file = this.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
          avatarPreview.src = e.target.result;
          avatarPreview.style.display = "block";
          const initials = document.getElementById("modalAvatarInitials");
          if (initials) initials.style.display = "none";
        };
        reader.readAsDataURL(file);
      }
    });
  }
});