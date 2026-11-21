<?php
$activeAdminPage = 'school-years';
$base = isset($baseUrl) ? $baseUrl : '';
$asset = isset($assetBase) ? $assetBase : (isset($baseUrl) ? $baseUrl . '/public/assets' : '/public/assets');
$schoolYears = isset($schoolYears) ? $schoolYears : [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Administration — Années Scolaires · EPI Tchach</title>
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
          <h1 class="page-title">Années Scolaires & Promotions</h1>
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

        <!-- Grille : Liste des Années + Formulaire d'ajout -->
        <div class="admin-section-grid" style="grid-template-columns: 2fr 1fr; gap: 2rem;">
          
          <!-- Colonne Gauche : Liste -->
          <div style="display:flex; flex-direction:column; gap:1.75rem;">
            <div class="admin-card">
              <div class="card-header-flex">
                <div>
                  <h2 class="card-title">Promotions de l'École</h2>
                  <p style="font-size:0.85rem; color:var(--admin-muted); margin:0.25rem 0 0 0;">
                    Une seule année scolaire est active à la fois pour organiser le binômage et les promotions.
                  </p>
                </div>
              </div>

              <div class="table-container" style="margin-top:1rem;">
                <table class="admin-table">
                  <thead>
                    <tr>
                      <th>Libellé</th>
                      <th>Dates</th>
                      <th>Étudiants</th>
                      <th>Statut</th>
                      <th style="text-align:right;">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($schoolYears)): ?>
                      <tr>
                        <td colspan="5" style="text-align:center; padding:2rem; color:var(--admin-muted);">
                          Aucune année scolaire enregistrée.
                        </td>
                      </tr>
                    <?php else: ?>
                      <?php foreach ($schoolYears as $year): ?>
                        <tr>
                          <td>
                            <strong style="font-size:0.95rem; color:var(--admin-text);"><?= htmlspecialchars($year['libelle']) ?></strong>
                          </td>
                          <td style="font-size:0.85rem; color:var(--admin-muted);">
                            Du <?= htmlspecialchars(date('d/m/Y', strtotime($year['date_debut']))) ?>
                            au <?= htmlspecialchars(date('d/m/Y', strtotime($year['date_fin']))) ?>
                          </td>
                          <td>
                            <span style="display:inline-flex; align-items:center; gap:0.35rem; font-size:0.85rem; font-weight:700; color:var(--admin-text);">
                              👥 <?= (int)($year['students_count'] ?? 0) ?>
                            </span>
                          </td>
                          <td>
                            <?php if (!empty($year['est_active'])): ?>
                              <span style="display:inline-block; padding:3px 10px; border-radius:9999px; font-size:0.75rem; font-weight:800; background:#dcfce7; color:#15803d; border:1px solid #bbf7d0;">
                                ✓ En cours (Active)
                              </span>
                            <?php else: ?>
                              <span style="display:inline-block; padding:3px 10px; border-radius:9999px; font-size:0.75rem; font-weight:700; background:#f1f5f9; color:#64748b;">
                                Inactive
                              </span>
                            <?php endif; ?>
                          </td>
                          <td style="text-align:right;">
                            <div style="display:inline-flex; align-items:center; gap:0.5rem;">
                              <?php if (empty($year['est_active'])): ?>
                                <form method="post" action="<?= $base ?>/admin/school-years/activate" style="display:inline;">
                                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf'] ?? '') ?>">
                                  <input type="hidden" name="id" value="<?= (int)$year['id'] ?>">
                                  <button type="submit" class="btn-table-action" onclick="return confirm('Définir <?= htmlspecialchars($year['libelle']) ?> comme l\'année scolaire active de l\'école ?');" style="background:#0d9488; color:#fff; font-size:0.75rem; padding:0.35rem 0.75rem;">
                                    Activer
                                  </button>
                                </form>

                                <?php if ((int)($year['students_count'] ?? 0) === 0): ?>
                                  <form method="post" action="<?= $base ?>/admin/school-years/delete" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf'] ?? '') ?>">
                                    <input type="hidden" name="id" value="<?= (int)$year['id'] ?>">
                                    <button type="submit" class="btn-table-action" onclick="return confirm('Supprimer définitivement cette année scolaire ?');" style="background:#fee2e2; color:#b91c1c; font-size:0.75rem; padding:0.35rem 0.75rem;">
                                      Supprimer
                                    </button>
                                  </form>
                                <?php endif; ?>
                              <?php else: ?>
                                <span style="font-size:0.75rem; color:#059669; font-weight:700;">Année courante</span>
                              <?php endif; ?>
                            </div>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Colonne Droite : Formulaire d'ajout -->
          <div>
            <div class="admin-card" style="position:sticky; top:1.5rem;">
              <h2 class="card-title" style="margin-bottom:1rem;">Nouvelle Année Scolaire</h2>
              <form method="post" action="<?= $base ?>/admin/school-years/create" style="display:flex; flex-direction:column; gap:1rem;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf'] ?? '') ?>">

                <div>
                  <label for="libelle" class="form-label" style="font-size:0.85rem; font-weight:700;">Libellé de l'année</label>
                  <input type="text" id="libelle" name="libelle" placeholder="Ex: 2027-2028" class="form-input" required style="width:100%; box-sizing:border-box;" />
                </div>

                <div>
                  <label for="date_debut" class="form-label" style="font-size:0.85rem; font-weight:700;">Date de rentrée (Début)</label>
                  <input type="date" id="date_debut" name="date_debut" class="form-input" required style="width:100%; box-sizing:border-box;" />
                </div>

                <div>
                  <label for="date_fin" class="form-label" style="font-size:0.85rem; font-weight:700;">Date de fin d'année</label>
                  <input type="date" id="date_fin" name="date_fin" class="form-input" required style="width:100%; box-sizing:border-box;" />
                </div>

                <div style="display:flex; align-items:center; gap:0.5rem; margin-top:0.25rem;">
                  <input type="checkbox" id="est_active" name="est_active" value="1" style="width:16px; height:16px; cursor:pointer;" />
                  <label for="est_active" style="font-size:0.85rem; font-weight:600; cursor:pointer;">
                    Activer immédiatement pour toute l'école
                  </label>
                </div>

                <button type="submit" class="btn-table-action" style="background:#0d9488; color:#fff; font-weight:700; padding:0.65rem 1rem; border-radius:8px; border:none; cursor:pointer; margin-top:0.5rem;">
                  + Ajouter l'année scolaire
                </button>
              </form>
            </div>
          </div>

        </div>
      </div>
    </main>
  </div>
</body>
</html>
