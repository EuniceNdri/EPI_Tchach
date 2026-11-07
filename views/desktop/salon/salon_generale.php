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
$feedView = isset($feedView) && in_array($feedView, ['latest', 'popular', 'saved', 'documents'], true)
  ? $feedView
  : 'latest';
$categoryId = isset($categoryId) ? max(0, (int)$categoryId) : null;
$zones = is_array($zones ?? null) ? $zones : [];
$composerZones = is_array($composerZones ?? null) ? $composerZones : [];
$selectedZone = is_array($selectedZone ?? null) ? $selectedZone : null;
$feedPosts = is_array($feedPosts ?? null) ? $feedPosts : [];
$pollOptions = is_array($pollOptions ?? null) ? $pollOptions : [];
$salonDocuments = is_array($salonDocuments ?? null) ? $salonDocuments : [];
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
$zoneContext = $selectedZone !== null ? '&zone=' . (int)$selectedZone['id'] : '';
$categoryContext = $categoryId ? '&category=' . (int)$categoryId : '';
$pageTitle = $selectedZone !== null ? $selectedZone['nom'] : 'Tous vos salons';
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
  <title><?= htmlspecialchars($pageTitle) ?> · EPI Tchach</title>
  <link rel="icon" type="image/svg+xml" href="<?= $asset ?>/images/logo_epi.svg" />
  <link rel="apple-touch-icon" href="<?= $asset ?>/images/logo.jpeg" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="<?= $asset ?>/css/desktop/salon.css" />
  <link rel="stylesheet" href="<?= $asset ?>/css/feedback.css" />
