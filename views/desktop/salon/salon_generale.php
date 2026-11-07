<?php
/**
 * Variables supplied by SalonController::index() before this view is included.
 *
 * @var string|null $baseUrl
 * @var string|null $assetBase
 * @var array|null $zones
 * @var array|null $feedPosts
 * @var array|null $postAttachments
 * @var array|null $savedAttachments
 * @var array|null $popularPosts
 * @var array|null $onlineUsers
 * @var string|null $flashSuccess
 * @var string|null $flashError
 */
$base = is_string($baseUrl ?? null) ? $baseUrl : '';
$asset = is_string($assetBase ?? null)
  ? $assetBase
  : ($base !== '' ? $base . '/public/assets' : '/public/assets');
$logoFile = $asset . '/images/logo.jpeg';
$feedView = isset($feedView) && in_array($feedView, ['latest', 'popular', 'saved'], true)
  ? $feedView
  : 'latest';
$categoryId = isset($categoryId) ? max(0, (int)$categoryId) : null;
$zones = is_array($zones ?? null) ? $zones : [];
$feedPosts = is_array($feedPosts ?? null) ? $feedPosts : [];
$postAttachments = is_array($postAttachments ?? null) ? $postAttachments : [];
$savedAttachments = is_array($savedAttachments ?? null) ? $savedAttachments : [];
$popularPosts = is_array($popularPosts ?? null) ? $popularPosts : [];
$onlineUsers = is_array($onlineUsers ?? null) ? $onlineUsers : [];
$unreadCount = max(0, (int)($unreadCount ?? 0));
$flashSuccess = is_string($flashSuccess ?? null) ? $flashSuccess : '';
$flashError = is_string($flashError ?? null) ? $flashError : '';
if (!isset($_SESSION) || !is_array($_SESSION)) {
  $_SESSION = [];
}
$viewLabels = ['latest' => 'Récentes', 'popular' => 'Populaires', 'saved' => 'Sauvegardées'];
$roleLabels = [
  'etudiant' => 'Étudiant',
  'professeur' => 'Professeur',
  'moderateur' => 'Modérateur',
  'delegue_general' => 'Délégué général',
  'administrateur' => 'Administrateur',
];

