<?php
$activeAdminPage = 'settings';
$base = isset($baseUrl) ? $baseUrl : '';
$asset = isset($assetBase) ? $assetBase : (isset($baseUrl) ? $baseUrl . '/public/assets' : '/public/assets');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Administration — Paramètres de la Plateforme · EPI Tchach</title>
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
          <h1 class="page-title">Configuration & Paramètres Généraux</h1>
        </div>
      </header>

      <div class="admin-content" style="max-width: 68rem;">
        <form id="adminSettingsForm" style="display:flex; flex-direction:column; gap:2rem;">
          
          <!-- 1. Informations de l'Établissement -->
          <div class="admin-card">
            <div class="card-header-flex">
              <h2 class="card-title">1. Informations sur l'Établissement</h2>
            </div>
            <div class="card-body" style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem;">
              <div>
                <label style="display:block; font-size:0.875rem; font-weight:700; margin-bottom:0.4rem;">Nom Officiel de l'École</label>
                <input type="text" class="search-box" style="width:100%; border-radius:var(--radius-sm); border:1.5px solid var(--admin-border); background:#ffffff; padding:0.75rem 1rem;" value="École Polytechnique Internationale (EPI)" required />
              </div>

              <div>
                <label style="display:block; font-size:0.875rem; font-weight:700; margin-bottom:0.4rem;">Nom de la Plateforme d'Entraide</label>
                <input type="text" class="search-box" style="width:100%; border-radius:var(--radius-sm); border:1.5px solid var(--admin-border); background:#ffffff; padding:0.75rem 1rem;" value="EPI Tchach" required />
              </div>

              <div>
                <label style="display:block; font-size:0.875rem; font-weight:700; margin-bottom:0.4rem;">Localisation du Campus</label>
                <input type="text" class="search-box" style="width:100%; border-radius:var(--radius-sm); border:1.5px solid var(--admin-border); background:#ffffff; padding:0.75rem 1rem;" value="Yamoussoukro, Quartier Millionnaire, Face Hôtel Concorde" required />
              </div>

              <div>
                <label style="display:block; font-size:0.875rem; font-weight:700; margin-bottom:0.4rem;">Site Web & Portail Officiel</label>
                <input type="url" class="search-box" style="width:100%; border-radius:var(--radius-sm); border:1.5px solid var(--admin-border); background:#ffffff; padding:0.75rem 1rem;" value="https://www.epiglobalgroup.com" required />
              </div>
            </div>
          </div>

          <!-- 2. Sécurité & Contrôle des Inscriptions -->
          <div class="admin-card">
            <div class="card-header-flex">
              <h2 class="card-title">2. Sécurité des Accès & Restreinte Institutionnelle</h2>
            </div>
            <div class="card-body" style="display:flex; flex-direction:column; gap:1.25rem;">
              <div>
                <label style="display:block; font-size:0.875rem; font-weight:700; margin-bottom:0.4rem;">Domaine Email Obligatoire pour Inscription</label>
                <input type="text" class="search-box" style="width:100%; border-radius:var(--radius-sm); border:1.5px solid var(--admin-border); background:#ffffff; padding:0.75rem 1rem;" value="@epi-yakro.ci" required />
                <p style="font-size:0.8rem; color:var(--admin-muted); margin-top:0.35rem;">Seuls les utilisateurs munis de ce suffixe peuvent créer un compte autonome.</p>
              </div>

              <div style="display:flex; flex-direction:column; gap:0.75rem; padding-top:0.5rem; border-top:1px solid var(--admin-border);">
                <label style="display:flex; align-items:center; gap:0.75rem; font-size:0.95rem; cursor:pointer;">
                  <input type="checkbox" checked style="width:1.2rem; height:1.2rem; accent-color:var(--admin-primary);" />
                  <span>Activer la validation par email pour tous les nouveaux comptes.</span>
                </label>

                <label style="display:flex; align-items:center; gap:0.75rem; font-size:0.95rem; cursor:pointer;">
                  <input type="checkbox" checked style="width:1.2rem; height:1.2rem; accent-color:var(--admin-primary);" />
                  <span>Imposer l'acceptation de la Charte d'Utilisation à l'inscription (popup obligatoire).</span>
                </label>

                <label style="display:flex; align-items:center; gap:0.75rem; font-size:0.95rem; cursor:pointer;">
                  <input type="checkbox" style="width:1.2rem; height:1.2rem; accent-color:var(--admin-accent);" />
                  <span style="color:var(--admin-accent); font-weight:700;">Activer le Mode Maintenance (suspend l'accès étudiant pour partiels ou mises à jour).</span>
                </label>
              </div>
            </div>
          </div>

          <!-- 3. Charte d'Utilisation Officielle -->
          <div class="admin-card">
            <div class="card-header-flex">
              <h2 class="card-title">3. Texte de la Charte Académique & Règlement</h2>
            </div>
            <div class="card-body">
              <p style="font-size:0.85rem; color:var(--admin-muted); margin-bottom:0.75rem;">Ce texte est affiché dans la fenêtre modale que les élèves doivent obligatoirement accepter.</p>
              <textarea class="search-box" rows="8" style="width:100%; border-radius:var(--radius-sm); border:1.5px solid var(--admin-border); background:#ffffff; padding:1rem; font-family:monospace; font-size:0.875rem; resize:vertical;">
1. Respect mutuel & Éthique académique
Tout propos haineux, diffamatoire ou irrespectueux envers un camarade ou enseignant est strictement interdit.

2. Utilisation de l'Email Institutionnel
L'accès est réservé aux détenteurs d'une adresse officielle @epi-yakro.ci.

3. Partage de Documents & Propriété Intellectuelle
Le partage d'annales est encouragé mais la diffusion de sujets sous secret d'examen est proscrite.

4. Confidentialité des données
Les données des élèves sont strictement protégées et réservées aux besoins de l'EPI.
              </textarea>
            </div>
          </div>

          <!-- Bouton Soumettre -->
          <div style="display:flex; justify-content:flex-end; gap:1rem;">
            <button type="reset" class="btn-table-action" style="padding:0.75rem 1.5rem;">Rétablir</button>
            <button type="submit" class="btn-table-action" style="background:var(--admin-primary); color:#ffffff; padding:0.75rem 2rem; font-size:0.95rem; border-radius:var(--radius-sm);">
              Enregistrer les Paramètres
            </button>
          </div>

        </form>
      </div>
    </main>
  </div>

  <script src="<?= $asset ?>/js/desktop/admin.js"></script>
</body>
</html>

