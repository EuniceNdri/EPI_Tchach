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
</head>
<body>
  <div class="admin-wrapper">
    <?php include ROOT_PATH . '/views/desktop/partials/admin-sidebar.php'; ?>

    <main class="admin-main-area">
      <header class="admin-topbar">
        <div>
          <h1 class="page-title"><?= htmlspecialchars($title ?? 'Dashboard Délégué') ?></h1>
          <p style="margin:0.25rem 0 0; color:var(--admin-muted);">Zone suivie : <?= htmlspecialchars($scopeLabel ?? 'Non renseignée') ?></p>
        </div>
      </header>

      <div class="admin-content">
        <div class="kpi-grid">
          <div class="kpi-card">
            <div class="kpi-header"><span class="kpi-title">Membres de la zone</span></div>
            <div class="kpi-value"><?= number_format(count($filteredUsers ?? []), 0, ',', ' ') ?></div>
          </div>
          <div class="kpi-card">
            <div class="kpi-header"><span class="kpi-title">Signalements en attente</span></div>
            <div class="kpi-value" style="color:var(--admin-accent);"><?= number_format(count($recentReports ?? []), 0, ',', ' ') ?></div>
          </div>
        </div>

        <div class="admin-card">
          <div class="card-header-flex">
            <h2 class="card-title">Membres suivis</h2>
            <span style="color:var(--admin-muted); font-size:0.85rem;">Rôle : <?= htmlspecialchars($roleLabel ?? 'Délégué') ?></span>
          </div>
          <div class="table-responsive">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Membre</th>
                  <th>Email</th>
                  <th>Filière</th>
                  <th>Rôle</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($filteredUsers)): ?>
                  <tr><td colspan="4" style="text-align:center; padding:1.5rem; color:var(--admin-muted);">Aucun membre dans cette zone.</td></tr>
                <?php else: ?>
                  <?php foreach ($filteredUsers as $user): ?>
                    <tr>
                      <td><?= htmlspecialchars(trim((string)($user['prenom'] ?? '') . ' ' . (string)($user['nom'] ?? ''))) ?></td>
                      <td><?= htmlspecialchars((string)($user['email'] ?? '')) ?></td>
                      <td><?= htmlspecialchars((string)($user['filiere'] ?? 'Non renseignée')) ?></td>
                      <td><?= htmlspecialchars((string)($user['role'] ?? '')) ?></td>
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
