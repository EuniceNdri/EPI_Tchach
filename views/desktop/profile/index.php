<?php
/**
 * Variables supplied by ProfileController::index() before this view is included.
 *
 * @var string|null $baseUrl
 * @var string|null $assetBase
 * @var array|null $user
 * @var array|null $profilePosts
 * @var array|null $profileZones
 * @var array|null $profileCategories
 * @var array|null $profileDocuments
 * @var array|null $profileStats
 * @var int|null $unreadCount
 * @var bool|null $isOwnProfile
 */
$base = is_string($baseUrl ?? null) ? $baseUrl : '';
$asset = is_string($assetBase ?? null)
  ? $assetBase
  : ($base !== '' ? $base . '/public/assets' : '/public/assets');
$logoFile = $asset . '/images/logo.jpeg';

// Récupération des informations du profil (utilisateur consulté ou session courante)
$user = is_array($user ?? null) ? $user : [];
$profilePosts = is_array($profilePosts ?? null) ? $profilePosts : [];
$profileZones = is_array($profileZones ?? null) ? $profileZones : [];
$profileCategories = is_array($profileCategories ?? null) ? $profileCategories : [];
$profileDocuments = is_array($profileDocuments ?? null) ? $profileDocuments : [];
$profileStats = array_merge(
  ['posts' => 0, 'comments' => 0, 'documents' => 0, 'zones' => 0],
  is_array($profileStats ?? null) ? $profileStats : []
);
$unreadCount = max(0, (int)($unreadCount ?? 0));
if (!isset($_SESSION) || !is_array($_SESSION)) {
  $_SESSION = [];
}
$sessionNom = $user['nom'] ?? '';
$sessionPrenom = $user['prenom'] ?? '';
$sessionFullName = trim($sessionPrenom . ' ' . $sessionNom);
$sessionEmail = $user['email'] ?? '';
$sessionRole = $user['role'] ?? 'etudiant';
$sessionBio = $user['biographie'] ?? '';
$sessionFiliere = $user['filiere'] ?? '';
$sessionModules = $user['modules_enseignes'] ?? '';
$initials = strtoupper(mb_substr($sessionPrenom, 0, 1) . mb_substr($sessionNom, 0, 1));
$isOwnProfile = isset($isOwnProfile)
  ? (bool)$isOwnProfile
  : (isset($_SESSION['user_id'], $user['id']) && (int)$_SESSION['user_id'] === (int)$user['id']);
