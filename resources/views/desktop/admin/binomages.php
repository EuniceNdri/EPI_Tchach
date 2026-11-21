<?php
$activeAdminPage = 'binomages';
$base = isset($baseUrl) ? $baseUrl : '';
$asset = isset($assetBase) ? $assetBase : (isset($baseUrl) ? $baseUrl . '/public/assets' : '/public/assets');
$binomages = isset($binomages) ? $binomages : [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Administration — Binômages & Mentorat · EPI Tchach</title>
  <link rel="icon" type="image/svg+xml" href="<?= $asset ?>/images/logo_epi.svg" />
  <link rel="apple-touch-icon" href="<?= $asset ?>/images/logo.jpeg" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="<?= $asset ?>/css/desktop/admin.css" />
  <?php include ROOT_PATH . '/resources/views/partials/theme-head.php'; ?>
</head>
<body data-base-url="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>">
  <?php include ROOT_PATH . '/resources/views/partials/theme-toggle.php'; ?>
  <div class="admin-wrapper">
    <!-- Barre Latérale Commune -->
    <?php include ROOT_PATH . '/resources/views/desktop/partials/admin-sidebar.php'; ?>

    <main class="admin-main-area">
      <!-- Topbar -->
      <header class="admin-topbar">
        <div class="topbar-left">
          <h1 class="page-title">Supervision du Binômage & Mentorat</h1>
        </div>
        <div class="topbar-right">
          <a href="<?= $base ?>/binomage" class="btn-table-action" target="_blank" style="background:#0d9488; color:#fff; border-radius:8px;">
            🎓 Voir l'espace Binômage étudiant
          </a>
        </div>
      </header>

      <div class="admin-content">
        <!-- Messages Flash -->
        <?php if (!empty($_SESSION['flash_success'])): ?>
          <div style="background:#d1fae5; color:#065f46; padding:0.85rem 1.25rem; border-radius:8px; margin-bottom:1.5rem; font-weight:600;">
            <?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
          </div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['flash_error'])): ?>
          <div style="background:#fee2e2; color:#991b1b; padding:0.85rem 1.25rem; border-radius:8px; margin-bottom:1.5rem; font-weight:600;">
            <?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
          </div>
        <?php endif; ?>

        <div class="admin-card">
          <div class="card-header-flex">
            <div>
              <h2 class="card-title">Tous les Binômes (<?= count($binomages) ?>)</h2>
              <p style="font-size:0.85rem; color:var(--admin-muted); margin:0.25rem 0 0 0;">
                Règle stricte : Mentoré (1ère année) → Mentor (2ème+ année) au sein de la même filière.
              </p>
            </div>
          </div>

          <div class="table-container" style="margin-top:1rem;">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Mentoré (1ère année)</th>
                  <th>Mentor (2ème+ année)</th>
                  <th>Filière & Promotion</th>
                  <th>Message / Motif</th>
                  <th>Statut</th>
                  <th>Date demande</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($binomages)): ?>
                  <tr>
                    <td colspan="6" style="text-align:center; padding:2.5rem; color:var(--admin-muted);">
                      Aucune demande ou relation de binômage pour le moment.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($binomages as $b): ?>
                    <?php
                      $statusBadge = match($b['statut']) {
                        'accepte' => '<span style="background:#dcfce7; color:#15803d; padding:3px 9px; border-radius:9999px; font-weight:800; font-size:0.75rem;">✓ Actif</span>',
                        'en_attente' => '<span style="background:#fef3c7; color:#b45309; padding:3px 9px; border-radius:9999px; font-weight:700; font-size:0.75rem;">⏳ En attente</span>',
                        'refuse' => '<span style="background:#fee2e2; color:#b91c1c; padding:3px 9px; border-radius:9999px; font-weight:700; font-size:0.75rem;">✕ Refusé</span>',
                        'termine' => '<span style="background:#f1f5f9; color:#64748b; padding:3px 9px; border-radius:9999px; font-weight:700; font-size:0.75rem;">Terminé</span>',
                        default => htmlspecialchars($b['statut'])
                      };
                    ?>
                    <tr>
                      <td>
                        <strong><?= htmlspecialchars($b['mentore_prenom'] . ' ' . $b['mentore_nom']) ?></strong>
                        <div style="font-size:0.75rem; color:var(--admin-muted);"><?= htmlspecialchars($b['mentore_email']) ?> (Année <?= (int)$b['mentore_annee'] ?>)</div>
                      </td>
                      <td>
                        <strong><?= htmlspecialchars($b['mentor_prenom'] . ' ' . $b['mentor_nom']) ?></strong>
                        <div style="font-size:0.75rem; color:var(--admin-muted);"><?= htmlspecialchars($b['mentor_email']) ?> (Année <?= (int)$b['mentor_annee'] ?>)</div>
                      </td>
                      <td>
                        <strong style="color:#0d9488;"><?= htmlspecialchars($b['filiere_nom'] ?: 'Campus') ?></strong>
                        <?php if (!empty($b['promo_libelle'])): ?>
                          <div style="font-size:0.75rem; color:#6366f1;">Promo <?= htmlspecialchars($b['promo_libelle']) ?></div>
                        <?php endif; ?>
                      </td>
                      <td style="max-width:240px; font-size:0.85rem; color:var(--admin-muted); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                        <?= htmlspecialchars($b['message_demande'] ?: '—') ?>
                      </td>
                      <td>
                        <?= $statusBadge ?>
                      </td>
                      <td style="font-size:0.8rem; color:var(--admin-muted);">
                        <?= htmlspecialchars(date('d/m/Y H:i', strtotime($b['date_demande']))) ?>
                      </td>
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
