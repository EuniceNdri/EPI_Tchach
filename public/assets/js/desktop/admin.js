/**
 * ============================================================
 * EPI TCHACH – Script JavaScript Desktop Administration (admin.js)
 * Interactions dynamiques : Recherche, Filtres, Modération & Actions
 * ============================================================
 */

document.addEventListener("DOMContentLoaded", () => {
    // 1. Recherche instantanée dans les tableaux d'utilisateurs
    const searchInput = document.getElementById("adminUserSearch");
    const usersTable = document.getElementById("usersTable");

    if (searchInput && usersTable) {
        searchInput.addEventListener("input", function() {
            const query = this.value.toLowerCase().trim();
            const rows = usersTable.querySelectorAll("tbody tr");

            rows.forEach((row) => {
                const text = row.textContent.toLowerCase();
                if (text.includes(query)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        });
    }

    // 2. Filtres par rôle dans la table utilisateurs
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
                        if (rowRole === targetRole) {
                            row.style.display = "";
                        } else {
                            row.style.display = "none";
                        }
                    }
                });
            });
        });
    }

    // 3. Actions de modération (Approuver / Supprimer / Avertir)
    document.querySelectorAll(".btn-mod-approve").forEach((btn) => {
        btn.addEventListener("click", function() {
            const reportCard = this.closest(".report-card");
            if (reportCard) {
                reportCard.style.transition = "all 0.3s ease";
                reportCard.style.opacity = "0";
                reportCard.style.transform = "scale(0.95)";
                setTimeout(() => {
                    reportCard.remove();
                    alert("Signalement approuvé et classé sans suite.");
                }, 300);
            }
        });
    });

    document.querySelectorAll(".btn-mod-delete").forEach((btn) => {
        btn.addEventListener("click", function() {
            if (confirm("Confirmez-vous la suppression définitive de cette publication non conforme à la charte ?")) {
                const reportCard = this.closest(".report-card");
                if (reportCard) {
                    reportCard.style.transition = "all 0.3s ease";
                    reportCard.style.opacity = "0";
                    reportCard.style.transform = "scale(0.95)";
                    setTimeout(() => {
                        reportCard.remove();
                        alert("Publication supprimée et auteur notifié.");
                    }, 300);
                }
            }
        });
    });

    // 4. Action de suspension / déblocage utilisateur
    document.querySelectorAll(".btn-toggle-ban").forEach((btn) => {
        btn.addEventListener("click", function() {
            const row = this.closest("tr");
            const statusPill = row ? row.querySelector(".status-pill") : null;
            const isBanned = this.getAttribute("data-banned") === "true";

            if (isBanned) {
                this.setAttribute("data-banned", "false");
                this.textContent = "Suspendre";
                this.className = "btn-table-action btn-table-danger btn-toggle-ban";
                if (statusPill) {
                    statusPill.className = "status-pill status-active";
                    statusPill.textContent = "Actif";
                }
                alert("Le compte de l'étudiant a été réactivé.");
            } else {
                if (confirm("Voulez-vous suspendre temporairement l'accès de cet utilisateur ?")) {
                    this.setAttribute("data-banned", "true");
                    this.textContent = "Réactiver";
                    this.className = "btn-table-action btn-toggle-ban";
                    if (statusPill) {
                        statusPill.className = "status-pill status-banned";
                        statusPill.textContent = "Suspendu";
                    }
                }
            }
        });
    });

    // 5. Simulation formulaire paramètres
    const settingsForm = document.getElementById("adminSettingsForm");
    if (settingsForm) {
        settingsForm.addEventListener("submit", (e) => {
            e.preventDefault();
            alert("Paramètres de la plateforme EPI Tchach enregistrés avec succès !");
        });
    }

    // 6. Simulation création de salon
    const newChannelForm = document.getElementById("newChannelForm");
    if (newChannelForm) {
        newChannelForm.addEventListener("submit", (e) => {
            e.preventDefault();
            alert("Nouveau salon d'échange créé avec succès et ouvert aux étudiants de la filière !");
            newChannelForm.reset();
        });
    }
});