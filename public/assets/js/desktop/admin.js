/**
 * ============================================================
 * EPI TCHACH – Script JavaScript Desktop Administration (admin.js)
 * Interactions dynamiques : Modale d'édition, Recherche, Filtres, Modération & Suppression
 * Zéro rechargement de page, zéro clignotement
 * ============================================================
 */

document.addEventListener("DOMContentLoaded", () => {
    // Détermination de la base URL relative
    const getBaseUrl = () => {
        const path = window.location.pathname;
        const match = path.match(/^(.*?\/admin)/);
        if (match) {
            return match[1].replace(/\/admin$/, "");
        }
        return "";
    };

    const baseUrl = getBaseUrl();

    // ------------------------------------------------------------
    // 0. Système de Toast Notifications léger & moderne
    // ------------------------------------------------------------
    const showToast = (message, type = "success") => {
        let container = document.querySelector(".admin-toast-container");
        if (!container) {
            container = document.createElement("div");
            container.className = "admin-toast-container";
            document.body.appendChild(container);
        }

        const toast = document.createElement("div");
        toast.className = `admin-toast ${type}`;
        
        const iconSvg = type === "success" 
            ? `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>`
            : `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`;

        toast.innerHTML = `${iconSvg}<span>${message}</span>`;
        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = "0";
            toast.style.transform = "translateY(15px)";
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    };

    // ------------------------------------------------------------
    // 1. Recherche instantanée dans les tableaux
    // ------------------------------------------------------------
    const searchInput = document.getElementById("adminUserSearch");
    const usersTable = document.getElementById("usersTable");

    if (searchInput && usersTable) {
        searchInput.addEventListener("input", function() {
            const query = this.value.toLowerCase().trim();
            const rows = usersTable.querySelectorAll("tbody tr");

            rows.forEach((row) => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? "" : "none";
            });
        });
    }

    // ------------------------------------------------------------
    // 2. Filtres par rôle dans la table utilisateurs
    // ------------------------------------------------------------
    const roleFilterBtns = document.querySelectorAll(".filter-btn[data-role]");
    if (roleFilterBtns.length > 0 && usersTable) {
        roleFilterBtns.forEach((btn) => {
            btn.addEventListener("click", () => {
                roleFilterBtns.forEach((b) => b.classList.remove("active"));
                btn.classList.add("active");

                const targetRole = btn.getAttribute("data-role");
                const rows = usersTable.querySelectorAll("tbody tr");

                rows.forEach((row) => {
                    if (targetRole === "all") {
                        row.style.display = "";
                    } else {
                        const rowRole = row.getAttribute("data-role");
                        row.style.display = (rowRole === targetRole) ? "" : "none";
                    }
                });
            });
        });
    }

    // ------------------------------------------------------------
    // 3. Modale Popup d'Édition Utilisateur (AJAX sans rechargement)
    // ------------------------------------------------------------
    const editModal = document.getElementById("adminEditUserModal");
    const editForm = document.getElementById("adminEditUserForm");
    const btnCloseModal = document.getElementById("btnCloseEditModal");
    const btnCancelModal = document.getElementById("btnCancelEditModal");
    const roleSelect = document.getElementById("editUserRole");
    const filiereGroup = document.getElementById("filiereGroup");
    const modulesGroup = document.getElementById("modulesGroup");

    const openEditModal = () => {
        if (editModal) {
            editModal.style.display = "flex";
            document.body.style.overflow = "hidden";
        }
    };

    const closeEditModal = () => {
        if (editModal) {
            editModal.style.display = "none";
            document.body.style.overflow = "";
            if (editForm) editForm.reset();
        }
    };

    if (btnCloseModal) btnCloseModal.addEventListener("click", closeEditModal);
    if (btnCancelModal) btnCancelModal.addEventListener("click", closeEditModal);
    if (editModal) {
        editModal.addEventListener("click", (e) => {
            if (e.target === editModal) closeEditModal();
        });
    }

    // Bascule dynamique filière vs modules selon le rôle sélectionné
    const updateRoleFields = (role) => {
        const isStudent = role === "etudiant";
        document.querySelectorAll("[data-zone-role]").forEach((select) => {
            select.disabled = !isStudent;
            if (!isStudent) select.value = "membre";
        });
        document.querySelectorAll("[data-zone-exception]").forEach((checkbox) => {
            checkbox.disabled = !isStudent;
            if (!isStudent) checkbox.checked = false;
        });
        if (!filiereGroup || !modulesGroup) return;
        if (role === "professeur") {
            filiereGroup.style.display = "none";
            modulesGroup.style.display = "block";
        } else if (role === "administrateur" || role === "admin") {
            filiereGroup.style.display = "none";
            modulesGroup.style.display = "none";
        } else {
            filiereGroup.style.display = "block";
            modulesGroup.style.display = "none";
        }
    };

    if (roleSelect) {
        roleSelect.addEventListener("change", function() {
            updateRoleFields(this.value);
        });
    }
    document.getElementById("editUserFiliere")?.addEventListener("input", () => {
        document.querySelectorAll("[data-zone-exception]").forEach((checkbox) => {
            checkbox.checked = false;
        });
    });

    // Clic sur un bouton "Éditer" (délégation pour tous les tableaux)
    document.addEventListener("click", async (e) => {
        const editBtn = e.target.closest(".btn-edit-user");
        if (!editBtn) return;

        const userId = editBtn.getAttribute("data-user-id");
        if (!userId) return;

        try {
            editBtn.disabled = true;
            editBtn.textContent = "...";

            const res = await fetch(`${baseUrl}/admin/users/json?id=${userId}`);
            const data = await res.json();

            editBtn.disabled = false;
            editBtn.textContent = "Éditer";

            if (data.success && data.user) {
                const u = data.user;
                document.getElementById("editUserId").value = u.id || "";
                document.getElementById("editUserPrenom").value = u.prenom || "";
                document.getElementById("editUserNom").value = u.nom || "";
                document.getElementById("editUserEmail").value = u.email || "";
                
                const userRole = (u.role || "etudiant").toLowerCase();
                const cleanRole = userRole === "admin" ? "administrateur" : userRole;
                if (roleSelect) roleSelect.value = cleanRole;

                document.getElementById("editUserFiliere").value = u.filiere || "";
                document.getElementById("editUserModules").value = u.modules_enseignes || "";
                document.getElementById("editUserBio").value = u.biographie || "";
                document.querySelectorAll("[data-zone-role]").forEach((select) => {
                    select.value = (data.zone_roles || {})[select.dataset.zoneRole] || "membre";
                });
                const exceptionZones = new Set((data.zone_exceptions || []).map(String));
                document.querySelectorAll("[data-zone-exception]").forEach((checkbox) => {
                    checkbox.checked = exceptionZones.has(checkbox.dataset.zoneException);
                });

                updateRoleFields(cleanRole);
                openEditModal();
            } else {
                showToast(data.message || "Impossible de charger cet utilisateur.", "error");
            }
        } catch (err) {
            editBtn.disabled = false;
            editBtn.textContent = "Éditer";
            showToast("Erreur de connexion au serveur.", "error");
        }
    });

    // Soumission du formulaire d'édition en AJAX
    if (editForm) {
        editForm.addEventListener("submit", async function(e) {
            e.preventDefault();

            const saveBtn = document.getElementById("btnSaveUserModal");
            const originalText = saveBtn ? saveBtn.textContent : "Enregistrer";

            try {
                if (saveBtn) {
                    saveBtn.disabled = true;
                    saveBtn.textContent = "Enregistrement...";
                }

                const formData = new FormData(editForm);
                const res = await fetch(`${baseUrl}/admin/users/edit`, {
                    method: "POST",
                    body: formData,
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    }
                });

                const data = await res.json();

                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.textContent = originalText;
                }

                if (data.success && data.user) {
                    const u = data.user;
                    const row = document.querySelector(`tr[data-user-id="${u.id}"]`);

                    if (row) {
                        // Mise à jour fluide du DOM sans recharger la page
                        const nameEl = row.querySelector(".user-full-name");
                        if (nameEl) nameEl.textContent = `${u.prenom} ${u.nom}`;

                        const emailEl = row.querySelector(".user-email-sub");
                        if (emailEl) emailEl.textContent = u.email;

                        const filiereEl = row.querySelector(".user-filiere-cell");
                        if (filiereEl) {
                            filiereEl.textContent = u.filiere || u.modules_enseignes || "Non spécifié";
                        }

                        const roleCell = row.querySelector(".user-role-cell") || row.querySelector("td:nth-child(4)");
                        const cleanRole = (u.role || "etudiant").toLowerCase();
                        row.setAttribute("data-role", cleanRole === "admin" ? "administrateur" : cleanRole);

                        if (roleCell) {
                            let roleHtml = `<span class="role-pill">Étudiant</span>`;
                            if (cleanRole === "administrateur" || cleanRole === "admin") {
                                roleHtml = `<span class="role-pill role-admin">Administrateur</span>`;
                            } else if (cleanRole === "professeur") {
                                roleHtml = `<span class="role-pill role-prof">Professeur</span>`;
                            }
                            roleCell.innerHTML = roleHtml;
                        }

                        const responsibilitiesCell = row.querySelector(".user-responsibilities-cell");
                        if (responsibilitiesCell) {
                            responsibilitiesCell.textContent = u.responsabilites || "Aucune";
                        }

                        // Effet visuel subtil de confirmation sur la ligne modifiée
                        row.style.transition = "background-color 0.4s ease";
                        row.style.backgroundColor = "rgba(34, 197, 94, 0.15)";
                        setTimeout(() => {
                            row.style.backgroundColor = "";
                        }, 1200);
                    }

                    closeEditModal();
                    showToast(data.message || "Utilisateur mis à jour avec succès !", "success");
                } else {
                    showToast(data.message || "Erreur lors de la mise à jour.", "error");
                }
            } catch (err) {
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.textContent = originalText;
                }
                showToast("Erreur de communication avec le serveur.", "error");
            }
        });
    }

    // ------------------------------------------------------------
    // 4. Suppression définitive d'un utilisateur (AJAX)
    // ------------------------------------------------------------
    document.addEventListener("click", async (e) => {
        const deleteBtn = e.target.closest(".btn-delete-user");
        if (!deleteBtn) return;

        const userId = deleteBtn.getAttribute("data-user-id");
        if (!userId) return;

        const row = deleteBtn.closest("tr");
        const userName = row ? row.querySelector(".user-full-name")?.textContent : "cet utilisateur";

        if (!confirm(`Êtes-vous certain de vouloir supprimer définitivement ${userName} ?\nCette action est irréversible.`)) {
            return;
        }

        try {
            deleteBtn.disabled = true;
            deleteBtn.textContent = "...";

            const formData = new FormData();
            formData.append("id", userId);

            const res = await fetch(`${baseUrl}/admin/users/delete`, {
                method: "POST",
                body: formData,
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            });

            const data = await res.json();

            if (data.success) {
                if (row) {
                    row.style.transition = "all 0.35s ease";
                    row.style.opacity = "0";
                    row.style.transform = "translateX(20px)";
                    setTimeout(() => {
                        row.remove();
                        // Mise à jour du compteur total
                        const countSpan = document.getElementById("userCountTotal");
                        if (countSpan) {
                            const current = parseInt(countSpan.textContent.replace(/\s+/g, ""), 10) || 0;
                            if (current > 0) countSpan.textContent = (current - 1).toLocaleString("fr-FR");
                        }
                    }, 350);
                }
                showToast(data.message || "Utilisateur supprimé de la base.", "success");
            } else {
                deleteBtn.disabled = false;
                deleteBtn.textContent = "Supprimer";
                showToast(data.message || "Impossible de supprimer cet utilisateur.", "error");
            }
        } catch (err) {
            deleteBtn.disabled = false;
            deleteBtn.textContent = "Supprimer";
            showToast("Erreur lors de la suppression.", "error");
        }
    });

    // ------------------------------------------------------------
    // 5. Décisions de modération :
    //    - avertiAuteur()      : notifie l'auteur, clôture le signalement
    //    - bloquerPublication(): verrouille la publication, notifie l'auteur, clôture le signalement
    //    - supprimerContenu()  : supprime le post en BD, notifie l'auteur du motif, clôture tous les signalements liés
    //    - rejeterSignalement(): statut 'rejeté' — si 0 signalement restant: est_signale = 0
    // ------------------------------------------------------------
    const modModal = document.getElementById("moderationDecisionModal");
    const modForm = document.getElementById("moderationActionForm");
    const modReportIdInput = document.getElementById("modReportId");
    const modActionInput = document.getElementById("modAction");
    const modModalTitle = document.getElementById("modModalTitle");
    const modModalSubtitle = document.getElementById("modModalSubtitle");
    const modModalIcon = document.getElementById("modModalIcon");
    const modMessageGroup = document.getElementById("modMessageGroup");
    const modMessageLabel = document.getElementById("modMessageLabel");
    const modMessageInput = document.getElementById("modMessageInput");
    const modMessageHint = document.getElementById("modMessageHint");
    const modConfirmNotice = document.getElementById("modConfirmNotice");
    const btnSubmitMod = document.getElementById("btnSubmitModDecision");
    const btnCloseMod = document.getElementById("btnCloseModModal");
    const btnCancelMod = document.getElementById("btnCancelModModal");

    let currentTargetCard = null;

    const closeModModal = () => {
        if (modModal) {
            modModal.style.display = "none";
        }
        if (modForm) {
            modForm.reset();
        }
        currentTargetCard = null;
    };

    if (btnCloseMod) btnCloseMod.addEventListener("click", closeModModal);
    if (btnCancelMod) btnCancelMod.addEventListener("click", closeModModal);
    if (modModal) {
        modModal.addEventListener("click", (e) => {
            if (e.target === modModal) closeModModal();
        });
    }

    const updateReportBadge = () => {
        const badge = document.getElementById("reportCounterBadge");
        const remaining = document.querySelectorAll(".report-card").length;
        if (badge) {
            badge.textContent = `${remaining} signalement(s) à traiter`;
        }
        if (remaining === 0) {
            const container = document.getElementById("reportsListContainer");
            if (container && !document.getElementById("noReportsCard")) {
                container.innerHTML = `
                    <div id="noReportsCard" class="admin-card" style="text-align:center; padding:3rem 1.5rem; color:var(--admin-muted);">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 0.75rem; display:block; opacity:0.6;"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                        <h3 style="font-size:1.1rem; font-weight:800; color:var(--admin-dark); margin:0 0 0.5rem 0;">Aucun signalement en attente</h3>
                        <p style="font-size:0.9rem; margin:0;">Tous les salons respectent actuellement la charte de modération du campus.</p>
                    </div>
                `;
            }
        }
    };

    // Configuration et ouverture de la modale de décision
    document.querySelectorAll(".btn-mod-action").forEach((btn) => {
        btn.addEventListener("click", function() {
            const reportId = this.getAttribute("data-report-id");
            const action = this.getAttribute("data-action");
            const author = this.getAttribute("data-author") || "l'auteur";
            const title = this.getAttribute("data-title") || "la publication";
            currentTargetCard = this.closest(".report-card");

            if (!modModal || !reportId || !action) return;

            modReportIdInput.value = reportId;
            modActionInput.value = action;

            if (action === "avertiAuteur") {
                modModalTitle.textContent = "Avertir l'auteur du contenu";
                modModalSubtitle.textContent = `Auteur ciblé : ${author}`;
                modModalIcon.style.background = "#fef3c7";
                modModalIcon.innerHTML = `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#b45309" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`;
                modMessageGroup.style.display = "block";
                modMessageLabel.textContent = "Motif ou message d'avertissement";
                modMessageInput.value = "Votre publication enfreint les règles de courtoisie et de bienséance du forum.";
                modMessageHint.textContent = "L'auteur recevra une notification formelle et le signalement sera clôturé.";
                modConfirmNotice.style.display = "none";
                btnSubmitMod.style.background = "#d97706";
                btnSubmitMod.style.color = "#ffffff";
                btnSubmitMod.textContent = "Envoyer l'avertissement";
            } else if (action === "supprimerContenu") {
                modModalTitle.textContent = "Supprimer le contenu de la base de données";
                modModalSubtitle.textContent = `Cible : "${title}" · Auteur : ${author}`;
                modModalIcon.style.background = "#fee2e2";
                modModalIcon.innerHTML = `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>`;
                modMessageGroup.style.display = "block";
                modMessageLabel.textContent = "Motif de la suppression communiqué à l'auteur";
                modMessageInput.value = "Non-respect de la charte de l'école (propos non conformes).";
                modMessageHint.textContent = "Supprime le post en BD, notifie l'auteur du motif et clôture tous les signalements liés.";
                modConfirmNotice.style.display = "none";
                btnSubmitMod.style.background = "#dc2626";
                btnSubmitMod.style.color = "#ffffff";
                btnSubmitMod.textContent = "Supprimer le contenu & Clôturer";
            } else if (action === "bloquerPublication") {
                modModalTitle.textContent = "Bloquer et verrouiller la publication";
                modModalSubtitle.textContent = `Publication : "${title}" · Auteur : ${author}`;
                modModalIcon.style.background = "#ede9fe";
                modModalIcon.innerHTML = `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#6d28d9" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>`;
                modMessageGroup.style.display = "none";
                modConfirmNotice.style.display = "block";
                modConfirmNotice.innerHTML = `🔒 <strong>Action immédiate :</strong> La publication sera verrouillée (statut <em>fermé</em> et commentaires désactivés). L'auteur sera immédiatement notifié et ce signalement sera clôturé.`;
                btnSubmitMod.style.background = "#6d28d9";
                btnSubmitMod.style.color = "#ffffff";
                btnSubmitMod.textContent = "Verrouiller la publication";
            } else if (action === "rejeterSignalement") {
                modModalTitle.textContent = "Rejeter le signalement";
                modModalSubtitle.textContent = `Publication : "${title}" (Contenu jugé conforme)`;
                modModalIcon.style.background = "#f1f5f9";
                modModalIcon.innerHTML = `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#475569" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>`;
                modMessageGroup.style.display = "none";
                modConfirmNotice.style.display = "block";
                modConfirmNotice.innerHTML = `✓ <strong>Classement sans suite :</strong> Le signalement passera au statut <em>rejeté</em>. Si 0 signalement restant en attente sur cette publication, son alerte sera levée (<strong>est_signale = 0</strong>).`;
                btnSubmitMod.style.background = "#0f172a";
                btnSubmitMod.style.color = "#ffffff";
                btnSubmitMod.textContent = "Rejeter le signalement";
            }

            modModal.style.display = "flex";
        });
    });

    // Traitement du formulaire de modération
    if (modForm) {
        modForm.addEventListener("submit", async function(e) {
            e.preventDefault();

            const reportId = modReportIdInput.value;
            const action = modActionInput.value;
            const message = modMessageInput.value.trim();

            if (!reportId || !action) return;

            const initialText = btnSubmitMod.textContent;
            btnSubmitMod.disabled = true;
            btnSubmitMod.textContent = "Traitement en cours...";

            const formData = new FormData();
            formData.append("report_id", reportId);
            formData.append("action", action);
            if (message) {
                formData.append("message", message);
                formData.append("motif", message);
            }

            try {
                const response = await fetch(`${baseUrl}/moderation/action`, {
                    method: "POST",
                    body: formData,
                    headers: { "X-Requested-With": "XMLHttpRequest" }
                });

                const data = await response.json();

                if (data.success) {
                    showToast(data.message || "Action de modération effectuée avec succès.", "success");
                    closeModModal();

                    if (currentTargetCard) {
                        currentTargetCard.style.transition = "all 0.35s cubic-bezier(0.16, 1, 0.3, 1)";
                        currentTargetCard.style.opacity = "0";
                        currentTargetCard.style.transform = "scale(0.95)";
                        setTimeout(() => {
                            currentTargetCard.remove();
                            updateReportBadge();
                        }, 350);
                    }
                } else {
                    showToast(data.message || "Erreur lors de la prise de décision.", "error");
                }
            } catch (err) {
                showToast("Erreur de connexion avec le serveur.", "error");
            } finally {
                btnSubmitMod.disabled = false;
                btnSubmitMod.textContent = initialText;
            }
        });
    }

    // Compatibilité boutons secondaires legacy
    document.querySelectorAll(".btn-mod-approve").forEach((btn) => {
        btn.addEventListener("click", async function() {
            const reportCard = this.closest(".report-card");
            const reportId = this.getAttribute("data-report-id") || (reportCard ? reportCard.getAttribute("data-report-id") : null);

            if (reportId) {
                const formData = new FormData();
                formData.append("report_id", reportId);
                formData.append("action", "rejeterSignalement");

                try {
                    await fetch(`${baseUrl}/moderation/action`, {
                        method: "POST",
                        body: formData,
                        headers: { "X-Requested-With": "XMLHttpRequest" }
                    });
                } catch (e) {}
            }

            if (reportCard) {
                reportCard.style.transition = "all 0.3s ease";
                reportCard.style.opacity = "0";
                reportCard.style.transform = "scale(0.96)";
                setTimeout(() => {
                    reportCard.remove();
                    updateReportBadge();
                }, 300);
            }
            showToast("Signalement rejeté (publication jugée conforme).", "success");
        });
    });

    document.querySelectorAll(".btn-mod-delete").forEach((btn) => {
        btn.addEventListener("click", async function() {
            if (!confirm("Confirmez-vous la suppression de cette publication non conforme ?")) {
                return;
            }

            const reportCard = this.closest(".report-card");
            const reportId = this.getAttribute("data-report-id") || (reportCard ? reportCard.getAttribute("data-report-id") : null);

            if (reportId) {
                const formData = new FormData();
                formData.append("report_id", reportId);
                formData.append("action", "supprimerContenu");
                formData.append("message", "Non-respect de la charte de modération.");

                try {
                    await fetch(`${baseUrl}/moderation/action`, {
                        method: "POST",
                        body: formData,
                        headers: { "X-Requested-With": "XMLHttpRequest" }
                    });
                } catch (e) {}
            }

            if (reportCard) {
                reportCard.style.transition = "all 0.3s ease";
                reportCard.style.opacity = "0";
                reportCard.style.transform = "scale(0.96)";
                setTimeout(() => {
                    reportCard.remove();
                    updateReportBadge();
                }, 300);
            }
            showToast("Publication supprimée en base de données et auteur notifié.", "success");
        });
    });

    // ------------------------------------------------------------
    // 6. Modale Fiche Détail Profil Membre (Popup dynamique)
    // ------------------------------------------------------------
    const viewProfileModal = document.getElementById("adminViewProfileModal");
    const btnCloseViewProfile = document.getElementById("btnCloseViewProfileModal");
    const btnCloseViewProfileBtn = document.getElementById("btnCloseViewProfileBtn");
    const btnEditFromView = document.getElementById("btnEditFromView");
    let currentViewedUserId = null;

    const closeViewProfileModal = () => {
        if (viewProfileModal) {
            viewProfileModal.style.display = "none";
            document.body.style.overflow = "";
        }
    };

    if (btnCloseViewProfile) btnCloseViewProfile.addEventListener("click", closeViewProfileModal);
    if (btnCloseViewProfileBtn) btnCloseViewProfileBtn.addEventListener("click", closeViewProfileModal);
    if (viewProfileModal) {
        viewProfileModal.addEventListener("click", (e) => {
            if (e.target === viewProfileModal) closeViewProfileModal();
        });
    }

    if (btnEditFromView) {
        btnEditFromView.addEventListener("click", () => {
            if (currentViewedUserId) {
                closeViewProfileModal();
                const editBtn = document.querySelector(`.btn-edit-user[data-user-id="${currentViewedUserId}"]`);
                if (editBtn) {
                    editBtn.click();
                } else {
                    // Clic virtuel si le bouton n'est pas dans le viewport
                    const triggerBtn = document.createElement("button");
                    triggerBtn.className = "btn-edit-user";
                    triggerBtn.setAttribute("data-user-id", currentViewedUserId);
                    document.body.appendChild(triggerBtn);
                    triggerBtn.click();
                    triggerBtn.remove();
                }
            }
        });
    }

    document.addEventListener("click", async (e) => {
        const viewBtn = e.target.closest(".btn-view-profile");
        if (!viewBtn) return;

        const userId = viewBtn.getAttribute("data-user-id");
        if (!userId) return;

        try {
            viewBtn.disabled = true;
            const originalText = viewBtn.textContent;
            viewBtn.textContent = "...";

            const res = await fetch(`${baseUrl}/admin/users/json?id=${userId}`);
            const data = await res.json();

            viewBtn.disabled = false;
            viewBtn.textContent = originalText;

            if (data.success && data.user) {
                const u = data.user;
                currentViewedUserId = u.id;

                const avatarEl = document.getElementById("viewUserAvatar");
                const nameEl = document.getElementById("viewUserName");
                const roleBadge = document.getElementById("viewUserRoleBadge");
                const matriculeEl = document.getElementById("viewUserMatricule");
                const emailEl = document.getElementById("viewUserEmail");
                const filiereLabel = document.getElementById("viewUserFiliereLabel");
                const filiereEl = document.getElementById("viewUserFiliere");
                const bioEl = document.getElementById("viewUserBio");
                const dateEl = document.getElementById("viewUserDate");
                const profileLink = document.getElementById("viewUserProfileLink");

                const fullName = `${u.prenom || ""} ${u.nom || ""}`.trim() || "Membre EPI";
                const initials = ((u.prenom?.[0] || "") + (u.nom?.[0] || "")).toUpperCase() || "EP";

                if (avatarEl) avatarEl.textContent = initials;
                if (nameEl) nameEl.textContent = fullName;
                if (matriculeEl) matriculeEl.textContent = `EPI-${String(u.id).padStart(4, "0")}`;
                if (emailEl) emailEl.textContent = u.email || "Non renseigné";
                if (bioEl) bioEl.textContent = u.biographie || "Aucune biographie renseignée pour ce membre.";
                if (dateEl) dateEl.textContent = `Inscrit le : ${u.created_at ? new Date(u.created_at).toLocaleDateString("fr-FR") : "Récemment"}`;

                if (profileLink) {
                    profileLink.href = `${baseUrl}/profile?id=${u.id}`;
                }

                const roleKey = (u.role || "etudiant").toLowerCase();
                if (roleBadge) {
                    let roleTitle = "Étudiant";
                    let roleClass = "role-pill";
                    if (roleKey === "professeur") {
                        roleTitle = "Professeur";
                        roleClass = "role-pill role-prof";
                    } else if (roleKey === "delegue") {
                        roleTitle = "Délégué de classe";
                        roleClass = "role-pill role-delegue";
                    } else if (roleKey === "administrateur" || roleKey === "admin") {
                        roleTitle = "Administrateur";
                        roleClass = "role-pill role-admin";
                    }
                    roleBadge.className = roleClass;
                    roleBadge.textContent = roleTitle;
                }

                if (filiereLabel && filiereEl) {
                    if (roleKey === "professeur") {
                        filiereLabel.textContent = "Modules & Filières Enseignés";
                        filiereEl.textContent = u.modules_enseignes || "Non spécifié";
                    } else {
                        filiereLabel.textContent = "Filière d'Étude";
                        filiereEl.textContent = u.filiere || "Non spécifiée";
                    }
                }

                if (viewProfileModal) {
                    viewProfileModal.style.display = "flex";
                    document.body.style.overflow = "hidden";
                }
            } else {
                showToast(data.message || "Impossible de charger la fiche du profil.", "error");
            }
        } catch (err) {
            viewBtn.disabled = false;
            viewBtn.textContent = "Détails";
            showToast("Erreur lors de la récupération du profil.", "error");
        }
    });

    // ------------------------------------------------------------
    // 7. Modale Création de Salon (Popup dynamique)
    // ------------------------------------------------------------
    const createChannelModal = document.getElementById("adminCreateChannelModal");
    const btnOpenCreateChannel = document.querySelectorAll(".btn-open-create-channel");
    const btnCloseCreateChannel = document.getElementById("btnCloseCreateChannelModal");
    const btnCancelCreateChannel = document.getElementById("btnCancelCreateChannel");
    const channelForm = document.getElementById("adminCreateChannelForm");
    const zoneSelect = document.getElementById("channelZoneSelect");
    const newZoneGroup = document.getElementById("newZoneNameGroup");
    const newZoneInput = document.getElementById("newZoneNameInput");

    const openCreateChannelModal = () => {
        if (createChannelModal) {
            createChannelModal.style.display = "flex";
            document.body.style.overflow = "hidden";
        }
    };

    const closeCreateChannelModal = () => {
        if (createChannelModal) {
            createChannelModal.style.display = "none";
            document.body.style.overflow = "";
            if (channelForm) channelForm.reset();
            if (newZoneGroup) newZoneGroup.style.display = "none";
            if (newZoneInput) newZoneInput.required = false;
        }
    };

    btnOpenCreateChannel.forEach((btn) => btn.addEventListener("click", openCreateChannelModal));
    if (btnCloseCreateChannel) btnCloseCreateChannel.addEventListener("click", closeCreateChannelModal);
    if (btnCancelCreateChannel) btnCancelCreateChannel.addEventListener("click", closeCreateChannelModal);
    if (createChannelModal) {
        createChannelModal.addEventListener("click", (e) => {
            if (e.target === createChannelModal) closeCreateChannelModal();
        });
    }

    if (zoneSelect) {
        zoneSelect.addEventListener("change", function() {
            if (this.value === "new") {
                if (newZoneGroup) newZoneGroup.style.display = "block";
                if (newZoneInput) {
                    newZoneInput.required = true;
                    newZoneInput.focus();
                }
            } else {
                if (newZoneGroup) newZoneGroup.style.display = "none";
                if (newZoneInput) newZoneInput.required = false;
            }
        });
    }

    if (channelForm) {
        channelForm.addEventListener("submit", async function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById("btnSubmitCreateChannel");
            const originalText = submitBtn ? submitBtn.textContent : "Créer le Salon";

            try {
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = "Création en cours...";
                }

                const formData = new FormData(channelForm);
                const res = await fetch(`${baseUrl}/admin/categories/create-category`, {
                    method: "POST",
                    body: formData,
                    headers: { "X-Requested-With": "XMLHttpRequest" }
                });

                const data = await res.json();

                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }

                if (data.success) {
                    closeCreateChannelModal();
                    showToast(data.message || "Nouveau salon créé avec succès !", "success");
                } else {
                    showToast(data.message || "Erreur lors de la création du salon.", "error");
                }
            } catch (err) {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
                showToast("Erreur de connexion lors de la création du salon.", "error");
            }
        });
    }
});