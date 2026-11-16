<?php
$base = $base ?? (isset($baseUrl) ? $baseUrl : '');
$asset = $asset ?? (isset($assetBase) ? $assetBase : '/public/assets');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($title ?? 'Dashboard Délégué') ?> · EPI Tchach</title>
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
          <h1 class="page-title"><?= htmlspecialchars($title ?? 'Dashboard Délégué') ?></h1>
          <p style="margin:0.25rem 0 0; color:var(--admin-muted);">Vue globale de l’activité de l’établissement — consultation uniquement.</p>
        </div>
      </header>

      <div class="admin-content">
        <div class="kpi-grid">
          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Utilisateurs</span>
              <div class="kpi-icon-pill pill-blue" aria-hidden="true">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
              </div>
            </div>
            <div class="kpi-value"><?= number_format((int)($globalKPIs['total_users'] ?? 0), 0, ',', ' ') ?></div>
          </div>
          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Étudiants</span>
              <div class="kpi-icon-pill pill-green" aria-hidden="true">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6"/><path d="M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
              </div>
            </div>
            <div class="kpi-value"><?= number_format((int)($globalKPIs['etudiants_count'] ?? 0), 0, ',', ' ') ?></div>
          </div>
          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Professeurs</span>
              <div class="kpi-icon-pill pill-amber" aria-hidden="true">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
              </div>
            </div>
            <div class="kpi-value"><?= number_format((int)($globalKPIs['professeurs_count'] ?? 0), 0, ',', ' ') ?></div>
          </div>
          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Publications</span>
              <div class="kpi-icon-pill pill-blue" aria-hidden="true">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
              </div>
            </div>
            <div class="kpi-value"><?= number_format((int)($globalKPIs['publications_count'] ?? 0), 0, ',', ' ') ?></div>
          </div>
          <div class="kpi-card">
            <div class="kpi-header">
              <span class="kpi-title">Signalements en attente</span>
              <div class="kpi-icon-pill pill-red" aria-hidden="true">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
              </div>
            </div>
            <div class="kpi-value" style="color:var(--admin-accent);"><?= number_format((int)($globalKPIs['signalements_count'] ?? 0), 0, ',', ' ') ?></div>
          </div>
        </div>

        <div class="admin-card">
          <div class="card-header-flex">
            <h2 class="card-title">Activité par zone</h2>
            <span style="color:var(--admin-muted); font-size:0.85rem;">Rôle : <?= htmlspecialchars($roleLabel ?? 'Délégué général') ?></span>
          </div>
          <div class="table-responsive">
            <table class="admin-table">
              <thead>
                <tr><th>Zone</th><th>Type</th><th>Membres</th><th>Publications</th></tr>
              </thead>
              <tbody>
                <?php if (empty($schoolActivity)): ?>
                  <tr><td colspan="4" style="text-align:center; padding:1.5rem; color:var(--admin-muted);">Aucune activité à afficher.</td></tr>
                <?php else: ?>
                  <?php foreach ($schoolActivity as $zone): ?>
                    <tr>
                      <td><?= htmlspecialchars((string)$zone['nom']) ?></td>
                      <td><?= htmlspecialchars(ucfirst((string)$zone['type_zone'])) ?></td>
                      <td><?= number_format((int)$zone['members_count'], 0, ',', ' ') ?></td>
                      <td><?= number_format((int)$zone['publications_count'], 0, ',', ' ') ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="admin-card">
          <div class="card-header-flex">
            <h2 class="card-title">Signalements récents</h2>
            <span style="color:var(--admin-muted); font-size:0.85rem;">Lecture seule — transmettez les situations à l’administration.</span>
          </div>
          <div class="table-responsive">
            <table class="admin-table">
              <thead><tr><th>Date</th><th>Zone</th><th>Contenu signalé</th><th>Motif</th><th>Statut</th></tr></thead>
              <tbody>
                <?php if (empty($recentReports)): ?>
                  <tr><td colspan="5" style="text-align:center; padding:1.5rem; color:var(--admin-muted);">Aucun signalement en attente.</td></tr>
                <?php else: ?>
                  <?php foreach ($recentReports as $report): ?>
                    <tr>
                      <td><?= htmlspecialchars((string)$report['date_signalement']) ?></td>
                      <td><?= htmlspecialchars((string)($report['zone_name'] ?? 'Zone inconnue')) ?></td>
                      <td><?= htmlspecialchars((string)($report['cible_titre'] ?? 'Contenu indisponible')) ?></td>
                      <td><?= htmlspecialchars((string)$report['motif']) ?></td>
                      <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', (string)$report['statut']))) ?></td>
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
</body>
</html>
