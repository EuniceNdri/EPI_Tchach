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
  <?php include ROOT_PATH . '/resources/views/partials/theme-head.php'; ?>
</head>
<body data-base-url="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>">
  <?php include ROOT_PATH . '/resources/views/partials/theme-toggle.php'; ?>
  <div class="admin-wrapper">
    <?php include ROOT_PATH . '/resources/views/desktop/partials/admin-sidebar.php'; ?>

    <main class="admin-main-area">
      <header class="admin-topbar">
        <div>
          <h1 class="page-title"><?= htmlspecialchars($title ?? 'Dashboard Chef de classe') ?></h1>
          <p style="margin:0.25rem 0 0; color:var(--admin-muted);">Zone suivie : <?= htmlspecialchars($scopeLabel ?? 'Non renseignée') ?></p>
        </div>
      </header>

      <div class="admin-content">
        <div class="kpi-grid">
          <div class="kpi-card">
            <div class="kpi-header"><span class="kpi-title">Membres de la filière</span></div>
            <div class="kpi-value"><?= number_format(count($filteredUsers ?? []), 0, ',', ' ') ?></div>
          </div>
          <div class="kpi-card">
            <div class="kpi-header"><span class="kpi-title">Signalements en attente</span></div>
            <div class="kpi-value" style="color:var(--admin-accent);"><?= number_format(count($recentReports ?? []), 0, ',', ' ') ?></div>
          </div>
        </div>

        <div class="admin-card">
          <div class="card-header-flex">
            <h2 class="card-title">Membres de la filière</h2>
            <span style="color:var(--admin-muted); font-size:0.85rem;">Rôle : <?= htmlspecialchars($roleLabel ?? 'Chef de classe') ?></span>
          </div>
          <div class="table-responsive">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Nom</th>
                  <th>Email</th>
                  <th>Filière</th>
                  <th>Rôle</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($filteredUsers)): ?>
                  <tr><td colspan="4" style="text-align:center; padding:1.5rem; color:var(--admin-muted);">Aucun membre dans cette filière.</td></tr>
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
        <div class="admin-card">
          <div class="card-header-flex">
            <h2 class="card-title">Signalements récents dans ma filière</h2>
            <a href="<?= $base ?>/moderation" class="epi-btn-primary" style="font-size:0.85rem; padding:0.4rem 0.8rem; border-radius:6px; text-decoration:none; color:#fff; background:var(--admin-accent, #e67e22);">Accéder à la modération</a>
          </div>
          <div class="table-responsive">
            <table class="admin-table">
              <thead><tr><th>Date</th><th>Contenu signalé</th><th>Motif</th><th>Statut</th><th>Action</th></tr></thead>
              <tbody>
                <?php if (empty($recentReports)): ?>
                  <tr><td colspan="5" style="text-align:center; padding:1.5rem; color:var(--admin-muted);">Aucun signalement en attente dans votre filière.</td></tr>
                <?php else: ?>
                  <?php foreach ($recentReports as $report): ?>
                    <tr>
                      <td><?= htmlspecialchars((string)($report['date_signalement'] ?? '')) ?></td>
                      <td><?= htmlspecialchars((string)($report['cible_titre'] ?? 'Contenu')) ?></td>
                      <td><?= htmlspecialchars((string)($report['motif'] ?? '')) ?></td>
                      <td><span class="badge badge-warning"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', (string)($report['statut'] ?? 'en attente')))) ?></span></td>
                      <td><a href="<?= $base ?>/moderation" style="color:var(--admin-accent); font-weight:600;">Traiter</a></td>
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
