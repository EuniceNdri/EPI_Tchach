<?php
$activeAdminPage = 'message-groups';
$base = isset($baseUrl) ? $baseUrl : '';
$asset = isset($assetBase) ? $assetBase : (isset($baseUrl) ? $baseUrl . '/public/assets' : '/public/assets');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Groupes de messagerie · Administration · EPI Tchach</title>
  <link rel="icon" type="image/svg+xml" href="<?= htmlspecialchars($asset, ENT_QUOTES, 'UTF-8') ?>/images/logo_epi.svg" />
  <link rel="stylesheet" href="<?= htmlspecialchars($asset, ENT_QUOTES, 'UTF-8') ?>/css/desktop/admin.css" />
  <?php include ROOT_PATH . '/views/partials/theme-head.php'; ?>
</head>
<body>
  <?php include ROOT_PATH . '/views/partials/theme-toggle.php'; ?>
  <div class="admin-wrapper">
    <?php include ROOT_PATH . '/views/desktop/partials/admin-sidebar.php'; ?>
    <main class="admin-main-area">
      <header class="admin-topbar">
        <div class="topbar-left">
          <h1 class="page-title">Groupes de messagerie par filière</h1>
        </div>
      </header>
      <div class="admin-content">
        <?php if (!empty($_SESSION['flash_success'])): ?>
          <div role="status" style="background:#d1fae5;color:#065f46;padding:.85rem 1.25rem;border-radius:8px;margin-bottom:1.5rem;font-weight:600;">
            <?= htmlspecialchars((string)$_SESSION['flash_success'], ENT_QUOTES, 'UTF-8') ?>
          </div>
          <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>
        <?php if (!empty($_SESSION['flash_error'])): ?>
          <div role="alert" style="background:#fee2e2;color:#991b1b;padding:.85rem 1.25rem;border-radius:8px;margin-bottom:1.5rem;font-weight:600;">
            <?= htmlspecialchars((string)$_SESSION['flash_error'], ENT_QUOTES, 'UTF-8') ?>
          </div>
          <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <section class="admin-card" style="margin-bottom:1.5rem;">
          <h2 class="card-title">Accès des professeurs</h2>
          <p style="color:var(--admin-muted);line-height:1.6;">
            Chaque groupe correspond à une filière et rassemble ses étudiants, toutes années confondues.
            Les professeurs ne voient que les groupes cochés et enregistrés ici.
          </p>
        </section>

        <?php if (!$professors): ?>
          <section class="admin-card">
            <p style="margin:0;color:var(--admin-muted);">Aucun professeur actif à autoriser pour le moment.</p>
          </section>
        <?php else: ?>
          <div style="display:grid;gap:1.25rem;">
            <?php foreach ($professors as $professor):
              $professorId = (int)$professor['id'];
              $assignedGroupIds = $professorAssignments[$professorId] ?? [];
            ?>
              <section class="admin-card">
                <div class="card-header-flex">
                  <div>
                    <h2 class="card-title"><?= htmlspecialchars(trim($professor['prenom'] . ' ' . $professor['nom']), ENT_QUOTES, 'UTF-8') ?></h2>
                    <p style="color:var(--admin-muted);margin:.25rem 0 0;"><?= htmlspecialchars($professor['email'], ENT_QUOTES, 'UTF-8') ?></p>
                  </div>
                </div>
                <form method="post" action="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/admin/message-groups/update">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                  <input type="hidden" name="professor_id" value="<?= $professorId ?>">
                  <?php if (!$groups): ?>
                    <p style="color:var(--admin-muted);">Aucune filière active ne possède de groupe.</p>
                  <?php else: ?>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:.5rem 1rem;margin:1rem 0;">
                      <?php foreach ($groups as $group): ?>
                        <label style="display:flex;align-items:center;gap:.55rem;padding:.65rem;border:1px solid var(--admin-border,#e5e7eb);border-radius:8px;">
                          <input type="checkbox" name="group_ids[]" value="<?= (int)$group['id'] ?>"
                            <?= in_array((int)$group['id'], $assignedGroupIds, true) ? 'checked' : '' ?>>
                          <span><?= htmlspecialchars($group['name'], ENT_QUOTES, 'UTF-8') ?></span>
                        </label>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                  <button type="submit" class="btn-table-action" style="background:var(--admin-primary);color:#fff;padding:.6rem 1rem;border:0;border-radius:9999px;cursor:pointer;">
                    Enregistrer les accès
                  </button>
                </form>
              </section>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </main>
  </div>
</body>
</html>
