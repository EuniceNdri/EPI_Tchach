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
<style>
    .btn-mod-action {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      padding: 0.45rem 0.85rem;
      font-size: 0.8rem;
      font-weight: 700;
      border-radius: var(--radius-sm, 6px);
      cursor: pointer;
      transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
      font-family: inherit;
      text-decoration: none;
      line-height: 1.2;
    }
    .btn-mod-warn {
      background: #fffbeb;
      border: 1px solid #fde68a;
      color: #b45309;
    }
    .btn-mod-warn:hover {
      background: #fef3c7;
      border-color: #f59e0b;
      color: #92400e;
      transform: translateY(-1px);
      box-shadow: 0 2px 5px rgba(245, 158, 11, 0.15);
    }
    .btn-mod-lock {
      background: #f5f3ff;
      border: 1px solid #ddd6fe;
      color: #6d28d9;
    }
    .btn-mod-lock:hover {
      background: #ede9fe;
      border-color: #8b5cf6;
      color: #5b21b6;
      transform: translateY(-1px);
      box-shadow: 0 2px 5px rgba(139, 92, 246, 0.15);
    }
    .btn-mod-delete {
      background: #fef2f2;
      border: 1px solid #fecaca;
      color: #dc2626;
    }
    .btn-mod-delete:hover {
      background: #fee2e2;
      border-color: #ef4444;
      color: #b91c1c;
      transform: translateY(-1px);
      box-shadow: 0 2px 5px rgba(239, 68, 68, 0.15);
    }
    .btn-mod-reject {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      color: #475569;
    }
    .btn-mod-reject:hover {
      background: #f1f5f9;
      border-color: #94a3b8;
      color: #0f172a;
      transform: translateY(-1px);
      box-shadow: 0 2px 5px rgba(100, 116, 139, 0.12);
    }
  </style>
  <?php include ROOT_PATH . '/views/partials/theme-head.php'; ?>