if (!function_exists('formatSalonDate')) {
  function formatSalonDate(string $date): string {
    try {
      $createdAt = new DateTimeImmutable($date);
      $seconds = max(0, time() - $createdAt->getTimestamp());
      if ($seconds < 60) return "À l'instant";
      if ($seconds < 3600) return 'Il y a ' . (int)floor($seconds / 60) . ' min';
      if ($seconds < 86400) return 'Il y a ' . (int)floor($seconds / 3600) . ' h';
      return 'Il y a ' . (int)floor($seconds / 86400) . ' j';
    } catch (Exception $exception) {
      return $date;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Salon Général & Discussions · EPI Tchach</title>
  <link rel="icon" type="image/svg+xml" href="<?= $asset ?>/images/logo_epi.svg" />
  <link rel="apple-touch-icon" href="<?= $asset ?>/images/logo.jpeg" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="<?= $asset ?>/css/desktop/salon.css" />
</head>
<body data-base-url="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>">
  <div class="tricolor-bar"></div>
  <div class="app">

    <!-- ========== SIDEBAR GAUCHE ========== -->
    <aside class="sidebar-left">
      <div class="logo">
        <img src="<?= htmlspecialchars($logoFile) ?>" alt="Logo EPI" style="width:36px; height:36px; border-radius:8px; object-fit:cover;" onerror="this.style.display='none'" />
        <div>
          <span style="font-size:1.1rem; font-weight:800; color:var(--primary);">EPI <span style="color:var(--accent);">Tchach</span></span>
          <div style="font-size:0.7rem; color:var(--text-muted); font-weight:600;">Salon Général</div>
        </div>
      </div>

      <nav class="nav-menu" aria-label="Navigation du salon">
        <a href="<?= $base ?>/salon" class="nav-item <?= $feedView === 'latest' && !$categoryId ? 'active' : '' ?>">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          <span>Accueil</span>
        </a>
        <a href="#accessible-categories" class="nav-item <?= $categoryId ? 'active' : '' ?>">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
          <span>Catégories</span>
        </a>
        <a href="<?= $base ?>/salon?view=popular" class="nav-item <?= $feedView === 'popular' ? 'active' : '' ?>">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>
          <span>Populaires</span>
        </a>
        <a href="<?= $base ?>/salon?view=saved" class="nav-item <?= $feedView === 'saved' ? 'active' : '' ?>">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
          <span>Sauvegardés</span>
        </a>
      </nav>

      <div id="accessible-categories">
        <div class="section-title">Catégories et salons accessibles</div>
        <div class="category-list">
          <?php if (empty($zones)): ?>
            <p class="sidebar-empty">Aucun salon n'est encore rattaché à votre compte.</p>
          <?php else: ?>
            <?php foreach ($zones as $zone): ?>
              <div class="category-zone-title"><?= htmlspecialchars($zone['nom']) ?></div>
              <?php foreach ($zone['categories'] as $category): ?>
                <a href="<?= $base ?>/salon?view=<?= htmlspecialchars($feedView) ?>&category=<?= (int)$category['id'] ?>"
                   class="category-item <?= $categoryId === (int)$category['id'] ? 'is-selected' : '' ?>">
                  <span class="category-icon" aria-hidden="true">#</span>
                  <span class="category-name"><?= htmlspecialchars($category['nom']) ?></span>
                  <span class="cat-count"><?= (int)$category['publications_count'] ?></span>
                </a>
              <?php endforeach; ?>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
        <?php if ($categoryId): ?>
          <a href="<?= $base ?>/salon?view=<?= htmlspecialchars($feedView) ?>" class="btn-browse">Afficher toutes les catégories</a>
        <?php endif; ?>
      </div>

      <div style="margin-top:auto; padding-top:1rem; border-top:1px solid var(--border);">
        <?php if (!empty($_SESSION['user_id'])): ?>
          <div style="font-size:0.85rem; font-weight:700; color:var(--text); margin-bottom:0.5rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
            👤 <?= htmlspecialchars(($_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? 'Membre')) ?>
          </div>
          <div style="display:flex; flex-direction:column; gap:0.25rem;">
            <a href="<?= $base ?>/profile" class="nav-item" style="padding:6px 8px; font-size:0.85rem;">Mon Profil</a>
            <a href="<?= $base ?>/notifications" class="nav-item nav-item-notifications" style="padding:6px 8px; font-size:0.85rem;">
              <span>Notifications</span>
              <?php if (!empty($unreadCount)): ?><span class="nav-notification-count"><?= (int)$unreadCount ?></span><?php endif; ?>
            </a>
            <?php if (strtolower($_SESSION['user_role'] ?? '') === 'administrateur'): ?>
              <a href="<?= $base ?>/admin" class="nav-item" style="padding:6px 8px; font-size:0.85rem; color:var(--accent);">Panneau Admin</a>
            <?php endif; ?>
            <a href="<?= $base ?>/logout" class="nav-item" style="padding:6px 8px; font-size:0.85rem; color:#dc2626;">Déconnexion</a>
          </div>
        <?php else: ?>
          <div style="display:flex; gap:0.5rem;">
            <a href="<?= $base ?>/login" style="flex:1; text-align:center; padding:6px; font-size:0.85rem; text-decoration:none; border-radius:6px; border:1px solid var(--border); color:var(--text);">Connexion</a>
            <a href="<?= $base ?>/register" style="flex:1; text-align:center; padding:6px; font-size:0.85rem; text-decoration:none; border-radius:6px; background:var(--primary); color:#fff;">Inscription</a>
          </div>
        <?php endif; ?>
      </div>
    </aside>

    <!-- ========== CONTENU CENTRAL ========== -->
    <main class="main">
      <div class="header-welcome">
        <?php $welcomeName = !empty($_SESSION['user_prenom']) ? $_SESSION['user_prenom'] : 'Membre'; ?>
        <h1>Bienvenue, <?= htmlspecialchars($welcomeName) ?> !</h1>
        <p>Rejoins la conversation ou démarre une nouvelle discussion sur EPI Tchach.</p>
      </div>

      <div class="search-bar">
        <input id="publicationSearch" type="search" class="search-input" placeholder="Rechercher des discussions, tags, utilisateurs ou fichiers..." autocomplete="off">
      </div>

      <?php if ($flashSuccess): ?><div class="salon-flash salon-flash-success" role="status"><?= htmlspecialchars($flashSuccess) ?></div><?php endif; ?>
      <?php if ($flashError): ?><div class="salon-flash salon-flash-error" role="alert"><?= htmlspecialchars($flashError) ?></div><?php endif; ?>

      <div class="tabs" aria-label="Trier les publications">
        <a class="tab <?= $feedView === 'latest' ? 'active' : '' ?>" href="<?= $base ?>/salon?view=latest">Récentes</a>
        <a class="tab <?= $feedView === 'popular' ? 'active' : '' ?>" href="<?= $base ?>/salon?view=popular">Populaires</a>
        <a class="tab <?= $feedView === 'saved' ? 'active' : '' ?>" href="<?= $base ?>/salon?view=saved">Sauvegardées</a>
        <?php if ($categoryId): ?>
          <a class="tab tab-clear-filter" href="<?= $base ?>/salon?view=<?= htmlspecialchars($feedView) ?>">Effacer le filtre</a>
        <?php endif; ?>
      </div>

      <div id="searchResults" class="posts" hidden aria-live="polite"></div>

      <div class="posts" id="publicationList">
        <?php if (empty($feedPosts) && empty($savedAttachments)): ?>
          <div class="salon-empty-state">
            <h2><?= $feedView === 'saved' ? 'Aucun élément sauvegardé' : 'Aucune publication à afficher' ?></h2>
            <p><?= $feedView === 'saved'
              ? 'Sauvegardez une publication ou un document pour le retrouver ici.'
              : 'Les publications de vos catégories accessibles apparaîtront ici.' ?></p>
          </div>
        <?php endif; ?>

        <?php foreach ($feedPosts as $post):
          $postId = (int)$post['id'];
          $authorName = trim($post['prenom'] . ' ' . $post['nom']);
          $authorInitials = strtoupper(mb_substr($post['prenom'], 0, 1) . mb_substr($post['nom'], 0, 1));
          $authorRole = $roleLabels[$post['author_role']] ?? ucfirst($post['author_role']);
          $isSaved = !empty($post['saved_id']);
          $excerpt = mb_strimwidth(strip_tags($post['contenu']), 0, 500, '…');
        ?>
          <article class="post-card" id="publication-<?= $postId ?>">
            <div class="post-header">
              <div class="avatar"><?= htmlspecialchars($authorInitials) ?></div>
              <div class="author-info">
                <div class="author-name"><?= htmlspecialchars($authorName) ?> <span class="badge badge-role"><?= htmlspecialchars($authorRole) ?></span></div>
                <div class="author-meta">
                  <?= htmlspecialchars($post['zone_name']) ?> · <?= htmlspecialchars($post['category_name']) ?> · <?= htmlspecialchars(formatSalonDate($post['date_creation'])) ?>
                </div>
              </div>
              <form action="<?= $base ?>/salon/save" method="post" class="save-form">
                <input type="hidden" name="target_type" value="publication">
                <input type="hidden" name="target_id" value="<?= $postId ?>">
                <input type="hidden" name="return_view" value="<?= htmlspecialchars($feedView) ?>">
                <input type="hidden" name="return_category" value="<?= (int)($categoryId ?? 0) ?>">
                <button type="submit" class="save-button <?= $isSaved ? 'is-saved' : '' ?>" aria-label="<?= $isSaved ? 'Retirer des sauvegardes' : 'Sauvegarder cette publication' ?>">
                  <?= $isSaved ? '★ Sauvegardée' : '☆ Sauvegarder' ?>
                </button>
              </form>
            </div>
            <h2 class="post-title"><?= htmlspecialchars($post['titre']) ?></h2>
            <p class="post-excerpt"><?= nl2br(htmlspecialchars($excerpt)) ?></p>

            <?php if (!empty($postAttachments[$postId])): ?>
              <div class="post-attachments" aria-label="Documents joints">
                <?php foreach ($postAttachments[$postId] as $attachment): ?>
                  <div class="post-attachment">
                    <a href="<?= $base ?>/attachment/download/<?= (int)$attachment['id'] ?>">
                      📎 <?= htmlspecialchars($attachment['nom_fichier']) ?>
                    </a>
                    <form action="<?= $base ?>/salon/save" method="post" class="save-form">
                      <input type="hidden" name="target_type" value="attachment">
                      <input type="hidden" name="target_id" value="<?= (int)$attachment['id'] ?>">
                      <input type="hidden" name="return_view" value="<?= htmlspecialchars($feedView) ?>">
                      <input type="hidden" name="return_category" value="<?= (int)($categoryId ?? 0) ?>">
                      <button type="submit" class="save-document-button">
                        <?= !empty($attachment['saved_id']) ? 'Retirer des favoris' : 'Sauvegarder le document' ?>
                      </button>
                    </form>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <div class="post-footer">
              <div class="tags">
                <span class="tag"><?= htmlspecialchars(ucfirst($post['type_publication'])) ?></span>
                <?php if ($post['statut'] === 'epingle'): ?><span class="tag">Épinglée</span><?php endif; ?>
                <?php if ($post['statut'] === 'resolu'): ?><span class="tag">Résolue</span><?php endif; ?>
              </div>
              <div class="stats">
                <span class="stat">♥ <?= (int)$post['likes_count'] ?> likes</span>
                <span class="stat">💬 <?= (int)$post['comments_count'] ?> commentaires</span>
                <?php if ((int)$post['attachment_count'] > 0): ?><span class="stat">📎 <?= (int)$post['attachment_count'] ?> document(s)</span><?php endif; ?>
              </div>
            </div>
          </article>
        <?php endforeach; ?>

        <?php if ($feedView === 'saved' && $savedAttachments): ?>
          <h2 class="saved-documents-heading">Documents sauvegardés</h2>
          <?php foreach ($savedAttachments as $attachment): ?>
            <article class="saved-document-card">
              <div>
                <strong><?= htmlspecialchars($attachment['nom_fichier']) ?></strong>
                <span><?= htmlspecialchars($attachment['titre']) ?> · <?= htmlspecialchars($attachment['zone_name']) ?> · <?= number_format(((int)$attachment['taille']) / 1048576, 2, ',', ' ') ?> Mo</span>
              </div>
              <a class="saved-document-download" href="<?= $base ?>/attachment/download/<?= (int)$attachment['id'] ?>">Télécharger</a>
              <form action="<?= $base ?>/salon/save" method="post" class="save-form">
                <input type="hidden" name="target_type" value="attachment">
                <input type="hidden" name="target_id" value="<?= (int)$attachment['id'] ?>">
                <input type="hidden" name="return_view" value="saved">
                <button type="submit" class="save-document-button">Retirer</button>
              </form>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </main>

    <!-- ========== SIDEBAR DROITE ========== -->
    <aside class="sidebar-right">
      <div>
        <div class="widget-title">
          <span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle; margin-right:4px; color:var(--accent);"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
            Publications populaires
          </span>
          <a href="<?= $base ?>/salon?view=popular">Voir tout</a>
        </div>
        <?php if (!$popularPosts): ?>
          <p class="sidebar-empty">Aucune publication populaire pour le moment.</p>
        <?php else: ?>
          <?php foreach ($popularPosts as $rank => $popularPost): ?>
            <a class="trending-item" href="<?= $base ?>/salon?view=popular#publication-<?= (int)$popularPost['id'] ?>">
              <span class="trending-rank"><?= $rank + 1 ?></span>
              <span class="trending-info">
                <strong><?= htmlspecialchars($popularPost['titre']) ?></strong>
                <span>♥ <?= (int)$popularPost['likes_count'] ?> · 💬 <?= (int)$popularPost['comments_count'] ?></span>
              </span>
            </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <div>
        <div class="widget-title">
          <span>
            <span style="display:inline-block; width:8px; height:8px; background:var(--success); border-radius:50%; margin-right:6px;"></span>
            En ligne (<?= count($onlineUsers) ?>)
          </span>
          <span class="online-refresh-note">activité des 5 dernières minutes</span>
        </div>
        <?php if (!$onlineUsers): ?>
          <p class="sidebar-empty">Aucun compte actif dans les cinq dernières minutes.</p>
        <?php else: ?>
          <?php foreach ($onlineUsers as $onlineUser):
            $onlineInitials = strtoupper(mb_substr($onlineUser['prenom'], 0, 1) . mb_substr($onlineUser['nom'], 0, 1));
            $onlineName = trim($onlineUser['prenom'] . ' ' . $onlineUser['nom']);
            $onlineRole = $roleLabels[$onlineUser['role']] ?? ucfirst($onlineUser['role']);
          ?>
            <div class="online-user">
              <div class="online-avatar"><?= htmlspecialchars($onlineInitials) ?><span class="online-dot" aria-label="En ligne"></span></div>
              <div class="online-user-info">
                <div class="online-name"><?= htmlspecialchars($onlineName) ?></div>
                <div class="online-role"><?= htmlspecialchars($onlineRole) ?><?= !empty($onlineUser['filiere']) ? ' · ' . htmlspecialchars($onlineUser['filiere']) : '' ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </aside>

  </div>
  <script src="<?= $asset ?>/js/desktop/salon.js"></script>
</body>
</html>