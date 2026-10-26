<?php
$activeAdminPage = 'categories';
$base = isset($baseUrl) ? $baseUrl : '';
$asset = isset($assetBase) ? $assetBase : (isset($baseUrl) ? $baseUrl . '/public/assets' : '/public/assets');
$zones = isset($zones) ? $zones : [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Administration — Filières & Salons · EPI Tchach</title>
  <link rel="icon" type="image/svg+xml" href="<?= $asset ?>/images/logo_epi.svg" />
  <link rel="apple-touch-icon" href="<?= $asset ?>/images/logo.jpeg" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="<?= $asset ?>/css/desktop/admin.css" />
</head>
<body>
  <div class="admin-tricolor"></div>

  <div class="admin-wrapper">
    <!-- Barre Latérale Commune -->
    <?php include ROOT_PATH . '/views/desktop/partials/admin-sidebar.php'; ?>

    <main class="admin-main-area">
      <!-- Topbar -->
      <header class="admin-topbar">
        <div class="topbar-left">
          <h1 class="page-title">Filières & Salons de Discussion</h1>
        </div>

        <div class="topbar-right">
          <a href="#nouveau-salon" class="btn-table-action" style="background:var(--admin-primary); color:#ffffff; padding:0.5rem 1.25rem; border-radius:9999px;">
            + Nouveau Salon de Cours
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

        <!-- Grille des Zones & Salons Dynamiques -->
        <div class="admin-section-grid" style="grid-template-columns: 2fr 1fr; gap: 2rem;">
          
          <div style="display:flex; flex-direction:column; gap:1.75rem;">
            <?php if (empty($zones)): ?>
              <div class="admin-card" style="text-align:center; padding:2.5rem; color:var(--admin-muted);">
                <p style="font-weight:600; font-size:1rem;">Aucune zone ou filière enregistrée pour le moment.</p>
                <span style="font-size:0.85rem;">Utilisez le formulaire à droite pour créer votre premier salon.</span>
              </div>
            <?php else: ?>
              <?php foreach ($zones as $zone): ?>
                <div class="admin-card">
                  <div class="card-header-flex">
                    <div>
                      <h2 class="card-title">
                        <?= htmlspecialchars($zone['nom']) ?>
                        <span style="font-size:0.7rem; font-weight:700; padding:2px 8px; background:rgba(30,58,138,0.1); color:var(--admin-primary); border-radius:4px; text-transform:uppercase; margin-left:0.5rem;">
                          <?= htmlspecialchars($zone['type_zone']) ?>
                        </span>
                      </h2>
                      <p style="font-size:0.8rem; color:var(--admin-muted); margin-top:0.2rem;"><?= htmlspecialchars($zone['description'] ?? 'Espace d\'échanges officiel') ?></p>
                    </div>
                    <span class="status-pill status-active"><?= (int)($zone['membres_count'] ?? 0) ?> membres · <?= (int)($zone['categories_count'] ?? 0) ?> salons</span>
                  </div>

                  <div class="table-responsive">
                    <table class="admin-table">
                      <thead>
                        <tr>
                          <th>Nom du Salon</th>
                          <th>Description</th>
                          <th>Publications</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php if (empty($zone['categories'])): ?>
                          <tr>
                            <td colspan="4" style="text-align:center; color:var(--admin-muted); padding:1rem;">Aucun salon d'échange dans cette zone.</td>
                          </tr>
                        <?php else: ?>
                          <?php foreach ($zone['categories'] as $cat): ?>
                            <tr>
                              <td><strong>#<?= htmlspecialchars($cat['nom']) ?></strong></td>
                              <td><?= htmlspecialchars($cat['description'] ?? 'Aucune description') ?></td>
                              <td><?= number_format((int)($cat['publications_count'] ?? 0), 0, ',', ' ') ?></td>
                              <td>
                                <button type="button" class="btn-table-action" onclick="alert('Salon #<?= htmlspecialchars($cat['nom']) ?> (ID: <?= (int)$cat['id'] ?>)');">Gérer</button>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                        <?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

          <!-- Formulaire Dynamique de Création de Salon & Zone -->
          <div>
            <div class="admin-card" id="nouveau-salon">
              <div class="card-header-flex">
                <h2 class="card-title">Créer un Nouveau Salon</h2>
              </div>
              <div class="card-body">
                <form id="newChannelForm" action="<?= $base ?>/admin/categories/create-category" method="POST" style="display:flex; flex-direction:column; gap:1.25rem;">
                  <input type="hidden" name="ajax" value="0" />

                  <div>
                    <label style="display:block; font-size:0.875rem; font-weight:700; margin-bottom:0.4rem;" for="salonNomInput">Nom du Salon (avec ou sans #)</label>
                    <input type="text" id="salonNomInput" name="nom" class="search-box" style="width:100%; border-radius:var(--radius-sm); border:1.5px solid var(--admin-border); background:#ffffff; padding:0.75rem 1rem;" placeholder="Ex: #Projets-Fin-Etudes" required />
                  </div>

                  <div>
                    <label style="display:block; font-size:0.875rem; font-weight:700; margin-bottom:0.4rem;" for="salonZoneSelect">Zone / Filière rattachée</label>
                    <select id="salonZoneSelect" name="zone_id" class="search-box" style="width:100%; border-radius:var(--radius-sm); border:1.5px solid var(--admin-border); background:#ffffff; padding:0.75rem 1rem;">
                      <?php if (!empty($zones)): ?>
                        <?php foreach ($zones as $z): ?>
                          <option value="<?= (int)$z['id'] ?>"><?= htmlspecialchars($z['nom']) ?> (<?= htmlspecialchars($z['type_zone']) ?>)</option>
                        <?php endforeach; ?>
                      <?php endif; ?>
                      <option value="new">+ Créer une nouvelle filière / zone...</option>
                    </select>
                  </div>

                  <div id="newZoneGroup" style="display:none;">
                    <label style="display:block; font-size:0.875rem; font-weight:700; margin-bottom:0.4rem;" for="newZoneInput">Nom de la nouvelle Filière / Zone</label>
                    <input type="text" id="newZoneInput" name="new_zone_name" class="search-box" style="width:100%; border-radius:var(--radius-sm); border:1.5px solid var(--admin-border); background:#ffffff; padding:0.75rem 1rem;" placeholder="Ex: Licence Sécurité Réseaux" />
                  </div>

                  <div>
                    <label style="display:block; font-size:0.875rem; font-weight:700; margin-bottom:0.4rem;" for="salonDescInput">Description du Salon & Objectifs</label>
                    <textarea id="salonDescInput" name="description" rows="3" class="search-box" style="width:100%; border-radius:var(--radius-sm); border:1.5px solid var(--admin-border); background:#ffffff; padding:0.75rem 1rem; resize:vertical;" placeholder="Espace d'échanges, questions de cours..."></textarea>
                  </div>

                  <button type="submit" class="btn-table-action" style="background:var(--admin-primary); color:#ffffff; padding:0.85rem; font-size:0.95rem; border-radius:var(--radius-sm); margin-top:0.5rem;">
                    Créer le Salon d'Échange
                  </button>
                </form>
              </div>
            </div>
          </div>

        </div>
      </div>
    </main>
  </div>

  <script>
    document.getElementById('salonZoneSelect').addEventListener('change', function() {
      const newGroup = document.getElementById('newZoneGroup');
      if (this.value === 'new') {
        newGroup.style.display = 'block';
        document.getElementById('newZoneInput').required = true;
      } else {
        newGroup.style.display = 'none';
        document.getElementById('newZoneInput').required = false;
      }
    });
  </script>
  <script src="<?= $asset ?>/js/desktop/admin.js"></script>
</body>
</html>
