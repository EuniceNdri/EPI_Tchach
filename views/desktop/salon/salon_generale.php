<?php
$base = isset($baseUrl) ? $baseUrl : '';
$asset = isset($assetBase) ? $assetBase : (isset($baseUrl) ? $baseUrl . '/public/assets' : '/public/assets');
$logoFile = $asset . '/images/logo.jpeg';
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
<body>
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

      <nav class="nav-menu">
        <a href="#" class="nav-item active">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          <span>Accueil</span>
        </a>
        <a href="#" class="nav-item">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
          <span>Catégories</span>
        </a>
        <a href="#" class="nav-item">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>
          <span>Populaires</span>
        </a>
        <a href="#" class="nav-item">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
          <span>Sauvegardés</span>
        </a>
      </nav>

      <div>
        <div class="section-title">Zones / Catégories</div>
        <div class="category-list">
          <a href="#" class="category-item">
            <div class="icon" style="background:#ede9fe; color:#7c3aed;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3zm-8.27 4a2 2 0 0 1-3.46 0"/></svg>
            </div>
            <div>
              <div>Annonces Officielles</div>
            </div>
          </a>
          <a href="#" class="category-item">
            <div class="icon" style="background:#dbeafe; color:#1d4ed8;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="m9 12 2 2 4-4"/></svg>
            </div>
            <div>
              <div>BTS IDA – Cours & TP</div>
            </div>
            <span class="cat-count">12.4k</span>
          </a>
          <a href="#" class="category-item">
            <div class="icon" style="background:#d1fae5; color:#047857;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 2v7.31L4 18h16l-6-8.69V2z"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
            </div>
            <div>
              <div>Projets de Classe</div>
            </div>
          </a>
          <a href="#" class="category-item">
            <div class="icon" style="background:#fef3c7; color:#b45309;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/></svg>
            </div>
            <div>
              <div>Examens & Révisions</div>
            </div>
          </a>
          <a href="#" class="category-item">
            <div class="icon" style="background:#fce7f3; color:#db2777;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
            </div>
            <div>
              <div>Licence Informatique</div>
            </div>
          </a>
          <a href="#" class="category-item">
            <div class="icon" style="background:#e0e7ff; color:#4338ca;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div>
              <div>Bureau de la Délégation</div>
            </div>
          </a>
        </div>
        <a href="#" class="btn-browse">Parcourir toutes les catégories →</a>
      </div>

      <div style="margin-top:auto; padding-top:1rem; border-top:1px solid var(--border);">
        <?php if (!empty($_SESSION['user_id'])): ?>
          <div style="font-size:0.85rem; font-weight:700; color:var(--text); margin-bottom:0.5rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
            👤 <?= htmlspecialchars(($_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? 'Membre')) ?>
          </div>
          <div style="display:flex; flex-direction:column; gap:0.25rem;">
            <a href="<?= $base ?>/profile" class="nav-item" style="padding:6px 8px; font-size:0.85rem;">Mon Profil</a>
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
        <input type="text" class="search-input" placeholder="Rechercher des discussions, tags ou utilisateurs...">
      </div>

      <div class="tabs">
        <button class="tab active">Récentes</button>
        <button class="tab">Top</button>
        <button class="tab">Suivies</button>
        <button class="tab" style="margin-left:auto;">Filtrer ▾</button>
      </div>

      <div class="posts">

        <!-- Publication 1 (Annonce épinglée) -->
        <article class="post-card">
          <div style="display:flex; gap:16px;">
            <div class="votes">
              <div class="vote-count">128</div>
              <div class="vote-label">votes</div>
            </div>
            <div style="flex:1;">
              <div class="post-header">
                <div class="avatar">AK</div>
                <div class="author-info">
                  <div class="author-name">Admin Système <span class="badge badge-pro">ADMIN</span></div>
                  <div class="author-meta">Annonces Officielles · il y a 2 h</div>
                </div>
              </div>
              <h2 class="post-title">Rentrée 2026 – Informations importantes</h2>
              <p class="post-excerpt">Chers étudiants, la rentrée officielle est fixée au 5 octobre 2026. Merci de consulter régulièrement cet espace pour les mises à jour.</p>
              <div class="post-footer">
                <div class="tags">
                  <span class="tag">annonce</span>
                  <span class="tag">rentrée</span>
                </div>
                <div class="stats">
                  <span class="stat">💬 12</span>
                  <span class="stat">📌 Épinglé</span>
                </div>
              </div>
            </div>
          </div>
        </article>

        <!-- Publication 2 -->
        <article class="post-card">
          <div style="display:flex; gap:16px;">
            <div class="votes">
              <div class="vote-count">96</div>
              <div class="vote-label">votes</div>
            </div>
            <div style="flex:1;">
              <div class="post-header">
                <div class="avatar">MK</div>
                <div class="author-info">
                  <div class="author-name">Momo Kouassi <span class="badge badge-dev">ÉTUDIANT</span></div>
                  <div class="author-meta">BTS IDA · Cours & TP · il y a 5 h</div>
                </div>
              </div>
              <h2 class="post-title">Erreur SQL dans mon TP Laravel</h2>
              <p class="post-excerpt">Bonjour, j’ai une erreur "Column not found" sur une jointure. Quelqu’un peut m’aider ?</p>
              <div class="post-footer">
                <div class="tags">
                  <span class="tag">laravel</span>
                  <span class="tag">php</span>
                  <span class="tag">mysql</span>
                  <span class="tag">debug</span>
                </div>
                <div class="stats">
                  <span class="stat">💬 3</span>
                  <span class="stat">✅ Résolu</span>
                </div>
              </div>
            </div>
          </div>
        </article>

        <!-- Publication 3 -->
        <article class="post-card">
          <div style="display:flex; gap:16px;">
            <div class="votes">
              <div class="vote-count">74</div>
              <div class="vote-label">votes</div>
            </div>
            <div style="flex:1;">
              <div class="post-header">
                <div class="avatar">ST</div>
                <div class="author-info">
                  <div class="author-name">Sarah Traoré</div>
                  <div class="author-meta">BTS IDA · Cours & TP · il y a 1 j</div>
                </div>
              </div>
              <h2 class="post-title">Comment faire une API REST avec Laravel ?</h2>
              <p class="post-excerpt">Quelles sont les bonnes pratiques pour les routes et les controllers ?</p>
              <div class="post-footer">
                <div class="tags">
                  <span class="tag">laravel</span>
                  <span class="tag">api</span>
                </div>
                <div class="stats">
                  <span class="stat">💬 2</span>
                  <span class="stat">✅ Résolu</span>
                </div>
              </div>
            </div>
          </div>
        </article>

        <!-- Publication 4 -->
        <article class="post-card">
          <div style="display:flex; gap:16px;">
            <div class="votes">
              <div class="vote-count">53</div>
              <div class="vote-label">votes</div>
            </div>
            <div style="flex:1;">
              <div class="post-header">
                <div class="avatar">AN</div>
                <div class="author-info">
                  <div class="author-name">Awa N'Guessan <span class="badge badge-mentor">CHEF DE CLASSE</span></div>
                  <div class="author-meta">BTS IDA · Examens & Révisions · il y a 2 j</div>
                </div>
              </div>
              <h2 class="post-title">Annales de l’examen de Base de données 2025</h2>
              <p class="post-excerpt">Voici les sujets de l’année dernière. Bonne révision !</p>
              <div class="post-footer">
                <div class="tags">
                  <span class="tag">mysql</span>
                  <span class="tag">examen</span>
                </div>
                <div class="stats">
                  <span class="stat">💬 2</span>
                  <span class="stat">📎 1 fichier</span>
                </div>
              </div>
            </div>
          </div>
        </article>

        <!-- Publication 5 -->
        <article class="post-card">
          <div style="display:flex; gap:16px;">
            <div class="votes">
              <div class="vote-count">41</div>
              <div class="vote-label">votes</div>
            </div>
            <div style="flex:1;">
              <div class="post-header">
                <div class="avatar">KY</div>
                <div class="author-info">
                  <div class="author-name">Kofi Yao <span class="badge badge-mentor">DÉLÉGUÉ</span></div>
                  <div class="author-meta">Bureau de la Délégation · il y a 3 j</div>
                </div>
              </div>
              <h2 class="post-title">Réunion du Bureau de la Délégation – 20 octobre</h2>
              <p class="post-excerpt">Ordre du jour : point sur les filières et organisation de la journée portes ouvertes.</p>
              <div class="post-footer">
                <div class="tags">
                  <span class="tag">délégation</span>
                  <span class="tag">réunion</span>
                </div>
                <div class="stats">
                  <span class="stat">💬 0</span>
                  <span class="stat">📌 Épinglé</span>
                </div>
              </div>
            </div>
          </div>
        </article>

      </div>
    </main>

    <!-- ========== SIDEBAR DROITE ========== -->
    <aside class="sidebar-right">
      <div>
        <div class="widget-title">
          <span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle; margin-right:4px; color:var(--accent);"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
            Tendances
          </span>
          <a href="#">Voir tout</a>
        </div>
        <div class="trending-item">
          <div class="trending-rank">1</div>
          <div class="trending-info">
            <h4>Laravel & API REST</h4>
            <span>128 posts</span>
          </div>
        </div>
        <div class="trending-item">
          <div class="trending-rank">2</div>
          <div class="trending-info">
            <h4>Examens BTS IDA</h4>
            <span>96 posts</span>
          </div>
        </div>
        <div class="trending-item">
          <div class="trending-rank">3</div>
          <div class="trending-info">
            <h4>Projets tuteurés</h4>
            <span>84 posts</span>
          </div>
        </div>
        <div class="trending-item">
          <div class="trending-rank">4</div>
          <div class="trending-info">
            <h4>Base de données</h4>
            <span>67 posts</span>
          </div>
        </div>
        <div class="trending-item">
          <div class="trending-rank">5</div>
          <div class="trending-info">
            <h4>Vie étudiante</h4>
            <span>53 posts</span>
          </div>
        </div>
      </div>

      <div>
        <div class="widget-title">
          <span>
            <span style="display:inline-block; width:8px; height:8px; background:var(--success); border-radius:50%; margin-right:6px;"></span>
            En ligne (24)
          </span>
          <a href="#">Voir tout</a>
        </div>
        <div class="online-user">
          <div class="online-avatar">SC<span class="online-dot"></span></div>
          <div>
            <div class="online-name">Sarah Chen</div>
            <div class="online-role">Product Designer</div>
          </div>
        </div>
        <div class="online-user">
          <div class="online-avatar">MR<span class="online-dot"></span></div>
          <div>
            <div class="online-name">Marcus Rodriguez</div>
            <div class="online-role">FrontendEngineer</div>
          </div>
        </div>
        <div class="online-user">
          <div class="online-avatar">EP<span class="online-dot"></span></div>
          <div>
            <div class="online-name">Emily Park</div>
            <div class="online-role">UX Researcher</div>
          </div>
        </div>
        <div class="online-user">
          <div class="online-avatar">JM<span class="online-dot"></span></div>
          <div>
            <div class="online-name">Jordan Miles</div>
            <div class="online-role">Software Engineer</div>
          </div>
        </div>
        <div class="online-user">
          <div class="online-avatar">PS<span class="online-dot"></span></div>
          <div>
            <div class="online-name">Priya Sharma</div>
            <div class="online-role">Community Manager</div>
          </div>
        </div>
      </div>

      <button class="btn-new-post">
        + Nouvelle publication
      </button>
    </aside>

  </div>
</body>
</html>