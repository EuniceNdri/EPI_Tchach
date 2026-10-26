<?php
$base = isset($baseUrl) ? $baseUrl : '';
$asset = isset($assetBase) ? $assetBase : (isset($baseUrl) ? $baseUrl . '/public/assets' : '/public/assets');
$logoFile = $asset . '/images/logo.jpeg';

// Récupération des informations du profil (utilisateur consulté ou session courante)
$sessionNom = !empty($user['nom']) ? $user['nom'] : ($_SESSION['user_nom'] ?? 'Kouassi');
$sessionPrenom = !empty($user['prenom']) ? $user['prenom'] : ($_SESSION['user_prenom'] ?? 'Momo');
$sessionFullName = trim($sessionPrenom . ' ' . $sessionNom);
$sessionEmail = !empty($user['email']) ? $user['email'] : ($_SESSION['user_email'] ?? 'momo.kouassi@epi-yakro.ci');
$sessionRole = !empty($user['role']) ? $user['role'] : ($_SESSION['user_role'] ?? 'etudiant');
$sessionBio = !empty($user['biographie']) ? $user['biographie'] : ($_SESSION['user_bio'] ?? "Passionné de développement web fullstack, base de données et architecture MVC. Prêt à s'entraider pour réussir les partiels et examens à l'EPI !");
$sessionFiliere = !empty($user['filiere']) ? $user['filiere'] : ($_SESSION['user_filiere'] ?? 'BTS IDA (Informatique Développeur)');
$sessionModules = !empty($user['modules_enseignes']) ? $user['modules_enseignes'] : ($_SESSION['user_modules'] ?? '');
$initials = strtoupper(mb_substr($sessionPrenom, 0, 1) . mb_substr($sessionNom, 0, 1));
$isOwnProfile = $isOwnProfile ?? (isset($_SESSION['user_id']) && (!isset($user['id']) || $_SESSION['user_id'] == $user['id']));
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
  <!-- Bande tricolore décorative officielle -->
  <div class="tricolor-bar"></div>

  <!-- Barre de navigation supérieure -->
  <header class="profile-header">
    <div class="profile-nav">
      <a href="<?= $base ?>/landing" class="brand-group">
        <img src="<?= htmlspecialchars($logoFile) ?>" alt="Logo EPI" class="brand-logo-img" onerror="this.src='<?= $asset ?>/images/logo.jpeg'" />
        <div>
          <span class="brand-title">EPI <span class="brand-accent">Tchach</span></span>
          <div class="brand-subtitle">Espace Membre</div>
        </div>
      </a>

      <div class="nav-user-actions">
        <a href="<?= $base ?>/landing" class="btn btn-outline btn-sm">Accueil Forum</a>
        <?php if ($sessionRole === 'administrateur'): ?>
          <a href="<?= $base ?>/admin" class="btn btn-accent btn-sm" title="Panneau d'administration">Panneau Admin</a>
        <?php endif; ?>
        <button type="button" class="btn btn-primary btn-sm open-profile-edit-modal">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
          <span>Modifier mon profil</span>
        </button>
        <a href="<?= $base ?>/logout" class="btn btn-outline btn-sm" style="color:var(--accent); border-color:#fca5a5;">Déconnexion</a>
        <a href="<?= $base ?>/salon" class="btn btn-outline btn-sm" style="color:var(--primary); border-color:#93c5fd;">Salon Général</a>
      </div>
    </div>
  </header>

  <!-- CONTENEUR GLOBAL AVEC LAYOUT SIDEBAR + PAGE PRINCIPALE -->
  <div class="profile-page-wrapper">
    <div class="profile-layout-grid">

      <!-- ========================================================
           1. SIDEBAR DU PROFIL (Fixée à gauche)
           ======================================================== -->
      <aside class="profile-sidebar-card">
        <div class="sidebar-profile-hero">
          <div class="sidebar-avatar-wrapper">
            <div class="sidebar-avatar profile-dynamic-initials"><?= htmlspecialchars($initials) ?></div>
            <div class="sidebar-online-dot" title="En ligne sur EPI Tchach"></div>
          </div>
          <h2 class="sidebar-user-name profile-dynamic-name"><?= htmlspecialchars($sessionFullName) ?></h2>
          <span class="sidebar-role-badge">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            <span><?= htmlspecialchars(ucfirst($sessionRole)) ?></span>
          </span>
