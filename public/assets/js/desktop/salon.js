/**
 * =============================================================
 * public/assets/js/desktop/salon.js
 * Gestion Interactive du Salon Général – EPI Tchach
 * Zero-reload AJAX, Modals, Sondages réactifs, Pièces jointes
 * =============================================================
 */

document.addEventListener("DOMContentLoaded", () => {
  const baseUrl = document.body.dataset.baseUrl || "";

  // Helper Toast Feedback si disponible
  const showToast = (message, type = "success") => {
    if (typeof window.showFeedbackToast === "function") {
      window.showFeedbackToast(message, type);
    } else {
      console.log(`[Toast ${type}] ${message}`);
    }
  };

  // Helper Échappement HTML sécurisé
  const escapeHtml = (value) =>
    String(value ?? "").replace(/[&<>"']/g, (char) => ({
      "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;",
    }[char]));

  const formatFileSize = (bytes) => `${(bytes / 1048576).toFixed(2)} Mo`;

  // Formatage de date relative
  const formatTimeRelative = (dateString) => {
    try {
      const date = new Date(dateString);
      const diffSec = Math.max(0, Math.floor((Date.now() - date.getTime()) / 1000));
      if (diffSec < 60) return "À l'instant";
      if (diffSec < 3600) return `Il y a ${Math.floor(diffSec / 60)} min`;
      if (diffSec < 86400) return `Il y a ${Math.floor(diffSec / 3600)} h`;
      if (diffSec < 604800) return `Il y a ${Math.floor(diffSec / 86400)} j`;
      return date.toLocaleDateString("fr-FR");
    } catch {
      return dateString;
    }
  };

  // Envoi AJAX avec suivi de progression pour gros uploads
  const sendAjaxWithProgress = (url, formData, onProgress) =>
    new Promise((resolve, reject) => {
      const xhr = new XMLHttpRequest();
      xhr.open("POST", url);
      xhr.setRequestHeader("Accept", "application/json");
      xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");

      if (xhr.upload && onProgress) {
        xhr.upload.addEventListener("progress", (e) => {
          if (e.lengthComputable) {
            onProgress(Math.round((e.loaded / e.total) * 100));
          }
        });
      }

      xhr.addEventListener("load", () => {
        const contentType = xhr.getResponseHeader("content-type") || "";
        if (!contentType.includes("application/json")) {
          if (xhr.responseURL && xhr.responseURL.includes("/login")) {
            reject(new Error("Votre session a expiré. Veuillez vous reconnecter."));
          } else {
            reject(new Error("Réponse inattendue du serveur."));
          }
          return;
        }

        let data;
        try {
          data = JSON.parse(xhr.responseText);
        } catch {
          reject(new Error("Format de réponse JSON invalide."));
          return;
        }

        if (xhr.status < 200 || xhr.status >= 300 || !data.success) {
          reject(new Error(data.message || "Une erreur est survenue lors de l'envoi."));
          return;
        }

        resolve(data);
      });

      xhr.addEventListener("error", () =>
        reject(new Error("Impossible de contacter le serveur."))
      );
      xhr.send(formData);
    });

  // =========================================================
  // 1. GESTION DE LA PRÉSENCE (EN LIGNE)
  // =========================================================
  const updatePresence = async () => {
    try {
      await fetch(`${baseUrl}/salon/presence`, {
        method: "POST",
        headers: { "X-Requested-With": "XMLHttpRequest", Accept: "application/json" },
      });
    } catch {
      // Échec discret
    }
  };

  setInterval(() => {
    if (document.visibilityState === "visible") updatePresence();
  }, 60000);
  document.addEventListener("visibilitychange", () => {
    if (document.visibilityState === "visible") updatePresence();
  });

  // =========================================================
  // 2. MODAL DE PUBLICATION (POST COMPOSER)
  // =========================================================
  const postComposerModal = document.getElementById("postComposerModal");
  const postForm = document.getElementById("salonPostForm");
  const btnClosePostModal = document.getElementById("btnClosePostModal");
  const btnCancelPostModal = document.getElementById("btnCancelPostModal");
  const btnOpenComposerModal = document.getElementById("btnOpenComposerModal");
  const btnQuickPublish = document.getElementById("btnQuickPublish");
  const btnEmptyCreatePost = document.getElementById("btnEmptyCreatePost");
  const publicationTypeInput = document.getElementById("publicationType");
  const modalCategorySelect = document.getElementById("modalCategorySelect");
  const pollOptionsFields = document.getElementById("pollOptionsFields");
  const pollOptionsList = document.getElementById("pollOptionsList");
  const addPollOptionBtn = document.getElementById("addPollOption");
  const attachmentInput = document.getElementById("salonPostAttachments");
  const attachmentPreview = document.getElementById("salonAttachmentPreview");
  const postFeedback = document.getElementById("salonPostFeedback");
  const submitPostBtn = document.getElementById("btnSubmitPost");
  const modalTypeTabs = document.querySelectorAll(".modal-type-tab");
  const previewObjectUrls = new Set();

  const openComposerModal = (targetType = "discussion", focusAttachments = false) => {
    if (!postComposerModal) return;
    postComposerModal.hidden = false;
    postComposerModal.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";

    setTypeTab(targetType);

    if (focusAttachments && attachmentInput) {
      setTimeout(() => attachmentInput.click(), 100);
    } else {
      setTimeout(() => {
        document.getElementById("salonPostTitle")?.focus();
      }, 100);
    }
  };

  const closeComposerModal = () => {
    if (!postComposerModal) return;
    postComposerModal.hidden = true;
    postComposerModal.setAttribute("aria-hidden", "true");
    document.body.style.overflow = "";
    if (postFeedback) postFeedback.textContent = "";
    if (typeof closeTagDropdown === "function") closeTagDropdown();
    if (typeof closeMentionDropdown === "function") closeMentionDropdown();
  };

  const setTypeTab = (type) => {
    if (!publicationTypeInput) return;
    publicationTypeInput.value = type;

    modalTypeTabs.forEach((tab) => {
      const isTarget = tab.dataset.typeTarget === type;
      tab.classList.toggle("is-active", isTarget);
      tab.setAttribute("aria-selected", isTarget ? "true" : "false");
    });

    const isPoll = type === "question";
    const isAnnonce = type === "annonce";
    const isDiscussion = type === "discussion";

    // 1. Gestion des champs de choix de sondage : STRICTEMENT réservés au type "question"
    if (pollOptionsFields) {
      pollOptionsFields.hidden = !isPoll;
      pollOptionsFields.classList.toggle("is-hidden", !isPoll);
      if (isPoll) {
        pollOptionsFields.style.setProperty("display", "flex", "important");
      } else {
        pollOptionsFields.style.setProperty("display", "none", "important");
      }
      pollOptionsFields.querySelectorAll("input").forEach((inp) => {
        inp.required = isPoll;
        inp.disabled = !isPoll;
        if (!isPoll) inp.value = "";
      });
    }

    // 2. Gestion de la bannière d'annonce officielle
    const annonceBanner = document.getElementById("annonceOfficialBanner");
    if (annonceBanner) {
      annonceBanner.hidden = !isAnnonce;
      annonceBanner.style.setProperty("display", isAnnonce ? "flex" : "none", "important");
    }

    // 3. Gestion dynamique des pièces jointes (masquées pour sondage pur, actives pour discussions et annonces)
    const attachmentsGroup = document.getElementById("composerAttachmentsGroup");
    if (attachmentsGroup) {
      attachmentsGroup.hidden = isPoll;
      attachmentsGroup.style.setProperty("display", isPoll ? "none" : "block", "important");
    }

    // 4. Adaptation des libellés et placeholders
    const heading = document.getElementById("postModalHeading");
    const titleLabel = document.querySelector("label[for='salonPostTitle']");
    const titleInput = document.getElementById("salonPostTitle");
    const contentLabel = document.getElementById("salonContentLabel");
    const contentTextarea = document.getElementById("salonPostContent");

    if (heading) {
      if (isPoll) heading.textContent = "Créer un sondage";
      else if (isAnnonce) heading.textContent = "Publier une annonce officielle";
      else heading.textContent = "Créer une publication";
    }

    if (titleLabel && titleInput) {
      if (isPoll) {
        titleLabel.textContent = "Question du sondage";
        titleInput.placeholder = "Ex: Quelle date pour la session de révision ?";
      } else if (isAnnonce) {
        titleLabel.textContent = "Objet de l'annonce officielle";
        titleInput.placeholder = "Ex: Planning des soutenances / Rattrapage du devoir...";
      } else {
        titleLabel.textContent = "Titre de la publication";
        titleInput.placeholder = "Ex: Exercice d’algorithmique sur les graphes / Fiche de cours...";
      }
    }

    if (contentLabel) {
      if (isPoll) contentLabel.textContent = "Précisions ou consignes (optionnel)";
      else if (isAnnonce) contentLabel.textContent = "Texte officiel de l'annonce";
      else contentLabel.textContent = "Message";
    }

    if (contentTextarea) {
      if (isPoll) {
        contentTextarea.required = false;
        contentTextarea.placeholder = "Ajoutez un contexte, des explications ou la date limite du vote (optionnel)...";
      } else if (isAnnonce) {
        contentTextarea.required = true;
        contentTextarea.placeholder = "Rédigez le texte officiel qui sera notifié et épinglé pour votre promotion...";
      } else {
        contentTextarea.required = true;
        contentTextarea.placeholder = "Détaillez votre question, partagez une ressource ou expliquez votre démarche...";
      }
    }
  };

  // Écouteurs d'ouverture du modal de publication
  const btnSidebarCreatePost = document.getElementById("btnSidebarCreatePost");
  btnSidebarCreatePost?.addEventListener("click", () => openComposerModal("discussion"));
  btnOpenComposerModal?.addEventListener("click", () => openComposerModal("discussion"));
  btnQuickPublish?.addEventListener("click", () => openComposerModal("discussion"));
  btnEmptyCreatePost?.addEventListener("click", () => openComposerModal("discussion"));

  document.querySelectorAll("[data-open-composer-type]").forEach((btn) => {
    btn.addEventListener("click", () => {
      const type = btn.dataset.openComposerType || "discussion";
      const focusAtt = btn.dataset.focusAttachments === "1";
      openComposerModal(type, focusAtt);
    });
  });

  btnClosePostModal?.addEventListener("click", closeComposerModal);
  btnCancelPostModal?.addEventListener("click", closeComposerModal);

  // Clic sur l'arrière-plan du modal pour fermer
  postComposerModal?.addEventListener("click", (e) => {
    if (e.target === postComposerModal) closeComposerModal();
  });

  // Changement d'onglet de type
  modalTypeTabs.forEach((tab) => {
    tab.addEventListener("click", () => {
      const targetType = tab.dataset.typeTarget;
      // Vérifier si annonce autorisée sur le salon sélectionné
      if (targetType === "annonce" && modalCategorySelect) {
        const selectedOpt = modalCategorySelect.selectedOptions[0];
        if (selectedOpt && selectedOpt.dataset.canAnnounce !== "1") {
          showToast("Seuls les délégués et l'administration peuvent publier une annonce dans ce salon.", "error");
          return;
        }
      }
      setTypeTab(targetType);
    });
  });

  modalCategorySelect?.addEventListener("change", () => {
    const selectedOpt = modalCategorySelect.selectedOptions[0];
    const canAnnounce = selectedOpt && selectedOpt.dataset.canAnnounce === "1";
    const annonceTab = document.getElementById("modalAnnonceTab");

    if (annonceTab) {
      if (!canAnnounce && publicationTypeInput.value === "annonce") {
        setTypeTab("discussion");
      }
      annonceTab.style.opacity = canAnnounce ? "1" : "0.5";
      annonceTab.title = canAnnounce ? "" : "Non autorisé dans ce salon";
    }
  });

  // Ajout dynamique d'options de sondage
  addPollOptionBtn?.addEventListener("click", () => {
    if (!pollOptionsList) return;
    const currentCount = pollOptionsList.querySelectorAll("input").length;
    if (currentCount >= 10) {
      showToast("Un sondage ne peut comporter que 10 choix au maximum.", "warning");
      return;
    }

    const nextIndex = currentCount + 1;
    const row = document.createElement("div");
    row.className = "poll-input-row";
    row.innerHTML = `
      <span class="poll-input-num">${nextIndex}</span>
      <input class="form-control" name="options[]" maxlength="255" placeholder="Choix ${nextIndex}" aria-label="Choix ${nextIndex}" required>
      <button type="button" class="btn-remove-poll-opt" aria-label="Supprimer ce choix" style="background:none; border:none; color:var(--danger); cursor:pointer; font-size:1.1rem; padding:0 0.4rem;">&times;</button>
    `;
    row.querySelector(".btn-remove-poll-opt")?.addEventListener("click", () => {
      row.remove();
      // Réindexer
      pollOptionsList.querySelectorAll(".poll-input-row").forEach((r, idx) => {
        const num = r.querySelector(".poll-input-num");
        if (num) num.textContent = String(idx + 1);
      });
    });
    pollOptionsList.appendChild(row);
    row.querySelector("input")?.focus();
  });

  // Prévisualisation des pièces jointes sélectionnées
  const renderAttachmentPreviews = () => {
    if (!attachmentInput || !attachmentPreview) return;
    previewObjectUrls.forEach((url) => URL.revokeObjectURL(url));
    previewObjectUrls.clear();
    attachmentPreview.replaceChildren();

    const files = Array.from(attachmentInput.files || []);
    attachmentPreview.hidden = (files.length === 0);
    if (!files.length) return;

    files.forEach((file, index) => {
      const item = document.createElement("div");
      item.className = "salon-attachment-preview-item";

      if (file.type.startsWith("image/")) {
        const img = document.createElement("img");
        const objUrl = URL.createObjectURL(file);
        previewObjectUrls.add(objUrl);
        img.src = objUrl;
        img.alt = file.name;
        item.append(img);
      } else {
        const icon = document.createElement("span");
        icon.style.fontWeight = "800";
        icon.style.color = "var(--primary)";
        icon.textContent = file.name.split(".").pop()?.toUpperCase().slice(0, 4) || "DOC";
        item.append(icon);
      }

      const info = document.createElement("div");
      info.style.display = "flex";
      info.style.flexDirection = "column";
      info.style.minWidth = "0";

      const name = document.createElement("strong");
      name.textContent = file.name;
      name.style.whiteSpace = "nowrap";
      name.style.overflow = "hidden";
      name.style.textOverflow = "ellipsis";
      name.style.maxWidth = "160px";

      const size = document.createElement("small");
      size.textContent = formatFileSize(file.size);
      size.style.color = "var(--text-muted)";
      info.append(name, size);

      const removeBtn = document.createElement("button");
      removeBtn.type = "button";
      removeBtn.className = "salon-attachment-remove";
      removeBtn.innerHTML = "&times;";
      removeBtn.title = `Retirer ${file.name}`;
      removeBtn.addEventListener("click", () => {
        const dt = new DataTransfer();
        Array.from(attachmentInput.files || []).forEach((f, idx) => {
          if (idx !== index) dt.items.add(f);
        });
        attachmentInput.files = dt.files;
        renderAttachmentPreviews();
      });

      item.append(info, removeBtn);
      attachmentPreview.append(item);
    });
  };

  attachmentInput?.addEventListener("change", () => {
    const files = Array.from(attachmentInput.files || []);
    if (files.length > 5) {
      showToast("Vous ne pouvez sélectionner que 5 fichiers au maximum.", "warning");
      const dt = new DataTransfer();
      files.slice(0, 5).forEach((f) => dt.items.add(f));
      attachmentInput.files = dt.files;
    }
    renderAttachmentPreviews();
  });

  // =========================================================
  // GESTION POPUP DES TAGS & SUGGESTIONS INTELLIGENTES
  // =========================================================
  const tagPickerInput = document.getElementById("tagPickerInput");
  const btnToggleTagDropdown = document.getElementById("btnToggleTagDropdown");
  const tagPickerDropdown = document.getElementById("tagPickerDropdown");
  const tagSuggestionItem = document.getElementById("tagSuggestionItem");
  const tagSuggestionLabel = document.getElementById("tagSuggestionLabel");
  const tagPickerList = document.getElementById("tagPickerList");
  const tagSelectedChips = document.getElementById("tagSelectedChips");
  const tagHiddenInputsContainer = document.getElementById("tagHiddenInputsContainer");
  const publicationTagProposals = document.getElementById("publicationTagProposals");

  const selectedExistingTags = new Map(); // id -> name
  const proposedTagsSet = new Set(); // string name

  const updateTagProposalsInput = () => {
    if (publicationTagProposals) {
      publicationTagProposals.value = Array.from(proposedTagsSet).join(",");
    }
  };

  const renderTagChips = () => {
    if (!tagSelectedChips || !tagHiddenInputsContainer) return;
    tagSelectedChips.replaceChildren();
    tagHiddenInputsContainer.replaceChildren();

    // 1. Tags existants sélectionnés
    selectedExistingTags.forEach((name, id) => {
      const chip = document.createElement("span");
      chip.className = "interactive-chip is-tag-chip";
      chip.innerHTML = `
        <span>#${escapeHtml(name)}</span>
        <button type="button" class="interactive-chip-remove" aria-label="Retirer le tag #${escapeHtml(name)}">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      `;
      chip.querySelector("button")?.addEventListener("click", () => {
        selectedExistingTags.delete(id);
        const badge = tagPickerList?.querySelector(`[data-tag-id="${id}"]`);
        if (badge) badge.classList.remove("is-selected");
        renderTagChips();
      });

      const hiddenInp = document.createElement("input");
      hiddenInp.type = "hidden";
      hiddenInp.name = "tag_ids[]";
      hiddenInp.value = String(id);
      tagHiddenInputsContainer.appendChild(hiddenInp);

      tagSelectedChips.appendChild(chip);
    });

    // 2. Tags nouveaux proposés
    proposedTagsSet.forEach((name) => {
      const chip = document.createElement("span");
      chip.className = "interactive-chip is-proposed-chip";
      chip.innerHTML = `
        <span>+ #${escapeHtml(name)} (nouveau)</span>
        <button type="button" class="interactive-chip-remove" aria-label="Retirer la proposition #${escapeHtml(name)}">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      `;
      chip.querySelector("button")?.addEventListener("click", () => {
        proposedTagsSet.delete(name);
        updateTagProposalsInput();
        renderTagChips();
      });
      tagSelectedChips.appendChild(chip);
    });

    updateTagProposalsInput();
  };

  const openTagDropdown = () => {
    if (tagPickerDropdown) tagPickerDropdown.hidden = false;
  };

  const closeTagDropdown = () => {
    if (tagPickerDropdown) tagPickerDropdown.hidden = true;
  };

  btnToggleTagDropdown?.addEventListener("click", (e) => {
    e.stopPropagation();
    if (tagPickerDropdown) {
      tagPickerDropdown.hidden = !tagPickerDropdown.hidden;
    }
  });

  tagPickerInput?.addEventListener("focus", openTagDropdown);

  tagPickerInput?.addEventListener("input", () => {
    openTagDropdown();
    const rawVal = tagPickerInput.value.trim().replace(/^#+/, "");
    const filterText = rawVal.toLowerCase();

    let matchedExact = false;
    let visibleCount = 0;

    tagPickerList?.querySelectorAll(".tag-picker-badge").forEach((badge) => {
      const name = (badge.dataset.tagName || "").toLowerCase();
      if (name === filterText) matchedExact = true;
      const matches = !filterText || name.includes(filterText);
      badge.hidden = !matches;
      if (matches) visibleCount++;
    });

    if (tagSuggestionItem && tagSuggestionLabel) {
      if (rawVal.length >= 2 && !matchedExact && !proposedTagsSet.has(rawVal)) {
        tagSuggestionLabel.textContent = `#${rawVal}`;
        tagSuggestionItem.hidden = false;
      } else {
        tagSuggestionItem.hidden = true;
      }
    }
  });

  // Clic sur un tag existant dans la liste
  tagPickerList?.addEventListener("click", (e) => {
    const badge = e.target.closest(".tag-picker-badge");
    if (!badge) return;
    const tagId = Number(badge.dataset.tagId);
    const tagName = badge.dataset.tagName || "";

    if (selectedExistingTags.has(tagId)) {
      selectedExistingTags.delete(tagId);
      badge.classList.remove("is-selected");
    } else {
      if (selectedExistingTags.size + proposedTagsSet.size >= 10) {
        showToast("Maximum 10 tags par publication.", "warning");
        return;
      }
      selectedExistingTags.set(tagId, tagName);
      badge.classList.add("is-selected");
    }
    renderTagChips();
  });

  // Clic sur l'élément de suggestion d'un nouveau tag
  tagSuggestionItem?.addEventListener("click", () => {
    const rawVal = tagPickerInput?.value.trim().replace(/^#+/, "");
    if (!rawVal || rawVal.length < 2) return;
    if (proposedTagsSet.size >= 5) {
      showToast("Vous pouvez proposer au maximum 5 nouveaux tags.", "warning");
      return;
    }
    if (selectedExistingTags.size + proposedTagsSet.size >= 10) {
      showToast("Maximum 10 tags au total par publication.", "warning");
      return;
    }

    proposedTagsSet.add(rawVal);
    renderTagChips();
    if (tagPickerInput) tagPickerInput.value = "";
    tagSuggestionItem.hidden = true;
    tagPickerList?.querySelectorAll(".tag-picker-badge").forEach((b) => (b.hidden = false));
  });

  // =========================================================
  // GESTION POPUP DES MENTIONS & PROFESSEURS
  // =========================================================
  const mentionPickerInput = document.getElementById("mentionPickerInput");
  const btnToggleMentionDropdown = document.getElementById("btnToggleMentionDropdown");
  const mentionPickerDropdown = document.getElementById("mentionPickerDropdown");
  const mentionPickerList = document.getElementById("mentionPickerList");
  const mentionSelectedChips = document.getElementById("mentionSelectedChips");
  const mentionHiddenInputsContainer = document.getElementById("mentionHiddenInputsContainer");

  const selectedMentionsMap = new Map(); // id -> { id, prenom, nom, role }
  let activeMentionFilter = "all";
  let mentionTimer = null;
  let cachedMentionUsers = [];

  const renderMentionChips = () => {
    if (!mentionSelectedChips || !mentionHiddenInputsContainer) return;
    mentionSelectedChips.replaceChildren();
    mentionHiddenInputsContainer.replaceChildren();

    selectedMentionsMap.forEach((user) => {
      const chip = document.createElement("span");
      const isProf = user.role === "professeur";
      chip.className = `interactive-chip is-mention-chip ${isProf ? "is-prof-chip" : ""}`;
      chip.innerHTML = `
        <span>@${escapeHtml(user.prenom)} ${escapeHtml(user.nom)}${isProf ? " (Prof)" : ""}</span>
        <button type="button" class="interactive-chip-remove" aria-label="Retirer @${escapeHtml(user.prenom)} ${escapeHtml(user.nom)}">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      `;
      chip.querySelector("button")?.addEventListener("click", () => {
        selectedMentionsMap.delete(user.id);
        const itemBtn = mentionPickerList?.querySelector(`[data-user-id="${user.id}"]`);
        if (itemBtn) itemBtn.classList.remove("is-selected");
        renderMentionChips();
      });

      const hiddenInp = document.createElement("input");
      hiddenInp.type = "hidden";
      hiddenInp.name = "mentioned_users[]";
      hiddenInp.value = String(user.id);
      mentionHiddenInputsContainer.appendChild(hiddenInp);

      mentionSelectedChips.appendChild(chip);
    });
  };

  const renderMentionUsersList = (users) => {
    if (!mentionPickerList) return;
    cachedMentionUsers = users;

    const filtered = users.filter((u) => {
      if (activeMentionFilter === "professeur") return u.role === "professeur";
      if (activeMentionFilter === "delegation") return ["delegue_general", "delegue_de_classe", "moderateur"].includes(u.role);
      return true;
    });

    if (filtered.length === 0) {
      mentionPickerList.innerHTML = '<div class="picker-dropdown-empty">Aucun résultat trouvé.</div>';
      return;
    }

    mentionPickerList.innerHTML = filtered.map((u) => {
      const isSelected = selectedMentionsMap.has(Number(u.id));
      const initials = escapeHtml(((u.prenom?.[0] || 'E') + (u.nom?.[0] || 'P')).toUpperCase());
      const role = u.role || 'etudiant';
      let roleBadgeClass = 'role-badge-etudiant';
      let roleLabel = 'Étudiant';
      if (role === 'professeur') { roleBadgeClass = 'role-badge-professeur'; roleLabel = 'Professeur'; }
      else if (['delegue_general', 'delegue_de_classe', 'moderateur'].includes(role)) { roleBadgeClass = 'role-badge-delegation'; roleLabel = 'Délégué'; }

      return `
        <button type="button" class="picker-user-item ${isSelected ? 'is-selected' : ''}" data-user-id="${u.id}" data-user-prenom="${escapeHtml(u.prenom)}" data-user-nom="${escapeHtml(u.nom)}" data-user-role="${escapeHtml(role)}">
          <div class="picker-user-info">
            <div class="picker-user-avatar">${initials}</div>
            <div class="picker-user-name">${escapeHtml(u.prenom)} ${escapeHtml(u.nom)}</div>
          </div>
          <span class="picker-user-role-badge ${roleBadgeClass}">${roleLabel}</span>
        </button>
      `;
    }).join('');
  };

  const fetchMentionUsers = async (q = "") => {
    const catId = Number(modalCategorySelect?.value || 0);
    if (catId <= 0 || !mentionPickerList) return;

    mentionPickerList.innerHTML = `
      <div class="picker-dropdown-loading">
        <div class="loader-circle" style="width:16px; height:16px;"></div>
        <span>Recherche des professeurs et membres…</span>
      </div>
    `;

    try {
      const res = await fetch(`${baseUrl}/api/mentions/search?category_id=${catId}&q=${encodeURIComponent(q)}`, {
        headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" }
      });
      const data = await res.json();
      if (res.ok && data.success && Array.isArray(data.users)) {
        renderMentionUsersList(data.users);
      } else {
        mentionPickerList.innerHTML = '<div class="picker-dropdown-empty">Aucun résultat.</div>';
      }
    } catch {
      mentionPickerList.innerHTML = '<div class="picker-dropdown-empty" style="color:var(--danger);">Erreur de chargement.</div>';
    }
  };

  const openMentionDropdown = () => {
    if (mentionPickerDropdown) mentionPickerDropdown.hidden = false;
    if (cachedMentionUsers.length === 0) {
      fetchMentionUsers("");
    }
  };

  const closeMentionDropdown = () => {
    if (mentionPickerDropdown) mentionPickerDropdown.hidden = true;
  };

  btnToggleMentionDropdown?.addEventListener("click", (e) => {
    e.stopPropagation();
    if (mentionPickerDropdown) {
      const willOpen = mentionPickerDropdown.hidden;
      mentionPickerDropdown.hidden = !willOpen;
      if (willOpen && cachedMentionUsers.length === 0) fetchMentionUsers("");
    }
  });

  mentionPickerInput?.addEventListener("focus", openMentionDropdown);

  mentionPickerInput?.addEventListener("input", () => {
    openMentionDropdown();
    clearTimeout(mentionTimer);
    mentionTimer = setTimeout(() => {
      fetchMentionUsers(mentionPickerInput.value.trim());
    }, 200);
  });

  // Onglets de filtre de mentions
  document.querySelectorAll(".mention-filter-tab").forEach((tab) => {
    tab.addEventListener("click", () => {
      document.querySelectorAll(".mention-filter-tab").forEach((t) => t.classList.remove("is-active"));
      tab.classList.add("is-active");
      activeMentionFilter = tab.dataset.mentionFilter || "all";
      renderMentionUsersList(cachedMentionUsers);
    });
  });

  // Sélection d'une personne dans la liste
  mentionPickerList?.addEventListener("click", (e) => {
    const itemBtn = e.target.closest(".picker-user-item");
    if (!itemBtn) return;
    const uid = Number(itemBtn.dataset.userId);
    const prenom = itemBtn.dataset.userPrenom || "";
    const nom = itemBtn.dataset.userNom || "";
    const role = itemBtn.dataset.userRole || "";

    if (selectedMentionsMap.has(uid)) {
      selectedMentionsMap.delete(uid);
      itemBtn.classList.remove("is-selected");
    } else {
      if (selectedMentionsMap.size >= 10) {
        showToast("Maximum 10 mentions par publication.", "warning");
        return;
      }
      selectedMentionsMap.set(uid, { id: uid, prenom, nom, role });
      itemBtn.classList.add("is-selected");
    }
    renderMentionChips();
  });

  // Fermeture des popups de tags et mentions au clic extérieur
  document.addEventListener("click", (e) => {
    const tagGroup = document.getElementById("tagPickerGroup");
    if (tagGroup && !tagGroup.contains(e.target)) {
      closeTagDropdown();
    }
    const mentionGroup = document.getElementById("mentionPickerGroup");
    if (mentionGroup && !mentionGroup.contains(e.target)) {
      closeMentionDropdown();
    }
  });

  // Rechargement fluide du flux sans rechargement de page
  const refreshPublicationList = async (targetUrl) => {
    try {
      const res = await fetch(targetUrl, { headers: { Accept: "text/html" } });
      if (!res.ok) throw new Error();
      const htmlText = await res.text();
      const parser = new DOMParser();
      const doc = parser.parseFromString(htmlText, "text/html");

      const newPubList = doc.getElementById("publicationList");
      const curPubList = document.getElementById("publicationList");
      if (newPubList && curPubList) {
        curPubList.replaceChildren(...Array.from(newPubList.childNodes));
      }

      const newTabs = doc.querySelector(".feed-tabs-row");
      const curTabs = document.querySelector(".feed-tabs-row");
      if (newTabs && curTabs) curTabs.replaceWith(newTabs);

      window.history.replaceState({}, "", targetUrl);
    } catch {
      window.location.assign(targetUrl);
    }
  };

  // Soumission AJAX du formulaire de publication
  if (postForm) {
    postForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      if (submitPostBtn) submitPostBtn.disabled = true;
      if (postFeedback) postFeedback.textContent = "";

      const spinner = submitPostBtn?.querySelector(".submit-spinner");
      const btnText = submitPostBtn?.querySelector(".btn-text");
      if (spinner) spinner.hidden = false;
      if (btnText) btnText.textContent = "Publication…";

      try {
        const formData = new FormData(postForm);
        formData.set("ajax", "1");

        const hasFiles = Array.from(attachmentInput?.files || []).length > 0;
        const result = await sendAjaxWithProgress(postForm.action, formData, (percent) => {
          if (postFeedback && hasFiles) {
            postFeedback.textContent = `Téléversement des fichiers… ${percent}%`;
          }
        });

        showToast(result.message || "Publication créée avec succès !", "success");
        closeComposerModal();

        // Réinitialisation du formulaire
        postForm.reset();
        selectedExistingTags.clear();
        proposedTagsSet.clear();
        renderTagChips();
        selectedMentionsMap.clear();
        renderMentionChips();
        renderAttachmentPreviews();
        setTypeTab("discussion");

        // Actualisation fluide de la liste
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set("view", "latest");
        if (result.categoryId) currentUrl.searchParams.set("category", String(result.categoryId));
        refreshPublicationList(currentUrl.toString());

      } catch (err) {
        if (postFeedback) postFeedback.textContent = err.message || "Impossible de publier.";
        showToast(err.message || "Une erreur est survenue.", "error");
      } finally {
        if (submitPostBtn) submitPostBtn.disabled = false;
        if (spinner) spinner.hidden = true;
        if (btnText) btnText.textContent = "Publier";
      }
    });
  }

  // =========================================================
  // 3. MODAL DE DISCUSSION COMPLET
  // =========================================================
  const discussionModal = document.getElementById("discussionModal");
  const discussionModalTitle = document.getElementById("discussionModalTitle");
  const discussionModalContent = document.getElementById("discussionModalContent");
  let activeDiscussionId = 0;

  const closeDiscussionModal = (updateHistory = true) => {
    if (!discussionModal) return;
    activeDiscussionId = 0;
    discussionModal.hidden = true;
    discussionModal.setAttribute("aria-hidden", "true");
    document.body.style.overflow = "";

    if (updateHistory) {
      const url = new URL(window.location.href);
      url.searchParams.delete("pub");
      url.searchParams.delete("discussion");
      window.history.replaceState({}, "", url.toString());
    }
  };

  const renderDiscussionData = (data) => {
    if (!discussionModalContent) return;
    const pub = data.publication;
    const atts = data.attachments || [];
    const comments = data.comments || [];

    if (discussionModalTitle) {
      discussionModalTitle.textContent = pub.titre;
    }

    // Images & Pièces jointes de la publication
    let attachmentsHtml = "";
    if (atts.length > 0) {
      const imgs = atts.filter((a) => (a.mime || a.type_mime || "").startsWith("image/"));
      const docs = atts.filter((a) => !(a.mime || a.type_mime || "").startsWith("image/"));

      let imgsHtml = "";
      if (imgs.length > 0) {
        imgsHtml = `
          <div class="attachments-gallery-grid count-${Math.min(4, imgs.length)}" style="margin-top:0.5rem;">
            ${imgs.map((img) => `
              <a href="${escapeHtml(img.view_url || img.url || '#')}" target="_blank" rel="noopener" class="gallery-item-link">
                <img src="${escapeHtml(img.view_url || img.url || '#')}" alt="${escapeHtml(img.filename || img.nom_fichier || '')}" class="gallery-thumb" loading="lazy">
              </a>
            `).join("")}
          </div>
        `;
      }

      let docsHtml = "";
      if (docs.length > 0) {
        docsHtml = `
          <div class="attachments-files-list" style="margin-top:0.5rem;">
            ${docs.map((doc) => `
              <div class="file-attachment-card">
                <div class="file-icon-box is-generic">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
                <div class="file-details">
                  <span class="file-name">${escapeHtml(doc.filename || doc.nom_fichier || 'Document')}</span>
                  <span class="file-size">${formatFileSize(Number(doc.size || doc.taille || 0))}</span>
                </div>
                <a href="${escapeHtml(doc.download_url || doc.url || '#')}" class="btn-file-icon is-download" title="Télécharger">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                </a>
              </div>
            `).join("")}
          </div>
        `;
      }

      attachmentsHtml = imgsHtml + docsHtml;
    }

    // Commentaires
    const commentsHtml = comments.length > 0
      ? comments.map((c) => {
          const authorInitials = (c.prenom?.[0] || 'E') + (c.nom?.[0] || 'P');
          const isLiked = Number(c.user_liked) === 1;
          const isSol = Number(c.est_solution) === 1;
          return `
            <article class="comment-card ${isSol ? 'is-solution' : ''}" id="comment-${c.id}">
              <div class="comment-header">
                <div class="comment-author-box">
                  <div class="comment-avatar">${escapeHtml(authorInitials.toUpperCase())}</div>
                  <div>
                    <strong class="comment-author-name">${escapeHtml(c.prenom)} ${escapeHtml(c.nom)}</strong>
                    <div style="font-size:0.72rem; color:var(--text-muted);">${formatTimeRelative(c.date_creation)}</div>
                  </div>
                </div>
                ${isSol ? '<span class="comment-solution-badge">✓ Solution validée</span>' : ''}
              </div>
              <p class="comment-content">${escapeHtml(c.contenu).replace(/\n/g, '<br>')}</p>
              <div class="comment-actions-bar">
                <button type="button" class="btn-interaction btn-like-action ${isLiked ? 'is-liked' : ''}"
                        data-toggle-reaction data-target-type="commentaire" data-target-id="${c.id}"
                        aria-pressed="${isLiked}">
                  <svg class="heart-icon" width="14" height="14" viewBox="0 0 24 24" fill="${isLiked ? 'currentColor' : 'none'}" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                  <span class="like-counter-text">${Number(c.likes_count) || 0}</span>
                </button>
              </div>
            </article>
          `;
        }).join("")
      : '<p style="text-align:center; padding:1.5rem; color:var(--text-muted); font-size:0.88rem;">Aucune réponse pour le moment. Soyez le premier à répondre !</p>';

    const canComment = Number(pub.commentaires_fermes || 0) === 0;

    discussionModalContent.innerHTML = `
      <!-- Publication principale -->
      <section class="discussion-main-post-card">
        <div class="post-header" style="margin-bottom:0.25rem;">
          <div class="post-author-box">
            <div class="author-avatar">${escapeHtml(((pub.prenom?.[0] || 'E') + (pub.nom?.[0] || 'P')).toUpperCase())}</div>
            <div class="author-details">
              <strong class="author-name">${escapeHtml(pub.prenom)} ${escapeHtml(pub.nom)}</strong>
              <div class="post-meta-subline">
                <span>${escapeHtml(pub.zone_name)} · #${escapeHtml(pub.category_name)}</span>
                <span class="meta-dot">·</span>
                <time>${formatTimeRelative(pub.date_creation)}</time>
              </div>
            </div>
          </div>
        </div>
        <h3 class="discussion-main-post-title">${escapeHtml(pub.titre)}</h3>
        <div class="discussion-main-post-body">${escapeHtml(pub.contenu).replace(/\n/g, '<br>')}</div>
        ${attachmentsHtml}
      </section>

      <!-- Section des réponses -->
      <section class="discussion-replies-section">
        <h4 class="discussion-replies-heading">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          <span>${comments.length} réponse${comments.length > 1 ? 's' : ''}</span>
        </h4>
        <div class="discussion-comments-stack">
          ${commentsHtml}
        </div>
      </section>

      <!-- Formulaire de réponse -->
      ${canComment ? `
        <form class="discussion-reply-form" id="discussionCommentForm" action="${baseUrl}/comment/create" method="post">
          <input type="hidden" name="ajax" value="1">
          <input type="hidden" name="publication_id" value="${pub.id}">
          <label for="discussionReplyText" class="form-label">Votre réponse</label>
          <textarea class="form-control" id="discussionReplyText" name="contenu" required rows="3"
                    placeholder="Écrivez une réponse constructive et respectueuse..."></textarea>
          <div class="discussion-form-footer">
            <span class="discussion-reply-feedback" style="font-size:0.8rem; font-weight:600; color:var(--text-muted);"></span>
            <button type="submit" class="btn-publish-highlight" style="padding:0.5rem 1.25rem;">
              <span>Répondre</span>
            </button>
          </div>
        </form>
      ` : '<div style="padding:1rem; text-align:center; color:var(--text-muted); font-size:0.85rem; background:var(--card-border-subtle); border-radius:var(--radius-sm);">Cette discussion est fermée aux nouvelles réponses.</div>'}
    `;
  };

  const openDiscussion = async (publicationId, updateHistory = true) => {
    if (!discussionModal || !discussionModalContent) return;
    const reqId = Number(publicationId);
    activeDiscussionId = reqId;

    discussionModal.hidden = false;
    discussionModal.setAttribute("aria-hidden", "false");

    discussionModalContent.innerHTML = `
      <div class="discussion-loading-spinner">
        <div class="loader-circle"></div>
        <span>Chargement de la discussion…</span>
      </div>
    `;

    if (updateHistory) {
      const url = new URL(window.location.href);
      url.searchParams.set("pub", String(reqId));
      window.history.pushState({}, "", url.toString());
    }

    try {
      const endpoint = `${baseUrl}/publication/discussion?id=${reqId}`;
      const res = await fetch(endpoint, {
        headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" },
      });
      const data = await res.json();
      if (!res.ok || !data.success) throw new Error(data.message || "Erreur de chargement.");

      if (activeDiscussionId === reqId) {
        renderDiscussionData(data);
      }
    } catch (err) {
      if (activeDiscussionId === reqId) {
        discussionModalContent.innerHTML = `
          <div class="empty-state-card" style="margin:2rem 0;">
            <p style="color:var(--danger); font-weight:600;">${escapeHtml(err.message || "La discussion n'a pas pu être chargée.")}</p>
          </div>
        `;
      }
    }
  };

  // Fermeture du modal de discussion
  document.querySelectorAll("[data-close-discussion]").forEach((btn) => {
    btn.addEventListener("click", () => closeDiscussionModal(true));
  });

  discussionModal?.addEventListener("click", (e) => {
    if (e.target === discussionModal) closeDiscussionModal(true);
  });

  // =========================================================
  // 4. DÉLÉGATION D'ÉVÉNEMENTS GLOBALE (CLICS & SOUMISSIONS)
  // =========================================================
  document.addEventListener("click", async (e) => {
    // A0. Clic sur une publication non-discussion dans les tendances (Sondage / Annonce) : scroll fluide + highlight
    const scrollPostBtn = e.target.closest("[data-scroll-to-post]");
    if (scrollPostBtn) {
      e.preventDefault();
      const targetPostId = scrollPostBtn.dataset.scrollToPost;
      const targetElement = document.getElementById(`publication-${targetPostId}`);
      if (targetElement) {
        targetElement.scrollIntoView({ behavior: "smooth", block: "center" });
        targetElement.classList.add("post-highlight-pulse");
        setTimeout(() => targetElement.classList.remove("post-highlight-pulse"), 2000);
      } else {
        // Si la publication n'est pas sur la page actuelle, rediriger vers son URL
        window.location.href = scrollPostBtn.getAttribute("href") || `${baseUrl}/salon?pub=${targetPostId}`;
      }
      return;
    }

    // A. Ouvrir la discussion
    const openBtn = e.target.closest("[data-open-discussion]");
    if (openBtn) {
      e.preventDefault();
      openDiscussion(openBtn.dataset.openDiscussion);
      return;
    }

    // B. Clic sur la carte discussion (hors liens/boutons)
    const discCard = e.target.closest("[data-discussion-card]");
    if (discCard && !e.target.closest("a, button, form, input, textarea, select, label, .poll-container-box")) {
      openDiscussion(discCard.dataset.discussionCard);
      return;
    }

    // C. Like / Réaction avec animation instantanée & retour haptique visuel
    const reactionBtn = e.target.closest("[data-toggle-reaction]");
    if (reactionBtn) {
      e.preventDefault();
      reactionBtn.disabled = true;

      const heartIcon = reactionBtn.querySelector(".heart-icon");
      const counter = reactionBtn.querySelector(".like-counter-text") || reactionBtn.querySelector("span");
      const wasLiked = reactionBtn.classList.contains("is-liked");
      const currentCount = counter ? (Number(counter.textContent) || 0) : 0;

      // Optimistic update : feedback visuel immédiat sans attendre le réseau
      const nextLiked = !wasLiked;
      reactionBtn.classList.toggle("is-liked", nextLiked);
      reactionBtn.classList.add("is-animating");
      reactionBtn.setAttribute("aria-pressed", String(nextLiked));
      if (heartIcon) heartIcon.setAttribute("fill", nextLiked ? "currentColor" : "none");
      if (counter) counter.textContent = String(Math.max(0, currentCount + (nextLiked ? 1 : -1)));

      try {
        const payload = {
          cible_id: reactionBtn.dataset.targetId,
          type_cible: reactionBtn.dataset.targetType,
          type_reaction: "like",
        };

        const res = await fetch(`${baseUrl}/reaction/toggle`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest",
            Accept: "application/json",
          },
          body: JSON.stringify(payload),
        });

        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.message || "Erreur de like.");

        const isLiked = data.user_reaction === "like";
        reactionBtn.classList.toggle("is-liked", isLiked);
        reactionBtn.setAttribute("aria-pressed", String(isLiked));
        if (heartIcon) heartIcon.setAttribute("fill", isLiked ? "currentColor" : "none");

        if (counter && data.counts?.like !== undefined) {
          counter.textContent = String(data.counts.like);
        }
      } catch (err) {
        // Rollback en cas d'erreur
        reactionBtn.classList.toggle("is-liked", wasLiked);
        reactionBtn.setAttribute("aria-pressed", String(wasLiked));
        if (heartIcon) heartIcon.setAttribute("fill", wasLiked ? "currentColor" : "none");
        if (counter) counter.textContent = String(currentCount);
        showToast(err.message || "Impossible d'enregistrer le j'aime.", "error");
      } finally {
        setTimeout(() => reactionBtn.classList.remove("is-animating"), 450);
        reactionBtn.disabled = false;
      }
      return;
    }

    // D. Sauvegarde / Favori (Publication ou Pièce jointe)
    const saveBtn = e.target.closest("[data-toggle-save]");
    if (saveBtn) {
      e.preventDefault();
      saveBtn.disabled = true;
      saveBtn.classList.add("is-saving-pulse");

      try {
        const formData = new FormData();
        formData.append("ajax", "1");
        formData.append("target_type", saveBtn.dataset.targetType || "publication");
        formData.append("target_id", saveBtn.dataset.targetId || "0");

        const res = await fetch(`${baseUrl}/salon/save`, {
          method: "POST",
          headers: {
            "X-Requested-With": "XMLHttpRequest",
            Accept: "application/json",
          },
          body: formData,
        });

        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.message || "Erreur d'enregistrement.");

        const isSaved = !!data.saved;
        saveBtn.classList.toggle("is-saved", isSaved);
        const svgPath = saveBtn.querySelector("svg");
        if (svgPath) svgPath.setAttribute("fill", isSaved ? "currentColor" : "none");

        showToast(data.message || (isSaved ? "Ajouté aux sauvegardes !" : "Retiré des sauvegardes !"), "success");
      } catch (err) {
        showToast(err.message || "Erreur lors de la sauvegarde.", "error");
      } finally {
        setTimeout(() => saveBtn.classList.remove("is-saving-pulse"), 300);
        saveBtn.disabled = false;
      }
      return;
    }

    // E. Partage / Copie du lien
    const shareBtn = e.target.closest("[data-share-url]");
    if (shareBtn) {
      e.preventDefault();
      const shareUrl = new URL(shareBtn.dataset.shareUrl || window.location.href, window.location.origin).toString();
      if (navigator.clipboard) {
        navigator.clipboard.writeText(shareUrl).then(() => {
          showToast("Lien de la discussion copié dans le presse-papier !", "success");
        }).catch(() => {
          showToast(`Lien : ${shareUrl}`, "info");
        });
      } else {
        showToast(`Lien : ${shareUrl}`, "info");
      }
      return;
    }
  });

  // Soumission des formulaires (Sondage & Commentaires)
  document.addEventListener("submit", async (e) => {
    // 1. Vote de sondage
    const pollForm = e.target.closest(".poll-form-widget");
    if (pollForm) {
      e.preventDefault();
      const submitBtn = pollForm.querySelector(".btn-vote-submit");
      const feedback = pollForm.querySelector(".poll-vote-feedback");
      if (submitBtn) submitBtn.disabled = true;
      if (feedback) feedback.textContent = "";

      try {
        const res = await fetch(pollForm.action, {
          method: "POST",
          headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" },
          body: new FormData(pollForm),
        });

        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.message || "Erreur de vote.");

        const options = data.options || [];
        const totalVotes = options.reduce((sum, o) => sum + Number(o.votes || 0), 0);

        pollForm.querySelectorAll(".poll-option-row").forEach((row) => {
          const radio = row.querySelector("input[type='radio']");
          const optId = Number(radio?.value || 0);
          const matchedOpt = options.find((o) => Number(o.id) === optId);
          if (!matchedOpt) return;

          const voteCount = Number(matchedOpt.votes || 0);
          const pct = totalVotes > 0 ? Math.round((voteCount / totalVotes) * 100) : 0;
          const isSelected = optId === Number(data.option_id);

          row.classList.toggle("is-selected", isSelected);
          if (radio) radio.checked = isSelected;

          const fillBar = row.querySelector(".poll-bar-fill");
          if (fillBar) fillBar.style.width = `${pct}%`;

          const statBadge = row.querySelector(".poll-stat-badge");
          if (statBadge) {
            statBadge.innerHTML = `<strong>${pct}%</strong><small>(${voteCount})</small>`;
          }
        });

        const totalCounter = pollForm.querySelector(".poll-total-counter");
        if (totalCounter) {
          totalCounter.textContent = `(${totalVotes} vote${totalVotes > 1 ? "s" : ""} au total)`;
        }

        if (submitBtn) submitBtn.textContent = "Modifier mon vote";
        if (feedback) feedback.textContent = "Vote enregistré !";
        showToast("Votre vote a bien été pris en compte.", "success");

      } catch (err) {
        if (feedback) feedback.textContent = err.message || "Erreur de vote.";
        showToast(err.message || "Impossible d'enregistrer le vote.", "error");
      } finally {
        if (submitBtn) submitBtn.disabled = false;
      }
      return;
    }

    // 2. Nouveau commentaire dans le modal de discussion avec animation de chargement
    const commentForm = e.target.closest("#discussionCommentForm");
    if (commentForm) {
      e.preventDefault();
      const submitBtn = commentForm.querySelector("button[type='submit']");
      const feedback = commentForm.querySelector(".discussion-reply-feedback");
      const origHtml = submitBtn ? submitBtn.innerHTML : "<span>Répondre</span>";

      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
          <div class="loader-circle" style="width:14px; height:14px; border:2px solid #ffffff; border-top-color:transparent; border-radius:50%; animation:spin 0.75s linear infinite;"></div>
          <span>Envoi…</span>
        `;
      }
      if (feedback) feedback.textContent = "";

      try {
        const formData = new FormData(commentForm);
        formData.set("ajax", "1");

        const res = await fetch(commentForm.action, {
          method: "POST",
          headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" },
          body: formData,
        });

        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.message || "Erreur d'envoi du commentaire.");

        showToast("Réponse publiée avec succès !", "success");
        // Recharger le contenu de la discussion
        if (activeDiscussionId) {
          await openDiscussion(activeDiscussionId, false);
        }
      } catch (err) {
        if (feedback) feedback.textContent = err.message || "Erreur.";
        showToast(err.message || "Impossible d'ajouter la réponse.", "error");
      } finally {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = origHtml;
        }
      }
      return;
    }
  });

  // Fermeture des modals avec la touche Échap
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      if (!discussionModal?.hidden) closeDiscussionModal(true);
      else if (!postComposerModal?.hidden) closeComposerModal();
    }
  });

  // Navigation dans l'historique (Back/Forward)
  window.addEventListener("popstate", () => {
    const pubId = Number(new URLSearchParams(window.location.search).get("pub") || 0);
    if (pubId > 0) openDiscussion(pubId, false);
    else closeDiscussionModal(false);
  });

  // Ouverture automatique si ?pub=X dans l'URL
  const initialPubId = Number(new URLSearchParams(window.location.search).get("pub") || 0);
  if (initialPubId > 0) {
    openDiscussion(initialPubId, false);
  }

  // =========================================================
  // 5. RECHERCHE EN DIRECT
  // =========================================================
  const searchInput = document.getElementById("publicationSearch");
  const clearSearchBtn = document.getElementById("clearSearchBtn");
  const defaultPostsStream = document.getElementById("publicationList");
  const searchResultsStream = document.getElementById("searchResults");
  let searchTimer = null;

  const performLiveSearch = async () => {
    const query = searchInput?.value.trim() || "";
    if (clearSearchBtn) clearSearchBtn.hidden = (query.length === 0);

    if (!query) {
      if (searchResultsStream) {
        searchResultsStream.hidden = true;
        searchResultsStream.replaceChildren();
      }
      if (defaultPostsStream) defaultPostsStream.hidden = false;
      return;
    }

    try {
      const searchUrl = new URL(`${baseUrl}/search`, window.location.origin);
      searchUrl.searchParams.set("q", query);

      const pageParams = new URLSearchParams(window.location.search);
      ["zone", "category"].forEach((f) => {
        if (pageParams.has(f)) searchUrl.searchParams.set(f, pageParams.get(f));
      });

      const res = await fetch(searchUrl, {
        headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" },
      });
      const data = await res.json();
      if (!res.ok || !data.success) throw new Error();

      if (defaultPostsStream) defaultPostsStream.hidden = true;
      if (searchResultsStream) {
        searchResultsStream.hidden = false;
        if (!data.results?.length) {
          searchResultsStream.innerHTML = `
            <div class="empty-state-card">
              <h3>Aucun résultat pour "${escapeHtml(query)}"</h3>
              <p>Vérifiez l’orthographe ou tentez une recherche plus large.</p>
            </div>
          `;
        } else {
          searchResultsStream.innerHTML = data.results.map((r) => `
            <article class="post-card post-card-discussion">
              <div class="post-header">
                <div class="post-author-box">
                  <div class="author-avatar">${escapeHtml(r.author.slice(0, 2).toUpperCase())}</div>
                  <div class="author-details">
                    <strong class="author-name">${escapeHtml(r.author)}</strong>
                    <div class="post-meta-subline">
                      <span>${escapeHtml(r.category || 'Général')} · ${escapeHtml(r.date || '')}</span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="post-content-container">
                <h3 class="post-title-heading">
                  <a href="${baseUrl}/salon?pub=${r.id}" class="post-title-link" data-open-discussion="${r.id}">
                    ${escapeHtml(r.title)}
                  </a>
                </h3>
                <p class="post-text-body">${escapeHtml(r.excerpt)}</p>
              </div>
              <div class="post-footer-actions-row">
                <div class="post-interactive-buttons">
                  <button type="button" class="btn-interaction btn-discussion-action" data-open-discussion="${r.id}">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <span>${r.comments || 0} réponse(s)</span>
                  </button>
                </div>
              </div>
            </article>
          `).join("");
        }
      }
    } catch {
      // Erreur de recherche discrète
    }
  };

  searchInput?.addEventListener("input", () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(performLiveSearch, 280);
  });

  clearSearchBtn?.addEventListener("click", () => {
    if (searchInput) searchInput.value = "";
    performLiveSearch();
    searchInput?.focus();
  });

  // =========================================================
  // 6. POPUP DE NOTIFICATIONS FLOTTANT DANS LE SALON
  // =========================================================
  const btnToggleNotifPopup = document.getElementById("btnToggleNotifPopup");
  const notifPopupDropdown = document.getElementById("notifPopupDropdown");
  const btnCloseNotifPopup = document.getElementById("btnCloseNotifPopup");
  const notifPopupBody = document.getElementById("notifPopupBody");
  const btnPopupMarkAllRead = document.getElementById("btnPopupMarkAllRead");
  const popupUnreadCount = document.getElementById("popupUnreadCount");
  let notifsLoadedOnce = false;

  const loadPopupNotifications = async () => {
    if (!notifPopupBody) return;
    try {
      const res = await fetch(`${baseUrl}/notifications`, {
        headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" },
      });
      const data = await res.json();
      if (!res.ok || !data.success) throw new Error();

      const notifs = data.notifications || [];
      const unreadCount = Number(data.unread_count || 0);

      if (popupUnreadCount) {
        popupUnreadCount.textContent = String(unreadCount);
        popupUnreadCount.style.display = unreadCount > 0 ? "inline-flex" : "none";
      }

      const navNotifCount = document.querySelector(".nav-notification-count");
      if (navNotifCount) {
        navNotifCount.textContent = String(unreadCount);
        navNotifCount.style.display = unreadCount > 0 ? "inline-flex" : "none";
      }

      if (notifs.length === 0) {
        notifPopupBody.innerHTML = `
          <div style="text-align:center; padding:2rem 1rem; color:var(--text-muted); font-size:0.85rem;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:0.4rem; color:var(--text-light);"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            <div>Aucune notification</div>
            <small style="color:var(--text-light);">Vous êtes parfaitement à jour !</small>
          </div>
        `;
        return;
      }

      notifPopupBody.innerHTML = notifs.map((n) => {
        const isUnread = Number(n.lu || 0) === 0;
        const type = n.type_notification || "systeme";
        let iconSvg = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>';

        if (type === "reaction") {
          iconSvg = '<svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>';
        } else if (type === "reponse" || type === "comment") {
          iconSvg = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>';
        } else if (type === "mention") {
          iconSvg = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 1 0-3.92 7.94"/></svg>';
        } else if (type === "avertissement" || type === "moderation") {
          iconSvg = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>';
        }

        return `
          <div class="notif-popup-item ${isUnread ? 'is-unread' : ''}" data-popup-notif-id="${n.id}"
               style="display:flex; align-items:flex-start; gap:0.6rem; padding:0.6rem 0.75rem; border-radius:var(--radius-sm); border:1px solid ${isUnread ? 'var(--primary-border)' : 'var(--card-border-subtle)'}; background:${isUnread ? 'var(--primary-light)' : 'var(--card-bg)'}; font-size:0.8rem;">
            <div style="flex-shrink:0; margin-top:2px;">${iconSvg}</div>
            <div style="flex-grow:1; min-width:0;">
              <div style="color:var(--text-main); line-height:1.4;">${escapeHtml(n.contenu || '')}</div>
              <div style="display:flex; align-items:center; justify-content:space-between; margin-top:0.35rem;">
                <span style="font-size:0.7rem; color:var(--text-light);">${formatTimeRelative(n.date_creation)}</span>
                <div style="display:flex; align-items:center; gap:0.5rem;">
                  ${n.lien ? `<a href="${escapeHtml(n.lien)}" style="font-size:0.72rem; font-weight:700; color:var(--primary); text-decoration:none;">Voir &rarr;</a>` : ''}
                  ${isUnread ? `<button type="button" class="btn-popup-mark-single" data-notif-id="${n.id}" style="background:none; border:none; font-size:0.7rem; font-weight:600; color:var(--text-muted); cursor:pointer;">Lu</button>` : ''}
                </div>
              </div>
            </div>
            ${isUnread ? '<span style="width:6px; height:6px; border-radius:50%; background:var(--accent); flex-shrink:0; margin-top:6px;"></span>' : ''}
          </div>
        `;
      }).join("");

      notifsLoadedOnce = true;
    } catch {
      notifPopupBody.innerHTML = '<div style="text-align:center; padding:1.5rem; color:var(--danger); font-size:0.82rem;">Erreur de chargement des notifications.</div>';
    }
  };

  btnToggleNotifPopup?.addEventListener("click", (e) => {
    e.stopPropagation();
    if (!notifPopupDropdown) return;
    const isHidden = notifPopupDropdown.hidden;
    notifPopupDropdown.hidden = !isHidden;
    if (isHidden) {
      loadPopupNotifications();
    }
  });

  btnCloseNotifPopup?.addEventListener("click", () => {
    if (notifPopupDropdown) notifPopupDropdown.hidden = true;
  });

  // Marquer toutes les alertes comme lues depuis le popup
  btnPopupMarkAllRead?.addEventListener("click", async () => {
    try {
      const res = await fetch(`${baseUrl}/notifications/mark-all-read`, {
        method: "POST",
        headers: { "X-Requested-With": "XMLHttpRequest", Accept: "application/json" },
      });
      const data = await res.json();
      if (!res.ok || !data.success) throw new Error();

      notifPopupBody.querySelectorAll(".notif-popup-item").forEach((el) => {
        el.classList.remove("is-unread");
        el.style.background = "var(--card-bg)";
        el.style.borderColor = "var(--card-border-subtle)";
        el.querySelector("span[style*='background:var(--accent)']")?.remove();
        el.querySelector(".btn-popup-mark-single")?.remove();
      });

      if (popupUnreadCount) popupUnreadCount.style.display = "none";
      const navNotifCount = document.querySelector(".nav-notification-count");
      if (navNotifCount) navNotifCount.style.display = "none";

      showToast("Toutes vos notifications sont marquées comme lues.", "success");
    } catch {
      showToast("Impossible de marquer comme lu.", "error");
    }
  });

  // Marquer une notification comme lue depuis le popup
  notifPopupBody?.addEventListener("click", async (e) => {
    const markSingleBtn = e.target.closest(".btn-popup-mark-single");
    if (!markSingleBtn) return;
    e.preventDefault();

    const notifId = markSingleBtn.dataset.notifId;
    const item = markSingleBtn.closest(".notif-popup-item");
    markSingleBtn.disabled = true;

    try {
      const body = new FormData();
      body.append("notification_id", String(notifId));
      const res = await fetch(`${baseUrl}/notifications/mark-read`, {
        method: "POST",
        headers: { "X-Requested-With": "XMLHttpRequest", Accept: "application/json" },
        body,
      });
      const data = await res.json();
      if (!res.ok || !data.success) throw new Error();

      if (item) {
        item.classList.remove("is-unread");
        item.style.background = "var(--card-bg)";
        item.style.borderColor = "var(--card-border-subtle)";
        item.querySelector("span[style*='background:var(--accent)']")?.remove();
        markSingleBtn.remove();
      }

      const unreadCount = Number(data.unread_count || 0);
      if (popupUnreadCount) popupUnreadCount.style.display = unreadCount > 0 ? "inline-flex" : "none";
      const navNotifCount = document.querySelector(".nav-notification-count");
      if (navNotifCount) navNotifCount.style.display = unreadCount > 0 ? "inline-flex" : "none";

      showToast("Notification marquée comme lue.", "success");
    } catch {
      showToast("Erreur lors de la mise à jour.", "error");
    }
  });

  // Fermeture au clic à l'extérieur
  document.addEventListener("click", (e) => {
    if (notifPopupDropdown && !notifPopupDropdown.hidden) {
      if (!notifPopupDropdown.contains(e.target) && !btnToggleNotifPopup?.contains(e.target)) {
        notifPopupDropdown.hidden = true;
      }
    }
  });
});
