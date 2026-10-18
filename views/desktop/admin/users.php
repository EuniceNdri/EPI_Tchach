<?php
$activeAdminPage = 'users';
$base = isset($baseUrl) ? $baseUrl : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Administration — Gestion des Utilisateurs · EPI Tchach</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="<?= $base ?>/public/assets/css/desktop/admin.css" />
</head>
<body>
  <div class="admin-tricolor"></div>

  <div class="admin-wrapper">
    <!-- Barre Latérale Commune -->
    <?php include ROOT_PATH . '/views/desktop/partials/admin-sidebar.php'; ?>

    <main class="admin-main-area">
      <!-- Topbar -->
      <header class="admin-topbar">
        <div class="topbar-left">
          <h1 class="page-title">Gestion des Utilisateurs (1 248)</h1>
          <div class="search-box">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="adminUserSearch" placeholder="Rechercher par nom, email, matricule..." />
          </div>
        </div>

        <div class="topbar-right">
          <button type="button" class="btn-table-action" style="background:var(--admin-primary); color:#ffffff; padding:0.55rem 1.25rem; border-radius:9999px;" onclick="alert('Formulaire d\'invitation / import en masse d\'étudiants (CSV).');">
            + Ajouter un membre
          </button>
        </div>
      </header>

      <div class="admin-content">
        <!-- Filtres par Rôle -->
        <div style="display:flex; align-items:center; gap:0.65rem; margin-bottom:1.5rem; flex-wrap:wrap;">
          <span style="font-size:0.85rem; font-weight:700; color:var(--admin-muted); margin-right:0.5rem;">Filtrer par rôle :</span>
          <button type="button" class="btn-table-action filter-btn active" data-role="all">Tous (1 248)</button>
          <button type="button" class="btn-table-action filter-btn" data-role="etudiant">Étudiants (1 184)</button>
          <button type="button" class="btn-table-action filter-btn" data-role="delegue">Délégués (32)</button>
          <button type="button" class="btn-table-action filter-btn" data-role="professeur">Professeurs (28)</button>
          <button type="button" class="btn-table-action filter-btn" data-role="admin">Administrateurs (4)</button>
        </div>

        <!-- Tableau des Utilisateurs -->
        <div class="admin-card">
          <div class="table-responsive">
            <table class="admin-table" id="usersTable">
              <thead>
                <tr>
                  <th>Nom & Identité</th>
                  <th>Matricule</th>
                  <th>Filière & Promotion</th>
                  <th>Rôle</th>
                  <th>Date d'inscription</th>
                  <th>Statut</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr data-role="delegue">
                  <td>
                    <div class="user-cell">
                      <div class="user-avatar-small" style="background:#1e3a8a;">MK</div>
                      <div class="user-names">
                        <span class="user-full-name">Momo Kouassi</span>
                        <span class="user-email-sub">momo.kouassi@epi-yakro.ci</span>
                      </div>
                    </div>
                  </td>
                  <td><strong>EPI-2024-IDA-042</strong></td>
                  <td>BTS IDA 2ème Année</td>
                  <td><span class="role-pill role-delegue">Délégué</span></td>
                  <td>12 Octobre 2024</td>
                  <td><span class="status-pill status-active">Actif</span></td>
                  <td>
                    <div class="action-buttons">
                      <a href="<?= $base ?>/profile" class="btn-table-action">Voir profil</a>
                      <button type="button" class="btn-table-action btn-table-danger btn-toggle-ban">Suspendre</button>
                    </div>
                  </td>
                </tr>

                <tr data-role="professeur">
                  <td>
                    <div class="user-cell">
                      <div class="user-avatar-small" style="background:#0284c7;">IK</div>
                      <div class="user-names">
                        <span class="user-full-name">Dr. Ibrahim Koné</span>
                        <span class="user-email-sub">i.kone@epi-yakro.ci</span>
                      </div>
                    </div>
                  </td>
                  <td><strong>EPI-PROF-012</strong></td>
                  <td>Enseignant Web & BDD</td>
                  <td><span class="role-pill role-prof">Professeur</span></td>
                  <td>01 Septembre 2023</td>
                  <td><span class="status-pill status-active">Actif</span></td>
                  <td>
                    <div class="action-buttons">
                      <a href="<?= $base ?>/profile" class="btn-table-action">Voir profil</a>
                      <button type="button" class="btn-table-action" onclick="alert('Modifier les filières assignées au professeur.');">Modifier</button>
                    </div>
                  </td>
                </tr>

                <tr data-role="etudiant">
                  <td>
                    <div class="user-cell">
                      <div class="user-avatar-small" style="background:#d97706;">AN</div>
                      <div class="user-names">
                        <span class="user-full-name">Awa N'Guessan</span>
                        <span class="user-email-sub">awa.nguessan@epi-yakro.ci</span>
                      </div>
                    </div>
                  </td>
                  <td><strong>EPI-2025-BAT-108</strong></td>
                  <td>BTS Bâtiment 1ère Année</td>
                  <td><span class="role-pill">Étudiante</span></td>
                  <td>15 Septembre 2025</td>
                  <td><span class="status-pill status-active">Actif</span></td>
                  <td>
                    <div class="action-buttons">
                      <a href="<?= $base ?>/profile" class="btn-table-action">Voir profil</a>
                      <button type="button" class="btn-table-action btn-table-danger btn-toggle-ban">Suspendre</button>
                    </div>
                  </td>
                </tr>

                <tr data-role="etudiant">
                  <td>
                    <div class="user-cell">
                      <div class="user-avatar-small" style="background:#dc2626;">JD</div>
                      <div class="user-names">
                        <span class="user-full-name">Jean Diallo</span>
                        <span class="user-email-sub">jean.diallo@epi-yakro.ci</span>
                      </div>
                    </div>
                  </td>
                  <td><strong>EPI-2024-LIC-019</strong></td>
                  <td>Licence Informatique 3</td>
                  <td><span class="role-pill">Étudiant</span></td>
                  <td>05 Novembre 2024</td>
                  <td><span class="status-pill status-active">Actif</span></td>
                  <td>
                    <div class="action-buttons">
                      <a href="<?= $base ?>/profile" class="btn-table-action">Voir profil</a>
                      <button type="button" class="btn-table-action btn-table-danger btn-toggle-ban">Suspendre</button>
                    </div>
                  </td>
                </tr>

                <tr data-role="admin">
                  <td>
                    <div class="user-cell">
                      <div class="user-avatar-small" style="background:#0f172a;">DS</div>
                      <div class="user-names">
                        <span class="user-full-name">Directeur Scolarité</span>
                        <span class="user-email-sub">direction.pedagogique@epi-yakro.ci</span>
                      </div>
                    </div>
                  </td>
                  <td><strong>EPI-ADM-001</strong></td>
                  <td>Direction des Études</td>
                  <td><span class="role-pill role-admin">Administrateur</span></td>
                  <td>01 Janvier 2023</td>
                  <td><span class="status-pill status-active">Protégé</span></td>
                  <td>
                    <div class="action-buttons">
                      <span style="font-size:0.75rem; color:var(--admin-muted);">Compte Maître</span>
                    </div>
                  </td>
                <tr data-role="delegue">
                  <td>
                    <div class="user-cell">
                      <div class="user-avatar-small" style="background:#8b5cf6;">KY</div>
                      <div class="user-names">
                        <span class="user-full-name">Kofi Yao</span>
                        <span class="user-email-sub">kofi.yao@epi-yakro.ci</span>
                      </div>
                    </div>
                  </td>
                  <td><strong>EPI-2024-DEL-001</strong></td>
                  <td>Délégation Générale (EPI)</td>
                  <td><span class="role-pill role-delegue">Délégué Général</span></td>
                  <td>02 Octobre 2024</td>
                  <td><span class="status-pill status-active">Actif</span></td>
                  <td>
                    <div class="action-buttons">
                      <a href="<?= $base ?>/profile" class="btn-table-action">Voir profil</a>
                      <button type="button" class="btn-table-action btn-table-danger btn-toggle-ban">Suspendre</button>
                    </div>
                  </td>
                </tr>

                <tr data-role="etudiant">
                  <td>
                    <div class="user-cell">
                      <div class="user-avatar-small" style="background:#ec4899;">ST</div>
                      <div class="user-names">
                        <span class="user-full-name">Sarah Traoré</span>
                        <span class="user-email-sub">sarah.traore@epi-yakro.ci</span>
                      </div>
                    </div>
                  </td>
                  <td><strong>EPI-2024-IDA-015</strong></td>
                  <td>BTS IDA 2ème Année</td>
                  <td><span class="role-pill">Étudiante</span></td>
                  <td>14 Octobre 2024</td>
                  <td><span class="status-pill status-active">Actif</span></td>
                  <td>
                    <div class="action-buttons">
                      <a href="<?= $base ?>/profile" class="btn-table-action">Voir profil</a>
                      <button type="button" class="btn-table-action btn-table-danger btn-toggle-ban">Suspendre</button>
                    </div>
                  </td>
                </tr>

                <tr data-role="professeur">
                  <td>
                    <div class="user-cell">
                      <div class="user-avatar-small" style="background:#14b8a6;">FB</div>
                      <div class="user-names">
                        <span class="user-full-name">Fatou Bamba</span>
                        <span class="user-email-sub">fatou.bamba@epi-yakro.ci</span>
                      </div>
                    </div>
                  </td>
                  <td><strong>EPI-PROF-018</strong></td>
                  <td>Réseaux & Systèmes</td>
                  <td><span class="role-pill role-prof">Professeure</span></td>
                  <td>01 Septembre 2023</td>
                  <td><span class="status-pill status-active">Actif</span></td>
                  <td>
                    <div class="action-buttons">
                      <a href="<?= $base ?>/profile" class="btn-table-action">Voir profil</a>
                      <button type="button" class="btn-table-action">Modifier</button>
                    </div>
                  </td>
                </tr>

                <tr data-role="admin">
                  <td>
                    <div class="user-cell">
                      <div class="user-avatar-small" style="background:#dc2626;">PM</div>
                      <div class="user-names">
                        <span class="user-full-name">Paul Modo</span>
                        <span class="user-email-sub">paul.modo@epi-yakro.ci</span>
                      </div>
                    </div>
                  </td>
                  <td><strong>EPI-MOD-003</strong></td>
                  <td>Modération Campus</td>
                  <td><span class="role-pill" style="background:rgba(220,38,38,0.15); color:var(--admin-accent);">Modérateur</span></td>
                  <td>10 Janvier 2024</td>
                  <td><span class="status-pill status-active">Actif</span></td>
                  <td>
                    <div class="action-buttons">
                      <a href="<?= $base ?>/admin/moderation" class="btn-table-action">Modération</a>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </main>
  </div>

  <script src="<?= $base ?>/public/assets/js/desktop/admin.js"></script>
</body>
</html>

