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
  <?php include ROOT_PATH . '/views/partials/theme-head.php'; ?>
</head>
<body>
  <?php include ROOT_PATH . '/views/partials/theme-toggle.php'; ?>
  <div class="admin-wrapper">
    <?php include ROOT_PATH . '/views/desktop/partials/admin-sidebar.php'; ?>

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
            <div class="kpi-header"><span class="kpi-title">Utilisateurs</span></div>
            <div class="kpi-value"><?= number_format((int)($globalKPIs['total_users'] ?? 0), 0, ',', ' ') ?></div>
          </div>
          <div class="kpi-card">
            <div class="kpi-header"><span class="kpi-title">Étudiants</span></div>
            <div class="kpi-value"><?= number_format((int)($globalKPIs['etudiants_count'] ?? 0), 0, ',', ' ') ?></div>
          </div>
          <div class="kpi-card">
            <div class="kpi-header"><span class="kpi-title">Professeurs</span></div>
            <div class="kpi-value"><?= number_format((int)($globalKPIs['professeurs_count'] ?? 0), 0, ',', ' ') ?></div>
          </div>
          <div class="kpi-card">
            <div class="kpi-header"><span class="kpi-title">Publications</span></div>
            <div class="kpi-value"><?= number_format((int)($globalKPIs['publications_count'] ?? 0), 0, ',', ' ') ?></div>
          </div>
          <div class="kpi-card">
            <div class="kpi-header"><span class="kpi-title">Signalements en attente</span></div>
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
