<?php
$base = $base ?? (isset($baseUrl) ? $baseUrl : '');
$asset = $asset ?? (isset($assetBase) ? $assetBase : '/public/assets');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($title ?? 'Dashboard Chef de classe') ?> · EPI Tchach</title>
  <link rel="stylesheet" href="<?= htmlspecialchars($asset) ?>/css/desktop/admin.css" />
</head>
<body>
  <div class="admin-wrapper">
    <?php include ROOT_PATH . '/views/desktop/partials/admin-sidebar.php'; ?>

    <main class="admin-main-area">
      <header class="admin-topbar">
        <div>
          <h1 class="page-title"><?= htmlspecialchars($title ?? 'Dashboard Chef de classe') ?></h1>
          <p style="margin:0.25rem 0 0; color:var(--admin-muted);">Filière : <?= htmlspecialchars($_SESSION['user_filiere'] ?? 'Non renseignée') ?> · Classe : <?= htmlspecialchars($_SESSION['user_classe'] ?? 'Non renseignée') ?></p>
        </div>
      </header>

      <div class="admin-content">
        <div class="kpi-grid">
          <div class="kpi-card">
            <div class="kpi-header"><span class="kpi-title">Étudiants du groupe</span></div>
            <div class="kpi-value"><?= number_format(count($filteredUsers ?? []), 0, ',', ' ') ?></div>
          </div>
          <div class="kpi-card">
            <div class="kpi-header"><span class="kpi-title">Signalements du groupe</span></div>
            <div class="kpi-value" style="color:var(--admin-accent);"><?= number_format(count($recentReports ?? []), 0, ',', ' ') ?></div>
          </div>
          <div class="kpi-card">
            <div class="kpi-header"><span class="kpi-title">Absences / alertes</span></div>
            <div class="kpi-value">0</div>
          </div>
          <div class="kpi-card">
            <div class="kpi-header"><span class="kpi-title">Activité récente</span></div>
            <div class="kpi-value"><?= number_format(max(1, count($filteredUsers ?? [])), 0, ',', ' ') ?></div>
          </div>
        </div>

        <div class="admin-card">
          <div class="card-header-flex">
            <h2 class="card-title">Membres du groupe</h2>
            <span style="color:var(--admin-muted); font-size:0.85rem;">Rôle : <?= htmlspecialchars($roleLabel ?? 'Chef de classe') ?></span>
          </div>
          <div class="table-responsive">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Nom</th>
                  <th>Email</th>
                  <th>Filière</th>
                  <th>Statut</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($filteredUsers)): ?>
                  <tr><td colspan="4" style="text-align:center; padding:1.5rem; color:var(--admin-muted);">Aucun membre dans ce groupe.</td></tr>
                <?php else: ?>
                  <?php foreach ($filteredUsers as $user): ?>
                    <tr>
                      <td><?= htmlspecialchars(trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? ''))) ?></td>
                      <td><?= htmlspecialchars($user['email'] ?? '') ?></td>
                      <td><?= htmlspecialchars($user['filiere'] ?? 'Non renseignée') ?></td>
                      <td><span class="status-pill status-active">Actif</span></td>
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
