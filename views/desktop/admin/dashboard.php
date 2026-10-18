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
              <div class="kpi-icon-pill pill-blue">🎓</div>
            </div>
            <div class="kpi-value">1 248</div>
            <div class="kpi-trend trend-up">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>
              <span>+14% ce mois</span>
            </div>
          </div>

          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Professeurs & Délégués</span>
              <div class="kpi-icon-pill pill-amber">👔</div>
            </div>
            <div class="kpi-value">64</div>
            <div class="kpi-trend trend-up">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>
              <span>100% actifs</span>
            </div>
          </div>

          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Sujets & Discussions</span>
              <div class="kpi-icon-pill pill-green">💬</div>
            </div>
            <div class="kpi-value">3 840</div>
            <div class="kpi-trend trend-up">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>
              <span>+186 cette semaine</span>
            </div>
          </div>

          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Signalements en Attente</span>
              <div class="kpi-icon-pill pill-red">⚠️</div>
            </div>
            <div class="kpi-value" style="color:var(--admin-accent);">3</div>
            <div class="kpi-trend trend-warning">
              <span>Action requise</span>
            </div>
          </div>
        </div>

        <!-- Section Graphique d'activité par Filière & Derniers Signalements -->
        <div class="admin-section-grid">
          <!-- Carte Activité par Filière -->
          <div class="admin-card">
            <div class="card-header-flex">
              <h2 class="card-title">Activité & Participation par Filière</h2>
              <span style="font-size:0.85rem; color:var(--admin-muted); font-weight:600;">Semestre 1 (2025-2026)</span>
            </div>
            <div class="card-body">
              <div class="filiere-chart">
                <div class="filiere-bar-item">
                  <div class="filiere-label-row">
                    <span>BTS IDA (Informatique Développeur d'Applications)</span>
                    <span>1 420 messages (88%)</span>
                  </div>
                  <div class="progress-track">
                    <div class="progress-fill fill-ida"></div>
                  </div>
                </div>

                <div class="filiere-bar-item">
                  <div class="filiere-label-row">
                    <span>BTS Génie Civil (Bâtiment & Travaux Publics)</span>
                    <span>980 messages (68%)</span>
                  </div>
                  <div class="progress-track">
                    <div class="progress-fill fill-civil"></div>
                  </div>
                </div>

                <div class="filiere-bar-item">
                  <div class="filiere-label-row">
                    <span>Mines, Pétrole & Géologie</span>
                    <span>650 messages (54%)</span>
                  </div>
                  <div class="progress-track">
                    <div class="progress-fill fill-mines"></div>
                  </div>
                </div>

                <div class="filiere-bar-item">
                  <div class="filiere-label-row">
                    <span>Tertiaire : Finance Comptabilité (FCGE)</span>
                    <span>540 messages (45%)</span>
                  </div>
                  <div class="progress-track">
                    <div class="progress-fill fill-fcge"></div>
                  </div>
                </div>

                <div class="filiere-bar-item">
                  <div class="filiere-label-row">
                    <span>Ressources Humaines & Communication</span>
                    <span>390 messages (32%)</span>
                  </div>
                  <div class="progress-track">
                    <div class="progress-fill fill-rh"></div>
                  </div>
                </div>
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
              <div class="report-card" style="margin:0;">
                <div class="report-header">
                  <span class="report-reason-badge">Propos inappropriés</span>
                  <span style="font-size:0.75rem; color:var(--admin-muted);">Il y a 20 min</span>
                </div>
                <div class="report-content-preview">
                  « Ce prof de maths ne sait pas expliquer son cours, personne ne comprend rien ! »
                </div>
                <div class="report-footer">
                  <span>Auteur : Étudiant BTP 1</span>
                  <div class="action-buttons">
                    <button type="button" class="btn-table-action btn-mod-approve">Classer</button>
                    <button type="button" class="btn-table-action btn-table-danger btn-mod-delete">Supprimer</button>
                  </div>
                </div>
              </div>

              <div class="report-card" style="margin:0;">
                <div class="report-header">
                  <span class="report-reason-badge">Spam / Publicité</span>
                  <span style="font-size:0.75rem; color:var(--admin-muted);">Il y a 1h</span>
                </div>
                <div class="report-content-preview">
                  « Rejoignez mon groupe Telegram pour acheter des dissertations toutes prêtes... »
                </div>
                <div class="report-footer">
                  <span>Auteur : Compte invité</span>
                  <div class="action-buttons">
                    <button type="button" class="btn-table-action btn-mod-approve">Classer</button>
                    <button type="button" class="btn-table-action btn-table-danger btn-mod-delete">Supprimer</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Section Dernières Inscriptions -->
        <div class="admin-card">
          <div class="card-header-flex">
            <h2 class="card-title">Dernières Inscriptions sur la Plateforme</h2>
            <a href="<?= $base ?>/admin/users" class="btn-table-action">Voir tous les utilisateurs (1 248)</a>
          </div>
          <div class="table-responsive">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Membre</th>
                  <th>Matricule</th>
                  <th>Filière & Promotion</th>
                  <th>Rôle</th>
                  <th>Statut</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr>
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
                  <td>BTS IDA 2</td>
                  <td><span class="role-pill role-delegue">Délégué</span></td>
                  <td><span class="status-pill status-active">Actif</span></td>
                  <td>
                    <div class="action-buttons">
                      <a href="<?= $base ?>/profile" class="btn-table-action">Profil</a>
                      <button type="button" class="btn-table-action btn-table-danger btn-toggle-ban">Suspendre</button>
                    </div>
                  </td>
                </tr>

                <tr>
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
                  <td>Enseignant BDD & Web</td>
                  <td><span class="role-pill role-prof">Professeur</span></td>
                  <td><span class="status-pill status-active">Vérifié</span></td>
                  <td>
                    <div class="action-buttons">
                      <a href="<?= $base ?>/profile" class="btn-table-action">Profil</a>
                      <button type="button" class="btn-table-action">Éditer</button>
                    </div>
                  </td>
                </tr>

                <tr>
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
                  <td>BTS Bâtiment 1</td>
                  <td><span class="role-pill">Étudiante</span></td>
                  <td><span class="status-pill status-active">Actif</span></td>
                  <td>
                    <div class="action-buttons">
                      <a href="<?= $base ?>/profile" class="btn-table-action">Profil</a>
                      <button type="button" class="btn-table-action btn-table-danger btn-toggle-ban">Suspendre</button>
                    </div>
                  </td>
                </tr>

                <tr>
                  <td>
                    <div class="user-cell">
                      <div class="user-avatar-small" style="background:#64748b;">ST</div>
                      <div class="user-names">
                        <span class="user-full-name">Souleymane Traoré</span>
                        <span class="user-email-sub">s.traore@gmail.com</span>
                      </div>
                    </div>
                  </td>
                  <td><em>En attente</em></td>
                  <td>Filière non renseignée</td>
                  <td><span class="role-pill">Non vérifié</span></td>
                  <td><span class="status-pill status-pending">Email externe</span></td>
                  <td>
                    <div class="action-buttons">
                      <button type="button" class="btn-table-action" onclick="alert('Email de vérification renvoyé.');">Valider</button>
                      <button type="button" class="btn-table-action btn-table-danger" onclick="this.closest('tr').remove();">Rejeter</button>
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

  <script src="<?= $asset ?>/js/desktop/admin.js"></script>
</body>
</html>