$roleLabels = [
  'etudiant' => 'Étudiant',
  'professeur' => 'Professeur',
  'moderateur' => 'Modérateur',
  'administrateur' => 'Administrateur',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Profil Membre — <?= htmlspecialchars($sessionFullName) ?> · EPI Tchach</title>
  <meta name="description" content="Espace profil membre et gestion du compte de l'École Polytechnique Internationale de Yamoussoukro" />
  <link rel="icon" type="image/svg+xml" href="<?= $asset ?>/images/logo_epi.svg" />
  <link rel="apple-touch-icon" href="<?= $asset ?>/images/logo.jpeg" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="<?= $asset ?>/css/desktop/profile.css" />
</head>
<body>
  <!-- CONTENEUR GLOBAL AVEC LAYOUT SIDEBAR + PAGE PRINCIPALE -->
  <div class="profile-page-wrapper">
    <div class="profile-layout-grid">

      <!-- ========================================================
           1. SIDEBAR DU PROFIL (Fixée à gauche)
           ======================================================== -->
      <aside class="profile-sidebar-card">
        <a href="<?= $base ?>/salon" class="profile-sidebar-brand">
          <img src="<?= htmlspecialchars($logoFile, ENT_QUOTES, 'UTF-8') ?>" alt="" class="brand-logo-img" />
          <span>EPI <strong>Tchach</strong></span>
        </a>
        <div class="sidebar-profile-hero">
          <div class="sidebar-avatar-wrapper">
            <div class="sidebar-avatar profile-dynamic-initials"><?= htmlspecialchars($initials) ?></div>
            <?php if ($isOwnProfile): ?><div class="sidebar-online-dot" title="En ligne sur EPI Tchach"></div><?php endif; ?>
          </div>
          <h2 class="sidebar-user-name profile-dynamic-name"><?= htmlspecialchars($sessionFullName) ?></h2>
          <span class="sidebar-role-badge">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            <span><?= htmlspecialchars($roleLabels[$sessionRole] ?? ucfirst($sessionRole)) ?></span>
          </span>
          <div style="display: flex; flex-wrap: wrap; gap: 0.35rem; justify-content: center; margin: 0.5rem 0;">
            <?php if (!empty($sessionFiliere)): ?>
              <span class="sidebar-role-badge" style="background: rgba(30, 58, 138, 0.08); color: var(--primary); font-size: 0.8rem; border: 1px solid rgba(30, 58, 138, 0.15);">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/></svg>
                <span><?= htmlspecialchars($sessionFiliere) ?></span>
              </span>
            <?php endif; ?>

            <?php if (!empty($sessionModules) && $sessionRole === 'professeur'): ?>
              <span class="sidebar-role-badge" style="background: rgba(234, 88, 12, 0.08); color: var(--accent); font-size: 0.78rem; border: 1px solid rgba(234, 88, 12, 0.2);" title="<?= htmlspecialchars($sessionModules) ?>">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 2 7 12 12 22 7 12 2"/></svg>
                <span><?= htmlspecialchars(mb_strimwidth($sessionModules, 0, 32, '...')) ?></span>
              </span>
            <?php endif; ?>
          </div>

          <p class="sidebar-bio-text profile-dynamic-bio"><?= htmlspecialchars($sessionBio ?: 'Aucune biographie renseignée.') ?></p>

          <!-- Bouton Popup Modifier Mon Profil -->
          <?php if ($isOwnProfile): ?>
            <button type="button" class="btn-sidebar-edit open-profile-edit-modal">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
              <span>Modifier mon profil</span>
            </button>
          <?php endif; ?>
        </div>

        <!-- Menu de navigation par onglets dans la sidebar -->
        <ul class="profile-sidebar-menu">
          <li>
            <a href="#flux" class="sidebar-tab-link active" data-tab="tab-posts">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
              <span>Flux & Publications</span>
            </a>
          </li>
          <li>
            <a href="#salons" class="sidebar-tab-link" data-tab="tab-channels">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
              <span>Mes Salons & Filières</span>
            </a>
          </li>
          <li>
            <a href="#documents" class="sidebar-tab-link" data-tab="tab-docs">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
              <span>Documents partagés</span>
            </a>
          </li>
          <li>
            <a href="#apropos" class="sidebar-tab-link" data-tab="tab-about">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
              <span>À propos & Infos</span>
            </a>
          </li>
        </ul>

        <!-- Coordonnées académiques rapides -->
        <ul class="sidebar-meta-list">
          <li class="sidebar-meta-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
            <span class="profile-dynamic-email"><?= htmlspecialchars($sessionEmail) ?></span>
          </li>
          <?php if (!empty($sessionFiliere)): ?>
          <li class="sidebar-meta-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6"/><path d="M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
            <span>Filière : <?= htmlspecialchars($sessionFiliere) ?></span>
          </li>
          <?php endif; ?>
        </ul>
        <nav class="profile-account-menu" aria-label="Compte">
          <a href="<?= $base ?>/notifications">
            Notifications
            <?php if (!empty($unreadCount)): ?><span class="profile-notification-count"><?= (int)$unreadCount ?></span><?php endif; ?>
          </a>
          <?php if ($sessionRole === 'administrateur'): ?><a href="<?= $base ?>/admin">Administration</a><?php endif; ?>
          <a class="profile-logout-link" href="<?= $base ?>/logout">Déconnexion</a>
        </nav>
      </aside>

      <!-- ========================================================
           2. ZONE PRINCIPALE (Page centrale & droite)
           ======================================================== -->
      <main class="profile-main-area">

        <!-- Bannière d'accueil -->
        <div class="profile-banner-card" style="margin-bottom:0;">
          <div class="profile-cover">
            <div class="profile-cover-pattern"></div>
          </div>
          <div style="padding: 1.5rem 1.75rem;">
            <h1 style="font-size: 1.65rem; font-weight: 800; color:var(--foreground);">
              <?= $isOwnProfile ? 'Mon espace membre' : 'Profil de ' . htmlspecialchars($sessionFullName) ?>
            </h1>
            <p style="color:var(--muted-foreground); margin-top:0.35rem; font-size:0.95rem;">
              Consultez vos échanges académiques, accédez à vos filières et posez vos questions en direct aux étudiants et professeurs.
            </p>
          </div>
        </div>

        <div class="profile-stats-grid" aria-label="Activité du membre">
          <div class="stat-box">
            <div class="stat-icon-wrapper icon-blue" aria-hidden="true">▤</div>
            <div><div class="stat-val" id="statSubjectsCount"><?= (int)$profileStats['posts'] ?></div><div class="stat-lbl">Publications</div></div>
          </div>
          <div class="stat-box">
            <div class="stat-icon-wrapper icon-green" aria-hidden="true">↩</div>
            <div><div class="stat-val"><?= (int)$profileStats['comments'] ?></div><div class="stat-lbl">Réponses publiées</div></div>
          </div>
          <div class="stat-box">
            <div class="stat-icon-wrapper icon-amber" aria-hidden="true">▣</div>
            <div><div class="stat-val" id="statDocumentsCount"><?= (int)$profileStats['documents'] ?></div><div class="stat-lbl">Documents partagés</div></div>
          </div>
          <div class="stat-box">
            <div class="stat-icon-wrapper icon-blue" aria-hidden="true">⌂</div>
            <div><div class="stat-val"><?= (int)$profileStats['zones'] ?></div><div class="stat-lbl">Salons rejoints</div></div>
          </div>
        </div>

        <!-- Boîte de Publication Rapide (AJAX sans rechargement de page) -->
        <?php if ($isOwnProfile): ?>
        <section class="quick-post-card">
          <form id="quickPostForm" action="<?= htmlspecialchars($base) ?>/publication/create" method="post" enctype="multipart/form-data">
            <input type="hidden" name="ajax" value="1" />
            <input type="hidden" name="type_publication" value="discussion" />
            <div class="quick-post-header">
              <div class="post-avatar-mini profile-dynamic-initials"><?= htmlspecialchars($initials) ?></div>
              <strong style="font-size:0.95rem;">Poser une question ou lancer une discussion</strong>
            </div>
            <input type="text" name="titre" class="quick-post-title-input" placeholder="Titre de votre question ou sujet (ex: Aide sur le TP de Réseaux)..." required />
            <textarea name="contenu" class="quick-post-textarea" placeholder="Expliquez votre problème ou détaillez votre partage ici pour les membres de l'école..." required></textarea>
            <label for="postAttachments" style="display:inline-flex; align-items:center; gap:0.4rem; margin-top:0.65rem; color:var(--muted-foreground); cursor:pointer; font-size:0.85rem;">
              <span aria-hidden="true">📎</span>
              <span>Joindre des fichiers (PDF, documents, images, ZIP — 10 Mo max chacun)</span>
            </label>
            <input id="postAttachments" type="file" name="pieces_jointes[]" multiple
              accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.png,.jpg,.jpeg,.gif"
              style="margin-top:0.35rem; max-width:100%;" />
            <div class="quick-post-footer">
              <select name="categorie_id" class="post-tag-select" required <?= empty($profileCategories) ? 'disabled' : '' ?>>
                <?php if (!$profileCategories): ?>
                  <option value="">Aucun salon accessible</option>
                <?php else: ?>
                  <?php foreach ($profileCategories as $category): ?>
                    <option value="<?= (int)$category['id'] ?>"><?= htmlspecialchars($category['zone_name'] . ' · ' . $category['nom']) ?></option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
              <button type="submit" class="btn btn-primary btn-sm" <?= empty($profileCategories) ? 'disabled' : '' ?>>
                <span>Publier le sujet</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
              </button>
            </div>
          </form>
        </section>
        <?php endif; ?>

        <!-- CONTENUS DES ONGLETS -->
        <div class="profile-tabs-card" style="padding: 1.75rem;">

          <!-- ONGLET 1 : FLUX & PUBLICATIONS -->
          <div class="tab-content active" id="tab-posts">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem; border-bottom:1px solid var(--border); padding-bottom:1rem;">
              <h3 style="font-size:1.15rem; font-weight:800;">Dernières publications sur le forum</h3>
              <span style="font-size:0.85rem; color:var(--muted-foreground);">Trié par récence</span>
            </div>

            <div class="posts-feed" id="postsFeedList">
              <?php if (!$profilePosts): ?>
                <p class="profile-empty">Aucune publication pour le moment.</p>
              <?php else: ?>
                <?php foreach ($profilePosts as $post): ?>
                  <article class="post-card">
                    <div class="post-header-flex">
                      <div class="post-author-box">
                        <div class="post-avatar-mini profile-dynamic-initials"><?= htmlspecialchars($initials) ?></div>
                        <div>
                          <div class="post-author-name profile-dynamic-name"><?= htmlspecialchars($sessionFullName) ?></div>
                          <div class="post-time"><?= htmlspecialchars($post['zone_name'] . ' · ' . $post['category_name'] . ' · ' . date('d/m/Y H:i', strtotime($post['date_creation']))) ?></div>
                        </div>
                      </div>
                      <span class="post-tag-pill"><?= htmlspecialchars(ucfirst($post['type_publication'])) ?></span>
                    </div>
                    <h4 class="post-title"><?= htmlspecialchars($post['titre']) ?></h4>
                    <p class="post-excerpt"><?= nl2br(htmlspecialchars(mb_strimwidth(strip_tags($post['contenu']), 0, 360, '…'))) ?></p>
                    <div class="post-footer-actions">
                      <span class="profile-post-stat">♥ <?= (int)$post['likes_count'] ?> likes</span>
                      <span class="profile-post-stat">💬 <?= (int)$post['comments_count'] ?> réponses</span>
                      <span class="profile-post-stat"><?= htmlspecialchars(ucfirst($post['statut'])) ?></span>
                    </div>
                  </article>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>

          <!-- ONGLET 2 : SALONS & FILIÈRES -->
          <div class="tab-content" id="tab-channels">
            <h3 class="profile-section-title">Salons rejoints</h3>
            <?php if (!$profileZones): ?>
              <p class="profile-empty">Aucun salon n'est associé à ce compte.</p>
            <?php else: ?>
              <div class="profile-zone-grid">
                <?php foreach ($profileZones as $zone): ?>
                  <a class="profile-zone-card" href="<?= $base ?>/salon">
                    <span class="post-tag-pill"><?= htmlspecialchars(ucfirst($zone['type_zone'])) ?></span>
                    <strong><?= htmlspecialchars($zone['nom']) ?></strong>
                    <?php if (!empty($zone['description'])): ?><span><?= htmlspecialchars($zone['description']) ?></span><?php endif; ?>
                    <small><?= (int)$zone['membres_count'] ?> membre(s) · <?= htmlspecialchars(str_replace('_', ' ', $zone['role_dans_zone'])) ?></small>
                  </a>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- ONGLET 3 : DOCUMENTS PARTAGÉS -->
          <div class="tab-content" id="tab-docs">
            <h3 class="profile-section-title">Documents partagés</h3>
            <?php if (!$profileDocuments): ?>
              <p class="profile-empty">Aucun document n'a été partagé par ce membre.</p>
            <?php else: ?>
              <div class="profile-documents-list">
                <?php foreach ($profileDocuments as $document): ?>
                  <div class="profile-document-row">
                    <div>
                      <strong><?= htmlspecialchars($document['nom_fichier']) ?></strong>
                      <span><?= htmlspecialchars($document['publication_title'] . ' · ' . $document['category_name']) ?> · <?= number_format(((int)$document['taille']) / 1048576, 2, ',', ' ') ?> Mo</span>
                    </div>
                    <a class="btn btn-outline btn-sm" href="<?= $base ?>/attachment/download/<?= (int)$document['id'] ?>">Télécharger</a>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- ONGLET 4 : À PROPOS -->
          <div class="tab-content" id="tab-about">
            <h3 class="profile-section-title">À propos</h3>
            <dl class="profile-about-list">
              <div><dt>Biographie</dt><dd class="profile-dynamic-bio"><?= htmlspecialchars($sessionBio ?: 'Aucune biographie renseignée.') ?></dd></div>
              <div><dt>Adresse e-mail</dt><dd class="profile-dynamic-email"><?= htmlspecialchars($sessionEmail) ?></dd></div>
              <div><dt>Rôle</dt><dd><?= htmlspecialchars($roleLabels[$sessionRole] ?? ucfirst($sessionRole)) ?></dd></div>
              <?php if ($sessionFiliere): ?><div><dt>Filière</dt><dd><?= htmlspecialchars($sessionFiliere) ?></dd></div><?php endif; ?>
              <?php if ($sessionRole === 'professeur' && $sessionModules): ?><div><dt>Modules enseignés</dt><dd><?= htmlspecialchars($sessionModules) ?></dd></div><?php endif; ?>
            </dl>
          </div>

        </div>
      </main>

    </div>
  </div>

  <!-- ============================================================
       3. MODALE POPUP D'ÉDITION DU PROFIL (Sans rechargement de page)
       ============================================================ -->
  <?php if ($isOwnProfile): ?>
  <div id="profileEditModal" class="modal-backdrop-profile" aria-hidden="true" role="dialog" aria-labelledby="modalEditTitle">
    <div class="modal-dialog-profile">
      
      <!-- En-tête de la popup -->
      <div class="modal-header-profile">
        <div>
          <h3 id="modalEditTitle" class="modal-title-profile">Modifier mon Profil Membre</h3>
          <p style="font-size:0.825rem; color:var(--muted-foreground); margin-top:0.15rem;">
            Vos modifications seront immédiatement appliquées sur la plateforme.
          </p>
        </div>
        <button type="button" class="btn-close-modal" id="closeEditModalBtn" aria-label="Fermer" style="background:none; border:none; cursor:pointer; color:var(--muted-foreground);">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>

      <!-- Formulaire AJAX -->
      <form id="ajaxProfileEditForm" action="<?= $base ?>/profile/update" method="POST">
        <div class="modal-body-profile">

          <!-- Ligne Nom & Prénom -->
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label" for="editNom">Nom de famille</label>
              <input type="text" id="editNom" name="nom" class="form-control" value="<?= htmlspecialchars($sessionNom) ?>" required />
            </div>
            <div class="form-group">
              <label class="form-label" for="editPrenom">Prénom</label>
              <input type="text" id="editPrenom" name="prenom" class="form-control" value="<?= htmlspecialchars($sessionPrenom) ?>" required />
            </div>
          </div>

          <!-- Biographie -->
          <div class="form-group">
            <label class="form-label" for="editBio">Présentation & Biographie académique</label>
            <textarea id="editBio" name="biographie" class="form-control" placeholder="Parlez de vos passions d'études, de vos projets..."><?= htmlspecialchars($sessionBio) ?></textarea>
          </div>

          <!-- Sécurité : Nouveau mot de passe (facultatif) -->
          <div class="form-group">
            <label class="form-label" for="editPassword">Nouveau mot de passe (laisser vide pour conserver)</label>
            <input type="password" id="editPassword" name="nouveau_mot_de_passe" class="form-control" placeholder="Minimum 8 caractères" minlength="8" />
          </div>

        </div>

        <!-- Pied de la popup -->
        <div class="modal-footer-profile">
          <button type="button" class="btn btn-outline btn-sm" id="cancelEditModalBtn">Annuler</button>
          <button type="submit" class="btn btn-primary btn-sm">
            <span>Enregistrer les modifications</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          </button>
        </div>
      </form>

    </div>
  </div>
  <?php endif; ?>

  <!-- Pied de page -->
  <footer class="profile-footer">
    <p>© 2026 École Polytechnique Internationale de Yamoussoukro — EPI Tchach</p>
  </footer>

  <!-- Scripts JavaScript Desktop -->
  <script src="<?= $asset ?>/js/desktop/profile.js"></script>
</body>
</html>
