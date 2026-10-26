<?php
$activeAdminPage = 'dashboard';
$base = isset($baseUrl) ? $baseUrl : '';
$asset = isset($assetBase) ? $assetBase : (isset($baseUrl) ? $baseUrl . '/public/assets' : '/public/assets');

?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Administration — Tableau de Bord · EPI Tchach</title>
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

    <!-- Zone Principale -->
    <main class="admin-main-area">
      <!-- Topbar -->
      <header class="admin-topbar">
        <div class="topbar-left">
          <h1 class="page-title">Tableau de Bord Général</h1>
        </div>

        <div class="topbar-right">
          <button type="button" class="btn-table-action btn-open-create-channel" style="background:var(--admin-primary); color:#ffffff; padding:0.5rem 1.25rem; border-radius:9999px;">
            + Nouveau Salon
          </button>
          <a href="<?= $base ?>/admin/moderation" class="btn-icon" title="Signalements urgents">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            <span class="notif-badge"></span>
          </a>
          <a href="<?= $base ?>/profile" class="btn-icon" title="Voir mon profil">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          </a>
          <a href="<?= $base ?>/landing" class="btn-table-action" style="padding:0.5rem 1rem; border-radius:9999px;">Quitter vers l'accueil</a>
        </div>
      </header>

      <!-- Contenu Dashboard -->
      <div class="admin-content">
        <!-- 4 Cartes KPIs -->
        <div class="kpi-grid">
          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Étudiants Inscrits</span>
              <div class="kpi-icon-pill pill-blue">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 10v6"/><path d="M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
              </div>
            </div>
            <div class="kpi-value"><?= number_format($kpis['etudiants_count'] ?? 0, 0, ',', ' ') ?></div>
            <div class="kpi-trend trend-up">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>
              <span>+14% ce mois</span>
            </div>
          </div>

          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Professeurs & Délégués</span>
              <div class="kpi-icon-pill pill-amber">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
              </div>
            </div>
            <div class="kpi-value"><?= number_format($kpis['professeurs_count'] ?? 0, 0, ',', ' ') ?></div>
            <div class="kpi-trend trend-up">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>
              <span>100% actifs</span>
            </div>
          </div>

          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Sujets & Discussions</span>
              <div class="kpi-icon-pill pill-green">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
              </div>
            </div>
            <div class="kpi-value"><?= number_format($kpis['publications_count'] ?? 0, 0, ',', ' ') ?></div>
            <div class="kpi-trend trend-up">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>
              <span>+186 cette semaine</span>
            </div>
          </div>

          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Signalements en Attente</span>
              <div class="kpi-icon-pill pill-red">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
              </div>
            </div>
            <div class="kpi-value" style="color:var(--admin-accent);"><?= number_format($kpis['signalements_count'] ?? 0, 0, ',', ' ') ?></div>
            <div class="kpi-trend trend-warning">
              <span><?= ($kpis['signalements_count'] ?? 0) > 0 ? 'Action requise' : 'Aucune alerte' ?></span>
            </div>
          </div>
        </div>

        <!-- Section Graphique d'activité par Filière & Derniers Signalements -->
        <div class="admin-section-grid">
          <!-- Carte Activité par Filière -->
          <div class="admin-card">
            <div class="card-header-flex">
              <h2 class="card-title">Activité & Participation par Filière</h2>
              <span style="font-size:0.85rem; color:var(--admin-muted); font-weight:600;">Année Académique 2025-2026</span>
            </div>
            <div class="card-body">
              <div class="filiere-chart">
                <?php
                $classes = ['fill-ida', 'fill-civil', 'fill-mines', 'fill-fcge', 'fill-rh'];
                $i = 0;
                foreach ($filiereActivity as $f):
                    $fillClass = $classes[$i % count($classes)];
                    $i++;
                ?>
                <div class="filiere-bar-item">
                  <div class="filiere-label-row">
                    <span><?= htmlspecialchars($f['nom']) ?></span>
                    <span><?= number_format($f['messages'], 0, ',', ' ') ?> messages (<?= (int)$f['pourcentage'] ?>%)</span>
                  </div>
                  <div class="progress-track">
                    <div class="progress-fill <?= $fillClass ?>" style="width: <?= min(100, (int)$f['pourcentage']) ?>%;"></div>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>

          <!-- Alertes Modération Rapide -->
          <div class="admin-card">
            <div class="card-header-flex">
              <h2 class="card-title">Signalements Récents</h2>
              <a href="<?= $base ?>/admin/moderation" style="font-size:0.85rem; color:var(--admin-primary); font-weight:700; text-decoration:none;">Tout voir →</a>
            </div>
            <div class="card-body" style="display:flex; flex-direction:column; gap:1rem;">
              <?php if (empty($recentReports)): ?>
                <div style="text-align:center; padding:2rem 1rem; color:var(--admin-muted);">
                  <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 0.5rem; display:block; opacity:0.6;"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                  <p style="font-size:0.9rem; font-weight:600; margin:0;">Aucun signalement en attente</p>
                  <span style="font-size:0.8rem;">La communauté respecte la charte de bon usage.</span>
                </div>
              <?php else: ?>
                <?php foreach (array_slice($recentReports, 0, 2) as $report): ?>
                <div class="report-card" style="margin:0;" data-report-id="<?= (int)$report['id'] ?>">
                  <div class="report-header">
                    <span class="report-reason-badge"><?= htmlspecialchars($report['raison'] ?? 'Contenu signalé') ?></span>
                    <span style="font-size:0.75rem; color:var(--admin-muted);"><?= htmlspecialchars($report['created_at'] ?? 'Récemment') ?></span>
                  </div>
                  <div class="report-content-preview">
                    « <?= htmlspecialchars(mb_strimwidth($report['message_contenu'] ?? 'Contenu du message signalé...', 0, 120, '...')) ?> »
                  </div>
                  <div class="report-footer">
                    <span>Auteur : <?= htmlspecialchars($report['auteur_nom'] ?? 'Membre') ?></span>
                    <div class="action-buttons">
                      <button type="button" class="btn-table-action btn-mod-approve" data-report-id="<?= (int)$report['id'] ?>">Classer</button>
                      <button type="button" class="btn-table-action btn-table-danger btn-mod-delete" data-report-id="<?= (int)$report['id'] ?>">Supprimer</button>
                    </div>
                  </div>
                </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Section Dernières Inscriptions -->
        <div class="admin-card">
          <div class="card-header-flex">
            <h2 class="card-title">Dernières Inscriptions sur la Plateforme</h2>
            <a href="<?= $base ?>/admin/users" class="btn-table-action">Voir tous les utilisateurs (<?= number_format($kpis['total_users'] ?? count($recentUsers), 0, ',', ' ') ?>)</a>
          </div>
          <div class="table-responsive">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Membre</th>
                  <th>Matricule / Réf</th>
                  <th>Filière & Spécialité</th>
                  <th>Rôle</th>
                  <th>Statut</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($recentUsers)): ?>
                  <tr>
                    <td colspan="6" style="text-align:center; padding:1.5rem; color:var(--admin-muted);">Aucun utilisateur enregistré pour le moment.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($recentUsers as $u):
                      $roleKey = strtolower($u['role'] ?? 'etudiant');
                      $initials = strtoupper(mb_substr($u['prenom'] ?? 'U', 0, 1) . mb_substr($u['nom'] ?? 'N', 0, 1));
                      $colors = ['#1e3a8a', '#0284c7', '#d97706', '#16a34a', '#8b5cf6', '#dc2626'];
                      $color = $colors[($u['id'] ?? 1) % count($colors)];
                  ?>
                  <tr data-user-id="<?= (int)$u['id'] ?>" data-role="<?= htmlspecialchars($roleKey) ?>">
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
                    <td class="user-filiere-cell"><?= htmlspecialchars(!empty($u['filiere']) ? $u['filiere'] : (!empty($u['modules_enseignes']) ? $u['modules_enseignes'] : 'Non spécifié')) ?></td>
                    <td>
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

  <!-- Modal Création de Salon (Popup dynamique) -->
  <div id="adminCreateChannelModal" class="admin-modal-overlay" style="display:none;">
    <div class="admin-modal-container" style="max-width: 540px;">
      <div class="admin-modal-header">
        <div style="display:flex; align-items:center; gap:0.6rem;">
          <div style="width:34px; height:34px; border-radius:8px; background:rgba(30,58,138,0.1); display:flex; align-items:center; justify-content:center; color:var(--admin-primary);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          </div>
          <h3 class="admin-modal-title">Créer un Nouveau Salon</h3>
        </div>
        <button type="button" class="btn-close-modal" id="btnCloseCreateChannelModal" aria-label="Fermer">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>

      <form id="adminCreateChannelForm" class="admin-modal-body">
        <input type="hidden" name="ajax" value="1" />

        <div style="margin-bottom:1rem;">
          <label for="channelNameInput" style="display:block; font-size:0.825rem; font-weight:700; color:var(--admin-dark); margin-bottom:0.35rem;">Nom du Salon (avec ou sans #)</label>
          <input type="text" id="channelNameInput" name="nom" required placeholder="Ex: #BTS-IDA-Web, #Mecanique-Sols" class="admin-input-text" style="width:100%; padding:0.6rem 0.85rem; border:1px solid #cbd5e1; border-radius:8px; font-family:inherit; font-size:0.9rem;" />
        </div>

        <div style="margin-bottom:1rem;">
          <label for="channelZoneSelect" style="display:block; font-size:0.825rem; font-weight:700; color:var(--admin-dark); margin-bottom:0.35rem;">Filière / Zone de rattachement</label>
          <select id="channelZoneSelect" name="zone_id" class="admin-input-text" style="width:100%; padding:0.6rem 0.85rem; border:1px solid #cbd5e1; border-radius:8px; font-family:inherit; font-size:0.9rem; background:#ffffff;">
            <?php if (!empty($zones)): ?>
              <?php foreach ($zones as $z): ?>
                <option value="<?= (int)$z['id'] ?>"><?= htmlspecialchars($z['nom']) ?></option>
              <?php endforeach; ?>
            <?php else: ?>
              <option value="1">BTS Informatique (IDA)</option>
              <option value="2">BTS Génie Civil & BTP</option>
              <option value="3">Mines, Pétrole & Géologie</option>
              <option value="4">Finance Comptabilité (FCGE)</option>
              <option value="5">Ressources Humaines & Communication</option>
            <?php endif; ?>
            <option value="new">+ Créer une nouvelle filière...</option>
          </select>
        </div>

        <div id="newZoneNameGroup" style="display:none; margin-bottom:1rem;">
          <label for="newZoneNameInput" style="display:block; font-size:0.825rem; font-weight:700; color:var(--admin-primary); margin-bottom:0.35rem;">Nom de la nouvelle Filière / Zone</label>
          <input type="text" id="newZoneNameInput" name="new_zone_name" placeholder="Ex: Licence Sécurité Réseaux, Master BTP..." class="admin-input-text" style="width:100%; padding:0.6rem 0.85rem; border:1px solid var(--admin-primary); border-radius:8px; font-family:inherit; font-size:0.9rem; background:rgba(30,58,138,0.02);" />
        </div>

        <div style="margin-bottom:1.25rem;">
          <label for="channelDescInput" style="display:block; font-size:0.825rem; font-weight:700; color:var(--admin-dark); margin-bottom:0.35rem;">Description du Salon & Objectifs</label>
          <textarea id="channelDescInput" name="description" rows="3" placeholder="Espace d'échanges, questions de cours et exercices..." class="admin-input-text" style="width:100%; padding:0.6rem 0.85rem; border:1px solid #cbd5e1; border-radius:8px; font-family:inherit; font-size:0.9rem; resize:vertical;"></textarea>
        </div>

        <div class="admin-modal-footer" style="display:flex; align-items:center; justify-content:flex-end; gap:0.75rem; padding-top:1rem; border-top:1px solid #f1f5f9;">
          <button type="button" class="btn-table-action" id="btnCancelCreateChannel">Annuler</button>
          <button type="submit" class="btn-table-action" id="btnSubmitCreateChannel" style="background:var(--admin-primary); color:#ffffff; font-weight:700;">Créer le Salon</button>
        </div>
      </form>
    </div>
  </div>

  <script src="<?= $asset ?>/js/desktop/admin.js"></script>
</body>
</html>

