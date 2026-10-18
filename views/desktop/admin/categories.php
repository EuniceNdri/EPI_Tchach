<?php
$activeAdminPage = 'categories';
$base = isset($baseUrl) ? $baseUrl : '';
$asset = isset($assetBase) ? $assetBase : (isset($baseUrl) ? $baseUrl . '/public/assets' : '/public/assets');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Administration — Filières & Salons · EPI Tchach</title>
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
        <!-- Grille des Départements & Filières Actives -->
        <div class="admin-section-grid" style="grid-template-columns: 2fr 1fr; gap: 2rem;">
          
          <div style="display:flex; flex-direction:column; gap:1.75rem;">
            <!-- Pôle 1 : Filières Industrielles -->
            <div class="admin-card">
              <div class="card-header-flex">
                <div>
                  <h2 class="card-title">Pôle Sciences, Technologies & Génie Industriel</h2>
                  <p style="font-size:0.8rem; color:var(--admin-muted); margin-top:0.2rem;">Salons réservés aux étudiants et enseignants techniques</p>
                </div>
                <span class="status-pill status-active">5 Salons Actifs</span>
              </div>

              <div class="table-responsive">
                <table class="admin-table">
                  <thead>
                    <tr>
                      <th>Nom du Salon</th>
                      <th>Filière Cible</th>
                      <th>Membres</th>
                      <th>Publications</th>
                      <th>Modérateur</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td><strong># BTS-IDA-2026</strong></td>
                      <td>BTS Informatique (IDA)</td>
                      <td>245 étudiants</td>
                      <td>1 420</td>
                      <td>Dr. Koné & Momo K.</td>
                      <td>
                        <button type="button" class="btn-table-action" onclick="alert('Paramètres du salon #BTS-IDA-2026 ouverts.');">Gérer</button>
                      </td>
                    </tr>
                    <tr>
                      <td><strong># Genies-Civil-Batiment</strong></td>
                      <td>BTS Bâtiment & TP</td>
                      <td>180 étudiants</td>
                      <td>680</td>
                      <td>Ing. Kouamé</td>
                      <td>
                        <button type="button" class="btn-table-action" onclick="alert('Paramètres du salon #Genies-Civil-Batiment.');">Gérer</button>
                      </td>
                    </tr>
                    <tr>
                      <td><strong># Mines-Geologie-Petrole</strong></td>
                      <td>BTS Mines & Géologie</td>
                      <td>120 étudiants</td>
                      <td>410</td>
                      <td>Dr. Bakayoko</td>
                      <td>
                        <button type="button" class="btn-table-action" onclick="alert('Paramètres du salon #Mines-Geologie-Petrole.');">Gérer</button>
                      </td>
                    </tr>
                    <tr>
                      <td><strong># Licence-Informatique-L3</strong></td>
                      <td>Licence Informatique</td>
                      <td>95 étudiants</td>
                      <td>530</td>
                      <td>Pr. Touré</td>
                      <td>
                        <button type="button" class="btn-table-action" onclick="alert('Paramètres du salon #Licence-Informatique-L3.');">Gérer</button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Pôle 2 : Filières Tertiaires -->
            <div class="admin-card">
              <div class="card-header-flex">
                <div>
                  <h2 class="card-title">Pôle Sciences Économiques & Gestion (Tertiaire)</h2>
                  <p style="font-size:0.8rem; color:var(--admin-muted); margin-top:0.2rem;">Espaces d'échanges en gestion d'entreprise, finance et commerce</p>
                </div>
                <span class="status-pill status-active">4 Salons Actifs</span>
              </div>

              <div class="table-responsive">
                <table class="admin-table">
                  <thead>
                    <tr>
                      <th>Nom du Salon</th>
                      <th>Filière Cible</th>
                      <th>Membres</th>
                      <th>Publications</th>
                      <th>Modérateur</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td><strong># Finance-Comptabilite-FCGE</strong></td>
                      <td>BTS FCGE</td>
                      <td>190 étudiants</td>
                      <td>540</td>
                      <td>M. Yao (Expert-comptable)</td>
                      <td>
                        <button type="button" class="btn-table-action" onclick="alert('Paramètres du salon #Finance-Comptabilite-FCGE.');">Gérer</button>
                      </td>
                    </tr>
                    <tr>
                      <td><strong># RH-Communication-RHCOM</strong></td>
                      <td>BTS RHCOM</td>
                      <td>140 étudiants</td>
                      <td>390</td>
                      <td>Mme Bamba</td>
                      <td>
                        <button type="button" class="btn-table-action" onclick="alert('Paramètres du salon #RH-Communication-RHCOM.');">Gérer</button>
                      </td>
                    </tr>
                    <tr>
                      <td><strong># Logistique-Transport</strong></td>
                      <td>BTS Logistique</td>
                      <td>110 étudiants</td>
                      <td>280</td>
                      <td>M. N'Dri</td>
                      <td>
                        <button type="button" class="btn-table-action" onclick="alert('Paramètres du salon #Logistique-Transport.');">Gérer</button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Formulaire Ajout de Salon -->
          <div>
            <div class="admin-card" id="nouveau-salon">
              <div class="card-header-flex">
                <h2 class="card-title">Créer un Nouveau Salon</h2>
              </div>
              <div class="card-body">
                <form id="newChannelForm" style="display:flex; flex-direction:column; gap:1.25rem;">
                  <div>
                    <label style="display:block; font-size:0.875rem; font-weight:700; margin-bottom:0.4rem;">Nom du Salon (avec #)</label>
                    <input type="text" class="search-box" style="width:100%; border-radius:var(--radius-sm); border:1.5px solid var(--admin-border); background:#ffffff; padding:0.75rem 1rem;" placeholder="Ex: #Projets-Fin-Etudes-2026" required />
                  </div>

                  <div>
                    <label style="display:block; font-size:0.875rem; font-weight:700; margin-bottom:0.4rem;">Pôle / Filière rattachée</label>
                    <select class="search-box" style="width:100%; border-radius:var(--radius-sm); border:1.5px solid var(--admin-border); background:#ffffff; padding:0.75rem 1rem;">
                      <option value="ida">BTS Informatique Développeur d'Applications (IDA)</option>
                      <option value="batiment">BTS Bâtiment & Travaux Publics</option>
                      <option value="mines">BTS Mines & Géologie</option>
                      <option value="fcge">BTS Finance Comptabilité (FCGE)</option>
                      <option value="rhcom">BTS Ressources Humaines & Communication</option>
                      <option value="all">Tronc Commun (Tous les étudiants)</option>
                    </select>
                  </div>

                  <div>
                    <label style="display:block; font-size:0.875rem; font-weight:700; margin-bottom:0.4rem;">Description de la matière / objectif</label>
                    <textarea class="search-box" rows="3" style="width:100%; border-radius:var(--radius-sm); border:1.5px solid var(--admin-border); background:#ffffff; padding:0.75rem 1rem; resize:vertical;" placeholder="Espace de partage pour les révisions de partiels..."></textarea>
                  </div>

                  <div>
                    <label style="display:block; font-size:0.875rem; font-weight:700; margin-bottom:0.4rem;">Modérateur Référent (Professeur ou Délégué)</label>
                    <input type="text" class="search-box" style="width:100%; border-radius:var(--radius-sm); border:1.5px solid var(--admin-border); background:#ffffff; padding:0.75rem 1rem;" placeholder="Ex: Dr. Ibrahim Koné" required />
                  </div>

                  <div style="display:flex; align-items:center; gap:0.5rem;">
                    <input type="checkbox" id="channelPublic" checked style="width:1.15rem; height:1.15rem; accent-color:var(--admin-primary);" />
                    <label for="channelPublic" style="font-size:0.875rem; font-weight:600; cursor:pointer;">Accessible immédiatement aux étudiants</label>
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

  <script src="<?= $asset ?>/js/desktop/admin.js"></script>
</body>
</html>

