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
          <h1 class="page-title">Centre de Modération des Salons</h1>
        </div>

        <div class="topbar-right">
          <span style="font-size:0.875rem; color:var(--admin-muted); font-weight:700;">
            <?= count($reports ?? []) ?> signalement(s) à traiter
          </span>
        </div>
      </header>

      <div class="admin-content">
        <!-- Bannière d'info charte -->
        <div style="background:rgba(30,58,138,0.06); border:1px solid rgba(30,58,138,0.2); border-radius:var(--radius-md); padding:1.25rem 1.5rem; margin-bottom:2rem; display:flex; align-items:center; justify-content:space-between;">
          <div style="display:flex; align-items:center; gap:0.75rem;">
            <div style="width:40px; height:40px; border-radius:8px; background:rgba(30,58,138,0.12); display:flex; align-items:center; justify-content:center; color:var(--admin-primary);">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m16 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/><path d="m2 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/><path d="M7 21h10"/><path d="M12 3v18"/><path d="M3 7h2c2 0 5-1 7-2 2 1 5 2 7 2h2"/></svg>
            </div>
            <div>
              <h3 style="font-size:1rem; font-weight:800; color:var(--admin-primary); margin:0 0 0.25rem 0;">Règles d'intervention des modérateurs</h3>
              <p style="font-size:0.85rem; color:var(--admin-muted); margin:0;">Toute suppression de message doit être motivée par un manquement explicite à la charte de l'école (propos irrespectueux, triche, spam).</p>
            </div>
          </div>
          <a href="<?= $base ?>/admin/settings" class="btn-table-action">Consulter la Charte</a>
        </div>

        <!-- Liste des Cartes de Signalement -->
        <div style="display:flex; flex-direction:column; gap:1.5rem;">
          <?php if (empty($reports)): ?>
            <div class="admin-card" style="text-align:center; padding:3rem 1.5rem; color:var(--admin-muted);">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 0.75rem; display:block; opacity:0.6;"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
              <h3 style="font-size:1.1rem; font-weight:800; color:var(--admin-dark); margin:0 0 0.5rem 0;">Aucun signalement en attente</h3>
              <p style="font-size:0.9rem; margin:0;">Tous les salons respectent actuellement la charte de modération du campus.</p>
            </div>
          <?php else: ?>
            <?php foreach ($reports as $r): ?>
            <div class="report-card" data-report-id="<?= (int)$r['id'] ?>">
              <div class="report-header">
                <div style="display:flex; align-items:center; gap:0.75rem;">
                  <span class="report-reason-badge"><?= htmlspecialchars($r['raison'] ?? 'Signalement') ?></span>
                  <span style="font-size:0.8rem; font-weight:700; color:var(--admin-primary);">Message ID: #<?= (int)($r['message_id'] ?? 0) ?></span>
                </div>
                <span style="font-size:0.8rem; color:var(--admin-muted);"><?= htmlspecialchars($r['created_at'] ?? 'Récemment') ?></span>
              </div>

              <div class="report-content-preview">
                « <?= htmlspecialchars($r['message_contenu'] ?? 'Contenu non disponible') ?> »
              </div>

              <div class="report-footer">
                <div>
                  <strong>Auteur du message :</strong> <?= htmlspecialchars($r['auteur_nom'] ?? 'Anonyme') ?> · 
                  <span style="color:var(--admin-muted);">Signalé par <?= htmlspecialchars($r['signaleur_nom'] ?? 'Membre') ?></span>
                </div>

                <div class="action-buttons">
                  <button type="button" class="btn-table-action btn-mod-approve" data-report-id="<?= (int)$r['id'] ?>">Classer sans suite</button>
                  <button type="button" class="btn-table-action btn-table-danger btn-mod-delete" data-report-id="<?= (int)$r['id'] ?>">Supprimer le message</button>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>
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