</head>
<body>
  <?php include ROOT_PATH . '/views/partials/theme-toggle.php'; ?>
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
          <span id="reportCounterBadge" style="font-size:0.875rem; color:var(--admin-muted); font-weight:700;">
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
              <p style="font-size:0.85rem; color:var(--admin-muted); margin:0;">4 décisions disponibles : avertir l'auteur, bloquer la publication, supprimer le contenu (avec motif) ou rejeter le signalement.</p>
            </div>
          </div>
          <a href="<?= $base ?>/admin/settings" class="btn-table-action">Consulter la Charte</a>
        </div>

        <!-- Liste des Cartes de Signalement -->
        <div id="reportsListContainer" style="display:flex; flex-direction:column; gap:1.5rem;">
          <?php if (empty($reports)): ?>
            <div id="noReportsCard" class="admin-card" style="text-align:center; padding:3rem 1.5rem; color:var(--admin-muted);">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 0.75rem; display:block; opacity:0.6;"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
              <h3 style="font-size:1.1rem; font-weight:800; color:var(--admin-dark); margin:0 0 0.5rem 0;">Aucun signalement en attente</h3>
              <p style="font-size:0.9rem; margin:0;">Tous les salons respectent actuellement la charte de modération du campus.</p>
            </div>
          <?php else: ?>
            <?php foreach ($reports as $r): 
              $authorName = !empty($r['cible_auteur_nom']) && $r['cible_auteur_nom'] !== 'Inconnu' 
                  ? $r['cible_auteur_nom'] 
                  : (!empty($r['auteur_nom']) ? $r['auteur_nom'] : 'Auteur inconnu');
              $reporterName = trim(($r['signaleur_prenom'] ?? '') . ' ' . ($r['signaleur_nom'] ?? 'Membre'));
              if (empty($reporterName)) {
                  $reporterName = 'Membre du forum';
              }
              $targetType = $r['type_cible'] ?? 'publication';
              $targetTitle = $r['cible_titre'] ?? ($targetType === 'publication' ? 'Publication' : 'Commentaire');
              $targetContent = $r['cible_contenu'] ?? $r['message_contenu'] ?? 'Contenu non disponible';
            ?>
            <div class="report-card" data-report-id="<?= (int)$r['id'] ?>" data-target-type="<?= htmlspecialchars($targetType) ?>">
              <div class="report-header">
                <div style="display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap;">
                  <span class="report-reason-badge">⚠️ <?= htmlspecialchars($r['raison'] ?? 'Signalement') ?></span>
                  <!-- Nom de l'utilisateur / auteur du contenu -->
                  <span style="font-size:0.85rem; font-weight:700; color:var(--admin-primary); display:inline-flex; align-items:center; gap:0.4rem;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Auteur : <?= htmlspecialchars($authorName) ?>
                  </span>
                  <span style="font-size:0.75rem; background:#f1f5f9; color:#475569; padding:0.15rem 0.5rem; border-radius:4px; font-weight:700; text-transform:uppercase;">
                    <?= htmlspecialchars($targetType) ?>
                  </span>
                </div>
                <span style="font-size:0.8rem; color:var(--admin-muted);"><?= htmlspecialchars($r['created_at'] ?? 'Récemment') ?></span>
              </div>

              <div class="report-content-preview">
                <?php if (!empty($r['cible_titre']) && $targetType === 'publication'): ?>
                  <div style="font-weight:700; color:var(--admin-dark); margin-bottom:0.35rem; font-size:0.95rem;">
                    <?= htmlspecialchars($r['cible_titre']) ?>
                  </div>
                <?php endif; ?>
                <div style="color:#334155; line-height:1.5;">
                  « <?= htmlspecialchars($targetContent) ?> »
                </div>
              </div>

              <div class="report-footer" style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:1rem; margin-top:1rem; padding-top:0.85rem; border-top:1px solid #f1f5f9;">
                <div>
                  <span style="color:var(--admin-muted); font-size:0.85rem;">
                    Signalé par <strong><?= htmlspecialchars($reporterName) ?></strong>
                  </span>
                  <?php if (!empty($r['total_signalements_cible']) && (int)$r['total_signalements_cible'] > 1): ?>
                    <span style="background:#fee2e2; color:#b91c1c; font-size:0.75rem; padding:0.2rem 0.55rem; border-radius:999px; font-weight:800; margin-left:0.5rem;">
                      <?= (int)$r['total_signalements_cible'] ?> signalements
                    </span>
                  <?php endif; ?>
                </div>

                <!-- 4 Actions décisives pour le modérateur -->
                <div class="action-buttons" style="display:flex; flex-wrap:wrap; gap:0.5rem; align-items:center;">
                  <!-- Action 1 : avertiAuteur() -->
                  <button type="button" 
                          class="btn-mod-action btn-mod-warn" 
                          data-report-id="<?= (int)$r['id'] ?>" 
                          data-action="avertiAuteur"
                          data-author="<?= htmlspecialchars($authorName) ?>"
                          data-title="<?= htmlspecialchars($targetTitle) ?>"
                          title="avertiAuteur() : Notifie l'auteur et clôture le signalement">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    Avertir l'auteur
                  </button>

                  <!-- Action 2 : bloquerPublication() -->
                  <button type="button" 
                          class="btn-mod-action btn-mod-lock" 
                          data-report-id="<?= (int)$r['id'] ?>" 
                          data-action="bloquerPublication"
                          data-author="<?= htmlspecialchars($authorName) ?>"
                          data-title="<?= htmlspecialchars($targetTitle) ?>"
                          title="bloquerPublication() : Verrouille la publication, notifie l'auteur et clôture le signalement">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Bloquer la publication
                  </button>

                  <!-- Action 3 : supprimerContenu() -->
                  <button type="button" 
                          class="btn-mod-action btn-mod-delete" 
                          data-report-id="<?= (int)$r['id'] ?>" 
                          data-action="supprimerContenu"
                          data-author="<?= htmlspecialchars($authorName) ?>"
                          data-title="<?= htmlspecialchars($targetTitle) ?>"
                          title="supprimerContenu() : Supprime le post en BD, notifie l'auteur du motif et clôture tous les signalements liés">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                    Supprimer le contenu
                  </button>

                  <!-- Action 4 : rejeterSignalement() -->
                  <button type="button" 
                          class="btn-mod-action btn-mod-reject" 
                          data-report-id="<?= (int)$r['id'] ?>" 
                          data-action="rejeterSignalement"
                          data-author="<?= htmlspecialchars($authorName) ?>"
                          data-title="<?= htmlspecialchars($targetTitle) ?>"
                          title="rejeterSignalement() : Statut 'rejeté' — Si 0 signalement restant: est_signale = 0">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
                    Rejeter le signalement
                  </button>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- Section Historique des Interventions -->
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

  <!-- Modale Interactive de Prise de Décision Modérateur -->
  <div id="moderationDecisionModal" class="admin-modal-overlay" style="display:none;">
    <div class="admin-modal-container" style="max-width:540px; padding:1.75rem;">
      <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:1.25rem;">
        <div style="display:flex; align-items:center; gap:0.75rem;">
          <div id="modModalIcon" style="width:42px; height:42px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
            <!-- Icon injected by JS -->
          </div>
          <div>
            <h3 id="modModalTitle" style="font-size:1.15rem; font-weight:800; color:var(--admin-dark); margin:0 0 0.2rem 0;">Décision de modération</h3>
            <p id="modModalSubtitle" style="font-size:0.825rem; color:var(--admin-muted); margin:0;"></p>
          </div>
        </div>
        <button type="button" id="btnCancelModModal" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:var(--admin-muted); line-height:1; padding:0.25rem;">&times;</button>
      </div>

      <form id="moderationActionForm">
        <input type="hidden" id="modReportId" name="report_id" value="" />
        <input type="hidden" id="modAction" name="action" value="" />

        <!-- Champ Motif (affiché pour avertissement et suppression) -->
        <div id="modMessageGroup" style="margin-bottom:1.25rem;">
          <label id="modMessageLabel" for="modMessageInput" style="display:block; font-size:0.85rem; font-weight:700; color:var(--admin-dark); margin-bottom:0.4rem;">
            Motif officiel communiqué à l'auteur
          </label>
          <textarea id="modMessageInput" name="message" rows="3" class="form-input" style="width:100%; box-sizing:border-box; resize:vertical; font-family:inherit; font-size:0.875rem; padding:0.65rem 0.85rem; border:1px solid var(--admin-border); border-radius:var(--radius-sm); outline:none; transition:border-color 0.2s;" placeholder="Indiquez le motif précis..."></textarea>
          <span id="modMessageHint" style="font-size:0.75rem; color:var(--admin-muted); display:block; margin-top:0.35rem;">
            Ce motif sera envoyé dans les notifications de l'auteur.
          </span>
        </div>

        <!-- Note explicative pour verrouillage et rejet -->
        <div id="modConfirmNotice" style="margin-bottom:1.25rem; font-size:0.875rem; color:#334155; background:#f8fafc; padding:0.9rem 1.1rem; border-radius:var(--radius-sm); border:1px solid #e2e8f0; line-height:1.5; display:none;">
        </div>

        <div style="display:flex; justify-content:flex-end; gap:0.75rem;">
          <button type="button" id="btnCloseModModal" class="btn-table-action" style="padding:0.55rem 1.1rem;">Annuler</button>
          <button type="submit" id="btnSubmitModDecision" class="btn-admin-primary" style="padding:0.55rem 1.35rem; border:none; border-radius:var(--radius-sm); font-weight:700; cursor:pointer; font-size:0.875rem; display:inline-flex; align-items:center; gap:0.45rem;">
            Confirmer la décision
          </button>
        </div>
      </form>
    </div>
  </div>

  <script src="<?= $asset ?>/js/desktop/admin.js"></script>
</body>
</html>
