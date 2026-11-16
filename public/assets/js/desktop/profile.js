/**
 * ============================================================
 * EPI TCHACH – Script JavaScript Desktop Profil (profile.js)
 * Navigation fluide par onglets horizontaux, Modale d'édition AJAX,
 * Publication instantanée avec retour optimiste et toasts.
 * ============================================================
 */

document.addEventListener("DOMContentLoaded", () => {
  // Fonction utilitaire d'envoi AJAX avec progression
  const sendFormWithProgress = (url, formData, onProgress) => new Promise((resolve, reject) => {
    const request = new XMLHttpRequest();
    request.open("POST", url);
    request.setRequestHeader("Accept", "application/json");
    request.setRequestHeader("X-Requested-With", "XMLHttpRequest");
    request.upload.addEventListener("progress", (event) => {
      if (event.lengthComputable && onProgress) {
        onProgress(Math.round((event.loaded / event.total) * 100));
      }
    });
    request.addEventListener("load", () => {
      if (!(request.getResponseHeader("content-type") || "").includes("application/json")) {
        reject(new Error(request.responseURL.includes("/login")
          ? "Votre session a expiré. Veuillez vous reconnecter."
          : "Le serveur a renvoyé une réponse inattendue."));
        return;
      }
      let result;
      try {
        result = JSON.parse(request.responseText);
      } catch {
        reject(new Error("La réponse du serveur est invalide."));
        return;
      }
      if (request.status < 200 || request.status >= 300 || !result.success) {
        reject(new Error(result.message || "L’envoi n’a pas pu être effectué."));
        return;
      }
      resolve(result);
    });
    request.addEventListener("error", () => reject(new Error("Impossible de joindre le serveur. Réessayez.")));
    request.send(formData);
  });

  // ============================================================
  // 1. GESTION DES ONGLETS DU PROFIL (Généraux & Spécifiques rôles)
  // ============================================================
  const tabNavButtons = document.querySelectorAll(".profile-nav-tab, .sidebar-tab-link");
  const tabContents = document.querySelectorAll(".tab-content");

  tabNavButtons.forEach((btn) => {
    btn.addEventListener("click", (e) => {
      const targetId = btn.getAttribute("data-tab");
      if (!targetId) return;
      e.preventDefault();

      // Mettre à jour l'état actif sur les boutons d'onglets
      tabNavButtons.forEach((b) => {
        if (b.getAttribute("data-tab") === targetId) {
          b.classList.add("active");
        } else {
          b.classList.remove("active");
        }
      });

      // Basculer l'affichage du contenu d'onglet correspondant
      tabContents.forEach((content) => {
        if (content.id === targetId) {
          content.classList.add("active");
        } else {
          content.classList.remove("active");
        }
      });

      // Synchroniser le paramètre d'URL sans rechargement de page
      const tabName = targetId.replace("tab-", "");
      const currentUrl = new URL(window.location.href);
      currentUrl.searchParams.set("tab", tabName);
      window.history.replaceState(null, "", currentUrl);
    });
  });

  // Filtres de recherche/filtrage dans l'onglet des publications
  const postCategoryFilter = document.getElementById("profilePostCategoryFilter");
  const postStatusFilter = document.getElementById("profilePostStatusFilter");
  const documentCategoryFilter = document.getElementById("profileDocumentCategoryFilter");

  const filterProfilePosts = () => {
    document.querySelectorAll("[data-profile-post]").forEach((post) => {
      const categoryMatches = !postCategoryFilter
        || postCategoryFilter.value === "all"
        || post.dataset.categoryId === postCategoryFilter.value;
      const statusMatches = !postStatusFilter
        || postStatusFilter.value === "all"
        || post.dataset.postStatus === postStatusFilter.value;
      post.hidden = !(categoryMatches && statusMatches);
    });
  };

  const filterProfileDocuments = () => {
    document.querySelectorAll("[data-profile-document]").forEach((documentRow) => {
      documentRow.hidden = Boolean(documentCategoryFilter)
        && documentCategoryFilter.value !== "all"
        && documentRow.dataset.categoryId !== documentCategoryFilter.value;
    });
  };

  postCategoryFilter?.addEventListener("change", filterProfilePosts);
  postStatusFilter?.addEventListener("change", filterProfilePosts);
  documentCategoryFilter?.addEventListener("change", filterProfileDocuments);

  // ============================================================
  // 2. MODALE POPUP D'ÉDITION DU PROFIL (Fluidité complète)
  // ============================================================
  const editModal = document.getElementById("profileEditModal");

  window.openProfileEditModal = function (e) {
    if (e) {
      if (typeof e.preventDefault === "function") e.preventDefault();
      if (typeof e.stopPropagation === "function") e.stopPropagation();
    }
    if (!editModal) return;
    editModal.classList.add("show");
    editModal.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";
  };

  window.closeProfileEditModal = function () {
    if (!editModal) return;
    if (editModal.contains(document.activeElement)) {
      document.activeElement.blur();
    }
    editModal.classList.remove("show");
    editModal.setAttribute("aria-hidden", "true");
    document.body.style.overflow = "";
  };

  if (editModal) {
    const closeBtns = editModal.querySelectorAll(".btn-close-modal, #cancelEditModalBtn");
    closeBtns.forEach((btn) => btn.addEventListener("click", window.closeProfileEditModal));

    editModal.addEventListener("click", (e) => {
      if (e.target === editModal) {
        window.closeProfileEditModal();
      }
    });

    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && editModal.classList.contains("show")) {
        window.closeProfileEditModal();
      }
    });

    document.addEventListener("click", (e) => {
      const trigger = e.target.closest(".open-profile-edit-modal, [data-open-modal='profileEditModal']");
      if (trigger) {
        e.preventDefault();
        window.openProfileEditModal(e);
      }
    });
  }

  // ============================================================
  // 3. MISE À JOUR DU PROFIL EN AJAX (Sans rechargement)
  // ============================================================
  const profileEditForm = document.getElementById("ajaxProfileEditForm");

  if (profileEditForm) {
    const avatarInput = profileEditForm.querySelector("[data-profile-avatar-input]");
    const progressFeedback = profileEditForm.querySelector("[data-profile-upload-feedback]");
    const avatarPreview = document.getElementById("editAvatarPreview");
    const removeAvatarInput = profileEditForm.querySelector("[data-remove-profile-avatar]");
    const clearAvatarButton = profileEditForm.querySelector("[data-clear-profile-avatar]");
    let avatarPreviewUrl = null;

    avatarInput?.addEventListener("change", () => {
      if (avatarInput.files?.[0] && removeAvatarInput) removeAvatarInput.value = "0";
      if (avatarPreviewUrl) URL.revokeObjectURL(avatarPreviewUrl);
      const file = avatarInput.files?.[0];
      if (!file || !avatarPreview) return;
      avatarPreviewUrl = URL.createObjectURL(file);
      avatarPreview.src = avatarPreviewUrl;
      avatarPreview.hidden = false;
    });

    clearAvatarButton?.addEventListener("click", () => {
      if (avatarInput) avatarInput.value = "";
      if (avatarPreviewUrl) URL.revokeObjectURL(avatarPreviewUrl);
      avatarPreviewUrl = null;
      if (avatarPreview) {
        avatarPreview.removeAttribute("src");
        avatarPreview.hidden = true;
      }
      if (removeAvatarInput) removeAvatarInput.value = "1";
    });

    profileEditForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const submitBtn = profileEditForm.querySelector("button[type='submit']");
      const originalBtnHtml = submitBtn ? submitBtn.innerHTML : "<span>Enregistrer</span>";

      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation:spin 1s linear infinite;"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
          <span>Enregistrement…</span>
        `;
      }

      const formData = new FormData(profileEditForm);
      formData.append("ajax", "1");
      const targetAction = profileEditForm.getAttribute("action") || window.location.pathname.replace(/\/edit\/?$/, "") + "/update";

      try {
        const data = await sendFormWithProgress(targetAction, formData, avatarInput?.files?.length
          ? (percent) => {
            if (progressFeedback) progressFeedback.textContent = `Envoi de la photo… ${percent} %`;
          }
          : null);

        if (data.success && data.user) {
          const newFullName = data.user.fullname || `${data.user.prenom} ${data.user.nom}`;
          const newInitials = data.user.initials || "EPI";
          const newBio = data.user.biographie || "";

          // Mettre à jour instantanément les éléments dans la page
          document.querySelectorAll(".profile-dynamic-name").forEach((el) => {
            el.textContent = newFullName;
          });
          document.querySelectorAll(".profile-dynamic-initials").forEach((el) => {
            el.textContent = newInitials;
          });
          document.querySelectorAll(".profile-avatar-image").forEach((image) => {
            if (data.user.avatar_url) {
              image.src = data.user.avatar_url;
              image.hidden = false;
            } else {
              image.removeAttribute("src");
              image.hidden = true;
            }
          });
          document.querySelectorAll(".profile-dynamic-bio").forEach((el) => {
            el.textContent = newBio || "Aucune biographie renseignée.";
          });

          window.closeProfileEditModal();
          window.showToast("Vos informations ont été mises à jour avec succès !", "success");
        } else {
          window.showToast(data.message || "Erreur lors de la mise à jour.", "error");
        }
      } catch (error) {
        window.showToast(error.message || "Impossible de joindre le serveur. Réessayez.", "error");
      } finally {
        if (progressFeedback) progressFeedback.textContent = "";
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalBtnHtml;
        }
      }
    });
  }

  // ============================================================
  // 4. PUBLICATION RAPIDE AVEC RETOUR EN DIRECT
  // ============================================================
  const quickPostForm = document.getElementById("quickPostForm");
  const postsFeedContainer = document.getElementById("postsFeedList");
  const postAttachments = document.getElementById("postAttachments");
  const selectedFilesBadge = document.getElementById("selectedFilesBadge");

  postAttachments?.addEventListener("change", () => {
    const count = postAttachments.files?.length || 0;
    if (selectedFilesBadge) {
      if (count > 0) {
        selectedFilesBadge.textContent = `${count} fichier(s) sélectionné(s)`;
        selectedFilesBadge.hidden = false;
      } else {
        selectedFilesBadge.hidden = true;
      }
    }
  });

  if (quickPostForm && postsFeedContainer) {
    quickPostForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const progressFeedback = document.getElementById("quickPostFeedback");
      const textarea = quickPostForm.querySelector("textarea[name='contenu']");
      const titleInput = quickPostForm.querySelector("input[name='titre']");
      const categorySelect = quickPostForm.querySelector("select[name='categorie_id']");

      const titre = titleInput ? titleInput.value.trim() : "";
      const contenu = textarea ? textarea.value.trim() : "";
      const categoryLabel = categorySelect?.selectedOptions[0]?.textContent.trim() || "Salon";
      const categoryId = categorySelect?.value || "";

      if (!titre || !contenu) return;

      const submitButton = quickPostForm.querySelector("button[type='submit']");
      const origBtnHtml = submitButton ? submitButton.innerHTML : "<span>Publier le sujet</span>";

      if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerHTML = `
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation:spin 0.8s linear infinite;"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
          <span>Publication…</span>
        `;
      }

      const formData = new FormData(quickPostForm);
      formData.set("ajax", "1");

      try {
        const attachmentInput = quickPostForm.querySelector("input[type='file']");
        const result = await sendFormWithProgress(quickPostForm.action, formData, attachmentInput?.files?.length
          ? (percent) => {
            if (progressFeedback) progressFeedback.textContent = `Envoi des fichiers… ${percent} %`;
          }
          : null);

        const escapeHtml = (value) => String(value).replace(/[&<>"']/g, (character) => ({
          "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;",
        }[character]));

        const userName = escapeHtml(document.querySelector(".profile-dynamic-name")?.textContent || "Moi");
        const userInitials = escapeHtml(document.querySelector(".profile-dynamic-initials")?.textContent || "EPI");

        // Retirer l'état vide si présent
        postsFeedContainer.querySelector(".profile-empty-state")?.remove();

        // Insérer la nouvelle carte de publication dans le feed
        const postCard = document.createElement("article");
        postCard.className = "post-card";
        postCard.dataset.profilePost = "";
        postCard.dataset.categoryId = categoryId;
        postCard.dataset.postStatus = result.moderationPending ? "reported" : "visible";
        postCard.innerHTML = `
          <div class="post-header-flex">
            <div class="post-author-box">
              <div class="post-avatar-mini">${userInitials}</div>
              <div>
                <div class="post-author-name">${userName}</div>
                <div class="post-time">À l'instant · ${escapeHtml(categoryLabel)}</div>
              </div>
            </div>
            <div class="profile-post-badges">
              <span class="post-tag-pill">${escapeHtml(categoryLabel)}</span>
              <span class="profile-status-badge ${result.moderationPending ? "is-warning" : "is-active"}">${result.moderationPending ? "En modération" : "Visible"}</span>
            </div>
          </div>
          <h4 class="post-title">
            <a href="${result.publication_id ? window.location.origin + '/publication/' + result.publication_id : '#'}" class="profile-post-title-link">${escapeHtml(result.title || titre)}</a>
          </h4>
          <p class="post-excerpt">${escapeHtml(result.content || contenu)}</p>
          ${result.moderationPending ? '<p class="profile-moderation-note">Cette publication est en cours d’examen par la modération.</p>' : ""}
          <div class="post-footer-actions">
            <span class="profile-post-stat">♥ 0 likes</span>
            <span class="profile-post-stat">💬 0 réponses</span>
          </div>
        `;

        postsFeedContainer.prepend(postCard);

        // Réinitialiser le formulaire
        quickPostForm.reset();
        if (selectedFilesBadge) selectedFilesBadge.hidden = true;

        // Mettre à jour le compteur de publications
        const statCounter = document.getElementById("statSubjectsCount");
        if (statCounter) {
          const currentCount = parseInt(statCounter.textContent, 10) || 0;
          statCounter.textContent = currentCount + 1;
        }

        window.showToast("Votre sujet a été publié avec succès !", "success");
      } catch (error) {
        window.showToast(error.message, "error");
      } finally {
        if (progressFeedback) progressFeedback.textContent = "";
        if (submitButton) {
          submitButton.disabled = false;
          submitButton.innerHTML = origBtnHtml;
        }
      }
    });
  }

  // ============================================================
  // 5. TOAST NOTIFICATIONS UNIVERSELLES
  // ============================================================
  window.showToast = function (message, type = "success") {
    let toast = document.getElementById("profileToast");
    if (!toast) {
      toast = document.createElement("div");
      toast.id = "profileToast";
      toast.style.cssText = `
        position: fixed;
        bottom: 1.5rem;
        right: 1.5rem;
        z-index: 9999;
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.8rem 1.25rem;
        border-radius: 12px;
        font-family: inherit;
        font-size: 0.88rem;
        font-weight: 750;
        box-shadow: 0 10px 30px rgba(0,0,0,0.35);
        opacity: 0;
        transform: translateY(12px);
        transition: opacity 0.22s ease, transform 0.22s ease;
      `;
      document.body.appendChild(toast);
    }

    if (type === "success") {
      toast.style.background = "#14251e";
      toast.style.border = "1px solid rgba(142, 211, 170, 0.4)";
      toast.style.color = "#8ed3aa";
    } else if (type === "error") {
      toast.style.background = "#2a1515";
      toast.style.border = "1px solid rgba(248, 113, 113, 0.4)";
      toast.style.color = "#f87171";
    } else {
      toast.style.background = "#182123";
      toast.style.border = "1px solid #2a383a";
      toast.style.color = "#e8efeb";
    }

    toast.textContent = message;
    toast.style.opacity = "1";
    toast.style.transform = "translateY(0)";

    clearTimeout(window.toastTimer);
    window.toastTimer = setTimeout(() => {
      toast.style.opacity = "0";
      toast.style.transform = "translateY(12px)";
    }, 3500);
  };
});