<?php 
$sessionFiliere = $_SESSION['user_filiere'] ?? ($user['filiere'] ?? null);
$sessionModules = $_SESSION['user_modules'] ?? ($user['modules_enseignes'] ?? null);
?>
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

          <p class="sidebar-bio-text profile-dynamic-bio"><?= htmlspecialchars($sessionBio) ?></p>

          <!-- Bouton Popup Modifier Mon Profil -->
          <button type="button" class="btn-sidebar-edit open-profile-edit-modal">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            <span>Modifier mon profil</span>
          </button>
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
          <li class="sidebar-meta-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
            <span>Campus Yamoussoukro</span>
          </li>
          <li class="sidebar-meta-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span>Membre actif 2026</span>
          </li>
        </ul>
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
              Bienvenue dans votre espace, <span class="profile-dynamic-name" style="color:var(--primary);"><?= htmlspecialchars($sessionFullName) ?></span> !
            </h1>
            <p style="color:var(--muted-foreground); margin-top:0.35rem; font-size:0.95rem;">
              Consultez vos échanges académiques, accédez à vos filières et posez vos questions en direct aux étudiants et professeurs.
            </p>
          </div>
        </div>

        <!-- 4 Cartes KPIs Statistiques -->
        <div class="profile-stats-grid">
          <div class="stat-box">
            <div class="stat-icon-wrapper icon-blue">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            </div>
            <div>
              <div class="stat-val" id="statSubjectsCount">28</div>
              <div class="stat-lbl">Sujets lancés</div>
            </div>
          </div>
          <div class="stat-box">
            <div class="stat-icon-wrapper icon-green">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/><path d="M9 18h6"/><path d="M10 22h4"/></svg>
            </div>
            <div>
              <div class="stat-val">142</div>
              <div class="stat-lbl">Réponses apportées</div>
            </div>
          </div>
          <div class="stat-box">
            <div class="stat-icon-wrapper icon-amber">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"/></svg>
            </div>
            <div>
              <div class="stat-val">19</div>
              <div class="stat-lbl">Documents & TP</div>
            </div>
          </div>
          <div class="stat-box">
            <div class="stat-icon-wrapper icon-purple">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            </div>
            <div>
              <div class="stat-val">98%</div>
              <div class="stat-lbl">Avis positifs</div>
            </div>
          </div>
        </div>

        <!-- Boîte de Publication Rapide (AJAX sans rechargement de page) -->
        <section class="quick-post-card">
          <form id="quickPostForm">
            <div class="quick-post-header">
              <div class="post-avatar-mini profile-dynamic-initials"><?= htmlspecialchars($initials) ?></div>
              <strong style="font-size:0.95rem;">Poser une question ou lancer une discussion</strong>
            </div>
            <input type="text" name="titre" class="quick-post-title-input" placeholder="Titre de votre question ou sujet (ex: Aide sur le TP de Réseaux)..." required />
            <textarea name="contenu" class="quick-post-textarea" placeholder="Expliquez votre problème ou détaillez votre partage ici pour les membres de l'école..." required></textarea>
            <div class="quick-post-footer">
              <select name="tag" class="post-tag-select">
                <option value="BTS-IDA"># BTS-IDA</option>
                <option value="Licence-Info"># Licence-Info</option>
                <option value="Génie-Civil"># Génie-Civil</option>
                <option value="Finance"># Finance</option>
                <option value="Entraide"># Entraide Générale</option>
              </select>
              <button type="submit" class="btn btn-primary btn-sm">
                <span>Publier le sujet</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
              </button>
            </div>
          </form>
        </section>

        <!-- CONTENUS DES ONGLETS -->
        <div class="profile-tabs-card" style="padding: 1.75rem;">

          <!-- ONGLET 1 : FLUX & PUBLICATIONS -->
          <div class="tab-content active" id="tab-posts">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem; border-bottom:1px solid var(--border); padding-bottom:1rem;">
              <h3 style="font-size:1.15rem; font-weight:800;">Dernières publications sur le forum</h3>
              <span style="font-size:0.85rem; color:var(--muted-foreground);">Trié par récence</span>
            </div>

            <div class="posts-feed" id="postsFeedList">
              <!-- Post 1 -->
              <article class="post-card">
                <div class="post-header-flex">
                  <div class="post-author-box">
                    <div class="post-avatar-mini profile-dynamic-initials"><?= htmlspecialchars($initials) ?></div>
                    <div>
                      <div class="post-author-name profile-dynamic-name"><?= htmlspecialchars($sessionFullName) ?></div>
                      <div class="post-time">Il y a 2 heures · Promotion BTS IDA 2026</div>
                    </div>
                  </div>
                  <span class="post-tag-pill"># PHP-MVC</span>
                </div>
                <h4 class="post-title">Comment configurer la relation One-to-Many avec PDO sous PHP 8.2 ?</h4>
                <p class="post-excerpt">Je partage un exemple complet pour lier les étudiants à leurs départements avec vérification de clés étrangères et intégrité référentielle.</p>
                <div class="post-footer-actions">
                  <button type="button" class="btn-post-action">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/></svg>
                    <span>14 Utile</span>
                  </button>
                  <button type="button" class="btn-post-action">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <span>8 Réponses</span>
                  </button>
                </div>
              </article>

              <!-- Post 2 -->
              <article class="post-card">
                <div class="post-header-flex">
                  <div class="post-author-box">
                    <div class="post-avatar-mini" style="background:#0284c7;">BK</div>
                    <div>
                      <div class="post-author-name">Brou Koffi (Enseignant)</div>
                      <div class="post-time">Hier à 14:30 · Pôle Informatique</div>
                    </div>
                  </div>
                  <span class="post-tag-pill" style="background:rgba(2,132,199,0.1); color:#0284c7;"># Annonce-Officielle</span>
                </div>
                <h4 class="post-title">Planning prévisionnel des devoirs surveillés du 1er semestre</h4>
                <p class="post-excerpt">Les épreuves sur table auront lieu du 12 au 17 novembre. Merci de vérifier vos salles sur le panneau d'affichage du campus.</p>
                <div class="post-footer-actions">
                  <button type="button" class="btn-post-action">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/></svg>
                    <span>42 Utile</span>
                  </button>
                  <button type="button" class="btn-post-action">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <span>19 Réponses</span>
                  </button>
                </div>
              </article>
            </div>
          </div>

          <!-- ONGLET 2 : SALONS & FILIÈRES -->
          <div class="tab-content" id="tab-channels">
            <h3 style="font-size:1.15rem; font-weight:800; margin-bottom:1.25rem;">Mes Salons de Discussion Rejoints</h3>
            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:1.25rem;">
              <div style="border:1.5px solid var(--border); border-radius:var(--radius-md); padding:1.25rem;">
                <span class="post-tag-pill">Pôle Tech</span>
                <h4 style="font-weight:800; margin:0.75rem 0 0.35rem;">BTS IDA 2ème Année</h4>
                <p style="font-size:0.85rem; color:var(--muted-foreground); margin-bottom:1rem;">Algorithmique, Base de données, Réseaux et Projets de fin d'études.</p>
                <div style="display:flex; align-items:center; gap:0.4rem; font-size:0.8rem; font-weight:700; color:var(--success);">
                  <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:currentColor;"></span>
                  <span>38 membres connectés</span>
                </div>
              </div>
              <div style="border:1.5px solid var(--border); border-radius:var(--radius-md); padding:1.25rem;">
                <span class="post-tag-pill" style="background:#e0f2fe; color:#0369a1;">Pôle Ingénierie</span>
                <h4 style="font-weight:800; margin:0.75rem 0 0.35rem;">Génie Civil & BTP</h4>
                <p style="font-size:0.85rem; color:var(--muted-foreground); margin-bottom:1rem;">Topographie, résistance des matériaux et plans AutoCAD.</p>
                <div style="display:flex; align-items:center; gap:0.4rem; font-size:0.8rem; font-weight:700; color:var(--success);">
                  <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:currentColor;"></span>
                  <span>21 membres connectés</span>
                </div>
              </div>
              <div style="border:1.5px solid var(--border); border-radius:var(--radius-md); padding:1.25rem;">
                <span class="post-tag-pill" style="background:#fef3c7; color:#b45309;">Pôle Économie</span>
                <h4 style="font-weight:800; margin:0.75rem 0 0.35rem;">Finance & Comptabilité</h4>
                <p style="font-size:0.85rem; color:var(--muted-foreground); margin-bottom:1rem;">Gestion financière, fiscalité ivoirienne et contrôle de gestion.</p>
                <div style="display:flex; align-items:center; gap:0.4rem; font-size:0.8rem; font-weight:700; color:var(--success);">
                  <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:currentColor;"></span>
                  <span>15 membres connectés</span>
                </div>
              </div>
            </div>
          </div>

          <!-- ONGLET 3 : DOCUMENTS PARTAGÉS -->
          <div class="tab-content" id="tab-docs">
            <h3 style="font-size:1.15rem; font-weight:800; margin-bottom:1.25rem;">Fichiers & Annales Déposés</h3>
            <div style="display:flex; flex-direction:column; gap:1rem;">
              <div style="display:flex; align-items:center; justify-content:space-between; border:1px solid var(--border); border-radius:var(--radius-sm); padding:1rem 1.25rem;">
                <div style="display:flex; align-items:center; gap:1rem;">
                  <div style="width:40px; height:40px; border-radius:8px; background:rgba(30, 58, 138, 0.08); display:flex; align-items:center; justify-content:center; color:var(--primary);">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                  </div>
                  <div>
                    <strong style="font-size:0.95rem;">Annales_Maths_BTS_2025_Corrige.pdf</strong>
                    <div style="font-size:0.8rem; color:var(--muted-foreground);">PDF · 2.4 Mo · 142 téléchargements</div>
                  </div>
                </div>
                <button type="button" class="btn btn-outline btn-sm" onclick="alert('Téléchargement du document...');">Télécharger</button>
              </div>
              <div style="display:flex; align-items:center; justify-content:space-between; border:1px solid var(--border); border-radius:var(--radius-sm); padding:1rem 1.25rem;">
                <div style="display:flex; align-items:center; gap:1rem;">
                  <div style="width:40px; height:40px; border-radius:8px; background:rgba(234, 88, 12, 0.08); display:flex; align-items:center; justify-content:center; color:var(--accent);">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                  </div>
                  <div>
                    <strong style="font-size:0.95rem;">TP_Reseaux_Configuration_Cisco_PacketTracer.zip</strong>
                    <div style="font-size:0.8rem; color:var(--muted-foreground);">ZIP · 5.8 Mo · 89 téléchargements</div>
                  </div>
                </div>
                <button type="button" class="btn btn-outline btn-sm" onclick="alert('Téléchargement du document...');">Télécharger</button>
              </div>
            </div>
          </div>

          <!-- ONGLET 4 : À PROPOS -->
          <div class="tab-content" id="tab-about">
            <h3 style="font-size:1.15rem; font-weight:800; margin-bottom:1.25rem;">Informations & Biographie Complète</h3>
            <div style="display:flex; flex-direction:column; gap:1.25rem;">
              <div>
                <span class="info-label">Biographie Personnelle</span>
                <p class="profile-dynamic-bio" style="font-size:0.95rem; line-height:1.6; margin-top:0.35rem; color:var(--foreground);">
                  <?= htmlspecialchars($sessionBio) ?>
                </p>
              </div>
              <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; border-top:1px solid var(--border); padding-top:1.25rem;">
                <div>
                  <span class="info-label">Établissement</span>
                  <div class="info-value">École Polytechnique Internationale (EPI)</div>
                </div>
                <div>
                  <span class="info-label">Localisation</span>
                  <div class="info-value">Yamoussoukro, Quartier Millionnaire</div>
                </div>
                <div>
                  <span class="info-label">Rôle Officiel</span>
                  <div class="info-value"><?= htmlspecialchars(ucfirst($sessionRole)) ?></div>
                </div>
                <div>
                  <span class="info-label">Email vérifié</span>
                  <div class="info-value profile-dynamic-email"><?= htmlspecialchars($sessionEmail) ?></div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </main>

    </div>
  </div>

  <!-- ============================================================
       3. MODALE POPUP D'ÉDITION DU PROFIL (Sans rechargement de page)
       ============================================================ -->
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

          <!-- Aperçu Photo -->
          <div style="display:flex; align-items:center; gap:1.25rem; padding-bottom:1rem; border-bottom:1px solid var(--border);">
            <div class="sidebar-avatar" style="width:64px; height:64px; font-size:1.5rem;" id="modalAvatarInitials">
              <?= htmlspecialchars($initials) ?>
            </div>
            <img id="modalAvatarPreview" style="display:none; width:64px; height:64px; border-radius:50%; object-fit:cover;" alt="Avatar" />
            <div>
              <strong style="font-size:0.95rem;">Photo de profil</strong>
              <div style="font-size:0.8rem; color:var(--muted-foreground); margin-top:0.2rem;">PNG, JPG jusqu'à 2 Mo</div>
              <label for="modalAvatarUpload" class="btn btn-outline btn-sm" style="margin-top:0.5rem; cursor:pointer; display:inline-block;">
                <span>Sélectionner une image</span>
                <input type="file" id="modalAvatarUpload" name="avatar" accept="image/*" style="display:none;" />
              </label>
            </div>
          </div>

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

  <!-- Pied de page -->
  <footer class="profile-footer">
    <p>© 2026 École Polytechnique Internationale de Yamoussoukro — EPI Tchach</p>
  </footer>

  <!-- Scripts JavaScript Desktop -->
  <script src="<?= $asset ?>/js/desktop/profile.js"></script>
</body>
</html>
