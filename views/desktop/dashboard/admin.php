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
  <?php include ROOT_PATH . '/views/partials/theme-head.php'; ?>
</head>
<body>
  <?php include ROOT_PATH . '/views/partials/theme-toggle.php'; ?>
  <div class="admin-wrapper">
    <?php include ROOT_PATH . '/views/desktop/partials/admin-sidebar.php'; ?>

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
            <div class="kpi-header"><span class="kpi-title">Utilisateurs</span></div>
            <div class="kpi-value"><?= number_format($kpis['total_users'] ?? 0, 0, ',', ' ') ?></div>
          </div>
          <div class="kpi-card">
            <div class="kpi-header"><span class="kpi-title">Filières</span></div>
            <div class="kpi-value"><?= number_format($kpis['zones_count'] ?? 0, 0, ',', ' ') ?></div>
          </div>
          <div class="kpi-card">
            <div class="kpi-header"><span class="kpi-title">Signalements</span></div>
            <div class="kpi-value" style="color:var(--admin-accent);"><?= number_format($kpis['signalements_count'] ?? 0, 0, ',', ' ') ?></div>
          </div>
          <div class="kpi-card">
            <div class="kpi-header"><span class="kpi-title">Publications</span></div>
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
