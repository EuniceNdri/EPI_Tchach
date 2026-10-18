<?php
$activeAdminPage = 'moderation';
$base = isset($baseUrl) ? $baseUrl : '';
$asset = isset($assetBase) ? $assetBase : (isset($baseUrl) ? $baseUrl . '/public/assets' : '/public/assets');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Administration — Modération des Contenus · EPI Tchach</title>
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
          <h1 class="page-title">Centre de Modération des Salons</h1>
        </div>

        <div class="topbar-right">
          <span style="font-size:0.875rem; color:var(--admin-muted); font-weight:700;">
            3 signalements à traiter
          </span>
        </div>
      </header>

      <div class="admin-content">
        <!-- Bannière d'info charte -->
        <div style="background:rgba(30,58,138,0.06); border:1px solid rgba(30,58,138,0.2); border-radius:var(--radius-md); padding:1.25rem 1.5rem; margin-bottom:2rem; display:flex; align-items:center; justify-content:space-between;">
          <div style="display:flex; align-items:center; gap:0.75rem;">
            <span style="font-size:1.5rem;">⚖️</span>
            <div>
              <h3 style="font-size:1rem; font-weight:800; color:var(--admin-primary);">Règles d'intervention des modérateurs</h3>
              <p style="font-size:0.85rem; color:var(--admin-muted);">Toute suppression de message doit être motivée par un manquement explicite à la charte de l'école (propos irrespectueux, triche, spam).</p>
            </div>
          </div>
          <a href="<?= $base ?>/admin/settings" class="btn-table-action">Consulter la Charte</a>
        </div>

        <!-- Liste des Cartes de Signalement -->
        <div style="display:flex; flex-direction:column; gap:1.5rem;">
          <!-- Signalement 1 -->
          <div class="report-card">
            <div class="report-header">
              <div style="display:flex; align-items:center; gap:0.75rem;">
                <span class="report-reason-badge">Propos Irrespectueux / Diffamation</span>
                <span style="font-size:0.8rem; font-weight:700; color:var(--admin-primary);">Salon : #BTS-IDA-2026</span>
              </div>
              <span style="font-size:0.8rem; color:var(--admin-muted);">Signalé il y a 25 minutes</span>
            </div>

            <div class="report-content-preview">
              « Le prof de statistiques ne sait même pas faire ses propres exercices, il nous donne des zéros pour rien ! C'est n'importe quoi cette école. »
            </div>

            <div class="report-footer">
              <div>
                <strong>Auteur du message :</strong> Étudiant Anonyme (ID: 412) · 
                <span style="color:var(--admin-muted);">Signalé par le Délégué Momo Kouassi</span>
              </div>

              <div class="action-buttons">
                <button type="button" class="btn-table-action btn-mod-approve">Classer sans suite</button>
                <button type="button" class="btn-table-action" onclick="alert('Un avertissement pédagogique a été envoyé à l\'auteur.');">Avertir l'auteur</button>
                <button type="button" class="btn-table-action btn-table-danger btn-mod-delete">Supprimer le message</button>
              </div>
            </div>
          </div>

          <!-- Signalement 2 -->
          <div class="report-card">
            <div class="report-header">
              <div style="display:flex; align-items:center; gap:0.75rem;">
                <span class="report-reason-badge">Fraude / Vente de Corrigés</span>
                <span style="font-size:0.8rem; font-weight:700; color:var(--admin-primary);">Salon : #Entraide-Partiels</span>
              </div>
              <span style="font-size:0.8rem; color:var(--admin-muted);">Signalé il y a 2 heures</span>
            </div>

            <div class="report-content-preview">
              « Disponibles : Sujets et corrigés officiels du partiel de Mécanique des Sols avant l'heure. Écrivez-moi sur WhatsApp au 07xxxxxx pour 5 000 FCFA. »
            </div>

            <div class="report-footer">
              <div>
                <strong>Auteur du message :</strong> Compte externe (ID: 981) · 
                <span style="color:var(--admin-muted);">Signalé par Dr. Ibrahim Koné</span>
              </div>

              <div class="action-buttons">
                <button type="button" class="btn-table-action btn-table-danger btn-mod-delete">Supprimer & Bannir</button>
              </div>
            </div>
          </div>

          <!-- Signalement 3 -->
          <div class="report-card">
            <div class="report-header">
              <div style="display:flex; align-items:center; gap:0.75rem;">
                <span class="report-reason-badge" style="background:#fef3c7; color:#d97706;">Hors Sujet / Spam</span>
                <span style="font-size:0.8rem; font-weight:700; color:var(--admin-primary);">Salon : #Licence-Informatique</span>
              </div>
              <span style="font-size:0.8rem; color:var(--admin-muted);">Signalé hier à 21:10</span>
            </div>

            <div class="report-content-preview">
              « Venez parier sur 1xBet avec mon code promo pour gagner de l'argent facile tous les week-ends ! »
            </div>

            <div class="report-footer">
              <div>
                <strong>Auteur du message :</strong> Compte étudiant (ID: 745) · 
                <span style="color:var(--admin-muted);">Signalé par 4 étudiants</span>
              </div>

              <div class="action-buttons">
                <button type="button" class="btn-table-action btn-mod-approve">Ignorer</button>
                <button type="button" class="btn-table-action btn-table-danger btn-mod-delete">Supprimer le post</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Section Sujets Verrouillés / Archivés -->
        <div class="admin-card" style="margin-top:2.5rem;">
          <div class="card-header-flex">
            <h2 class="card-title">Historique des 3 Dernières Interventions</h2>
            <span style="font-size:0.8rem; color:var(--admin-muted);">Toutes les actions sont tracées dans les logs de sécurité</span>
          </div>
          <div class="table-responsive">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Date & Heure</th>
                  <th>Modérateur</th>
                  <th>Action Réalisée</th>
                  <th>Cible</th>
                  <th>Motif</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>17 Octobre, 14:20</td>
                  <td><strong>Directeur Scolarité</strong></td>
                  <td><span class="status-pill status-banned">Suspension (7 jours)</span></td>
                  <td>Étudiant ID #302</td>
                  <td>Propos injurieux répétés envers un délégué</td>
                </tr>
                <tr>
                  <td>16 Octobre, 11:05</td>
                  <td><strong>Dr. Ibrahim Koné</strong></td>
                  <td><span class="status-pill status-active">Message Épinglé</span></td>
                  <td>Sujet Annales BDD 2025</td>
                  <td>Mise en avant ressource pédagogique officielle</td>
                </tr>
                <tr>
                  <td>15 Octobre, 09:40</td>
                  <td><strong>Directeur Scolarité</strong></td>
                  <td><span class="status-pill" style="background:#f1f5f9; color:#475569;">Salon Verrouillé</span></td>
                  <td>#Elections-Bureau-Etudiants</td>
                  <td>Fin de la période électorale académique</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </main>
  </div>

  <script src="<?= $asset ?>/js/desktop/admin.js"></script>
</body>
</html>

