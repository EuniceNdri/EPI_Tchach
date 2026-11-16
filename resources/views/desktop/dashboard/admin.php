<?php
$base = $base ?? (isset($baseUrl) ? $baseUrl : '');
$asset = $asset ?? (isset($assetBase) ? $assetBase : '/public/assets');
$activeAdminPage = $activeAdminPage ?? 'dashboard';
$users = $users ?? [];
$recentReports = $recentReports ?? [];
$topUsers = array_slice($users, 0, 6);
$topReports = array_slice($recentReports, 0, 4);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($title ?? 'Dashboard') ?> · EPI Tchach</title>
  <link rel="stylesheet" href="<?= htmlspecialchars($asset) ?>/css/desktop/admin.css" />
  <?php include ROOT_PATH . '/resources/views/partials/theme-head.php'; ?>
</head>
<body>
  <?php include ROOT_PATH . '/resources/views/partials/theme-toggle.php'; ?>
  <div class="admin-wrapper">
    <?php include ROOT_PATH . '/resources/views/desktop/partials/admin-sidebar.php'; ?>

    <main class="admin-main-area">
      <header class="admin-topbar">
        <div>
          <h1 class="page-title"><?= htmlspecialchars($title ?? 'Dashboard') ?></h1>
          <p style="margin:0.25rem 0 0; color:var(--admin-muted);">Rôle : <?= htmlspecialchars($roleLabel ?? 'Administrateur') ?></p>
        </div>
      </header>

      <div class="admin-content">
        <div class="kpi-grid">
          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Utilisateurs inscrits</span>
              <div class="kpi-icon-pill pill-blue" aria-hidden="true">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
              </div>
            </div>
            <div class="kpi-value"><?= number_format($kpis['total_users'] ?? 0, 0, ',', ' ') ?></div>
          </div>
          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Filières actives</span>
              <div class="kpi-icon-pill pill-green" aria-hidden="true">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6"/><path d="M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
              </div>
            </div>
            <div class="kpi-value"><?= number_format($kpis['zones_count'] ?? 0, 0, ',', ' ') ?></div>
          </div>
          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Signalements urgents</span>
              <div class="kpi-icon-pill pill-red" aria-hidden="true">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
              </div>
            </div>
            <div class="kpi-value" style="color:var(--admin-accent);"><?= number_format($kpis['signalements_count'] ?? 0, 0, ',', ' ') ?></div>
          </div>
          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Publications partagées</span>
              <div class="kpi-icon-pill pill-amber" aria-hidden="true">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
              </div>
            </div>
            <div class="kpi-value"><?= number_format($kpis['publications_count'] ?? 0, 0, ',', ' ') ?></div>
          </div>
        </div>

        <div class="admin-section-grid">
          <div class="admin-card">
            <div class="card-header-flex">
              <h2 class="card-title">Gestion des utilisateurs</h2>
              <a href="<?= $base ?>/admin/users" class="btn-table-action">Voir tous</a>
            </div>
            <div class="table-responsive">
              <table class="admin-table">
                <thead>
                  <tr>
                    <th>Nom</th>
                    <th>Rôle</th>
                    <th>Filière</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($topUsers)): ?>
                  <tr>
                    <td colspan="3" style="text-align:center; color:var(--admin-muted);">Aucun utilisateur disponible pour le moment.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($topUsers as $user): ?>
                    <tr>
                      <td><?= htmlspecialchars(trim((string)($user['prenom'] ?? '') . ' ' . (string)($user['nom'] ?? ''))) ?: 'Utilisateur' ?></td>
                      <td><?= htmlspecialchars(strtoupper((string)($user['role'] ?? 'etudiant'))) ?></td>
                      <td><?= htmlspecialchars((string)($user['filiere'] ?? 'Non renseignée')) ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>

          <div class="admin-card">
            <div class="card-header-flex">
              <h2 class="card-title">Signalements à traiter</h2>
              <a href="<?= $base ?>/admin/moderation" class="btn-table-action">Voir tout</a>
            </div>
            <div class="card-body">
              <?php if (empty($recentReports)): ?>
                <p style="margin:0; color:var(--admin-muted);">Aucun signalement en attente.</p>
              <?php else: ?>
                <?php foreach ($topReports as $report): ?>
                  <?php
                    $reportLabel = (string)($report['raison'] ?? $report['type_cible'] ?? 'Signalement');
                    $reportText = (string)($report['motif'] ?? $report['cible_contenu'] ?? 'Aucun motif détaillé');
                  ?>
                  <div class="report-card" style="margin-bottom:0.75rem;">
                    <div class="report-header">
                      <span class="report-reason-badge"><?= htmlspecialchars($reportLabel) ?></span>
                    </div>
                    <div class="report-content-preview">
                      <?= htmlspecialchars(mb_strimwidth($reportText, 0, 120, '...')) ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
