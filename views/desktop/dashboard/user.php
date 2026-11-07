<?php
$base = $base ?? (isset($baseUrl) ? $baseUrl : '');
$asset = $asset ?? (isset($assetBase) ? $assetBase : '/public/assets');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($title ?? 'Mon dashboard') ?> · EPI Tchach</title>
  <link rel="stylesheet" href="<?= htmlspecialchars($asset) ?>/css/desktop/admin.css" />
</head>
<body>
  <div class="admin-wrapper">
    <?php include ROOT_PATH . '/views/desktop/partials/admin-sidebar.php'; ?>

    <main class="admin-main-area">
      <header class="admin-topbar">
        <div>
          <h1 class="page-title"><?= htmlspecialchars($title ?? 'Mon dashboard') ?></h1>
          <p style="margin:0.25rem 0 0; color:var(--admin-muted);">Rôle : <?= htmlspecialchars($roleLabel ?? 'Utilisateur') ?></p>
        </div>
      </header>

      <div class="admin-content">
        <div class="kpi-grid">
          <div class="kpi-card">
            <div class="kpi-header"><span class="kpi-title">Filière</span></div>
            <div class="kpi-value"><?= htmlspecialchars($_SESSION['user_filiere'] ?? 'Non renseignée') ?></div>
          </div>
          <div class="kpi-card">
            <div class="kpi-header"><span class="kpi-title">Publications</span></div>
            <div class="kpi-value"><?= number_format($kpis['publications_count'] ?? 0, 0, ',', ' ') ?></div>
          </div>
          <div class="kpi-card">
            <div class="kpi-header"><span class="kpi-title">Signalements</span></div>
            <div class="kpi-value"><?= number_format($kpis['signalements_count'] ?? 0, 0, ',', ' ') ?></div>
          </div>
          <div class="kpi-card">
            <div class="kpi-header"><span class="kpi-title">Mon profil</span></div>
            <div class="kpi-value">OK</div>
          </div>
        </div>

        <div class="admin-card">
          <div class="card-header-flex">
            <h2 class="card-title">Informations utilisateur</h2>
          </div>
          <div class="card-body">
            <p><strong>Nom :</strong> <?= htmlspecialchars($_SESSION['user_nom'] ?? '') ?></p>
            <p><strong>Prénom :</strong> <?= htmlspecialchars($_SESSION['user_prenom'] ?? '') ?></p>
            <p><strong>Email :</strong> <?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></p>
            <p><strong>Rôle :</strong> <?= htmlspecialchars($_SESSION['user_role'] ?? 'Utilisateur') ?></p>
          </div>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
