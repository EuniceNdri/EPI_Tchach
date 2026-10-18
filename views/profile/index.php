<?php
$base = isset($baseUrl) ? $baseUrl : '';
$logoFile = file_exists(ROOT_PATH . '/public/assets/images/logo.jpeg') 
    ? ($base . '/public/assets/images/logo.jpeg') 
    : ($base . '/assets/images/logo.jpeg');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Profil Étudiant — Momo Kouassi · EPI Tchach</title>
  <meta name="description" content="Profil membre de l'École Polytechnique Internationale de Yamoussoukro" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="<?= $base ?>/public/assets/css/desktop/profile.css" />
</head>
<body>
  <div class="tricolor-bar"></div>

  <!-- Barre de navigation du profil -->
  <header class="profile-header">
    <div class="profile-nav">
      <a href="<?= $base ?>/landing" class="brand-group">
        <img src="<?= htmlspecialchars($logoFile) ?>" alt="Logo EPI" class="brand-logo-img" onerror="this.src='public/assets/images/logo.jpeg'" />
        <div>
          <span class="brand-title">EPI <span class="brand-accent">Tchach</span></span>
          <div class="brand-subtitle">Espace Membre</div>
        </div>
      </a>

      <div class="nav-user-actions">
        <a href="<?= $base ?>/landing" class="btn btn-outline btn-sm">Accueil</a>
        <a href="<?= $base ?>/admin" class="btn btn-accent btn-sm" title="Basculer vers l'espace Administration">Panneau Admin</a>
        <a href="<?= $base ?>/profile/edit" class="btn btn-primary btn-sm">Modifier mon profil</a>
        <a href="<?= $base ?>/logout" class="btn btn-outline btn-sm" style="color:var(--accent); border-color:#fca5a5;">Déconnexion</a>
      </div>
    </div>
  </header>

  <main class="profile-container">
    <!-- Bannière & Infos de Profil -->
    <div class="profile-banner-card">
      <div class="profile-cover">
        <div class="profile-cover-pattern"></div>
      </div>

      <div class="profile-hero">
        <div class="hero-left">
          <div class="profile-avatar-wrapper">
            <div class="profile-avatar">MK</div>
            <div class="online-status" title="En ligne"></div>
          </div>

          <div class="profile-info">
            <div class="profile-name-row">
              <h1 class="profile-name">Momo Kouassi</h1>
              <span class="role-badge role-delegue">★ Délégué de promotion</span>
              <span class="role-badge role-etudiant">BTS IDA 2ème Année</span>
            </div>
            <p class="profile-bio">Passionné de développement web fullstack, base de données et architecture MVC. Prêt à s'entraider pour réussir les partiels et examens du BTS 2026 !</p>
            <div class="profile-meta-tags">
              <span class="meta-tag">📍 Campus Yamoussoukro</span>
              <span class="meta-tag">📧 momo.kouassi@epi-yakro.ci</span>
              <span class="meta-tag">📅 Membre depuis Octobre 2024</span>
              <span class="meta-tag">🎓 Matricule : <strong>EPI-2024-IDA-042</strong></span>
            </div>
          </div>
        </div>

        <div class="hero-right">
          <a href="<?= $base ?>/profile/edit" class="btn btn-outline">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            Éditer mes infos
          </a>
        </div>
      </div>
    </div>

    <!-- Statistiques d'activité -->
    <div class="profile-stats-grid">
      <div class="stat-box">
        <div class="stat-icon-wrapper icon-blue">💬</div>
        <div>
          <div class="stat-val">28</div>
          <div class="stat-lbl">Sujets lancés</div>
        </div>
      </div>

      <div class="stat-box">
        <div class="stat-icon-wrapper icon-green">✓</div>
        <div>
          <div class="stat-val">42</div>
          <div class="stat-lbl">Solutions apportées</div>
        </div>
      </div>

      <div class="stat-box">
        <div class="stat-icon-wrapper icon-red">❤️</div>
        <div>
          <div class="stat-val">156</div>
          <div class="stat-lbl">Réactions & Soutiens</div>
        </div>
      </div>

      <div class="stat-box">
        <div class="stat-icon-wrapper icon-amber">🏆</div>
        <div>
          <div class="stat-val">Top 3</div>
          <div class="stat-lbl">Rang promotion IDA</div>
        </div>
      </div>
    </div>

    <!-- Grille Principale (Onglets d'activité + Sidebar) -->
    <div class="profile-main-grid">
      <!-- Colonne Onglets -->
      <div class="profile-tabs-card">
        <div class="tabs-header">
          <button type="button" class="tab-btn active" data-tab="tab-posts">
            <span>Mes Publications</span>
            <span class="tab-badge">28</span>
          </button>
          <button type="button" class="tab-btn" data-tab="tab-solutions">
            <span>Solutions validées</span>
            <span class="tab-badge">42</span>
          </button>
          <button type="button" class="tab-btn" data-tab="tab-documents">
            <span>Documents partagés</span>
            <span class="tab-badge">8</span>
          </button>
          <button type="button" class="tab-btn" data-tab="tab-badges">
            <span>Badges d'Honneur</span>
            <span class="tab-badge">5</span>
          </button>
        </div>

        <!-- Onglet 1 : Publications -->
        <div id="tab-posts" class="tab-content active">
          <div class="post-list">
            <article class="post-card">
              <div class="post-top">
                <span class="post-filiere">Zone BTS IDA</span>
                <span class="post-date">Hier à 16:45</span>
              </div>
              <a href="#" class="post-heading">Comment configurer la relation One-to-Many avec Eloquent sous PHP 8.2 ?</a>
              <p class="post-desc">J'ai résumé la méthode vue avec Dr. Koné lors du TP de mardi pour ceux qui ont rencontré le bug de clé étrangère sur MySQL...</p>
              <div class="post-meta-row">
                <div class="post-tags">
                  <span class="tag-item">#laravel</span>
                  <span class="tag-item">#php</span>
                  <span class="tag-item">#mysql</span>
                </div>
                <div>💬 14 réponses · 👍 24 soutiens</div>
              </div>
            </article>

            <article class="post-card">
              <div class="post-top">
                <span class="post-filiere">Partiels & Examens</span>
                <span class="post-date">Il y a 3 jours</span>
              </div>
              <a href="#" class="post-heading">Planning prévisionnel des devoirs surveillés du 1er semestre BTS IDA</a>
              <p class="post-desc">En tant que délégué, voici les dates confirmées par la scolarité pour les épreuves d'Algorithmique, BDD et Comptabilité générale.</p>
              <div class="post-meta-row">
                <div class="post-tags">
                  <span class="tag-item">#planning</span>
                  <span class="tag-item">#bts2026</span>
                </div>
                <div>💬 31 réponses · 👍 48 soutiens</div>
              </div>
            </article>

            <article class="post-card">
              <div class="post-top">
                <span class="post-filiere">Projets de Groupe</span>
                <span class="post-date">Il y a 1 semaine</span>
              </div>
              <a href="#" class="post-heading">Recherche binôme pour le projet d'Architecture Réseaux & Télécoms</a>
              <p class="post-desc">Étudiant sérieux cherchant un partenaire pour configurer la maquette Cisco Packet Tracer avec routage inter-VLAN.</p>
              <div class="post-meta-row">
                <div class="post-tags">
                  <span class="tag-item">#cisco</span>
                  <span class="tag-item">#reseaux</span>
                </div>
                <div>💬 6 réponses · 👍 12 soutiens</div>
              </div>
            </article>
          </div>
        </div>

        <!-- Onglet 2 : Solutions -->
        <div id="tab-solutions" class="tab-content">
          <div class="post-list">
            <article class="post-card" style="border-left: 4px solid var(--success);">
              <div class="post-top">
                <span class="post-filiere" style="background:var(--success-bg); color:var(--success);">✓ Validé par le Professeur</span>
                <span class="post-date">Il y a 4 jours</span>
              </div>
              <a href="#" class="post-heading">Solution à l'erreur SQLSTATE[42S22] dans la migration Laravel</a>
              <p class="post-desc">Il fallait s'assurer d'exécuter la création de la table parents avant d'appeler <code>foreignId('user_id')->constrained()</code>. Code corrigé fourni.</p>
            </article>

            <article class="post-card" style="border-left: 4px solid var(--success);">
              <div class="post-top">
                <span class="post-filiere" style="background:var(--success-bg); color:var(--success);">✓ Solution acceptée par l'auteur</span>
                <span class="post-date">Il y a 6 jours</span>
              </div>
              <a href="#" class="post-heading">Calcul de l'amortissement linéaire en Gestion Financière</a>
              <p class="post-desc">Démonstration détaillée du calcul au prorata temporis pour le matériel acquis en cours d'exercice comptable.</p>
            </article>
          </div>
        </div>

        <!-- Onglet 3 : Documents -->
        <div id="tab-documents" class="tab-content">
          <div class="post-list">
            <div class="post-card" style="display:flex; align-items:center; justify-content:space-between;">
              <div style="display:flex; align-items:center; gap:1rem;">
                <div style="font-size:2rem;">📄</div>
                <div>
                  <h4 style="font-size:1rem; font-weight:800;">Corrigé Partiel Blanc BDD 2025.pdf</h4>
                  <p style="font-size:0.8rem; color:var(--muted-foreground);">Filière BTS IDA · Partagé le 12 Octobre · 1.4 Mo</p>
                </div>
              </div>
              <button type="button" class="btn btn-outline btn-sm">Télécharger</button>
            </div>

            <div class="post-card" style="display:flex; align-items:center; justify-content:space-between;">
              <div style="display:flex; align-items:center; gap:1rem;">
                <div style="font-size:2rem;">📊</div>
                <div>
                  <h4 style="font-size:1rem; font-weight:800;">Fiche de Synthèse Algorithmique et Arbres Binaires.pdf</h4>
                  <p style="font-size:0.8rem; color:var(--muted-foreground);">Filière BTS IDA & Licence · Partagé le 8 Octobre · 850 Ko</p>
                </div>
              </div>
              <button type="button" class="btn btn-outline btn-sm">Télécharger</button>
            </div>
          </div>
        </div>

        <!-- Onglet 4 : Badges -->
        <div id="tab-badges" class="tab-content">
          <div class="badges-grid">
            <div class="academic-badge-card">
              <div class="badge-icon-lg">🥇</div>
              <div class="badge-title">Entraideur d'Or</div>
              <p class="badge-desc">Plus de 40 solutions validées par les étudiants et enseignants de l'EPI.</p>
            </div>

            <div class="academic-badge-card">
              <div class="badge-icon-lg">🎖️</div>
              <div class="badge-title">Délégué Engagé</div>
              <p class="badge-desc">Rôle officiel de relais d'information pour la promotion BTS IDA 2.</p>
            </div>

            <div class="academic-badge-card">
              <div class="badge-icon-lg">⚡</div>
              <div class="badge-title">Réponse Éclair</div>
              <p class="badge-desc">Temps de réponse moyen inférieur à 15 minutes sur les sujets de cours.</p>
            </div>

            <div class="academic-badge-card">
              <div class="badge-icon-lg">📚</div>
              <div class="badge-title">Bibliothécaire</div>
              <p class="badge-desc">A partagé plus de 5 annales et fiches de révision certifiées.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Colonne Latérale -->
      <aside class="profile-sidebar">
        <!-- Informations Académiques -->
        <div class="sidebar-card">
          <h3 class="sidebar-heading">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6"/><path d="M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
            Cursus à l'EPI
          </h3>
          <ul class="academic-info-list">
            <li class="info-item">
              <span class="info-label">Établissement</span>
              <span class="info-value">EPI Yamoussoukro</span>
            </li>
            <li class="info-item">
              <span class="info-label">Département</span>
              <span class="info-value">Sciences & Technologies</span>
            </li>
            <li class="info-item">
              <span class="info-label">Filière Actuelle</span>
              <span class="info-value">BTS IDA (Informatique Développeur d'Applications)</span>
            </li>
            <li class="info-item">
              <span class="info-label">Niveau d'étude</span>
              <span class="info-value">Deuxième Année (IDA 2)</span>
            </li>
            <li class="info-item">
              <span class="info-label">Délégué Référent</span>
              <span class="info-value">Dr. Ibrahim Koné</span>
            </li>
          </ul>
        </div>

        <!-- Salons Favoris -->
        <div class="sidebar-card">
          <h3 class="sidebar-heading">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
            Salons Favoris
          </h3>
          <ul class="academic-info-list">
            <li class="info-item">
              <a href="#" style="text-decoration:none; color:var(--primary); font-weight:700;"># BTS-IDA-2026</a>
              <span style="font-size:0.8rem; color:var(--muted-foreground);">124 étudiants actifs</span>
            </li>
            <li class="info-item">
              <a href="#" style="text-decoration:none; color:var(--primary); font-weight:700;"># Base-de-Données-Avancee</a>
              <span style="font-size:0.8rem; color:var(--muted-foreground);">Avec Dr. Koné</span>
            </li>
            <li class="info-item">
              <a href="#" style="text-decoration:none; color:var(--primary); font-weight:700;"># Entraide-Annales-BTS</a>
              <span style="font-size:0.8rem; color:var(--muted-foreground);">Banque d'exercices corrigés</span>
            </li>
          </ul>
        </div>
      </aside>
    </div>
  </main>

  <footer class="profile-footer">
    <p>© 2026 École Polytechnique Internationale de Yamoussoukro — EPI Tchach</p>
  </footer>

  <script src="<?= $base ?>/public/assets/js/desktop/profile.js"></script>
</body>
</html>