</head>
<body data-base-url="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>">
  <?php include ROOT_PATH . '/views/partials/feedback-toast.php'; ?>
  <div class="tricolor-bar"></div>
  <div class="app">

    <!-- ========== SIDEBAR GAUCHE ========== -->
    <aside class="sidebar-left">
      <div class="logo">
        <img src="<?= htmlspecialchars($logoFile) ?>" alt="Logo EPI" style="width:36px; height:36px; border-radius:8px; object-fit:cover;" onerror="this.style.display='none'" />
        <div>
          <span style="font-size:1.1rem; font-weight:800; color:var(--primary);">EPI <span style="color:var(--accent);">Tchach</span></span>
          <div style="font-size:0.7rem; color:var(--text-muted); font-weight:600;"><?= htmlspecialchars($pageTitle) ?></div>
        </div>
      </div>

      <nav class="nav-menu" aria-label="Navigation du salon">
        <a href="<?= $base ?>/salon" class="nav-item <?= $selectedZone === null && $feedView === 'latest' && !$categoryId ? 'active' : '' ?>">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          <span>Tous les salons</span>
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
        <div class="section-title">Salons accessibles</div>
        <div class="category-list">
          <?php if (empty($zones)): ?>
            <p class="sidebar-empty">Aucun salon n'est encore rattaché à votre compte.</p>
          <?php else: ?>
            <?php foreach ($zones as $zone): ?>
              <a class="category-zone-title <?= $selectedZone !== null && (int)$selectedZone['id'] === (int)$zone['id'] ? 'is-selected' : '' ?>"
                 href="<?= $base ?>/salon?view=<?= rawurlencode($feedView) ?>&zone=<?= (int)$zone['id'] ?>">
                <?= htmlspecialchars($zone['nom']) ?>
                <span class="cat-count"><?= (int)$zone['membres_count'] ?></span>
              </a>
              <?php foreach ($zone['categories'] as $category): ?>
                <a href="<?= $base ?>/salon?view=<?= rawurlencode($feedView) ?>&zone=<?= (int)$zone['id'] ?>&category=<?= (int)$category['id'] ?>"
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
          <a href="<?= $base ?>/salon?view=<?= rawurlencode($feedView) ?><?= $zoneContext ?>" class="btn-browse">Afficher toutes les catégories</a>
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
        <h1><?= htmlspecialchars($pageTitle) ?></h1>
        <p><?= $selectedZone !== null
          ? htmlspecialchars((string)($selectedZone['description'] ?? 'Espace de discussion et d’entraide.'))
          : 'Retrouvez les discussions de tous les salons auxquels vous avez accès.' ?></p>
      </div>

      <div class="search-bar">
        <input id="publicationSearch" type="search" class="search-input" placeholder="Rechercher des discussions, tags, utilisateurs ou fichiers..." autocomplete="off">
      </div>

      <?php if ($flashSuccess): ?><div class="salon-flash salon-flash-success" role="status"><?= htmlspecialchars($flashSuccess) ?></div><?php endif; ?>
      <?php if ($flashError): ?><div class="salon-flash salon-flash-error" role="alert"><?= htmlspecialchars($flashError) ?></div><?php endif; ?>

      <?php
      $postCategories = [];
      foreach ($composerZones as $composerZone) {
        foreach ($composerZone['categories'] as $composerCategory) {
          $postCategories[] = [
            'id' => (int)$composerCategory['id'],
            'zone_id' => (int)$composerZone['id'],
            'zone_name' => (string)$composerZone['nom'],
            'name' => (string)$composerCategory['nom'],
          ];
        }
      }
      $defaultPostCategoryId = $categoryId !== null && $categoryId > 0
        ? $categoryId
        : (int)($postCategories[0]['id'] ?? 0);
      ?>
      <section class="salon-composer" aria-labelledby="salonComposerTitle">
        <h2 id="salonComposerTitle">Démarrer une discussion</h2>
        <?php if (!$postCategories): ?>
          <p class="salon-composer-hint">Aucun salon accessible pour publier. Rejoignez un salon pour participer.</p>
        <?php else: ?>
          <form id="salonPostForm" action="<?= $base ?>/publication/create" method="post" enctype="multipart/form-data">
            <input type="hidden" name="ajax" value="1">
            <input type="hidden" name="return_view" value="<?= htmlspecialchars($feedView, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="return_zone" value="<?= (int)($selectedZone['id'] ?? 0) ?>">
            <input type="hidden" name="return_category" value="<?= (int)($categoryId ?? 0) ?>">
            <label class="salon-form-label" for="salonPostTitle">Titre</label>
            <input class="salon-form-control" id="salonPostTitle" name="titre" type="text" maxlength="255" required placeholder="Le sujet de votre discussion">
            <label class="salon-form-label" id="salonContentLabel" for="salonPostContent">Votre message</label>
            <textarea class="salon-form-control salon-form-textarea" id="salonPostContent" name="contenu" rows="4" required placeholder="Partagez une question, une idée ou une ressource..."></textarea>
            <div class="salon-composer-footer">
              <select class="salon-form-control" id="publicationType" name="type_publication" aria-label="Type de publication">
                <option value="discussion">Discussion</option>
                <option value="question">Question / sondage</option>
                <option value="annonce">Annonce</option>
              </select>
              <select class="salon-form-control" name="categorie_id" required aria-label="Salon de destination">
                <?php foreach ($postCategories as $postCategory): ?>
                  <option value="<?= $postCategory['id'] ?>" data-zone-id="<?= $postCategory['zone_id'] ?>"
                    <?= $postCategory['id'] === $defaultPostCategoryId ? 'selected' : '' ?>>
                    <?= htmlspecialchars($postCategory['zone_name'] . ' · ' . $postCategory['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <div id="pollOptionsFields" class="poll-options-fields" hidden>
                <strong>Choix du sondage</strong>
                <div id="pollOptionsList">
                  <input class="salon-form-control" name="options[]" maxlength="255" placeholder="Choix 1" aria-label="Choix 1">
                  <input class="salon-form-control" name="options[]" maxlength="255" placeholder="Choix 2" aria-label="Choix 2">
                </div>
                <button class="poll-add-option" type="button" id="addPollOption">Ajouter un choix</button>
                <small>De 2 à 10 choix. Chaque membre peut voter une fois.</small>
              </div>
              <div class="salon-attachment-field">
                <label class="salon-attachment-picker" for="salonPostAttachments">
                  <span class="salon-attachment-picker-icon" aria-hidden="true">＋</span>
                  <span>
                    <strong>Ajouter des fichiers</strong>
                    <small>Photos, PDF et documents · 5 fichiers max · 10 Mo chacun</small>
                  </span>
                </label>
                <input id="salonPostAttachments" class="salon-attachment-input" type="file" name="pieces_jointes[]" multiple
                  accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.png,.jpg,.jpeg,.gif">
              </div>
              <button class="salon-submit-button" type="submit">Publier</button>
            </div>
            <div id="salonAttachmentPreview" class="salon-attachment-preview" aria-live="polite" hidden></div>
            <p id="salonPostFeedback" class="salon-composer-feedback" role="status" aria-live="polite"></p>
          </form>
        <?php endif; ?>
      </section>

      <div class="tabs" aria-label="Trier les publications">
        <a class="tab <?= $feedView === 'latest' ? 'active' : '' ?>" href="<?= $base ?>/salon?view=latest<?= $zoneContext ?><?= $categoryContext ?>">Récentes</a>
        <a class="tab <?= $feedView === 'popular' ? 'active' : '' ?>" href="<?= $base ?>/salon?view=popular<?= $zoneContext ?><?= $categoryContext ?>">Populaires</a>
        <a class="tab <?= $feedView === 'saved' ? 'active' : '' ?>" href="<?= $base ?>/salon?view=saved<?= $zoneContext ?><?= $categoryContext ?>">Sauvegardées</a>
        <a class="tab <?= $feedView === 'documents' ? 'active' : '' ?>" href="<?= $base ?>/salon?view=documents<?= $zoneContext ?><?= $categoryContext ?>">Documents partagés</a>
        <?php if ($categoryId): ?>
          <a class="tab tab-clear-filter" href="<?= $base ?>/salon?view=<?= rawurlencode($feedView) ?><?= $zoneContext ?>">Effacer le filtre</a>
        <?php endif; ?>
      </div>

      <div id="searchResults" class="posts" hidden aria-live="polite"></div>

      <div class="posts" id="publicationList">
        <?php if ($feedView === 'documents'): ?>
          <section class="shared-documents" aria-labelledby="sharedDocumentsTitle">
            <h2 id="sharedDocumentsTitle">Documents partagés<?= $selectedZone !== null ? ' · ' . htmlspecialchars($selectedZone['nom']) : '' ?></h2>
            <p>Les fichiers sont classés selon le salon de la publication à laquelle ils sont joints.</p>
            <?php if (!$salonDocuments): ?>
              <div class="salon-empty-state"><h3>Aucun document partagé</h3><p>Les PDF et autres fichiers ajoutés aux publications de ce salon apparaîtront ici.</p></div>
            <?php else: ?>
              <?php foreach ($salonDocuments as $document): ?>
                <?php
                  $documentIsPdf = (string)$document['type_mime'] === 'application/pdf';
                  $documentPublicationUrl = $base . '/salon?view=latest&zone=' . (int)$document['zone_id']
                    . '&category=' . (int)$document['category_id'] . '#publication-' . (int)$document['publication_id'];
                ?>
                <article class="shared-document-card">
                  <span class="shared-document-icon" aria-hidden="true"><?= $documentIsPdf ? 'PDF' : 'DOC' ?></span>
                  <div class="shared-document-info">
                    <strong><?= htmlspecialchars($document['nom_fichier']) ?></strong>
                    <span><?= htmlspecialchars($document['zone_name']) ?> · <?= htmlspecialchars($document['category_name']) ?> · <?= htmlspecialchars(trim($document['prenom'] . ' ' . $document['nom'])) ?></span>
                    <a href="<?= htmlspecialchars($documentPublicationUrl, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($document['titre']) ?></a>
                  </div>
                  <?php if ($documentIsPdf): ?>
                    <a class="shared-document-action" href="<?= $base ?>/attachment/view/<?= (int)$document['id'] ?>" target="_blank" rel="noopener">Ouvrir</a>
                  <?php endif; ?>
                  <a class="shared-document-action" href="<?= $base ?>/attachment/download/<?= (int)$document['id'] ?>">Télécharger</a>
                </article>
              <?php endforeach; ?>
            <?php endif; ?>
          </section>
        <?php endif; ?>
        <?php if ($feedView !== 'documents' && empty($feedPosts) && empty($savedAttachments)): ?>
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
          <article class="post-card" id="publication-<?= $postId ?>"
            <?= $post['type_publication'] === 'discussion' ? 'data-discussion-card="' . $postId . '"' : '' ?>>
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
                  <?php $isImageAttachment = str_starts_with((string)$attachment['type_mime'], 'image/'); ?>
                  <div class="post-attachment <?= $isImageAttachment ? 'is-image' : '' ?>">
                    <?php if ($isImageAttachment): ?>
                      <a class="post-attachment-preview" href="<?= $base ?>/attachment/view/<?= (int)$attachment['id'] ?>" target="_blank" rel="noopener" aria-label="Aperçu de <?= htmlspecialchars($attachment['nom_fichier'], ENT_QUOTES, 'UTF-8') ?>">
                        <img src="<?= $base ?>/attachment/view/<?= (int)$attachment['id'] ?>" alt="Aperçu : <?= htmlspecialchars($attachment['nom_fichier'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
                      </a>
                    <?php else: ?>
                      <span class="post-attachment-icon" aria-hidden="true">▤</span>
                    <?php endif; ?>
                    <div class="post-attachment-info">
                      <strong title="<?= htmlspecialchars($attachment['nom_fichier'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($attachment['nom_fichier']) ?></strong>
                      <span><?= number_format(((int)$attachment['taille']) / 1048576, 2, ',', ' ') ?> Mo</span>
                    </div>
                    <a class="post-attachment-download" href="<?= $base ?>/attachment/download/<?= (int)$attachment['id'] ?>">Télécharger</a>
                    <form action="<?= $base ?>/salon/save" method="post" class="save-form">
                      <input type="hidden" name="target_type" value="attachment">
                      <input type="hidden" name="target_id" value="<?= (int)$attachment['id'] ?>">
                      <input type="hidden" name="return_view" value="<?= htmlspecialchars($feedView) ?>">
                      <input type="hidden" name="return_category" value="<?= (int)($categoryId ?? 0) ?>">
                      <button type="submit" class="save-document-button attachment-save-button"
                        aria-label="<?= !empty($attachment['saved_id']) ? 'Retirer des favoris' : 'Ajouter aux favoris' ?>"
                        title="<?= !empty($attachment['saved_id']) ? 'Retirer des favoris' : 'Ajouter aux favoris' ?>">
                        <?= !empty($attachment['saved_id']) ? '★' : '☆' ?>
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
                <?php if ($post['type_publication'] !== 'question'): ?><span class="stat publication-like-count">♥ <?= (int)$post['likes_count'] ?> likes</span><?php endif; ?>
                <?php if ($post['type_publication'] === 'discussion'): ?><span class="stat">💬 <?= (int)$post['comments_count'] ?> commentaires</span><?php endif; ?>
                <?php if ((int)$post['attachment_count'] > 0): ?><span class="stat">📎 <?= (int)$post['attachment_count'] ?> document(s)</span><?php endif; ?>
              </div>
            </div>
            <?php if ($post['type_publication'] === 'question'): ?>
              <?php
                $options = $pollOptions[$postId] ?? [];
                $totalVotes = array_sum(array_map(static fn (array $option): int => (int)$option['votes'], $options));
                $hasVoted = (bool)array_filter($options, static fn (array $option): bool => (bool)$option['user_voted']);
              ?>
              <?php if (!$options): ?>
                <p class="poll-feedback">Ce sondage ne contient pas encore de choix.</p>
              <?php else: ?>
              <form class="poll-form" action="<?= $base ?>/publication/vote" method="post" data-publication-id="<?= $postId ?>">
                <fieldset <?= $hasVoted ? 'class="poll-has-voted"' : '' ?>>
                  <legend><?= $hasVoted ? 'Résultats du sondage' : 'Votez pour une réponse' ?></legend>
                  <?php foreach ($options as $option): ?>
                    <?php $votePercent = $totalVotes > 0 ? (int)round(((int)$option['votes'] / $totalVotes) * 100) : 0; ?>
                    <label class="poll-option">
                      <input type="radio" name="option_id" value="<?= (int)$option['id'] ?>" <?= $option['user_voted'] ? 'checked' : '' ?> required>
                      <span class="poll-option-result" style="--vote-percent: <?= $votePercent ?>%">
                        <span><?= htmlspecialchars($option['texte']) ?></span>
                        <strong><?= (int)$option['votes'] ?> · <?= $votePercent ?>%</strong>
                      </span>
                    </label>
                  <?php endforeach; ?>
                </fieldset>
                <input type="hidden" name="publication_id" value="<?= $postId ?>">
                <button class="poll-vote-button" type="submit"><?= $hasVoted ? 'Modifier mon vote' : 'Voter' ?></button>
                <span class="poll-feedback" role="status" aria-live="polite"></span>
              </form>
              <?php endif; ?>
            <?php else: ?>
              <div class="post-actions">
                <button class="post-action-button <?= !empty($post['user_liked']) ? 'is-active' : '' ?>"
                  type="button" data-toggle-reaction data-target-type="publication" data-target-id="<?= $postId ?>"
                  aria-pressed="<?= !empty($post['user_liked']) ? 'true' : 'false' ?>">
                  <?= !empty($post['user_liked']) ? '♥ Aimé' : '♡ J’aime' ?>
                </button>
                <?php if ($post['type_publication'] === 'discussion'): ?>
                  <button class="post-action-button post-discussion-button" type="button"
                    data-open-discussion="<?= $postId ?>" aria-label="Ouvrir la discussion : <?= htmlspecialchars($post['titre'], ENT_QUOTES, 'UTF-8') ?>">
                    Voir la discussion
                  </button>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>

        <?php if ($feedView === 'saved' && $savedAttachments): ?>
          <h2 class="saved-documents-heading">Documents sauvegardés</h2>
          <?php foreach ($savedAttachments as $attachment): ?>
            <?php $isImageAttachment = str_starts_with((string)$attachment['type_mime'], 'image/'); ?>
            <article class="saved-document-card <?= $isImageAttachment ? 'is-image' : '' ?>">
              <?php if ($isImageAttachment): ?>
                <a class="saved-document-preview" href="<?= $base ?>/attachment/view/<?= (int)$attachment['id'] ?>" target="_blank" rel="noopener" aria-label="Aperçu de <?= htmlspecialchars($attachment['nom_fichier'], ENT_QUOTES, 'UTF-8') ?>">
                  <img src="<?= $base ?>/attachment/view/<?= (int)$attachment['id'] ?>" alt="Aperçu : <?= htmlspecialchars($attachment['nom_fichier'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
                </a>
              <?php else: ?>
                <span class="post-attachment-icon" aria-hidden="true">▤</span>
              <?php endif; ?>
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

    <aside id="discussionPanel" class="discussion-panel" aria-labelledby="discussionPanelTitle" aria-hidden="true" hidden>
      <div class="discussion-panel-header">
        <div><span>Discussion</span><h2 id="discussionPanelTitle">Publication</h2></div>
        <button type="button" class="discussion-panel-close" data-close-discussion aria-label="Fermer la discussion">×</button>
      </div>
      <div id="discussionPanelContent" class="discussion-panel-content" aria-live="polite"></div>
    </aside>

  </div>
  <script src="<?= $asset ?>/js/desktop/salon.js"></script>
  <script src="<?= $asset ?>/js/feedback.js" defer></script>
</body>
</html>