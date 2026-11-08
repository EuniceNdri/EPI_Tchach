<?php
$base = is_string($pageBase ?? null) ? $pageBase : '';
$asset = is_string($pageAsset ?? null) ? $pageAsset : ($base . '/public/assets');
$authorName = trim((string)$publication['prenom'] . ' ' . (string)$publication['nom']);
$authorAvatar = !empty($publication['avatar_url'])
    ? url('public/' . ltrim((string)$publication['avatar_url'], '/'))
    : '';
$currentAvatar = !empty($_SESSION['user_avatar'])
    ? url('public/' . ltrim((string)$_SESSION['user_avatar'], '/'))
    : '';
$commentCount = count($comments);
$roleLabels = [
    'etudiant' => 'Étudiant',
    'professeur' => 'Professeur',
    'moderateur' => 'Modérateur',
    'delegue_general' => 'Délégué général',
    'administrateur' => 'Administrateur',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars((string)$publication['titre']) ?> · EPI Tchach</title>
  <link rel="icon" type="image/svg+xml" href="<?= htmlspecialchars($asset) ?>/images/logo_epi.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= htmlspecialchars($asset) ?>/css/desktop/community-shell.css">
  <link rel="stylesheet" href="<?= htmlspecialchars($asset) ?>/css/desktop/salon.css?v=<?= (int)filemtime(ROOT_PATH . '/public/assets/css/desktop/salon.css') ?>">
  <link rel="stylesheet" href="<?= htmlspecialchars($asset) ?>/css/feedback.css">
  <link rel="stylesheet" href="<?= htmlspecialchars($asset) ?>/css/desktop/publication.css?v=<?= (int)filemtime(ROOT_PATH . '/public/assets/css/desktop/publication.css') ?>">
  <?php include ROOT_PATH . '/views/partials/theme-head.php'; ?>
</head>
<body class="community-shell community-salon publication-detail-page" data-base-url="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>" data-publication-id="<?= (int)$publication['id'] ?>">
  <?php include ROOT_PATH . '/views/partials/theme-toggle.php'; ?>
  <?php include ROOT_PATH . '/views/partials/feedback-toast.php'; ?>
  <div class="tricolor-bar"></div>
  <header class="epi-topbar">
    <div class="epi-topbar-inner">
      <div class="epi-topbar-left">
        <a class="mobile-page-nav mobile-back-button" href="<?= htmlspecialchars($base) ?>/salon" data-history-back aria-label="Retour" title="Retour">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/><path d="M9 12h12"/></svg>
        </a>
        <a class="epi-brand" href="<?= htmlspecialchars($base) ?>/salon" aria-label="Accueil EPI Tchach">
          <div class="epi-brand-icon"><img src="<?= htmlspecialchars($asset) ?>/images/logo.jpeg" alt="EPI"></div>
          <span class="epi-brand-title">EPI <span class="accent">Tchach</span></span>
        </a>
        <a class="epi-community-pill" href="<?= htmlspecialchars($base) ?>/salon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          <span>Accueil</span>
        </a>
      </div>
      <div class="epi-topbar-center">
        <div class="epi-search-box">
          <span class="epi-search-icon" aria-hidden="true">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          </span>
          <input type="search" class="epi-search-input" placeholder="Rechercher des publications ou des profils..." aria-label="Rechercher des publications ou des profils" autocomplete="off">
        </div>
      </div>
      <div class="epi-topbar-right">
        <a href="<?= htmlspecialchars($base) ?>/messages" class="epi-icon-action" title="Messagerie" aria-label="Messagerie">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
          <?php if ($unreadMessagesCount > 0): ?><span class="epi-badge-count"><?= $unreadMessagesCount > 99 ? '99+' : (int)$unreadMessagesCount ?></span><?php endif; ?>
        </a>
        <a class="epi-create-post-btn" href="<?= htmlspecialchars($base) ?>/salon?compose=1" aria-label="Créer une publication">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          <span>Créer</span>
        </a>
        <a href="<?= htmlspecialchars($base) ?>/notifications" class="epi-icon-action epi-notif-action" title="Notifications" aria-label="Notifications">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <?php if ($unreadCount > 0): ?><span class="epi-badge-count"><?= (int)$unreadCount ?></span><?php endif; ?>
        </a>
        <div class="epi-user-menu-wrapper">
          <button type="button" class="epi-profile-pill" id="userMenuToggle" aria-label="Menu du profil utilisateur" aria-expanded="false">
            <div class="epi-avatar-bubble">
              <span class="epi-avatar-fallback"><?= htmlspecialchars(strtoupper(mb_substr((string)($_SESSION['user_prenom'] ?? 'M'), 0, 1))) ?></span>
              <img class="epi-avatar-image" src="<?= htmlspecialchars($currentAvatar, ENT_QUOTES, 'UTF-8') ?>" alt="" <?= $currentAvatar === '' ? 'hidden' : '' ?>>
              <span class="epi-status-dot" aria-label="En ligne"></span>
            </div>
            <div class="epi-profile-info">
              <span class="epi-profile-name"><?= htmlspecialchars($_SESSION['user_prenom'] ?? 'Membre') ?></span>
              <span class="epi-profile-role"><?= htmlspecialchars($roleLabels[$_SESSION['user_role'] ?? 'etudiant'] ?? 'Étudiant') ?></span>
            </div>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
          </button>
          <div class="epi-user-dropdown" id="userMenuDropdown" hidden>
            <div class="epi-dropdown-header">
              <strong><?= htmlspecialchars(($_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? '')) ?></strong>
              <small><?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></small>
            </div>
            <a href="<?= htmlspecialchars($base) ?>/profile" class="epi-dropdown-item">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg><span>Mon profil</span>
            </a>
            <a href="<?= htmlspecialchars($base) ?>/salon?view=saved" class="epi-dropdown-item">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg><span>Mes sauvegardes</span>
            </a>
            <?php if (strtolower((string)($_SESSION['user_role'] ?? '')) === 'administrateur'): ?>
              <a href="<?= htmlspecialchars($base) ?>/admin" class="epi-dropdown-item" style="color:var(--community-orange);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg><span>Panneau Admin</span>
              </a>
            <?php endif; ?>
            <div class="epi-dropdown-divider"></div>
            <a href="<?= htmlspecialchars($base) ?>/logout" class="epi-dropdown-item epi-logout-item">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg><span>Déconnexion</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </header>

  <main class="publication-detail-layout">
    <section class="publication-detail-main" aria-label="Publication et commentaires">
      <article class="epi-post-card publication-full-card" data-live-publication-id="<?= (int)$publication['id'] ?>">
        <div class="epi-card-header">
          <div class="epi-card-meta">
            <a class="epi-salon-tag" href="<?= htmlspecialchars($base) ?>/salon?category=<?= (int)$publication['categorie_id'] ?>">
              <span>s/<?= htmlspecialchars((string)$publication['category_name']) ?></span>
            </a>
            <span>•</span>
            <span class="epi-author-badge">Posté par <?= htmlspecialchars($authorName) ?></span>
            <span class="epi-role-pill"><?= htmlspecialchars($roleLabels[$publication['author_role']] ?? ucfirst((string)$publication['author_role'])) ?></span>
            <span>•</span>
            <time datetime="<?= htmlspecialchars((string)$publication['date_creation']) ?>"><?= htmlspecialchars(date('d/m/Y à H:i', strtotime((string)$publication['date_creation']))) ?></time>
          </div>
          <?php if ($publication['statut'] === 'epingle'): ?><span class="epi-status-pill is-pinned">📌 Épinglée</span><?php endif; ?>
          <?php if ($publication['statut'] === 'resolu'): ?><span class="epi-status-pill is-solved">✓ Résolue</span><?php endif; ?>
        </div>
        <div class="publication-detail-author">
          <div class="publication-detail-avatar">
            <?php if ($authorAvatar !== ''): ?><img src="<?= htmlspecialchars($authorAvatar, ENT_QUOTES, 'UTF-8') ?>" alt=""><?php else: ?><span><?= htmlspecialchars(strtoupper(mb_substr((string)$publication['prenom'], 0, 1) . mb_substr((string)$publication['nom'], 0, 1))) ?></span><?php endif; ?>
          </div>
          <div><strong><?= htmlspecialchars($authorName) ?></strong><span><?= htmlspecialchars((string)$publication['zone_name']) ?> · <?= htmlspecialchars((string)$publication['category_name']) ?></span></div>
        </div>
        <h1 class="epi-post-title publication-detail-title"><?= htmlspecialchars((string)$publication['titre']) ?></h1>
        <div class="epi-post-body publication-detail-body"><?= nl2br(htmlspecialchars((string)$publication['contenu'])) ?></div>

        <?php if ($publicationAttachments): ?>
          <div class="epi-media-container">
            <?php foreach ($publicationAttachments as $attachment): ?>
              <?php if (str_starts_with((string)$attachment['type_mime'], 'image/')): ?>
                <a href="<?= htmlspecialchars($base) ?>/attachment/view/<?= (int)$attachment['id'] ?>" target="_blank" rel="noopener">
                  <img class="epi-media-image" src="<?= htmlspecialchars($base) ?>/attachment/view/<?= (int)$attachment['id'] ?>" alt="<?= htmlspecialchars((string)$attachment['nom_fichier']) ?>">
                </a>
              <?php else: ?>
                <div class="epi-file-pill">
                  <span class="epi-file-badge"><?= htmlspecialchars(strtoupper(pathinfo((string)$attachment['nom_fichier'], PATHINFO_EXTENSION) ?: 'DOC')) ?></span>
                  <div class="publication-file-details"><strong><?= htmlspecialchars((string)$attachment['nom_fichier']) ?></strong><span><?= number_format((int)$attachment['taille'] / 1048576, 2, ',', ' ') ?> Mo</span></div>
                  <a class="epi-pill-btn" href="<?= htmlspecialchars($base) ?>/attachment/download/<?= (int)$attachment['id'] ?>">Télécharger</a>
                </div>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <?php if ($publication['type_publication'] === 'question' && $pollOptions): ?>
          <?php
            $pollTotal = array_sum(array_map(static fn (array $option): int => (int)$option['votes'], $pollOptions));
            $hasVoted = (bool)array_filter($pollOptions, static fn (array $option): bool => (bool)$option['user_voted']);
          ?>
          <form class="poll-form publication-detail-poll" action="<?= htmlspecialchars($base) ?>/publication/vote" method="post">
            <fieldset <?= $hasVoted ? 'class="poll-has-voted"' : '' ?>>
              <?php foreach ($pollOptions as $option): ?>
                <?php $percent = $pollTotal ? (int)round((int)$option['votes'] / $pollTotal * 100) : 0; ?>
                <label class="poll-option">
                  <input type="radio" name="option_id" value="<?= (int)$option['id'] ?>" <?= $option['user_voted'] ? 'checked' : '' ?> required>
                  <span class="poll-option-result" style="--vote-percent: <?= $percent ?>%">
                    <span><?= htmlspecialchars((string)$option['texte']) ?></span><strong><?= (int)$option['votes'] ?> vote(s) · <?= $percent ?>%</strong>
                  </span>
                </label>
              <?php endforeach; ?>
            </fieldset>
            <input type="hidden" name="publication_id" value="<?= (int)$publication['id'] ?>">
            <button class="poll-vote-button" type="submit"><?= $hasVoted ? 'Modifier mon vote' : 'Voter' ?></button>
            <span class="poll-feedback" role="status" aria-live="polite"></span>
          </form>
        <?php endif; ?>

        <div class="epi-card-actions publication-detail-actions">
          <div class="epi-vote-capsule">
            <button type="button" class="epi-vote-btn upvote <?= !empty($publication['user_liked']) ? 'is-voted' : '' ?>" data-toggle-reaction data-target-type="publication" data-target-id="<?= (int)$publication['id'] ?>" aria-label="Aimer la publication" aria-pressed="<?= !empty($publication['user_liked']) ? 'true' : 'false' ?>">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
            </button>
            <span class="epi-vote-count publication-like-count" data-live-likes><?= (int)$publication['likes_count'] ?></span>
          </div>
          <a class="epi-pill-btn post-discussion-button publication-comment-count" href="#publicationCommentsTitle" aria-label="Commentaires" title="Commentaires">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-.9 1.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8z"/></svg>
            <span><span data-live-comment-count><?= $commentCount ?></span><span class="epi-action-label" data-live-comment-label> commentaire<?= $commentCount === 1 ? '' : 's' ?></span></span>
          </a>
          <button type="button" class="epi-pill-btn epi-share-btn" data-share-url="<?= htmlspecialchars($base) ?>/publication/<?= (int)$publication['id'] ?>" aria-label="Partager" title="Partager">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
            <span>Partager</span>
          </button>
          <form class="publication-save-form" action="<?= htmlspecialchars($base) ?>/salon/save" method="post">
            <input type="hidden" name="target_type" value="publication">
            <input type="hidden" name="target_id" value="<?= (int)$publication['id'] ?>">
            <input type="hidden" name="return_page" value="publication">
            <input type="hidden" name="return_publication_id" value="<?= (int)$publication['id'] ?>">
            <button type="submit" class="epi-pill-btn epi-save-post-btn <?= !empty($publication['saved_id']) ? 'is-saved' : '' ?>" aria-label="<?= !empty($publication['saved_id']) ? 'Retirer des sauvegardes' : 'Sauvegarder' ?>" title="<?= !empty($publication['saved_id']) ? 'Retirer des sauvegardes' : 'Sauvegarder' ?>">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="<?= !empty($publication['saved_id']) ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
              <span><?= !empty($publication['saved_id']) ? 'Sauvegardé' : 'Sauvegarder' ?></span>
            </button>
          </form>
        </div>
      </article>

      <section class="publication-comments-section" data-live-publication-id="<?= (int)$publication['id'] ?>" aria-labelledby="publicationCommentsTitle">
        <div class="publication-comments-heading">
          <h2 id="publicationCommentsTitle">Commentaires <span data-live-comment-count><?= $commentCount ?></span></h2>
          <span>La conversation continue ici</span>
        </div>

        <?php if (!(bool)$publication['commentaires_fermes']): ?>
          <form class="discussion-comment-form publication-comment-form" action="<?= htmlspecialchars($base) ?>/comment/create" method="post">
            <input type="hidden" name="ajax" value="1">
            <input type="hidden" name="publication_id" value="<?= (int)$publication['id'] ?>">
            <div class="publication-comment-input-avatar">
              <?php if ($currentAvatar !== ''): ?><img src="<?= htmlspecialchars($currentAvatar, ENT_QUOTES, 'UTF-8') ?>" alt=""><?php else: ?><span><?= htmlspecialchars(strtoupper(mb_substr((string)($_SESSION['user_prenom'] ?? 'M'), 0, 1))) ?></span><?php endif; ?>
            </div>
            <div class="publication-comment-editor">
              <label class="sr-only" for="publicationCommentContent">Votre commentaire</label>
              <textarea class="epi-form-control" id="publicationCommentContent" name="contenu" maxlength="5000" required placeholder="Ajouter un commentaire…"></textarea>
              <div><span class="discussion-form-feedback" role="status" aria-live="polite"></span><button class="epi-btn-submit" type="submit">Commenter</button></div>
            </div>
          </form>
        <?php else: ?>
          <p class="publication-comments-closed">Les commentaires sont fermés pour cette publication.</p>
        <?php endif; ?>

        <?php if (!$comments): ?>
          <div class="publication-no-comments"><span>💬</span><strong>Pas encore de commentaire</strong><p>Soyez la première personne à répondre à cette publication.</p></div>
        <?php else: ?>
          <div class="publication-comment-list">
            <?php foreach ($comments as $comment): ?>
              <?php
                $commentAvatar = !empty($comment['avatar_url']) ? url('public/' . ltrim((string)$comment['avatar_url'], '/')) : '';
                $commentName = trim((string)$comment['prenom'] . ' ' . (string)$comment['nom']);
              ?>
              <article class="publication-comment<?= !empty($comment['est_solution']) ? ' is-solution' : '' ?><?= $comment['parent_comment_id'] !== null ? ' is-reply' : '' ?>"
                data-live-comment-id="<?= (int)$comment['id'] ?>"
                data-parent-comment-id="<?= $comment['parent_comment_id'] !== null ? (int)$comment['parent_comment_id'] : '' ?>">
                <div class="publication-comment-avatar">
                  <?php if ($commentAvatar !== ''): ?><img src="<?= htmlspecialchars($commentAvatar, ENT_QUOTES, 'UTF-8') ?>" alt=""><?php else: ?><span><?= htmlspecialchars(strtoupper(mb_substr((string)$comment['prenom'], 0, 1) . mb_substr((string)$comment['nom'], 0, 1))) ?></span><?php endif; ?>
                </div>
                <div class="publication-comment-content">
                  <div class="publication-comment-meta">
                    <strong><?= htmlspecialchars($commentName) ?></strong>
                    <time datetime="<?= htmlspecialchars((string)$comment['date_creation']) ?>"><?= htmlspecialchars(date('d/m/Y à H:i', strtotime((string)$comment['date_creation']))) ?></time>
                    <?php if (!empty($comment['est_solution'])): ?><span class="epi-status-pill is-solved">✓ Solution</span><?php endif; ?>
                  </div>
                  <p><?= nl2br(htmlspecialchars((string)$comment['contenu'])) ?></p>
                  <?php foreach ($commentAttachments[(int)$comment['id']] ?? [] as $attachment): ?>
                    <?php if (str_starts_with((string)$attachment['type_mime'], 'image/')): ?>
                      <a class="publication-comment-attachment-image" href="<?= htmlspecialchars($base) ?>/attachment/view/<?= (int)$attachment['id'] ?>" target="_blank" rel="noopener"><img src="<?= htmlspecialchars($base) ?>/attachment/view/<?= (int)$attachment['id'] ?>" alt="<?= htmlspecialchars((string)$attachment['nom_fichier']) ?>"></a>
                    <?php else: ?>
                      <a class="epi-pill-btn publication-comment-attachment-file" href="<?= htmlspecialchars($base) ?>/attachment/download/<?= (int)$attachment['id'] ?>">📎 <?= htmlspecialchars((string)$attachment['nom_fichier']) ?></a>
                    <?php endif; ?>
                  <?php endforeach; ?>
                  <div class="publication-comment-actions">
                    <button type="button" class="epi-pill-btn <?= !empty($comment['user_liked']) ? 'is-voted' : '' ?>" data-toggle-reaction data-target-type="commentaire" data-target-id="<?= (int)$comment['id'] ?>" aria-pressed="<?= !empty($comment['user_liked']) ? 'true' : 'false' ?>">
                      <?= !empty($comment['user_liked']) ? '♥ Aimé' : '♡ J’aime' ?> · <span data-live-comment-likes><?= (int)$comment['likes_count'] ?></span>
                    </button>
                    <?php if (!(bool)$publication['commentaires_fermes']): ?>
                      <button type="button" class="epi-pill-btn publication-reply-toggle" aria-expanded="false">↩ Répondre</button>
                    <?php endif; ?>
                  </div>
                  <?php if (!(bool)$publication['commentaires_fermes']): ?>
                    <form class="discussion-comment-form publication-reply-form" action="<?= htmlspecialchars($base) ?>/comment/create" method="post" hidden>
                      <input type="hidden" name="ajax" value="1">
                      <input type="hidden" name="publication_id" value="<?= (int)$publication['id'] ?>">
                      <input type="hidden" name="parent_comment_id" value="<?= (int)$comment['id'] ?>">
                      <label class="sr-only" for="replyContent<?= (int)$comment['id'] ?>">Votre réponse à <?= htmlspecialchars($commentName) ?></label>
                      <textarea class="epi-form-control" id="replyContent<?= (int)$comment['id'] ?>" name="contenu" maxlength="5000" required placeholder="Répondre à <?= htmlspecialchars($comment['prenom']) ?>…"></textarea>
                      <div>
                        <span class="discussion-form-feedback" role="status" aria-live="polite"></span>
                        <button class="epi-btn-submit" type="submit">Répondre</button>
                      </div>
                    </form>
                  <?php endif; ?>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>
    </section>
    <aside class="publication-detail-sidebar">
      <div class="publication-sidebar-card">
        <span class="publication-sidebar-kicker">À propos du salon</span>
        <h2><?= htmlspecialchars((string)$publication['zone_name']) ?></h2>
        <p>Cette publication est partagée dans le salon <strong><?= htmlspecialchars((string)$publication['category_name']) ?></strong>.</p>
        <a class="epi-pill-btn" href="<?= htmlspecialchars($base) ?>/salon?category=<?= (int)$publication['categorie_id'] ?>">Voir le salon</a>
      </div>
      <div class="publication-sidebar-card publication-sidebar-guidelines">
        <strong>Gardons la discussion constructive</strong>
        <p>Échangez avec respect et restez dans le sujet.</p>
      </div>
    </aside>
  </main>
  <script src="<?= htmlspecialchars($asset) ?>/js/desktop/salon.js?v=<?= (int)filemtime(ROOT_PATH . '/public/assets/js/desktop/salon.js') ?>"></script>
  <script src="<?= htmlspecialchars($asset) ?>/js/feedback.js" defer></script>
</body>
</html>
