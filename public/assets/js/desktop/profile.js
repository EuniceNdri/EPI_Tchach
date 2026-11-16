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
        const isActive = b.getAttribute("data-tab") === targetId;
        b.classList.toggle("active", isActive);
        b.classList.toggle("is-active", isActive);
      });

      // Basculer l'affichage du contenu sans rechargement
      tabContents.forEach((c) => {
        const isActive = c.id === targetId;
        c.classList.toggle("active", isActive);
        c.classList.toggle("is-active", isActive);
        if (isActive) {
          c.style.animation = "fadeInTab 0.3s cubic-bezier(0.16, 1, 0.3, 1)";
        }
      });

      const tabName = targetId.replace("tab-", "");
      const currentUrl = new URL(window.location.href);
      currentUrl.searchParams.set("tab", tabName);
      window.history.replaceState(null, "", currentUrl);
    });
  });

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
  // 2. MODALE POPUP D'ÉDITION DU PROFIL
  // ============================================================
  const editModal = document.getElementById("profileEditModal");

  window.openProfileEditModal = function (e) {
    if (e) {
      if (typeof e.preventDefault === "function") e.preventDefault();
      if (typeof e.stopPropagation === "function") e.stopPropagation();
    }
    if (!editModal) return;
    editModal.hidden = false;
    editModal.removeAttribute("hidden");
    editModal.classList.add("show");
    editModal.style.display = "flex";
    editModal.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";
  };

  window.closeProfileEditModal = function () {
    if (!editModal) return;
    if (editModal.contains(document.activeElement)) {
      document.activeElement.blur();
    }
    editModal.classList.remove("show");
    editModal.style.display = "none";
    editModal.hidden = true;
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
    const avatarInput = profileEditForm.querySelector("[data-profile-avatar-input]");
    const avatarPreview = document.getElementById("editAvatarPreview");
    const cropModal = document.getElementById("avatarCropModal");
    const cropViewport = document.getElementById("avatarCropViewport");
    const cropImage = document.getElementById("avatarCropImage");
    const cropZoom = document.getElementById("avatarCropZoom");
    const applyCropButton = document.getElementById("applyAvatarCropBtn");
    let cropImageUrl = null;
    let avatarPreviewUrl = null;
    let acceptedAvatarFile = null;
    let cropDimensions = null;
    let cropOffset = { x: 0, y: 0 };
    let dragPosition = null;

    const setCropImagePosition = () => {
      if (!cropDimensions || !cropImage || !cropViewport) return;
      const { width, height, baseScale } = cropDimensions;
      const scale = baseScale * Number(cropZoom?.value || 1);
      const imageWidth = width * scale;
      const imageHeight = height * scale;
      const viewportSize = cropViewport.clientWidth;
      cropOffset.x = Math.min(0, Math.max(viewportSize - imageWidth, cropOffset.x));
      cropOffset.y = Math.min(0, Math.max(viewportSize - imageHeight, cropOffset.y));
      cropImage.style.width = `${imageWidth}px`;
      cropImage.style.height = `${imageHeight}px`;
      cropImage.style.left = `${cropOffset.x}px`;
      cropImage.style.top = `${cropOffset.y}px`;
    };

    const closeCropModal = (restorePreviousFile) => {
      if (!cropModal) return;
      cropModal.classList.remove("show");
      cropModal.hidden = true;
      cropModal.setAttribute("aria-hidden", "true");
      if (restorePreviousFile && avatarInput) {
        const transfer = new DataTransfer();
        if (acceptedAvatarFile) transfer.items.add(acceptedAvatarFile);
        avatarInput.files = transfer.files;
      }
      if (cropImageUrl) URL.revokeObjectURL(cropImageUrl);
      cropImageUrl = null;
      cropImage?.removeAttribute("src");
    };

    avatarInput?.addEventListener("change", () => {
      const file = avatarInput.files?.[0];
      if (!file || !cropModal || !cropImage || !cropViewport) return;
      if (!["image/jpeg", "image/png", "image/webp"].includes(file.type)) {
        window.showToast("Choisissez une image JPG, PNG ou WEBP.", "error");
        closeCropModal(true);
        return;
      }
      if (file.size > 5 * 1024 * 1024) {
        window.showToast("La photo de profil doit peser au maximum 5 Mo.", "error");
        closeCropModal(true);
        return;
      }

      if (cropImageUrl) URL.revokeObjectURL(cropImageUrl);
      cropImageUrl = URL.createObjectURL(file);
      cropModal.hidden = false;
      cropModal.classList.add("show");
      cropModal.setAttribute("aria-hidden", "false");
      cropImage.onload = () => {
        const viewportSize = cropViewport.clientWidth;
        const baseScale = Math.max(viewportSize / cropImage.naturalWidth, viewportSize / cropImage.naturalHeight);
        cropDimensions = { width: cropImage.naturalWidth, height: cropImage.naturalHeight, baseScale };
        cropOffset = {
          x: (viewportSize - cropImage.naturalWidth * baseScale) / 2,
          y: (viewportSize - cropImage.naturalHeight * baseScale) / 2,
        };
        if (cropZoom) cropZoom.value = "1";
        setCropImagePosition();
        document.getElementById("cancelAvatarCropBtn")?.focus();
      };
      cropImage.onerror = () => {
        closeCropModal(true);
        window.showToast("Cette image ne peut pas être ouverte. Essayez un autre fichier.", "error");
      };
      cropImage.src = cropImageUrl;
    });

    cropZoom?.addEventListener("input", setCropImagePosition);
    cropViewport?.addEventListener("pointerdown", (event) => {
      if (!cropDimensions) return;
      dragPosition = { x: event.clientX - cropOffset.x, y: event.clientY - cropOffset.y };
      cropViewport.classList.add("is-dragging");
      cropViewport.setPointerCapture(event.pointerId);
    });
    cropViewport?.addEventListener("pointermove", (event) => {
      if (!dragPosition) return;
      cropOffset = { x: event.clientX - dragPosition.x, y: event.clientY - dragPosition.y };
      setCropImagePosition();
    });
    const stopCropDrag = () => {
      dragPosition = null;
      cropViewport?.classList.remove("is-dragging");
    };
    cropViewport?.addEventListener("pointerup", stopCropDrag);
    cropViewport?.addEventListener("pointercancel", stopCropDrag);
    document.getElementById("cancelAvatarCropBtn")?.addEventListener("click", () => closeCropModal(true));
    document.getElementById("closeAvatarCropBtn")?.addEventListener("click", () => closeCropModal(true));
    cropModal?.addEventListener("click", (event) => {
      if (event.target === cropModal) closeCropModal(true);
    });
    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape" && cropModal?.classList.contains("show")) {
        closeCropModal(true);
      }
    });

    applyCropButton?.addEventListener("click", () => {
      if (!cropDimensions || !cropImage || !avatarInput || !avatarPreview || !cropViewport) return;
      const outputSize = 512;
      const scale = cropDimensions.baseScale * Number(cropZoom?.value || 1);
      const cropSize = cropViewport.clientWidth / scale;
      const sourceX = -cropOffset.x / scale;
      const sourceY = -cropOffset.y / scale;
      const canvas = document.createElement("canvas");
      canvas.width = outputSize;
      canvas.height = outputSize;
      const context = canvas.getContext("2d");
      if (!context) {
        window.showToast("Le recadrage de cette photo est impossible sur cet appareil.", "error");
        return;
      }
      context.drawImage(cropImage, sourceX, sourceY, cropSize, cropSize, 0, 0, outputSize, outputSize);
      canvas.toBlob((blob) => {
        if (!blob) {
          window.showToast("La photo recadrée n’a pas pu être préparée.", "error");
          return;
        }
        acceptedAvatarFile = new File([blob], "avatar.jpg", { type: "image/jpeg", lastModified: Date.now() });
        const transfer = new DataTransfer();
        transfer.items.add(acceptedAvatarFile);
        avatarInput.files = transfer.files;
        if (avatarPreviewUrl) URL.revokeObjectURL(avatarPreviewUrl);
        avatarPreviewUrl = URL.createObjectURL(acceptedAvatarFile);
        avatarPreview.src = avatarPreviewUrl;
        avatarPreview.hidden = false;
        const initials = avatarPreview.parentElement?.querySelector(".profile-dynamic-initials");
        if (initials) initials.hidden = true;
        closeCropModal(false);
      }, "image/jpeg", 0.92);
    });

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

          document.querySelectorAll(".profile-avatar-image, .epi-avatar-image").forEach((image) => {
            if (data.user.avatar_url) {
              image.src = data.user.avatar_url;
              image.hidden = false;
            } else {
              image.removeAttribute("src");
              image.hidden = true;
            }
            const fallback = image.parentElement?.querySelector(".profile-dynamic-initials, .epi-avatar-fallback");
            if (fallback) fallback.hidden = Boolean(data.user.avatar_url);
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
      } catch (error) {
        window.showToast(error.message || "Impossible de joindre le serveur. Réessayez.", "error");
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
    quickPostForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const textarea = quickPostForm.querySelector("textarea[name='contenu']");
      const titleInput = quickPostForm.querySelector("input[name='titre']");
      const categorySelect = quickPostForm.querySelector("select[name='categorie_id']");

      const titre = titleInput ? titleInput.value.trim() : "";
      const contenu = textarea ? textarea.value.trim() : "";
      const categoryLabel = categorySelect?.selectedOptions[0]?.textContent.trim() || "Salon";
      const categoryId = categorySelect?.value || "";

      if (!titre || !contenu) return;

      const appendProfileDocuments = (uploadedAttachments) => {
        if (!uploadedAttachments.length) return;

        const documentTab = document.getElementById("tab-docs");
        if (!documentTab) return;

        let documentList = documentTab.querySelector(".profile-documents-list");
        if (!documentList) {
          documentList = document.createElement("div");
          documentList.className = "profile-documents-list";
          documentTab.querySelector(".profile-empty")?.remove();
          documentTab.append(documentList);
        }

        if (documentCategoryFilter && categoryId
          && !Array.from(documentCategoryFilter.options).some((option) => option.value === categoryId)) {
          const option = document.createElement("option");
          option.value = categoryId;
          option.textContent = categoryLabel;
          documentCategoryFilter.append(option);
        }

        const routeBase = new URL(quickPostForm.action, window.location.href)
          .pathname.replace(/\/publication\/create\/?$/, "");
        uploadedAttachments.forEach((attachment) => {
          const row = document.createElement("div");
          row.className = "profile-document-row";
          row.dataset.profileDocument = "";
          row.dataset.categoryId = categoryId;

          const details = document.createElement("div");
          const name = document.createElement("strong");
          name.textContent = attachment.name;
          const metadata = document.createElement("span");
          metadata.textContent = `${result.title || titre} · ${categoryLabel} · ${(attachment.size / 1048576).toFixed(2)} Mo`;
          details.append(name, metadata);
          if (result.moderationPending) {
            const moderation = document.createElement("small");
            moderation.className = "profile-status-badge is-warning";
            moderation.textContent = "Publication en modération";
            details.append(moderation);
          }

          const actions = document.createElement("div");
          actions.className = "profile-file-actions";
          if (String(attachment.type || "").startsWith("image/")) {
            const preview = document.createElement("a");
            preview.className = "btn btn-outline btn-sm";
            preview.href = attachment.download_url.replace("/attachment/download/", "/attachment/view/");
            preview.target = "_blank";
            preview.rel = "noopener";
            preview.textContent = "Voir la photo";
            actions.append(preview);
          }
          const download = document.createElement("a");
          download.className = "btn btn-outline btn-sm";
          download.href = attachment.download_url;
          download.textContent = "Télécharger";
          actions.append(download);

          const saveForm = document.createElement("form");
          saveForm.action = `${routeBase}/salon/save`;
          saveForm.method = "post";
          [["target_type", "attachment"], ["target_id", attachment.id],
            ["return_page", "profile"], ["return_tab", "docs"]].forEach(([name, value]) => {
            const input = document.createElement("input");
            input.type = "hidden";
            input.name = name;
            input.value = String(value);
            saveForm.append(input);
          });
          const saveButton = document.createElement("button");
          saveButton.className = "btn btn-outline btn-sm";
          saveButton.type = "submit";
          saveButton.textContent = "☆ Ajouter aux favoris";
          saveForm.append(saveButton);
          actions.append(saveForm);
          row.append(details, actions);
          documentList.prepend(row);
        });
        filterProfileDocuments();
      };

      const submitButton = quickPostForm.querySelector("button[type='submit']");
      if (submitButton) submitButton.disabled = true;

      const formData = new FormData(quickPostForm);
      formData.set("ajax", "1");
      try {
        const response = await fetch(quickPostForm.action, {
          method: "POST",
          headers: { "Accept": "application/json" },
          body: formData,
        });
        const contentType = response.headers.get("content-type") || "";
        if (!contentType.includes("application/json")) {
          throw new Error(response.redirected || response.url.includes("/login")
            ? "Votre session a expiré. Veuillez vous reconnecter."
            : "Le serveur a renvoyé une réponse inattendue.");
        }
        const result = await response.json();
        if (!response.ok || !result.success) {
          throw new Error(result.message || "La publication n'a pas pu être enregistrée.");
        }

        const escapeHtml = (value) => String(value).replace(/[&<>"']/g, (character) => ({
          "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;",
        }[character]));
        const userName = escapeHtml(document.querySelector(".profile-dynamic-name")?.textContent || "");
        const userInitials = escapeHtml(document.querySelector(".profile-dynamic-initials")?.textContent || "");
        const attachments = (result.attachments || []).map((attachment) =>
          `<a href="${escapeHtml(attachment.download_url)}" class="btn-post-action">📎 ${escapeHtml(attachment.name)}</a>`
        ).join("");

        // Création de la carte dans le DOM avec animation
        const postCard = document.createElement("article");
        postCard.className = "post-card animated-entry";
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
        <h4 class="post-title">${escapeHtml(result.title || titre)}</h4>
        <p class="post-excerpt">${escapeHtml(result.content || contenu)}</p>
        ${result.moderationPending ? '<p class="profile-moderation-note">Cette publication est conservée dans votre historique, mais n’est pas affichée dans le salon pendant son examen.</p>' : ""}
        <div class="post-footer-actions">
          ${attachments}
          <span class="profile-post-stat">♥ 0 likes</span>
          <span class="profile-post-stat">💬 0 réponses</span>
        </div>
      `;

        postsFeedContainer.prepend(postCard);
        filterProfilePosts();
        appendProfileDocuments(result.attachments || []);

        // Réinitialiser le formulaire
        quickPostForm.reset();

        // Incrémenter le compteur de sujets
        const statCounter = document.getElementById("statSubjectsCount");
        if (statCounter) {
          const currentCount = parseInt(statCounter.textContent, 10) || 0;
          statCounter.textContent = currentCount + 1;
        }
        const documentCounter = document.getElementById("statDocumentsCount");
        if (documentCounter && attachments) {
          const currentCount = parseInt(documentCounter.textContent, 10) || 0;
          documentCounter.textContent = currentCount + (result.attachments || []).length;
        }

        window.showToast("✓ Votre question a été publiée avec succès !", "success");
      } catch (error) {
        const attachmentInput = quickPostForm.querySelector("input[type='file']");
        if (attachmentInput) attachmentInput.value = "";
        window.showToast(error.message, "error");
      } finally {
        if (submitButton) submitButton.disabled = false;
      }
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
    toast.textContent = message;

    clearTimeout(window.toastTimer);
    window.toastTimer = setTimeout(() => {
      toast.classList.remove("show");
    }, 3500);
  };

});