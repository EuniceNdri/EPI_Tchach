document.addEventListener("DOMContentLoaded", () => {
            const baseUrl = document.body.dataset.baseUrl || "";

            document.querySelectorAll("[data-history-back]").forEach((link) => {
                link.addEventListener("click", (event) => {
                    if (document.referrer && new URL(document.referrer).origin === window.location.origin && window.history.length > 1) {
                        event.preventDefault();
                        window.history.back();
                    }
                });
            });

            const refreshPublicationStats = async() => {
                const publicationIds = [...new Set(
                    [...document.querySelectorAll("[data-live-publication-id]")]
                    .map((element) => Number(element.dataset.livePublicationId))
                    .filter((id) => Number.isInteger(id) && id > 0)
                )];
                if (!publicationIds.length) return;

                try {
                    for (let offset = 0; offset < publicationIds.length; offset += 50) {
                        const batch = publicationIds.slice(offset, offset + 50);
                        const batchSet = new Set(batch.map(String));
                        const commentIds = [...new Set(
                            [...document.querySelectorAll("[data-live-comment-id]")]
                            .filter((comment) => batchSet.has(comment.closest("[data-live-publication-id]")?.dataset.livePublicationId))
                            .map((comment) => Number(comment.dataset.liveCommentId))
                            .filter((id) => Number.isInteger(id) && id > 0)
                        )];
                        const commentBatches = [];
                        for (let commentOffset = 0; commentOffset < commentIds.length; commentOffset += 200) {
                            commentBatches.push(commentIds.slice(commentOffset, commentOffset + 200));
                        }
                        if (!commentBatches.length) commentBatches.push([]);

                        for (const commentBatch of commentBatches) {
                            const query = new URLSearchParams({ ids: batch.join(",") });
                            if (commentBatch.length) query.set("comment_ids", commentBatch.join(","));
                            const response = await fetch(`${baseUrl}/publication/live-stats?${query}`, {
                                headers: { Accept: "application/json" },
                                cache: "no-store",
                            });
                            if (!response.ok) throw new Error("Les compteurs des publications n’ont pas pu être actualisés.");
                            const result = await response.json();
                            if (!result.success) throw new Error(result.message || "Les compteurs des publications n’ont pas pu être actualisés.");

                            document.querySelectorAll("[data-live-publication-id]").forEach((publication) => {
                                const stats = result.publications?.[publication.dataset.livePublicationId];
                                if (!stats) return;
                                publication.querySelectorAll("[data-live-likes]").forEach((count) => {
                                    count.textContent = String(stats.likes_count);
                                });
                                publication.querySelectorAll("[data-live-comment-count]").forEach((count) => {
                                    count.textContent = String(stats.comments_count);
                                });
                                publication.querySelectorAll("[data-live-comment-label]").forEach((label) => {
                                    label.textContent = Number(stats.comments_count) === 1 ? "commentaire" : "commentaires";
                                });
                                const likeButton = publication.querySelector('[data-toggle-reaction][data-target-type="publication"]');
                                if (likeButton) {
                                    const userReaction = stats.user_reaction || "";
                                    likeButton.dataset.currentReaction = userReaction;
                                    likeButton.classList.remove(
                                        "is-reacted-like",
                                        "is-reacted-jadore",
                                        "is-reacted-utile",
                                        "is-reacted-haha",
                                        "is-reacted-wouah",
                                        "is-reacted-triste"
                                    );
                                    likeButton.classList.toggle("is-voted", Boolean(userReaction));
                                    likeButton.setAttribute("aria-pressed", String(Boolean(userReaction)));
                                    if (userReaction) likeButton.classList.add(`is-reacted-${userReaction}`);
                                    const icon = likeButton.querySelector(".epi-btn-reaction-icon");
                                    if (icon) {
                                        icon.innerHTML = userReaction
                                            ? fbEmojis[userReaction] || fbEmojis.like
                                            : fbDefaultArrow;
                                    }
                                }
                            });

                            document.querySelectorAll("[data-live-comment-id]").forEach((comment) => {
                                const stats = result.comments?.[comment.dataset.liveCommentId];
                                if (!stats) return;
                                const likeButton = comment.querySelector('[data-toggle-reaction][data-target-type="commentaire"]');
                                if (!likeButton) return;
                                likeButton.classList.toggle("is-voted", stats.user_liked);
                                likeButton.setAttribute("aria-pressed", String(stats.user_liked));
                                const count = likeButton.querySelector("[data-live-comment-likes]");
                                if (count) count.textContent = String(stats.likes_count);
                                const reactionLabel = [...likeButton.childNodes].find(
                                    (node) => node.nodeType === Node.TEXT_NODE && node.textContent.includes("·")
                                );
                                if (reactionLabel) reactionLabel.textContent = `${stats.user_liked ? "♥ Aimé" : "♡ J’aime"} · `;
                            });
                        }
                    }
                } catch (error) {
                    console.error("Actualisation des compteurs impossible :", error);
                }
            };

            if (document.querySelector("[data-live-publication-id]")) {
                let statsRequestRunning = false;
                const pollPublicationStats = async() => {
                    if (document.visibilityState !== "visible" || statsRequestRunning) return;
                    statsRequestRunning = true;
                    try {
                        await refreshPublicationStats();
                    } finally {
                        statsRequestRunning = false;
                    }
                };
                window.setInterval(pollPublicationStats, 3000);
                document.addEventListener("visibilitychange", pollPublicationStats);
            }

            // Mise à jour de présence périodique
            const updatePresence = async() => {
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
            if (document.querySelector(".epi-main-feed")) {
                window.setInterval(() => {
                    if (document.visibilityState === "visible") updatePresence();
                }, 60000);
                document.addEventListener("visibilitychange", () => {
                    if (document.visibilityState === "visible") updatePresence();
                });
            }

            // Éléments du DOM
            const searchInput = document.getElementById("publicationSearch");
            const searchClearBtn = document.getElementById("searchClearBtn");
            const defaultPosts = document.querySelector(".epi-main-feed .posts:not(#searchResults)");
            const searchResults = document.getElementById("searchResults");
            const postForm = document.getElementById("salonPostForm");
            const attachmentInput = document.getElementById("salonPostAttachments");
            const attachmentPreview = document.getElementById("salonAttachmentPreview");
            const publicationType = document.getElementById("publicationType");
            const postTitle = document.getElementById("salonPostTitle");
            const postContent = document.getElementById("salonPostContent");
            const postCategory = document.getElementById("modalCategorySelect");
            const submitPostButton = document.getElementById("submitPostBtn");
            const pollOptionsFields = document.getElementById("pollOptionsFields");
            const pollOptionsList = document.getElementById("pollOptionsList");

            // Modale Composer EPI
            const modal = document.getElementById("epiPostModal");
            const openModalButtons = document.querySelectorAll("[data-open-create-post-modal], #openCreatePostModal");
            const closeModalBtn = document.getElementById("closeCreatePostModal");
            const cancelPostBtn = document.getElementById("cancelPostBtn");
            const dummyComposerInput = document.getElementById("triggerComposerClick");
            const quickAddMedia = document.getElementById("quickAddMedia");
            const quickAddFile = document.getElementById("quickAddFile");
            const quickAddPoll = document.getElementById("quickAddPoll");
            let modalOpener = null;

            const updatePostSubmitState = () => {
                if (!submitPostButton || !postTitle || !postContent || !postCategory) return;
                const pollChoices = Array.from(postForm?.querySelectorAll("input[name='options[]']:not(:disabled)") || [])
                    .filter((input) => input.value.trim() !== "");
                const validPoll = publicationType?.value !== "question" || pollChoices.length >= 2;
                submitPostButton.disabled = !postTitle.value.trim() || !postContent.value.trim() ||
                    !postCategory.value || !validPoll;
            };

            postForm?.addEventListener("input", updatePostSubmitState);
            postForm?.addEventListener("change", updatePostSubmitState);

            document.querySelectorAll(".epi-search-input").forEach((input) => {
                input.addEventListener("keydown", (event) => {
                    if (event.key !== "Enter") return;
                    const query = input.value.trim();
                    if (!query) return;
                    event.preventDefault();
                    const searchUrl = new URL(`${baseUrl}/salon`, window.location.origin);
                    searchUrl.searchParams.set("q", query);
                    window.location.assign(searchUrl);
                });
            });

            // Menu Utilisateur
            const userMenuToggle = document.getElementById("userMenuToggle");
            const userMenuDropdown = document.getElementById("userMenuDropdown");

            const previewObjectUrls = new Set();
            const formatFileSize = (size) => `${(size / 1048576).toFixed(2)} Mo`;
            const escapeHtml = (value) => String(value).replace(/[&<>"']/g, (character) => ({
                "&": "&amp;",
                "<": "&lt;",
                ">": "&gt;",
                '"': "&quot;",
                "'": "&#039;",
            }[character]));

            // Gestion du menu utilisateur dropdown
            if (userMenuToggle && userMenuDropdown) {
                userMenuToggle.addEventListener("click", (e) => {
                    e.stopPropagation();
                    const isOpen = !userMenuDropdown.hidden;
                    userMenuDropdown.hidden = isOpen;
                    userMenuToggle.classList.toggle("is-active", !isOpen);
                    userMenuToggle.setAttribute("aria-expanded", String(!isOpen));
                });
                document.addEventListener("click", (e) => {
                    if (!userMenuDropdown.contains(e.target) && !userMenuToggle.contains(e.target)) {
                        userMenuDropdown.hidden = true;
                        userMenuToggle.classList.remove("is-active");
                        userMenuToggle.setAttribute("aria-expanded", "false");
                    }
                });
            }

            // Fonctions de modale Composer EPI
            const openComposerModal = (mode = "discussion") => {
                if (!modal) return;
                modalOpener = document.activeElement instanceof HTMLElement ? document.activeElement : null;
                modal.removeAttribute("hidden");
                modal.hidden = false;
                modal.style.display = "flex";
                modal.setAttribute("aria-hidden", "false");
                document.body.style.overflow = "hidden";

                // Synchronisation de l'onglet actif
                const typeButtons = modal.querySelectorAll("[data-type-target]");
                typeButtons.forEach((btn) => {
                    btn.classList.toggle("is-active", btn.dataset.typeTarget === mode);
                });
                if (publicationType) {
                    publicationType.value = mode;
                    updatePollComposer();
                }

                if (mode === "media" || mode === "file") {
                    attachmentInput?.click();
                }

                setTimeout(() => postTitle?.focus(), 80);
            };

            const closeComposerModal = () => {
                if (!modal) return;
                modal.setAttribute("hidden", "");
                modal.hidden = true;
                modal.style.display = "none";
                modal.setAttribute("aria-hidden", "true");
                document.body.style.overflow = "";
                modalOpener?.focus();
                modalOpener = null;
            };

            // Fermer impérativement au chargement initial
            closeComposerModal();

            openModalButtons.forEach((openModalBtn) => openModalBtn.addEventListener("click", (e) => {
                e.preventDefault();
                openComposerModal("discussion");
            }));
            dummyComposerInput?.addEventListener("click", (e) => {
                e.preventDefault();
                openComposerModal("discussion");
            });
            quickAddMedia?.addEventListener("click", (e) => {
                e.preventDefault();
                openComposerModal("discussion");
            });
            quickAddFile?.addEventListener("click", (e) => {
                e.preventDefault();
                openComposerModal("discussion");
            });
            quickAddPoll?.addEventListener("click", (e) => {
                e.preventDefault();
                openComposerModal("question");
            });

            closeModalBtn?.addEventListener("click", (e) => {
                e.preventDefault();
                closeComposerModal();
            });
            cancelPostBtn?.addEventListener("click", (e) => {
                e.preventDefault();
                closeComposerModal();
            });

            modal?.addEventListener("click", (e) => {
                if (e.target === modal) closeComposerModal();
            });
            document.addEventListener("keydown", (event) => {
                if (event.key === "Escape" && modal && !modal.hidden) closeComposerModal();
            });

            // Délégation globale de fermeture pour parer à tout conflit
            document.addEventListener("click", (e) => {
                if (e.target.closest("#closeCreatePostModal") || e.target.closest("#cancelPostBtn")) {
                    e.preventDefault();
                    closeComposerModal();
                }
            });

            // Onglets dans la modale de création
            modal?.querySelectorAll("[data-type-target]").forEach((tabBtn) => {
                tabBtn.addEventListener("click", () => {
                    const mode = tabBtn.dataset.typeTarget;
                    modal.querySelectorAll("[data-type-target]").forEach((b) => b.classList.remove("is-active"));
                    tabBtn.classList.add("is-active");
                    if (publicationType) {
                        publicationType.value = mode;
                        updatePollComposer();
                    }
                });
            });

            // Actualisation dynamique du fil
            const refreshPublicationList = async(targetUrl) => {
                try {
                    const response = await fetch(targetUrl, { headers: { Accept: "text/html" } });
                    if (!response.ok) throw new Error("Le fil de publications n’a pas pu être actualisé.");
                    const documentFromResponse = new DOMParser().parseFromString(await response.text(), "text/html");
                    const refreshedList = documentFromResponse.getElementById("publicationList");
                    const currentList = document.getElementById("publicationList");
                    if (!refreshedList || !currentList) throw new Error("Le fil de publications est indisponible.");
                    currentList.replaceChildren(...Array.from(refreshedList.childNodes));
                    const refreshedTabs = documentFromResponse.querySelector(".epi-feed-nav");
                    const currentTabs = document.querySelector(".epi-feed-nav");
                    if (refreshedTabs && currentTabs) currentTabs.replaceWith(refreshedTabs);
                    window.history.replaceState({}, "", targetUrl);
                } catch (error) {
                    console.error("Actualisation du fil impossible :", error);
                    window.location.assign(targetUrl);
                }
            };

            // Aperçu des pièces jointes
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
                    item.className = "epi-file-pill";
                    item.style.padding = "0.4rem 0.75rem";
                    if (file.type.startsWith("image/")) {
                        const image = document.createElement("img");
                        const objectUrl = URL.createObjectURL(file);
                        previewObjectUrls.add(objectUrl);
                        image.src = objectUrl;
                        image.alt = `Aperçu de ${file.name}`;
                        image.style.width = "32px";
                        image.style.height = "32px";
                        image.style.borderRadius = "4px";
                        image.style.objectFit = "cover";
                        item.append(image);
                    } else {
                        const icon = document.createElement("span");
                        icon.className = "epi-file-badge";
                        icon.textContent = file.name.split(".").pop()?.toUpperCase().slice(0, 4) || "DOC";
                        item.append(icon);
                    }

                    const info = document.createElement("div");
                    info.style.flex = "1";
                    info.style.overflow = "hidden";
                    const name = document.createElement("strong");
                    name.title = file.name;
                    name.textContent = file.name;
                    name.style.fontSize = "0.8rem";
                    name.style.display = "block";
                    name.style.whiteSpace = "nowrap";
                    name.style.textOverflow = "ellipsis";
                    name.style.overflow = "hidden";
                    const size = document.createElement("span");
                    size.textContent = formatFileSize(file.size);
                    size.style.fontSize = "0.7rem";
                    size.style.color = "var(--community-muted)";
                    info.append(name, size);

                    const remove = document.createElement("button");
                    remove.type = "button";
                    remove.className = "epi-modal-close";
                    remove.dataset.removeAttachment = String(index);
                    remove.setAttribute("aria-label", `Retirer ${file.name}`);
                    remove.textContent = "×";
                    remove.style.fontSize = "1.2rem";
                    item.append(info, remove);
                    attachmentPreview.append(item);
                });
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

            // Soumission du formulaire de publication
            if (postForm) {
                postForm.addEventListener("submit", async(event) => {
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
                            throw new Error(response.redirected ?
                                "Votre session a expiré. Veuillez vous reconnecter." :
                                "Le serveur a renvoyé une réponse inattendue.");
                        }

                        const result = await response.json();
                        if (!response.ok || !result.success) {
                            throw new Error(result.message || "La publication n'a pas pu être enregistrée.");
                        }
                        postForm.reset();
                        renderAttachmentPreview();
                        updatePollComposer();
                        updatePostSubmitState();
                        if (searchInput) searchInput.value = "";
                        if (searchResults) {
                            searchResults.hidden = true;
                            searchResults.replaceChildren();
                        }
                        if (defaultPosts) defaultPosts.hidden = false;

                        closeComposerModal();

                        if (typeof window.showFeedback === "function") {
                            window.showFeedback(result.message || "Publication créée avec succès.", "success");
                        }

                        if (!result.moderationPending && document.getElementById("publicationList")) {
                            const target = new URL(`${baseUrl}/salon`, window.location.origin);
                            target.searchParams.set("view", "latest");
                            window.setTimeout(() => refreshPublicationList(target.toString()), 250);
                        }
                    } catch (error) {
                        if (feedback) feedback.textContent = error.message || "Impossible de joindre le serveur.";
                    } finally {
                        updatePostSubmitState();
                    }
                });
            }

            // Mise à jour de l'affichage du sondage
            const updatePollComposer = () => {
                if (!publicationType || !pollOptionsFields) return;
                const isPoll = publicationType.value === "question";
                pollOptionsFields.hidden = !isPoll;
                pollOptionsFields.querySelectorAll("input[name='options[]']").forEach((input) => {
                    input.required = isPoll;
                    input.disabled = !isPoll;
                });
                const content = document.getElementById("salonPostContent");
                const contentLabel = document.getElementById("salonContentLabel");
                if (contentLabel) contentLabel.textContent = isPoll ? "Votre question" : "Votre message";
                if (content) content.placeholder = isPoll ? "Posez une question à la communauté..." : "Partagez une question, une idée ou une ressource...";
                updatePostSubmitState();
            };
            publicationType?.addEventListener("change", updatePollComposer);
            updatePollComposer();

            if (new URLSearchParams(window.location.search).get("compose") === "1") {
                openComposerModal("discussion");
                const url = new URL(window.location.href);
                url.searchParams.delete("compose");
                window.history.replaceState({}, "", url);
            }

            // Ajouter un choix de sondage
            document.getElementById("addPollOption")?.addEventListener("click", () => {
                if (!pollOptionsList || pollOptionsList.querySelectorAll("input").length >= 10) return;
                const index = pollOptionsList.querySelectorAll("input").length + 1;
                const input = document.createElement("input");
                input.className = "epi-form-control";
                input.name = "options[]";
                input.maxLength = 255;
                input.placeholder = `Choix ${index}`;
                input.setAttribute("aria-label", `Choix ${index}`);
                input.required = true;
                pollOptionsList.append(input);
                updatePostSubmitState();
            });

            // Gestion des clics globaux (navigation, réactions, partage, etc.)
            document.addEventListener("click", async(event) => {
                const replyToggle = event.target.closest(".publication-reply-toggle");
                if (replyToggle) {
                    const form = replyToggle.closest(".publication-comment")?.querySelector(".publication-reply-form");
                    if (form) {
                        const isOpening = form.hidden;
                        form.hidden = !isOpening;
                        replyToggle.setAttribute("aria-expanded", String(isOpening));
                        if (isOpening) form.querySelector("textarea")?.focus();
                    }
                    return;
                }

                const publicationCard = event.target.closest("[data-publication-card]");
                if (publicationCard && !event.target.closest("a, button, form, input, textarea, select, label")) {
                    window.location.href = `${baseUrl}/publication/${encodeURIComponent(publicationCard.dataset.publicationCard)}`;
                    return;
                }

                // Bouton Partager style EPI
                const shareBtn = event.target.closest(".epi-share-btn");
                if (shareBtn) {
                    event.preventDefault();
                    const shareUrl = shareBtn.dataset.shareUrl || window.location.href;
                    const fullUrl = new URL(shareUrl, window.location.origin).toString();
                    try {
                        await navigator.clipboard.writeText(fullUrl);
                        const originalText = shareBtn.innerHTML;
                        shareBtn.innerHTML = `<span>✓ Copié !</span>`;
                        if (typeof window.showFeedback === "function") {
                            window.showFeedback("Lien de la discussion copié dans le presse-papiers !", "info");
                        }
                        setTimeout(() => { shareBtn.innerHTML = originalText; }, 2000);
                    } catch {
                        window.prompt("Copiez le lien ci-dessous :", fullUrl);
                    }
                    return;
                }

                // Bouton Downvote (feedback UX EPI)
                const downvoteBtn = event.target.closest(".epi-vote-btn.downvote");
                if (downvoteBtn) {
                    downvoteBtn.style.transform = "translateY(2px)";
                    setTimeout(() => { downvoteBtn.style.transform = ""; }, 180);
                    return;
                }

                // Réaction emoji Facebook (clic sur une option emoji)
                const reactionOption = event.target.closest(".epi-reaction-option");
                if (reactionOption) {
                    event.preventDefault();
                    event.stopPropagation();
                    const container = reactionOption.closest(".epi-reaction-container");
                    const reactionBtn = container?.querySelector("[data-toggle-reaction]");
                    if (reactionBtn) {
                        const type = reactionOption.dataset.reactionType || "like";
                        const targetId = reactionBtn.dataset.targetId;
                        const targetType = reactionBtn.dataset.targetType;
                        handleFacebookReaction(targetId, targetType, type, reactionBtn);
                    }
                    return;
                }

                // Bouton Réaction / Upvote (clic direct)
                const reactionButton = event.target.closest("[data-toggle-reaction]");
                if (!reactionButton) return;

                event.preventDefault();
                const currentReaction = reactionButton.dataset.currentReaction || "";
                const targetType = reactionButton.dataset.targetType || "publication";
                const targetId = reactionButton.dataset.targetId;
                // Si déjà réagi, on renvoie la même réaction pour basculer (retirer), sinon 'like' par défaut
                const reactionToSend = currentReaction !== "" ? currentReaction : "like";
                handleFacebookReaction(targetId, targetType, reactionToSend, reactionButton);
            });

            const fbEmojis = {
                like: "👍",
                jadore: "❤️",
                utile: "💡",
                haha: "😂",
                wouah: "😮",
                triste: "😢"
            };

            const fbLabels = {
                like: "J’aime",
                jadore: "J’adore",
                utile: "Utile",
                haha: "Haha",
                wouah: "Wouah",
                triste: "Triste"
            };

            const fbDefaultArrow = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>`;

            async function handleFacebookReaction(targetId, targetType, requestedType, reactionButton) {
                if (!targetId || !targetType || !reactionButton) return;
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
                            cible_id: targetId,
                            type_cible: targetType,
                            type_reaction: requestedType,
                        }),
                    });

                    const result = await response.json();
                    if (!response.ok || !result.success) throw new Error(result.message || "Le vote n’a pas pu être enregistré.");

                    const newReaction = result.user_reaction; // string ("like", "jadore", etc.) ou null
                    const isReacted = Boolean(newReaction);

                    // Réinitialiser les classes
                    reactionButton.classList.remove(
                        "is-voted",
                        "is-reacted-like",
                        "is-reacted-jadore",
                        "is-reacted-utile",
                        "is-reacted-haha",
                        "is-reacted-wouah",
                        "is-reacted-triste"
                    );
                    reactionButton.dataset.currentReaction = newReaction || "";
                    reactionButton.setAttribute("aria-pressed", String(isReacted));

                    if (isReacted) {
                        reactionButton.classList.add("is-voted", `is-reacted-${newReaction}`);
                    }

                    // Mise à jour de l'icône dans le bouton
                    const iconSpan = reactionButton.querySelector(".epi-btn-reaction-icon");
                    if (iconSpan) {
                        if (isReacted && fbEmojis[newReaction]) {
                            iconSpan.textContent = fbEmojis[newReaction];
                        } else {
                            iconSpan.innerHTML = fbDefaultArrow;
                        }
                    }

                    // Total des votes
                    const totalCount = Number(result.counts?.total ?? result.counts?.like ?? 0);

                    if (targetType === "publication") {
                        const card = reactionButton.closest("[data-live-publication-id]");
                        const count = card?.querySelector("[data-live-likes]");
                        if (count) count.textContent = `${totalCount}`;

                        if (typeof window.showFeedback === "function") {
                            const msg = isReacted ?
                                `${fbEmojis[newReaction] || "👍"} ${fbLabels[newReaction] || "Réaction"} ajoutée !` :
                                "Réaction retirée.";
                            window.showFeedback(msg, "info");
                        }
                    } else {
                        const comment = reactionButton.closest("[data-live-comment-id]");
                        if (comment) comment.dataset.liveCommentId = targetId;
                        const countHtml = `<span data-live-comment-likes>${totalCount}</span>`;
                        const labelText = isReacted ? `${fbEmojis[newReaction] || "♥"} ${fbLabels[newReaction] || "Aimé"}` : "♡ J’aime";
                        reactionButton.innerHTML = labelText + " · " + countHtml;

                        if (typeof window.showFeedback === "function") {
                            window.showFeedback(isReacted ? "Commentaire réagi !" : "Réaction retirée.", "info");
                        }
                    }
                } catch (error) {
                    console.error(error);
                    if (typeof window.showFeedback === "function") {
                        window.showFeedback(error.message || "Erreur de réaction.", "error");
                    }
                } finally {
                    reactionButton.disabled = false;
                }
            }

            // Gestion des formulaires de sondage et commentaires
            document.addEventListener("submit", async(event) => {
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
                            label.querySelector("strong").textContent = `${Number(option.votes)} vote(s) · ${percent}%`;
                        });
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
                    if (document.body.dataset.publicationId) {
                        window.location.reload();
                    } else {
                        feedback.textContent = result.message || "Commentaire ajouté.";
                        commentForm.reset();
                    }
                } catch (error) {
                    feedback.textContent = error.message || "Le commentaire n’a pas pu être ajouté.";
                } finally {
                    button.disabled = false;
                }
            });

            // Échappement touches clavier
            document.addEventListener("keydown", (event) => {
                if (event.key === "Escape") {
                    if (modal && !modal.hidden) closeComposerModal();
                }
            });

            // Recherche EPI en direct dans la Topbar
            if (!searchInput || !defaultPosts || !searchResults) return;

            let searchTimer;

            const renderResults = (results, profiles = []) => {
                    searchResults.replaceChildren();
                    if (results.length === 0 && profiles.length === 0) {
                        searchResults.innerHTML = `
        <div style="background:var(--community-surface); border:1px solid var(--community-line); border-radius:var(--community-radius-lg); padding:2.5rem; text-align:center;">
          <h3 style="margin:0 0 0.5rem;">Aucun résultat trouvé</h3>
          <p style="color:var(--community-muted); margin:0;">Essayez un autre mot-clé ou vérifiez l'orthographe.</p>
        </div>`;
                        return;
                    }

                    if (profiles.length > 0) {
                        const section = document.createElement("section");
                        section.className = "epi-search-section";
                        const heading = document.createElement("h2");
                        heading.className = "epi-search-section-title";
                        heading.textContent = "Profils";
                        section.appendChild(heading);

                        const list = document.createElement("div");
                        list.className = "epi-search-profile-list";
                        profiles.forEach((profile) => {
                            const link = document.createElement("a");
                            link.className = "epi-search-profile";
                            link.href = `${baseUrl}/profile/${encodeURIComponent(profile.id)}`;

                            const avatar = document.createElement("span");
                            avatar.className = "epi-search-profile-avatar";
                            const initials = String(profile.name || "")
                                .trim()
                                .split(/\s+/)
                                .slice(0, 2)
                                .map((part) => part.charAt(0))
                                .join("")
                                .toUpperCase();
                            const fallback = document.createElement("span");
                            fallback.className = "epi-search-profile-initials";
                            fallback.textContent = initials;
                            avatar.appendChild(fallback);

                            if (profile.avatar_url) {
                                const image = document.createElement("img");
                                image.src = `${baseUrl}/public/${String(profile.avatar_url).replace(/^\/+/, "")}`;
                                image.alt = "";
                                image.loading = "lazy";
                                image.addEventListener("error", () => image.remove(), { once: true });
                                avatar.appendChild(image);
                            }

                            const details = document.createElement("span");
                            details.className = "epi-search-profile-details";
                            const name = document.createElement("strong");
                            name.textContent = profile.name;
                            const description = document.createElement("span");
                            const roleLabels = {
                                etudiant: "Étudiant",
                                professeur: "Professeur",
                                moderateur: "Modérateur",
                                administrateur: "Administrateur",
                            };
                            description.textContent = [
                                roleLabels[profile.role] || profile.role,
                                profile.filiere,
                            ].filter(Boolean).join(" · ");
                            details.append(name, description);
                            link.append(avatar, details);
                            list.appendChild(link);
                        });

                        section.appendChild(list);
                        searchResults.appendChild(section);
                    }

                    if (results.length > 0) {
                        const section = document.createElement("section");
                        section.className = "epi-search-section";
                        const heading = document.createElement("h2");
                        heading.className = "epi-search-section-title";
                        heading.textContent = "Publications";
                        section.appendChild(heading);

                        results.forEach((result) => {
                                    const card = document.createElement("article");
                                    card.className = "epi-post-card";
                                    const files = result.files.length ?
                                        `<div class="epi-media-container">${result.files.map((file) => {
          const isImage = String(file.type || "").startsWith("image/");
          const viewUrl = file.download_url.replace("/attachment/download/", "/attachment/view/");
          return isImage
            ? `<a href="${escapeHtml(viewUrl)}" target="_blank" rel="noopener"><img class="epi-media-image" src="${escapeHtml(viewUrl)}" alt="${escapeHtml(file.name)}" loading="lazy"></a>`
            : `<div class="epi-file-pill"><span class="epi-file-badge">DOC</span><div style="flex:1;"><strong>${escapeHtml(file.name)}</strong></div><a class="epi-pill-btn" href="${escapeHtml(file.download_url)}">Télécharger</a></div>`;
        }).join("")}</div>`
        : "";
      card.innerHTML = `
        <div class="epi-card-header">
          <div class="epi-card-meta">
            <span class="epi-salon-tag">s/${escapeHtml(result.category)}</span>
            <span>•</span>
            <span class="epi-author-badge">Posté par ${escapeHtml(result.author)}</span>
            <span>•</span>
            <span>${escapeHtml(result.date)}</span>
          </div>
        </div>
        <h2 class="epi-post-title">
          <a href="${baseUrl}/publication/${result.id}">${escapeHtml(result.title)}</a>
        </h2>
        <div class="epi-post-body">${escapeHtml(result.excerpt)}</div>
        ${files}
        <div class="epi-card-actions">
          <a class="epi-pill-btn post-discussion-button" href="${baseUrl}/publication/${result.id}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            <span>${result.comments} commentaires</span>
          </a>
        </div>`;
        section.appendChild(card);
      });
      searchResults.appendChild(section);
    }
  };

  const performSearch = async () => {
    const query = searchInput.value.trim();
    if (searchClearBtn) searchClearBtn.hidden = !query;

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
      renderResults(data.results, data.profiles || []);
      defaultPosts.hidden = true;
      searchResults.hidden = false;
    } catch (error) {
      renderResults([]);
      defaultPosts.hidden = true;
      searchResults.hidden = false;
    }
  };

  searchInput.addEventListener("input", () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(performSearch, 280);
  });

  searchClearBtn?.addEventListener("click", () => {
    searchInput.value = "";
    searchClearBtn.hidden = true;
    performSearch();
    searchInput.focus();
  });

  const initialQuery = new URLSearchParams(window.location.search).get("q") || "";
  if (initialQuery) {
    searchInput.value = initialQuery;
    if (searchClearBtn) searchClearBtn.hidden = false;
    performSearch();
  }

  // ============================================================
  // PANNEAU LATÉRAL À DROITE : DISCUSSION & COMMENTAIRES (Option A)
  // Permet de lire et poster des commentaires tout en défilant le salon
  // ============================================================
  const commentsDrawer = document.getElementById("commentsSideDrawer");
  const closeDrawerBtn = document.getElementById("closeCommentsDrawerBtn");
  const drawerCommentsHeaderCount = document.getElementById("drawerCommentsHeaderCount");
  const drawerPostTitle = document.getElementById("drawerPostTitle");
  const drawerPostContent = document.getElementById("drawerPostContent");
  const drawerPinnedContainer = document.getElementById("drawerPinnedCommentContainer");
  const drawerCommentsCount = document.getElementById("drawerCommentsCount");
  const drawerCommentsList = document.getElementById("drawerCommentsList");
  const drawerPublicationIdInput = document.getElementById("drawerPublicationId");
  const drawerFullViewLink = document.getElementById("drawerFullViewLink");
  const drawerCommentForm = document.getElementById("drawerCommentForm");
  const drawerCommentInput = document.getElementById("drawerCommentInput");

  const buildCommentHtml = (c, isPinned = false, pinLabel = "") => {
    const cInit = (c.prenom || 'M').charAt(0).toUpperCase();
    const cAvatar = c.author_avatar
      ? `<img src="${baseUrl}/public/${c.author_avatar.replace(/^\//, '')}" alt="">`
      : `<span>${cInit}</span>`;
    const likes = Number(c.likes_count || 0);
    const userLiked = Boolean(c.user_liked);
    const likeIcon = userLiked ? '❤️' : '🤍';
    const cardClass = isPinned ? 'drawer-pinned-card' : 'drawer-comment-card';

    const pinBadgeHtml = isPinned
      ? `<div class="drawer-pinned-badge">📌 ${pinLabel}</div>`
      : '';

    return `
      <div class="${cardClass}" data-comment-id="${c.id}" data-live-comment-id="${c.id}">
        ${pinBadgeHtml}
        <div class="drawer-comment-header">
          <div style="display:flex; align-items:center; gap:0.5rem;">
            <div class="drawer-mini-avatar" style="width:28px; height:28px; font-size:0.75rem;">${cAvatar}</div>
            <div>
              <span class="drawer-comment-author">${c.prenom || ''} ${c.nom || ''}</span>
            </div>
          </div>
          <span class="drawer-comment-time">${c.date_creation || ''}</span>
        </div>
        <p class="drawer-comment-text">${(c.contenu || '').replace(/</g, '&lt;').replace(/>/g, '&gt;')}</p>
        <div class="drawer-comment-actions">
          <button type="button" class="drawer-comment-action-btn ${userLiked ? 'is-voted' : ''}" data-toggle-reaction data-target-type="commentaire" data-target-id="${c.id}" data-current-reaction="${userLiked ? 'like' : ''}" aria-label="Aimer ce commentaire">
            <span class="epi-btn-reaction-icon">${likeIcon}</span>
            <span data-live-comment-likes>${likes}</span>
          </button>
          <button type="button" class="drawer-comment-action-btn drawer-reply-btn" data-reply-author="${c.prenom || ''}">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 17 4 12 9 7"/><path d="M20 18v-2a4 4 0 0 0-4-4H4"/></svg>
            <span>Répondre</span>
          </button>
        </div>
      </div>
    `;
  };

  const openDiscussionDrawer = async (publicationId) => {
    if (!commentsDrawer) return;

    // Mise en surbrillance de la carte active dans le fil
    document.querySelectorAll(".campus-post-card.is-active-discussion").forEach(el => el.classList.remove("is-active-discussion"));
    const activeCard = document.querySelector(`article[data-live-publication-id="${publicationId}"]`);
    if (activeCard) {
      activeCard.classList.add("is-active-discussion");
    }

    commentsDrawer.hidden = false;
    commentsDrawer.classList.add("is-open");
    drawerPublicationIdInput.value = publicationId;
    drawerFullViewLink.href = `${baseUrl}/publication/${publicationId}`;
    if (drawerCommentsHeaderCount) drawerCommentsHeaderCount.textContent = "Commentaires (...)";
    drawerPostTitle.textContent = "Publication : « Chargement... »";
    drawerPostTitle.title = "Publication";
    drawerPostContent.innerHTML = `<div style="text-align:center; padding:1.5rem; color:#64748b;">⏳ Récupération de la discussion...</div>`;
    if (drawerPinnedContainer) {
      drawerPinnedContainer.hidden = true;
      drawerPinnedContainer.innerHTML = "";
    }
    drawerCommentsList.innerHTML = "";
    drawerCommentsCount.textContent = "Chargement...";

    try {
      const response = await fetch(`${baseUrl}/publication/discussion?id=${publicationId}`, {
        headers: { "Accept": "application/json" }
      });
      const data = await response.json();
      if (!data.success || !data.publication) {
        throw new Error(data.message || "Impossible de charger la discussion.");
      }

      const pub = data.publication;
      const titleShort = (pub.titre || "Publication").trim();
      drawerPostTitle.textContent = `Publication : « ${titleShort} »`;
      drawerPostTitle.title = titleShort;

      const authorInitial = (pub.prenom || "E").charAt(0).toUpperCase();
      const authorAvatar = pub.author_avatar ? `<img src="${baseUrl}/public/${pub.author_avatar.replace(/^\//, '')}" alt="">` : `<span>${authorInitial}</span>`;

      let attachmentsHtml = "";
      if (Array.isArray(data.attachments) && data.attachments.length > 0) {
        attachmentsHtml = `<div style="display:flex; flex-wrap:wrap; gap:0.4rem; margin-top:0.75rem;">` +
          data.attachments.map(att => `<a href="${att.download_url}" class="campus-doc-btn" style="padding:0.3rem 0.6rem; font-size:0.75rem;" target="_blank">📎 ${att.name}</a>`).join("") +
          `</div>`;
      }

      drawerPostContent.innerHTML = `
        <div class="drawer-post-author-row">
          <div class="drawer-mini-avatar">${authorAvatar}</div>
          <div>
            <div class="drawer-author-name">${pub.prenom || ''} ${pub.nom || ''}</div>
            <div class="drawer-author-meta">${pub.category_name || pub.zone_name || 'Salon'} · ${pub.date_creation || ''}</div>
          </div>
        </div>
        <div style="font-weight:700; margin-bottom:0.4rem; font-size:0.95rem;">${pub.titre || ''}</div>
        <div>${(pub.contenu || '').replace(/\n/g, '<br>')}</div>
        ${attachmentsHtml}
      `;

      const comments = Array.isArray(data.comments) ? data.comments : [];
      const totalCount = comments.length;
      if (drawerCommentsHeaderCount) {
        drawerCommentsHeaderCount.textContent = `Commentaires (${totalCount})`;
      }
      drawerCommentsCount.textContent = `${totalCount} commentaire${totalCount > 1 ? 's' : ''}`;

      if (totalCount === 0) {
        if (drawerPinnedContainer) drawerPinnedContainer.hidden = true;
        drawerCommentsList.innerHTML = `<div style="text-align:center; padding:2rem 1rem; color:var(--community-muted); font-size:0.85rem;">Aucun commentaire pour le moment. Soyez le premier à répondre !</div>`;
      } else {
        // Recherche d'un commentaire épinglé (solution ou plus grand nombre de likes >= 3)
        let pinnedIndex = comments.findIndex(c => Number(c.est_solution) === 1);
        let pinLabel = "Solution";

        if (pinnedIndex === -1) {
          // Si aucune solution, vérifier si le commentaire ayant le max de likes en a au moins 3
          let maxLikes = 0;
          let maxLikesIndex = -1;
          comments.forEach((c, idx) => {
            const lk = Number(c.likes_count || 0);
            if (lk >= 3 && lk > maxLikes) {
              maxLikes = lk;
              maxLikesIndex = idx;
            }
          });
          if (maxLikesIndex !== -1) {
            pinnedIndex = maxLikesIndex;
            pinLabel = "Épinglé (plus de likes)";
          }
        }

        if (pinnedIndex !== -1 && drawerPinnedContainer) {
          const pinnedComment = comments[pinnedIndex];
          drawerPinnedContainer.innerHTML = buildCommentHtml(pinnedComment, true, pinLabel);
          drawerPinnedContainer.hidden = false;
        } else if (drawerPinnedContainer) {
          drawerPinnedContainer.hidden = true;
          drawerPinnedContainer.innerHTML = "";
        }

        // Afficher les commentaires réguliers
        drawerCommentsList.innerHTML = comments.map((c, idx) => {
          if (idx === pinnedIndex) return ""; // Déjà mis en avant dans l'encart épinglé
          return buildCommentHtml(c, false);
        }).join("");
      }

    } catch (err) {
      drawerPostContent.innerHTML = `<div style="color:#ef4444; padding:1rem;">⚠️ ${err.message}</div>`;
      if (drawerCommentsHeaderCount) drawerCommentsHeaderCount.textContent = "Commentaires (0)";
      drawerCommentsCount.textContent = "0 commentaire";
      if (drawerPinnedContainer) drawerPinnedContainer.hidden = true;
    }
  };

  const closeDiscussionDrawer = () => {
    if (!commentsDrawer) return;
    commentsDrawer.classList.remove("is-open");
    document.querySelectorAll(".campus-post-card.is-active-discussion").forEach(el => el.classList.remove("is-active-discussion"));
    setTimeout(() => {
      if (!commentsDrawer.classList.contains("is-open")) {
        commentsDrawer.hidden = true;
      }
    }, 350);
  };

  if (closeDrawerBtn) {
    closeDrawerBtn.addEventListener("click", closeDiscussionDrawer);
  }

  // Fermer le panneau avec la touche Échap
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && commentsDrawer && commentsDrawer.classList.contains("is-open")) {
      closeDiscussionDrawer();
    }
  });

  // Action Répondre dans le panneau : préremplit @Nom et donne le focus
  document.addEventListener("click", (e) => {
    const replyBtn = e.target.closest(".drawer-reply-btn");
    if (replyBtn) {
      e.preventDefault();
      const author = replyBtn.dataset.replyAuthor || "";
      if (drawerCommentInput) {
        const prefix = author ? `@${author} ` : "";
        if (!drawerCommentInput.value.startsWith(prefix)) {
          drawerCommentInput.value = prefix + drawerCommentInput.value;
        }
        drawerCommentInput.focus();
        drawerCommentInput.setSelectionRange(drawerCommentInput.value.length, drawerCommentInput.value.length);
      }
    }
  });

  // Raccourci clavier dans le textarea : Entrée pour envoyer (Maj+Entrée pour nouvelle ligne)
  if (drawerCommentInput && drawerCommentForm) {
    drawerCommentInput.addEventListener("keydown", (e) => {
      if (e.key === "Enter" && !e.shiftKey) {
        e.preventDefault();
        drawerCommentForm.dispatchEvent(new Event("submit", { cancelable: true, bubbles: true }));
      }
    });
  }

  // Intercepter les clics sur les cartes ou boutons de discussion du salon
  document.addEventListener("click", (e) => {
    // 1. Bouton spécifique [data-open-drawer-discussion] ou lien de discussion
    const btn = e.target.closest("[data-open-drawer-discussion]");
    if (btn) {
      const pubId = btn.dataset.openDrawerDiscussion;
      if (pubId) {
        e.preventDefault();
        openDiscussionDrawer(pubId);
        return;
      }
    }

    // 2. Clic sur le titre de la publication dans le fil si c'est une discussion
    const postTitleLink = e.target.closest(".campus-post-title-link");
    if (postTitleLink) {
      const postCard = postTitleLink.closest(".campus-post-card");
      if (postCard && postCard.dataset.postType === "discussion") {
        e.preventDefault();
        const pubId = postCard.dataset.livePublicationId || postCard.dataset.publicationCard;
        if (pubId) {
          openDiscussionDrawer(pubId);
          return;
        }
      }
    }

    // 3. Clic général sur la carte si l'utilisateur clique hors boutons interactifs
    const postCard = e.target.closest(".campus-post-card");
    if (postCard && postCard.dataset.postType === "discussion") {
      const isInteractive = e.target.closest("button, a, input, textarea, select, .epi-reaction-picker, .campus-code-block, fieldset");
      if (!isInteractive) {
        const pubId = postCard.dataset.livePublicationId || postCard.dataset.publicationCard;
        if (pubId) {
          openDiscussionDrawer(pubId);
        }
      }
    }
  });

  // Soumission AJAX du formulaire de commentaire dans le drawer
  if (drawerCommentForm) {
    drawerCommentForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const content = drawerCommentInput.value.trim();
      const publicationId = drawerPublicationIdInput.value;
      if (!content || !publicationId) return;

      const submitBtn = document.getElementById("drawerSubmitCommentBtn");
      if (submitBtn) submitBtn.disabled = true;

      const formData = new FormData(drawerCommentForm);
      formData.set("ajax", "1");

      try {
        const response = await fetch(drawerCommentForm.action, {
          method: "POST",
          headers: { "Accept": "application/json" },
          body: formData
        });
        const result = await response.json();
        if (!response.ok || !result.success) {
          throw new Error(result.message || "Erreur lors de l'envoi du commentaire.");
        }

        drawerCommentInput.value = "";

        // Ajouter le nouveau commentaire à la liste
        const data = result.data || {};
        const authorName = `${data.prenom || ''} ${data.nom || ''}`.trim() || "Vous";
        const commentObj = {
          id: data.id || Date.now(),
          prenom: data.prenom || 'Vous',
          nom: data.nom || '',
          author_avatar: data.author_avatar || '',
          date_creation: "À l'instant",
          contenu: content,
          likes_count: 0,
          user_liked: false
        };

        if (drawerCommentsList.children.length === 1 && drawerCommentsList.children[0].textContent.includes("Aucun commentaire")) {
          drawerCommentsList.innerHTML = "";
        }

        const tempDiv = document.createElement("div");
        tempDiv.innerHTML = buildCommentHtml(commentObj, false);
        const newCard = tempDiv.firstElementChild;
        drawerCommentsList.appendChild(newCard);
        newCard.scrollIntoView({ behavior: "smooth", block: "nearest" });

        const currentCount = drawerCommentsList.querySelectorAll(".drawer-comment-card").length + (drawerPinnedContainer && !drawerPinnedContainer.hidden ? 1 : 0);
        if (drawerCommentsHeaderCount) {
          drawerCommentsHeaderCount.textContent = `Commentaires (${currentCount})`;
        }
        drawerCommentsCount.textContent = `${currentCount} commentaire${currentCount > 1 ? 's' : ''}`;

        const cardCommentCount = document.querySelector(`article[data-live-publication-id="${publicationId}"] [data-live-comment-count]`);
        if (cardCommentCount) {
          cardCommentCount.textContent = currentCount;
        }

        if (typeof window.showFeedback === "function") {
          window.showFeedback("Commentaire publié !", "success");
        }

      } catch (err) {
        alert(err.message || "Erreur lors de l'envoi.");
      } finally {
        if (submitBtn) submitBtn.disabled = false;
      }
    });
  }
});