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
  <?php include ROOT_PATH . '/views/partials/theme-head.php'; ?>
</head>
<body>
  <?php include ROOT_PATH . '/views/partials/theme-toggle.php'; ?>
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
        <div class="admin-card">
          <div class="card-header-flex">
            <h2 class="card-title">Informations utilisateur</h2>
          </div>
          <div class="card-body">
            <p><strong>Nom :</strong> <?= htmlspecialchars((string)($_SESSION['user_nom'] ?? '')) ?></p>
            <p><strong>Prénom :</strong> <?= htmlspecialchars((string)($_SESSION['user_prenom'] ?? '')) ?></p>
            <p><strong>Email :</strong> <?= htmlspecialchars((string)($_SESSION['user_email'] ?? '')) ?></p>
            <p><strong>Rôle :</strong> <?= htmlspecialchars((string)($_SESSION['user_role'] ?? 'Utilisateur')) ?></p>
            <p><strong>Filière :</strong> <?= htmlspecialchars((string)($_SESSION['user_filiere'] ?? 'Non renseignée')) ?></p>
          </div>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
