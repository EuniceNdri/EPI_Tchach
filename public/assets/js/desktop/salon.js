document.addEventListener("DOMContentLoaded", () => {
  const baseUrl = document.body.dataset.baseUrl || "";
  const updatePresence = async () => {
    try {
      const response = await fetch(`${baseUrl}/salon/presence`, {
        method: "POST",
        headers: { "X-Requested-With": "XMLHttpRequest", Accept: "application/json" },
      });
      if (!response.ok) throw new Error("La mise à jour de présence a échoué.");
    } catch (error) {
      console.error("Erreur lors de la mise à jour de présence :", error);
    }
  };
  window.setInterval(() => {
    if (document.visibilityState === "visible") updatePresence();
  }, 60000);
  document.addEventListener("visibilitychange", () => {
    if (document.visibilityState === "visible") updatePresence();
  });

  const searchInput = document.getElementById("publicationSearch");
  const defaultPosts = document.querySelector("main .posts:not(#searchResults)");
  const searchResults = document.getElementById("searchResults");
  const postForm = document.getElementById("salonPostForm");
  const attachmentInput = document.getElementById("salonPostAttachments");
  const attachmentPreview = document.getElementById("salonAttachmentPreview");
  const publicationType = document.getElementById("publicationType");
  const pollOptionsFields = document.getElementById("pollOptionsFields");
  const pollOptionsList = document.getElementById("pollOptionsList");
  const discussionPanel = document.getElementById("discussionPanel");
  const discussionPanelContent = document.getElementById("discussionPanelContent");
  const discussionPanelTitle = document.getElementById("discussionPanelTitle");
  const previewObjectUrls = new Set();
  const formatFileSize = (size) => `${(size / 1048576).toFixed(2)} Mo`;
  const escapeHtml = (value) => String(value).replace(/[&<>"']/g, (character) => ({
    "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;",
  }[character]));

  const refreshPublicationList = async (targetUrl) => {
    try {
      const response = await fetch(targetUrl, { headers: { Accept: "text/html" } });
      if (!response.ok) throw new Error("Le fil de publications n’a pas pu être actualisé.");
      const documentFromResponse = new DOMParser().parseFromString(await response.text(), "text/html");
      const refreshedList = documentFromResponse.getElementById("publicationList");
      const currentList = document.getElementById("publicationList");
      if (!refreshedList || !currentList) throw new Error("Le fil de publications est indisponible.");
      currentList.replaceChildren(...Array.from(refreshedList.childNodes));
      const refreshedTabs = documentFromResponse.querySelector("main .tabs");
      const currentTabs = document.querySelector("main .tabs");
      if (refreshedTabs && currentTabs) currentTabs.replaceWith(refreshedTabs);
      window.history.replaceState({}, "", targetUrl);
    } catch (error) {
      console.error("Actualisation du fil impossible :", error);
      window.location.assign(targetUrl);
    }
  };

  const renderAttachmentPreview = () => {
    if (!attachmentInput || !attachmentPreview) return;

    previewObjectUrls.forEach((url) => URL.revokeObjectURL(url));
    previewObjectUrls.clear();
    attachmentPreview.replaceChildren();
    const files = Array.from(attachmentInput.files || []);
    attachmentPreview.hidden = files.length === 0;
    if (!files.length) return;

    files.forEach((file, index) => {
      const item = document.createElement("div");
      item.className = "salon-attachment-preview-item";
      if (file.type.startsWith("image/")) {
        const image = document.createElement("img");
        const objectUrl = URL.createObjectURL(file);
        previewObjectUrls.add(objectUrl);
        image.src = objectUrl;
        image.alt = `Aperçu de ${file.name}`;
        item.append(image);
      } else {
        const icon = document.createElement("span");
        icon.className = "salon-attachment-file-icon";
        icon.setAttribute("aria-hidden", "true");
        icon.textContent = file.name.split(".").pop()?.toUpperCase().slice(0, 4) || "DOC";
        item.append(icon);
      }

      const info = document.createElement("div");
      info.className = "salon-attachment-preview-info";
      const name = document.createElement("strong");
      name.title = file.name;
      name.textContent = file.name;
      const size = document.createElement("span");
      size.textContent = formatFileSize(file.size);
      info.append(name, size);

      const remove = document.createElement("button");
      remove.type = "button";
      remove.className = "salon-attachment-remove";
      remove.dataset.removeAttachment = String(index);
      remove.setAttribute("aria-label", `Retirer ${file.name}`);
      remove.textContent = "×";
      item.append(info, remove);
      attachmentPreview.append(item);
    });

    const count = document.createElement("p");
    count.className = "salon-attachment-count";
    count.textContent = `${files.length} fichier${files.length > 1 ? "s" : ""} sélectionné${files.length > 1 ? "s" : ""} · 5 maximum`;
    attachmentPreview.append(count);
  };

  attachmentInput?.addEventListener("change", () => {
    const files = Array.from(attachmentInput.files || []);
    if (files.length > 5) {
      const transfer = new DataTransfer();
      files.slice(0, 5).forEach((file) => transfer.items.add(file));
      attachmentInput.files = transfer.files;
    }
    renderAttachmentPreview();
  });

  attachmentPreview?.addEventListener("click", (event) => {
    const removeButton = event.target.closest("[data-remove-attachment]");
    if (!removeButton || !attachmentInput) return;

    const removeIndex = Number(removeButton.dataset.removeAttachment);
    const transfer = new DataTransfer();
    Array.from(attachmentInput.files || []).forEach((file, index) => {
      if (index !== removeIndex) transfer.items.add(file);
    });
    attachmentInput.files = transfer.files;
    renderAttachmentPreview();
  });

  if (postForm) {
    postForm.addEventListener("submit", async (event) => {
      event.preventDefault();
      const submitButton = postForm.querySelector("button[type='submit']");
      const feedback = document.getElementById("salonPostFeedback");
      if (submitButton) submitButton.disabled = true;
      if (feedback) feedback.textContent = "";

      try {
        const formData = new FormData(postForm);
        formData.set("ajax", "1");
        const response = await fetch(postForm.action, {
          method: "POST",
          headers: { Accept: "application/json" },
          body: formData,
        });
        const contentType = response.headers.get("content-type") || "";
        if (!contentType.includes("application/json")) {
          throw new Error(response.redirected
            ? "Votre session a expiré. Veuillez vous reconnecter."
            : "Le serveur a renvoyé une réponse inattendue.");
        }

        const result = await response.json();
        if (!response.ok || !result.success) {
          throw new Error(result.message || "La publication n'a pas pu être enregistrée.");
        }
        const category = postForm.querySelector("select[name='categorie_id']");
        const selectedOption = category?.selectedOptions[0];
        const categoryId = Number(category?.value || 0);
        const zoneId = Number(selectedOption?.dataset.zoneId || 0);
        postForm.reset();
        renderAttachmentPreview();
        updatePollComposer();
        if (searchInput) searchInput.value = "";
        if (searchResults) {
          searchResults.hidden = true;
          searchResults.replaceChildren();
        }
        if (defaultPosts) defaultPosts.hidden = false;
        if (feedback) feedback.textContent = result.message || "Publication créée avec succès.";

        if (!result.moderationPending && categoryId > 0 && zoneId > 0) {
          const target = new URL(`${baseUrl}/salon`, window.location.origin);
          target.searchParams.set("view", "latest");
          target.searchParams.set("zone", String(zoneId));
          target.searchParams.set("category", String(categoryId));
          window.setTimeout(() => refreshPublicationList(target.toString()), 250);
        }
      } catch (error) {
        if (feedback) feedback.textContent = error.message || "Impossible de joindre le serveur.";
      } finally {
        if (submitButton) submitButton.disabled = false;
      }
    });
  }

  const updatePollComposer = () => {
    if (!publicationType || !pollOptionsFields) return;
    const isPoll = publicationType.value === "question";
    pollOptionsFields.hidden = !isPoll;
    pollOptionsFields.querySelectorAll("input[name='options[]']").forEach((input) => {
      input.required = isPoll;
      input.disabled = !isPoll;
    });
    const title = document.getElementById("salonComposerTitle");
    const content = document.getElementById("salonPostContent");
    const contentLabel = document.getElementById("salonContentLabel");
    if (title) title.textContent = isPoll ? "Créer un sondage" : "Démarrer une discussion";
    if (title && publicationType.value === "annonce") title.textContent = "Créer une annonce";
    if (contentLabel) contentLabel.textContent = isPoll ? "Votre question" : "Votre message";
    if (content) content.placeholder = isPoll ? "Posez une question à la communauté..." : "Partagez une question, une idée ou une ressource...";
  };
  publicationType?.addEventListener("change", updatePollComposer);
  updatePollComposer();

  document.getElementById("addPollOption")?.addEventListener("click", () => {
    if (!pollOptionsList || pollOptionsList.querySelectorAll("input").length >= 10) return;
    const index = pollOptionsList.querySelectorAll("input").length + 1;
    const input = document.createElement("input");
    input.className = "salon-form-control";
    input.name = "options[]";
    input.maxLength = 255;
    input.placeholder = `Choix ${index}`;
    input.setAttribute("aria-label", `Choix ${index}`);
    input.required = true;
    pollOptionsList.append(input);
  });

  const renderDiscussion = (data) => {
    const publication = data.publication;
    discussionPanelTitle.textContent = publication.titre;
    const comments = data.comments.map((comment) => `
      <article class="discussion-comment ${Number(comment.est_solution) ? "is-solution" : ""}">
        <div class="discussion-comment-header">
          <strong>${escapeHtml(`${comment.prenom} ${comment.nom}`)}</strong>
          ${Number(comment.est_solution) ? '<span class="discussion-solution-badge">✓ Solution</span>' : ""}
        </div>
        <p class="discussion-comment-content">${escapeHtml(comment.contenu).replace(/\n/g, "<br>")}</p>
        <div class="discussion-comment-actions">
          <button type="button" class="post-action-button ${Number(comment.user_liked) ? "is-active" : ""}"
            data-toggle-reaction data-target-type="commentaire" data-target-id="${Number(comment.id)}"
            aria-pressed="${Number(comment.user_liked) ? "true" : "false"}">
            ${Number(comment.user_liked) ? "♥ Aimé" : "♡ J’aime"}
          </button>
          <span class="discussion-like-count">${Number(comment.likes_count)} j’aime</span>
        </div>
      </article>`).join("");
    const canComment = Number(publication.commentaires_fermes || 0) === 0;
    discussionPanelContent.innerHTML = `
      <article class="discussion-publication">
        <div class="discussion-meta">${escapeHtml(`${publication.prenom} ${publication.nom}`)} · ${escapeHtml(publication.zone_name)} · ${escapeHtml(publication.category_name)}</div>
        <h3>${escapeHtml(publication.titre)}</h3>
        <p>${escapeHtml(publication.contenu).replace(/\n/g, "<br>")}</p>
      </article>
      <section class="discussion-comments" aria-label="Commentaires">
        <h3>${data.comments.length} commentaire${data.comments.length === 1 ? "" : "s"}</h3>
        ${comments || '<p class="discussion-meta">Aucun commentaire pour le moment.</p>'}
      </section>
      ${canComment ? `
        <form class="discussion-comment-form" action="${baseUrl}/comment/create" method="post">
          <input type="hidden" name="ajax" value="1">
          <input type="hidden" name="publication_id" value="${Number(publication.id)}">
          <label for="discussionCommentContent">Votre commentaire</label>
          <textarea class="salon-form-control" id="discussionCommentContent" name="contenu" maxlength="5000" required></textarea>
          <button class="poll-vote-button" type="submit">Commenter</button>
          <span class="discussion-form-feedback" role="status" aria-live="polite"></span>
        </form>` : '<p class="discussion-meta">Les commentaires sont fermés.</p>'}`;
  };

  let activeDiscussionId = 0;
  const closeDiscussion = (updateUrl = true) => {
    if (!discussionPanel) return;
    activeDiscussionId = 0;
    discussionPanel.hidden = true;
    discussionPanel.setAttribute("aria-hidden", "true");
    document.body.classList.remove("discussion-open");
    if (updateUrl) {
      const target = new URL(window.location.href);
      target.searchParams.delete("discussion");
      window.history.replaceState({}, "", target.toString());
    }
  };
  const openDiscussion = async (publicationId, updateHistory = true) => {
    if (!discussionPanel || !discussionPanelContent) return;
    const requestedId = Number(publicationId);
    activeDiscussionId = requestedId;
    discussionPanel.hidden = false;
    discussionPanel.setAttribute("aria-hidden", "false");
    document.body.classList.add("discussion-open");
    discussionPanelContent.innerHTML = '<p class="discussion-meta">Chargement de la discussion…</p>';
    if (updateHistory) {
      const target = new URL(window.location.href);
      target.searchParams.set("discussion", String(activeDiscussionId));
      window.history.pushState({}, "", target.toString());
    }
    try {
      const endpoint = new URL(`${baseUrl}/publication/discussion`, window.location.origin);
      endpoint.searchParams.set("id", String(requestedId));
      const response = await fetch(endpoint, { headers: { Accept: "application/json" } });
      const data = await response.json();
      if (!response.ok || !data.success) throw new Error(data.message || "La discussion n’a pas pu être chargée.");
      if (activeDiscussionId === requestedId) renderDiscussion(data);
    } catch (error) {
      if (activeDiscussionId === requestedId) {
        discussionPanelContent.innerHTML = `<p class="discussion-meta">${escapeHtml(error.message || "Erreur de chargement.")}</p>`;
      }
    }
  };

  document.addEventListener("click", async (event) => {
    const openButton = event.target.closest("[data-open-discussion]");
    if (openButton) {
      openDiscussion(openButton.dataset.openDiscussion);
      return;
    }
    const discussionCard = event.target.closest("[data-discussion-card]");
    if (discussionCard && !event.target.closest("a, button, form, input, textarea, select, label")) {
      openDiscussion(discussionCard.dataset.discussionCard);
      return;
    }
    if (event.target.closest("[data-close-discussion]")) {
      closeDiscussion();
      return;
    }
    const reactionButton = event.target.closest("[data-toggle-reaction]");
    if (!reactionButton) return;

    reactionButton.disabled = true;
    try {
      const response = await fetch(`${baseUrl}/reaction/toggle`, {
        method: "POST",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          Accept: "application/json",
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          cible_id: reactionButton.dataset.targetId,
          type_cible: reactionButton.dataset.targetType,
          type_reaction: "like",
        }),
      });
      const result = await response.json();
      if (!response.ok || !result.success) throw new Error(result.message || "Le like n’a pas pu être enregistré.");
      const isLiked = result.user_reaction === "like";
      reactionButton.classList.toggle("is-active", isLiked);
      reactionButton.setAttribute("aria-pressed", String(isLiked));
      reactionButton.textContent = isLiked ? "♥ Aimé" : "♡ J’aime";

      if (reactionButton.dataset.targetType === "publication") {
        const card = reactionButton.closest(".post-card");
        const count = card?.querySelector(".publication-like-count");
        if (count) count.textContent = `♥ ${Number(result.counts.like)} likes`;
      } else if (activeDiscussionId) {
        await openDiscussion(activeDiscussionId, false);
      }
    } catch (error) {
      let feedback = reactionButton.parentElement?.querySelector(".salon-action-feedback");
      if (!feedback) {
        feedback = document.createElement("span");
        feedback.className = "salon-action-feedback";
        feedback.setAttribute("role", "status");
        reactionButton.parentElement?.append(feedback);
      }
      if (feedback) feedback.textContent = error.message || "Le like n’a pas pu être enregistré.";
    } finally {
      reactionButton.disabled = false;
    }
  });

  document.addEventListener("submit", async (event) => {
    const pollForm = event.target.closest(".poll-form");
    if (pollForm) {
      event.preventDefault();
      const button = pollForm.querySelector("button[type='submit']");
      const feedback = pollForm.querySelector(".poll-feedback");
      button.disabled = true;
      feedback.textContent = "";
      try {
        const response = await fetch(pollForm.action, {
          method: "POST",
          headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" },
          body: new FormData(pollForm),
        });
        const result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.message || "Le vote n’a pas pu être enregistré.");
        const options = result.options || [];
        const total = options.reduce((sum, option) => sum + Number(option.votes), 0);
        const fieldset = pollForm.querySelector("fieldset");
        fieldset.classList.add("poll-has-voted");
        fieldset.querySelectorAll(".poll-option").forEach((label) => {
          const optionId = Number(label.querySelector("input").value);
          const option = options.find((item) => Number(item.id) === optionId);
          if (!option) return;
          const percent = total ? Math.round((Number(option.votes) / total) * 100) : 0;
          label.querySelector("input").checked = Number(option.id) === Number(result.option_id);
          label.querySelector(".poll-option-result").style.setProperty("--vote-percent", `${percent}%`);
          label.querySelector("strong").textContent = `${Number(option.votes)} · ${percent}%`;
        });
        fieldset.querySelector("legend").textContent = "Résultats du sondage";
        button.textContent = "Modifier mon vote";
        feedback.textContent = result.message;
      } catch (error) {
        feedback.textContent = error.message || "Le vote n’a pas pu être enregistré.";
      } finally {
        button.disabled = false;
      }
      return;
    }

    const commentForm = event.target.closest(".discussion-comment-form");
    if (!commentForm) return;
    event.preventDefault();
    const button = commentForm.querySelector("button[type='submit']");
    const feedback = commentForm.querySelector(".discussion-form-feedback");
    button.disabled = true;
    feedback.textContent = "";
    try {
      const formData = new FormData(commentForm);
      formData.set("ajax", "1");
      const response = await fetch(commentForm.action, {
        method: "POST",
        headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" },
        body: formData,
      });
      const result = await response.json();
      if (!response.ok || !result.success) throw new Error(result.message || "Le commentaire n’a pas pu être ajouté.");
      await openDiscussion(activeDiscussionId, false);
    } catch (error) {
      feedback.textContent = error.message || "Le commentaire n’a pas pu être ajouté.";
    } finally {
      button.disabled = false;
    }
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && activeDiscussionId) closeDiscussion();
  });
  window.addEventListener("popstate", () => {
    const publicationId = Number(new URLSearchParams(window.location.search).get("discussion") || 0);
    if (publicationId > 0) openDiscussion(publicationId, false);
    else closeDiscussion(false);
  });
  const initialDiscussionId = Number(new URLSearchParams(window.location.search).get("discussion") || 0);
  if (initialDiscussionId > 0) openDiscussion(initialDiscussionId, false);

  if (!searchInput || !defaultPosts || !searchResults) return;

  let timer;

  const renderResults = (results) => {
    searchResults.innerHTML = "";
    if (results.length === 0) {
      searchResults.innerHTML = '<p class="search-empty">Aucun résultat trouvé.</p>';
      return;
    }

    results.forEach((result) => {
      const card = document.createElement("article");
      card.className = "post-card";
      const files = result.files.length
        ? `<div class="post-attachments" aria-label="Documents joints">${result.files.map((file) => {
          const isImage = String(file.type || "").startsWith("image/");
          const viewUrl = file.download_url.replace("/attachment/download/", "/attachment/view/");
          return `<div class="post-attachment ${isImage ? "is-image" : ""}">
            ${isImage ? `<a class="post-attachment-preview" href="${escapeHtml(viewUrl)}" target="_blank" rel="noopener" aria-label="Aperçu de ${escapeHtml(file.name)}"><img src="${escapeHtml(viewUrl)}" alt="Aperçu : ${escapeHtml(file.name)}" loading="lazy"></a>` : '<span class="post-attachment-icon" aria-hidden="true">▤</span>'}
            <div class="post-attachment-info"><strong title="${escapeHtml(file.name)}">${escapeHtml(file.name)}</strong><span>Fichier joint</span></div>
            <a class="post-attachment-download" href="${escapeHtml(file.download_url)}">Télécharger</a>
          </div>`;
        }).join("")}</div>`
        : "";
      card.innerHTML = `
        <div class="post-header">
          <div class="avatar">${escapeHtml(result.author.slice(0, 2).toUpperCase())}</div>
          <div class="author-info">
            <div class="author-name">${escapeHtml(result.author)}</div>
            <div class="author-meta">${escapeHtml(result.category)} · ${escapeHtml(result.date)}</div>
          </div>
        </div>
        <h2 class="post-title">${escapeHtml(result.title)}</h2>
        <p class="post-excerpt">${escapeHtml(result.excerpt)}</p>
        <div class="post-footer">
          ${files}
          <div class="stats"><span class="stat">💬 ${result.comments} réponse(s)</span></div>
        </div>`;
      searchResults.appendChild(card);
    });
  };

  const performSearch = async () => {
    const query = searchInput.value.trim();
    if (!query) {
      searchResults.hidden = true;
      defaultPosts.hidden = false;
      searchResults.innerHTML = "";
      return;
    }

    try {
      const searchUrl = new URL(`${baseUrl}/search`, window.location.origin);
      searchUrl.searchParams.set("q", query);
      const pageParams = new URLSearchParams(window.location.search);
      ["zone", "category"].forEach((filter) => {
        if (pageParams.has(filter)) searchUrl.searchParams.set(filter, pageParams.get(filter));
      });
      const response = await fetch(searchUrl, {
        headers: { Accept: "application/json" },
      });
      const data = await response.json();
      if (!response.ok || !data.success) {
        throw new Error(data.message || "La recherche a échoué.");
      }
      renderResults(data.results);
      defaultPosts.hidden = true;
      searchResults.hidden = false;
    } catch (error) {
      renderResults([]);
      defaultPosts.hidden = true;
      searchResults.hidden = false;
      searchResults.innerHTML = `<p class="search-empty">${escapeHtml(error.message)}</p>`;
    }
  };

  searchInput.addEventListener("input", () => {
    clearTimeout(timer);
    timer = setTimeout(performSearch, 300);
  });
});
