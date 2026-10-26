<?php
$activeAdminPage = 'users';
$base = isset($baseUrl) ? $baseUrl : '';
$asset = isset($assetBase) ? $assetBase : (isset($baseUrl) ? $baseUrl . '/public/assets' : '/public/assets');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Administration — Gestion des Utilisateurs · EPI Tchach</title>
  <link rel="icon" type="image/svg+xml" href="<?= $asset ?>/images/logo_epi.svg" />
  <link rel="apple-touch-icon" href="<?= $asset ?>/images/logo.jpeg" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="<?= $asset ?>/css/desktop/admin.css" />
</head>
<body>
  <div class="admin-tricolor"></div>

  <div class="admin-wrapper">
    <!-- Barre Latérale Commune -->
    <?php include ROOT_PATH . '/views/desktop/partials/admin-sidebar.php'; ?>

    <main class="admin-main-area">
      <!-- Topbar -->
      <header class="admin-topbar">
        <?php
          $totalCount = array_sum($roleCounts ?? []);
          $countEtudiants = $roleCounts['etudiant'] ?? 0;
          $countDelegues = $roleCounts['delegue'] ?? 0;
          $countProfesseurs = $roleCounts['professeur'] ?? 0;
          $countAdmins = ($roleCounts['administrateur'] ?? 0) + ($roleCounts['admin'] ?? 0);
        ?>
        <div class="topbar-left">
          <h1 class="page-title">Gestion des Utilisateurs (<span id="userCountTotal"><?= number_format($totalCount, 0, ',', ' ') ?></span>)</h1>
          <div class="search-box">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="adminUserSearch" placeholder="Rechercher par nom, email, matricule, filière..." />
          </div>
        </div>

        <div class="topbar-right">
          <button type="button" class="btn-table-action" style="background:var(--admin-primary); color:#ffffff; padding:0.55rem 1.25rem; border-radius:9999px;" onclick="window.location.reload();">
            Actualiser
          </button>
        </div>
      </header>

      <div class="admin-content">
        <!-- Filtres par Rôle -->
        <div style="display:flex; align-items:center; gap:0.65rem; margin-bottom:1.5rem; flex-wrap:wrap;">
          <span style="font-size:0.85rem; font-weight:700; color:var(--admin-muted); margin-right:0.5rem;">Filtrer par rôle :</span>
          <button type="button" class="btn-table-action filter-btn active" data-role="all">Tous (<?= number_format($totalCount, 0, ',', ' ') ?>)</button>
          <button type="button" class="btn-table-action filter-btn" data-role="etudiant">Étudiants (<?= number_format($countEtudiants, 0, ',', ' ') ?>)</button>
          <button type="button" class="btn-table-action filter-btn" data-role="delegue">Délégués (<?= number_format($countDelegues, 0, ',', ' ') ?>)</button>
          <button type="button" class="btn-table-action filter-btn" data-role="professeur">Professeurs (<?= number_format($countProfesseurs, 0, ',', ' ') ?>)</button>
          <button type="button" class="btn-table-action filter-btn" data-role="administrateur">Administrateurs (<?= number_format($countAdmins, 0, ',', ' ') ?>)</button>
        </div>

        <!-- Tableau des Utilisateurs -->
        <div class="admin-card">
          <div class="table-responsive">
            <table class="admin-table" id="usersTable">
              <thead>
                <tr>
                  <th>Nom & Identité</th>
                  <th>Matricule / Réf</th>
                  <th>Filière / Modules</th>
                  <th>Rôle</th>
                  <th>Date d'inscription</th>
                  <th>Statut</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($users)): ?>
                  <tr>
                    <td colspan="7" style="text-align:center; padding:2rem; color:var(--admin-muted);">Aucun utilisateur trouvé.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($users as $u):
                      $roleKey = strtolower($u['role'] ?? 'etudiant');
                      $initials = strtoupper(mb_substr($u['prenom'] ?? 'U', 0, 1) . mb_substr($u['nom'] ?? 'N', 0, 1));
                      $colors = ['#1e3a8a', '#0284c7', '#d97706', '#16a34a', '#8b5cf6', '#dc2626'];
                      $color = $colors[($u['id'] ?? 1) % count($colors)];
                      $createdAt = !empty($u['created_at']) ? date('d/m/Y', strtotime($u['created_at'])) : 'Récemment';
                      $filiereText = !empty($u['filiere']) ? $u['filiere'] : (!empty($u['modules_enseignes']) ? $u['modules_enseignes'] : 'Non renseigné');
                  ?>
                  <tr data-user-id="<?= (int)$u['id'] ?>" data-role="<?= htmlspecialchars($roleKey === 'admin' ? 'administrateur' : $roleKey) ?>">
                    <td>
                      <div class="user-cell">
                        <div class="user-avatar-small" style="background:<?= $color ?>;"><?= htmlspecialchars($initials) ?></div>
                        <div class="user-names">
                          <span class="user-full-name"><?= htmlspecialchars(($u['prenom'] ?? '') . ' ' . ($u['nom'] ?? '')) ?></span>
                          <span class="user-email-sub"><?= htmlspecialchars($u['email'] ?? '') ?></span>
                        </div>
                      </div>
                    </td>
                    <td><strong>EPI-<?= str_pad((string)$u['id'], 4, '0', STR_PAD_LEFT) ?></strong></td>
                    <td class="user-filiere-cell"><?= htmlspecialchars($filiereText) ?></td>
                    <td class="user-role-cell">
                      <?php if ($roleKey === 'administrateur' || $roleKey === 'admin'): ?>
                        <span class="role-pill role-admin">Administrateur</span>
                      <?php elseif ($roleKey === 'professeur'): ?>
                        <span class="role-pill role-prof">Professeur</span>
                      <?php elseif ($roleKey === 'delegue'): ?>
                        <span class="role-pill role-delegue">Délégué</span>
                      <?php else: ?>
                        <span class="role-pill">Étudiant</span>
                      <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($createdAt) ?></td>
                    <td><span class="status-pill status-active">Actif</span></td>
                    <td>
                      <div class="action-buttons">
                        <button type="button" class="btn-table-action btn-view-profile" data-user-id="<?= (int)$u['id'] ?>">Détails</button>
                        <button type="button" class="btn-table-action btn-edit-user" data-user-id="<?= (int)$u['id'] ?>">Éditer</button>
                        <button type="button" class="btn-table-action btn-table-danger btn-delete-user" data-user-id="<?= (int)$u['id'] ?>">Supprimer</button>
                      </div>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </main>
  </div>

  <!-- Modal Fiche Détail Profil Membre (Popup dynamique) -->
  <div id="adminViewProfileModal" class="admin-modal-overlay" style="display:none;">
    <div class="admin-modal-container" style="max-width: 520px;">
      <div class="admin-modal-header">
        <div style="display:flex; align-items:center; gap:0.6rem;">
          <div style="width:34px; height:34px; border-radius:8px; background:rgba(30,58,138,0.1); display:flex; align-items:center; justify-content:center; color:var(--admin-primary);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          </div>
          <h3 class="admin-modal-title">Fiche Détaillée du Membre</h3>
        </div>
        <button type="button" class="btn-close-modal" id="btnCloseViewProfileModal" aria-label="Fermer">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>

      <div class="admin-modal-body" style="padding:1.5rem;">
        <!-- Profil Header -->
        <div style="display:flex; align-items:center; gap:1.25rem; margin-bottom:1.5rem; padding-bottom:1.25rem; border-bottom:1px solid #f1f5f9;">
          <div id="viewUserAvatar" class="user-avatar-small" style="width:58px; height:58px; font-size:1.3rem; border-radius:12px; background:#1e3a8a; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:800;">
            MK
          </div>
          <div>
            <h4 id="viewUserName" style="font-size:1.15rem; font-weight:800; color:var(--admin-dark); margin:0 0 0.25rem 0;">Momo Kouassi</h4>
            <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
              <span id="viewUserRoleBadge" class="role-pill">Étudiant</span>
              <span id="viewUserMatricule" style="font-size:0.8rem; font-weight:700; color:var(--admin-muted);">EPI-0042</span>
            </div>
          </div>
        </div>

        <!-- Informations détaillées -->
        <div style="display:grid; grid-template-columns:1fr; gap:1rem;">
          <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:0.85rem 1rem;">
            <div style="font-size:0.75rem; font-weight:700; color:var(--admin-muted); text-transform:uppercase; margin-bottom:0.25rem;">Email Institutionnel</div>
            <div id="viewUserEmail" style="font-size:0.95rem; font-weight:700; color:var(--admin-primary);">momo.kouassi@epi-yakro.ci</div>
          </div>

          <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:0.85rem 1rem;">
            <div id="viewUserFiliereLabel" style="font-size:0.75rem; font-weight:700; color:var(--admin-muted); text-transform:uppercase; margin-bottom:0.25rem;">Filière d'Étude</div>
            <div id="viewUserFiliere" style="font-size:0.95rem; font-weight:600; color:var(--admin-dark);">BTS IDA (Informatique Développeur)</div>
          </div>

          <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:0.85rem 1rem;">
            <div style="font-size:0.75rem; font-weight:700; color:var(--admin-muted); text-transform:uppercase; margin-bottom:0.25rem;">Biographie / Note</div>
            <div id="viewUserBio" style="font-size:0.875rem; color:var(--admin-text); line-height:1.5;">Aucune biographie renseignée.</div>
          </div>

          <div style="display:flex; justify-content:space-between; font-size:0.8rem; color:var(--admin-muted); padding:0.25rem 0.5rem;">
            <span>Statut : <strong style="color:var(--admin-success);">Actif</strong></span>
            <span id="viewUserDate">Inscrit le : 14/10/2024</span>
          </div>
        </div>
      </div>

      <div class="admin-modal-footer" style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.5rem; background:#f8fafc; border-top:1px solid var(--admin-border);">
        <a id="viewUserProfileLink" href="#" target="_blank" class="btn-table-action" style="display:inline-flex; align-items:center; gap:0.4rem;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
          <span>Page Profil Complète</span>
        </a>
        <div style="display:flex; gap:0.5rem;">
          <button type="button" class="btn-table-action" id="btnEditFromView">Modifier</button>
          <button type="button" class="btn-table-action" id="btnCloseViewProfileBtn">Fermer</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Édition Utilisateur (Popup dynamique) -->
  <div id="adminEditUserModal" class="admin-modal-overlay" style="display:none;">
    <div class="admin-modal-container">
      <div class="admin-modal-header">
        <div style="display:flex; align-items:center; gap:0.6rem;">
          <div style="width:34px; height:34px; border-radius:8px; background:rgba(30,58,138,0.1); display:flex; align-items:center; justify-content:center; color:var(--admin-primary);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
          </div>
          <h3 class="admin-modal-title">Modifier l'utilisateur</h3>
        </div>
        <button type="button" class="btn-close-modal" id="btnCloseEditModal" aria-label="Fermer">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>

      <form id="adminEditUserForm" class="admin-modal-body">
        <input type="hidden" name="id" id="editUserId" value="" />
        <input type="hidden" name="ajax" value="1" />

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem; margin-bottom:1rem;">
          <div>
            <label for="editUserPrenom" class="form-label" style="display:block; font-size:0.825rem; font-weight:700; color:var(--admin-dark); margin-bottom:0.35rem;">Prénom</label>
            <input type="text" id="editUserPrenom" name="prenom" required class="admin-input-text" style="width:100%; padding:0.6rem 0.85rem; border:1px solid #cbd5e1; border-radius:8px; font-family:inherit; font-size:0.9rem;" />
          </div>
          <div>
            <label for="editUserNom" class="form-label" style="display:block; font-size:0.825rem; font-weight:700; color:var(--admin-dark); margin-bottom:0.35rem;">Nom</label>
            <input type="text" id="editUserNom" name="nom" required class="admin-input-text" style="width:100%; padding:0.6rem 0.85rem; border:1px solid #cbd5e1; border-radius:8px; font-family:inherit; font-size:0.9rem;" />
          </div>
        </div>

        <div style="margin-bottom:1rem;">
          <label for="editUserEmail" class="form-label" style="display:block; font-size:0.825rem; font-weight:700; color:var(--admin-dark); margin-bottom:0.35rem;">Adresse Email</label>
          <input type="email" id="editUserEmail" name="email" required class="admin-input-text" style="width:100%; padding:0.6rem 0.85rem; border:1px solid #cbd5e1; border-radius:8px; font-family:inherit; font-size:0.9rem;" />
        </div>

        <div style="margin-bottom:1rem;">
          <label for="editUserRole" class="form-label" style="display:block; font-size:0.825rem; font-weight:700; color:var(--admin-dark); margin-bottom:0.35rem;">Rôle sur la plateforme</label>
          <select id="editUserRole" name="role" class="admin-input-text" style="width:100%; padding:0.6rem 0.85rem; border:1px solid #cbd5e1; border-radius:8px; font-family:inherit; font-size:0.9rem; background:#ffffff;">
            <option value="etudiant">Étudiant</option>
            <option value="delegue">Délégué de classe</option>
            <option value="professeur">Professeur / Enseignant</option>
            <option value="administrateur">Administrateur</option>
          </select>
        </div>

        <div id="filiereGroup" style="margin-bottom:1rem;">
          <label for="editUserFiliere" class="form-label" style="display:block; font-size:0.825rem; font-weight:700; color:var(--admin-dark); margin-bottom:0.35rem;">Filière (pour Étudiant / Délégué)</label>
          <input type="text" id="editUserFiliere" name="filiere" placeholder="Ex: BTS IDA 2, Génie Civil, Mines..." class="admin-input-text" style="width:100%; padding:0.6rem 0.85rem; border:1px solid #cbd5e1; border-radius:8px; font-family:inherit; font-size:0.9rem;" />
        </div>

        <div id="modulesGroup" style="margin-bottom:1rem;">
          <label for="editUserModules" class="form-label" style="display:block; font-size:0.825rem; font-weight:700; color:var(--admin-dark); margin-bottom:0.35rem;">Modules / Filières enseignés (pour Professeur)</label>
          <input type="text" id="editUserModules" name="modules_enseignes" placeholder="Ex: BDD, Algorithmique, Web PHP, IDA, RIT" class="admin-input-text" style="width:100%; padding:0.6rem 0.85rem; border:1px solid #cbd5e1; border-radius:8px; font-family:inherit; font-size:0.9rem;" />
        </div>

        <div style="margin-bottom:1.25rem;">
          <label for="editUserBio" class="form-label" style="display:block; font-size:0.825rem; font-weight:700; color:var(--admin-dark); margin-bottom:0.35rem;">Biographie / Note interne</label>
          <textarea id="editUserBio" name="biographie" rows="3" class="admin-input-text" style="width:100%; padding:0.6rem 0.85rem; border:1px solid #cbd5e1; border-radius:8px; font-family:inherit; font-size:0.9rem; resize:vertical;"></textarea>
        </div>

        <div class="admin-modal-footer" style="display:flex; align-items:center; justify-content:flex-end; gap:0.75rem; padding-top:1rem; border-top:1px solid #f1f5f9;">
          <button type="button" class="btn-table-action" id="btnCancelEditModal">Annuler</button>
          <button type="submit" class="btn-table-action" id="btnSaveUserModal" style="background:var(--admin-primary); color:#ffffff; font-weight:700;">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>

  <script src="<?= $asset ?>/js/desktop/admin.js"></script>
</body>
</html>

