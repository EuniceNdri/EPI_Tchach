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
  <title>EPI Tchach — Plateforme Officielle d'Échange & d'Entraide de l'EPI</title>
  <meta name="description" content="Plateforme officielle de discussion, d'entraide et de partage pour les étudiants, délégués et professeurs de l'École Polytechnique Internationale de Yamoussoukro." />
  
  <!-- Polices Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
  
  <!-- Feuilles de style Desktop Landing -->
  <link rel="stylesheet" href="<?= $base ?>/public/assets/css/desktop/landing.css" />
  <link rel="stylesheet" href="<?= $base ?>/assets/css/desktop/landing.css" />
</head>
<body>
  <!-- Bande tricolore décorative -->
  <div class="tricolor-bar"></div>

  <!-- ===== Barre de navigation fixe (Widescreen) ===== -->
  <header class="header-wrapper">
    <nav class="nav container-fluid animate-fade-down">
      <a href="<?= $base ?>/landing" class="brand">
        <div class="brand-logo-wrapper">
          <img src="<?= htmlspecialchars($logoFile) ?>" 
               alt="Logo École Polytechnique Internationale Yamoussoukro" 
               class="brand-logo-img" 
               onerror="this.src='public/assets/images/logo.jpeg'" />
        </div>
        <div class="brand-text">
          <span class="brand-title">EPI <span class="brand-accent">Tchach</span></span>
          <span class="brand-subtitle">Yamoussoukro</span>
        </div>
      </a>

      <!-- Liens de navigation internes -->
      <ul class="nav-links">
        <li><a href="#apercu" class="nav-link">Aperçu</a></li>
        <li><a href="#filieres" class="nav-link">Filières</a></li>
        <li><a href="#comment-ca-marche" class="nav-link">Fonctionnement</a></li>
        <li><a href="#temoignages" class="nav-link">Témoignages</a></li>
        <li><a href="#faq" class="nav-link">FAQ</a></li>
      </ul>

      <div class="nav-actions">
        <a href="<?= $base ?>/login" class="btn btn-outline btn-sm">Se connecter</a>
        <a href="<?= $base ?>/register" class="btn btn-primary btn-sm">S'inscrire</a>
      </div>
    </nav>
  </header>

  <main>
    <!-- ===== 1. Section Héros (Plein Écran Widescreen) ===== -->
    <section class="hero-section">
      <div class="blob blob-left"></div>
      <div class="blob blob-right"></div>

      <div class="container-fluid">
        <div class="hero-grid">
          <!-- Colonne texte -->
          <div class="hero-text animate-fade-up">
            <div class="badge-wrapper">
              <span class="badge">École Polytechnique Internationale</span>
              <span class="badge badge-live">
                <span class="pulse-dot"></span>
                En direct de Yamoussoukro
              </span>
            </div>

            <h1>Toute l'école, <span class="text-gradient">une seule conversation.</span></h1>
            <p class="lead">Étudiants de BTS, Licence et Master, professeurs et délégués : échangez vos cours, résolvez vos exercices et restez informés en temps réel au sein de l'EPI.</p>
            
            <div class="hero-actions">
              <a href="<?= $base ?>/register" class="btn btn-accent group">
                Commencer gratuitement
                <svg class="icon arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                </svg>
              </a>
              <a href="#apercu" class="btn btn-outline">Explorer le forum</a>
            </div>

            <!-- Tags rapides -->
            <div class="hero-tags">
              <span class="tag-label">Salons actifs :</span>
              <a href="#filieres" class="hero-tag">#BTS-IDA</a>
              <a href="#filieres" class="hero-tag">#Licence-Info</a>
              <a href="#filieres" class="hero-tag">#Génie-Civil</a>
              <a href="#filieres" class="hero-tag">#Finance</a>
              <a href="#filieres" class="hero-tag">#Annales-2025</a>
            </div>
          </div>

          <!-- Colonne bulles interactives -->
          <div class="hero-bubbles">
            <div class="bubble bubble-primary animate-pop" style="animation-delay: 0.25s">
              <div class="bubble-header">
                <span class="bubble-who">Dr. Koné · Professeur</span>
                <span class="bubble-time">Il y a 5 min</span>
              </div>
              <p class="bubble-msg">Rappel : mise en ligne des sujets d'annales de Base de Données 📚</p>
            </div>

            <div class="bubble bubble-card animate-pop shifted" style="animation-delay: 0.55s">
              <div class="bubble-header">
                <span class="bubble-who">Momo · BTS IDA</span>
                <span class="bubble-time">Il y a 2 min</span>
              </div>
              <p class="bubble-msg">Quelqu'un a compris l'erreur SQL sur le TP de migration ? 💡</p>
            </div>

            <div class="bubble bubble-accent animate-pop" style="animation-delay: 0.85s">
              <div class="bubble-header">
                <span class="bubble-who">Sarah · BTS IDA</span>
                <span class="bubble-time">À l'instant</span>
              </div>
              <p class="bubble-msg">Oui ! Viens dans le salon BTS IDA, je t'ai partagé la solution 🙌</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== 2. Bandeau Statistiques Chiffrées ===== -->
    <section class="stats-banner">
      <div class="container-fluid">
        <div class="stats-grid">
          <div class="stat-card reveal" style="--delay: 0s">
            <div class="stat-number" data-target="1250" data-prefix="+">+1 250</div>
            <div class="stat-label">Membres Actifs</div>
            <div class="stat-desc">Étudiants, professeurs & délégués</div>
          </div>

          <div class="stat-card reveal" style="--delay: 0.15s">
            <div class="stat-number" data-target="12">12</div>
            <div class="stat-label">Filières Officielles</div>
            <div class="stat-desc">Pôles Industriels & Tertiaires</div>
          </div>

          <div class="stat-card reveal" style="--delay: 0.3s">
            <div class="stat-number" data-target="850" data-prefix="+">+850</div>
            <div class="stat-label">Ressources & Annales</div>
            <div class="stat-desc">Sujets corrigés, TP et fiches</div>
          </div>

          <div class="stat-card reveal" style="--delay: 0.45s">
            <div class="stat-number" data-target="98" data-suffix="%">98%</div>
            <div class="stat-label">Taux d'Entraide</div>
            <div class="stat-desc">Questions avec solution validée</div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== 3. Aperçu Interactif du Forum (Mockup UI) ===== -->
    <section id="apercu" class="preview-section">
      <div class="container-fluid">
        <div class="section-intro reveal">
          <span class="section-badge">Interface Intuitive</span>
          <h2>Un aperçu du forum pensé pour la réussite</h2>
          <p>Retrouvez toutes vos discussions de classe, les documents des enseignants et les annonces importantes au même endroit.</p>
        </div>

        <div class="forum-window reveal">
          <!-- Barre de fenêtre style moderne -->
          <div class="window-bar">
            <div class="window-dots">
              <span class="dot dot-red"></span>
              <span class="dot dot-yellow"></span>
              <span class="dot dot-green"></span>
            </div>
            <div class="window-search">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
              <span>Rechercher une question, une annale, un TP...</span>
            </div>
          </div>

          <!-- Corps de l'interface miniature -->
          <div class="forum-body">
            <!-- Barre latérale -->
            <aside class="forum-sidebar">
              <div class="sidebar-title">Espaces de cours</div>
              <ul class="sidebar-menu">
                <li class="sidebar-item active">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M9 3v18"/></svg>
                  <span>Zone BTS IDA</span>
                </li>
                <li class="sidebar-item">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                  <span>Vie de l'École</span>
                </li>
                <li class="sidebar-item">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/></svg>
                  <span>Licence Informatique</span>
                </li>
                <li class="sidebar-item">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6"/><path d="M2 10l10-5 10 5-10 5z"/></svg>
                  <span>Espace Professeurs</span>
                </li>
              </ul>
            </aside>

            <!-- Fil de discussion d'exemple -->
            <div class="forum-feed">
              <!-- Post 1 : Annonce Professeur -->
              <article class="feed-post">
                <div class="post-header">
                  <div class="post-author">
                    <div class="author-avatar" style="background:#1e3a8a;">IK</div>
                    <div class="author-meta">
                      <span class="author-name">Dr. Ibrahim Koné</span>
                      <span class="author-role">Professeur · Dév Web & BDD</span>
                    </div>
                  </div>
                  <span class="post-tag-badge badge-pinned">📌 Épinglé</span>
                </div>
                <h4 class="post-title">Sujets d'annales corrigés de Base de Données 2025</h4>
                <p class="post-snippet">Chers étudiants de BTS IDA, retrouvez le corrigé détaillé du partiel blanc ainsi que la grille d'évaluation des requêtes jointures.</p>
                <div class="post-footer">
                  <span class="post-action">📎 annales_bdd_2025.pdf (240 Ko)</span>
                  <span class="post-action">👍 24 réactions</span>
                  <span class="post-action">💬 6 réponses</span>
                </div>
              </article>

              <!-- Post 2 : Question Résolue -->
              <article class="feed-post">
                <div class="post-header">
                  <div class="post-author">
                    <div class="author-avatar" style="background:#dc2626;">MK</div>
                    <div class="author-meta">
                      <span class="author-name">Momo Kouassi</span>
                      <span class="author-role">Étudiant BTS IDA 2ème année</span>
                    </div>
                  </div>
                  <span class="post-tag-badge badge-solved">✓ Résolu</span>
                </div>
                <h4 class="post-title">Comment résoudre l'erreur SQLSTATE[42S22] dans le TP Laravel ?</h4>
                <p class="post-snippet">J'avais oublié de déclarer la contrainte de clé étrangère dans la migration. Solution validée et testée !</p>
                <div class="post-footer">
                  <span class="post-action">#laravel</span>
                  <span class="post-action">#mysql</span>
                  <span class="post-action">👍 15 soutiens</span>
                  <span class="post-action">💬 4 solutions</span>
                </div>
              </article>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== 4. Section Filières & Pôles de Formation ===== -->
    <section id="filieres" class="features">
      <div class="container-fluid">
        <div class="section-intro reveal">
          <span class="section-badge">Formations d'Excellence</span>
          <h2>Pensé pour <span class="text-primary">toutes</span> les <span class="text-accent">filières</span></h2>
          <p>Chaque promotion dispose de son salon sécurisé pour progresser sereinement.</p>
        </div>

        <div class="features-grid">
          <article class="feature-card reveal" style="--delay: 0s">
            <div class="feature-icon icon-blue">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m18 16 4-4-4-4"/><path d="m6 8-4 4 4 4"/><path d="m14.5 4-5 16"/></svg>
            </div>
            <h3>Filières Industrielles</h3>
            <p>Salons d'échange spécialisés : Informatique Développeur d'Applications (IDA), Génie Civil (Bâtiment, Travaux Publics, Géomètre), Mines & Géologie, Électrotechnique et Télécoms.</p>
          </article>

          <article class="feature-card reveal" style="--delay: 0.15s">
            <div class="feature-icon icon-red">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            </div>
            <h3>Filières Tertiaires</h3>
            <p>Espaces collaboratifs : Finance Comptabilité & Gestion des Entreprises, Gestion Commerciale, Ressources Humaines & Communication, Logistique et Assistanat de Direction.</p>
          </article>

          <article class="feature-card reveal" style="--delay: 0.3s">
            <div class="feature-icon icon-blue">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/><path d="M6 6h10"/><path d="M6 10h10"/></svg>
            </div>
            <h3>Sujets d'Examens & TP</h3>
            <p>Banque d'annales BTS, partiels blancs, fiches de synthèse de cours et entraide en groupe avant chaque session d'examens.</p>
          </article>

          <article class="feature-card reveal" style="--delay: 0.45s">
            <div class="feature-icon icon-red">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <h3>Entraide Profs & Délégués</h3>
            <p>Une communication directe et transparente pour poser des questions de cours et recevoir des explications certifiées.</p>
          </article>
        </div>
      </div>
    </section>

    <!-- ===== 5. Section Comment ça marche ? ===== -->
    <section id="comment-ca-marche" class="how-it-works">
      <div class="container-fluid">
        <div class="section-intro reveal">
          <span class="section-badge">Prise en Main Rapide</span>
          <h2>Comment ça marche ?</h2>
          <p>Rejoignez la plateforme en moins de deux minutes et accédez immédiatement à votre classe.</p>
        </div>

        <div class="steps-grid">
          <div class="step-card reveal" style="--delay: 0s">
            <span class="step-number">01</span>
            <div class="step-icon">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
            </div>
            <h3>Créez votre profil</h3>
            <p>Inscrivez-vous simplement avec votre adresse email institutionnelle <code>@epi-yakro.ci</code> et choisissez votre statut.</p>
          </div>

          <div class="step-card reveal" style="--delay: 0.2s">
            <span class="step-number">02</span>
            <div class="step-icon">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
            </div>
            <h3>Rejoignez votre filière</h3>
            <p>Accédez instantanément aux salons de discussion de votre promotion, de vos cours et des projets de groupe.</p>
          </div>

          <div class="step-card reveal" style="--delay: 0.4s">
            <span class="step-number">03</span>
            <div class="step-icon">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <h3>Échangez & Réussissez</h3>
            <p>Posez vos questions, proposez des corrigés, téléchargez les supports de cours et progressez ensemble.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== 6. Section Témoignages de la communauté ===== -->
    <section id="temoignages" class="testimonials-section">
      <div class="container-fluid">
        <div class="section-intro reveal">
          <span class="section-badge">Retours d'Expérience</span>
          <h2>Ce que dit la communauté de l'EPI</h2>
          <p>Étudiants, délégués et enseignants témoignent de l'impact de la plateforme au quotidien.</p>
        </div>

        <div class="testimonials-grid">
          <div class="testimonial-card reveal" style="--delay: 0s">
            <svg class="quote-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
            <p class="testimonial-text">« EPI Tchach nous permet d'accompagner les étudiants même après les heures de cours. Les questions sont regroupées et les réponses profitent à toute la classe. »</p>
            <div class="testimonial-user">
              <div class="user-avatar-initials">IK</div>
              <div class="user-info">
                <span class="user-name">Dr. Ibrahim Koné</span>
                <span class="user-role">Professeur · Dév Web & BDD</span>
              </div>
            </div>
          </div>

          <div class="testimonial-card reveal" style="--delay: 0.2s">
            <svg class="quote-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
            <p class="testimonial-text">« En tant que déléguée, je gagne un temps précieux pour transmettre les dates de devoirs et partager les annales sans que l'info ne se perde sur WhatsApp. »</p>
            <div class="testimonial-user">
              <div class="user-avatar-initials" style="background:#dc2626;">AN</div>
              <div class="user-info">
                <span class="user-name">Awa N'Guessan</span>
                <span class="user-role">Déléguée · BTS IDA 2</span>
              </div>
            </div>
          </div>

          <div class="testimonial-card reveal" style="--delay: 0.4s">
            <svg class="quote-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
            <p class="testimonial-text">« J'ai pu débloquer mes TPs de programmation en quelques minutes grâce aux explications de camarades et aux remarques des enseignants. Une vraie entraide ! »</p>
            <div class="testimonial-user">
              <div class="user-avatar-initials" style="background:#2563eb;">JD</div>
              <div class="user-info">
                <span class="user-name">Jean Diallo</span>
                <span class="user-role">Étudiant · Licence Informatique</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== 7. Section FAQ (Accordéon interactif) ===== -->
    <section id="faq" class="faq-section">
      <div class="container-fluid">
        <div class="section-intro reveal">
          <span class="section-badge">Questions Fréquentes</span>
          <h2>Tout ce que vous devez savoir</h2>
          <p>Retrouvez les réponses aux questions les plus posées sur la plateforme.</p>
        </div>

        <div class="faq-list">
          <div class="faq-item active reveal" style="--delay: 0s">
            <button type="button" class="faq-question">
              <span>Qui a le droit de s'inscrire sur EPI Tchach ?</span>
              <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
            </button>
            <div class="faq-answer">
              <p>L'accès à la plateforme est strictement réservé aux étudiants régulièrement inscrits à l'EPI (BTS, Licence, Master), ainsi qu'aux professeurs et au personnel administratif de l'école via une adresse email institutionnelle.</p>
            </div>
          </div>

          <div class="faq-item reveal" style="--delay: 0.1s">
            <button type="button" class="faq-question">
              <span>La plateforme est-elle gratuite ?</span>
              <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
            </button>
            <div class="faq-answer">
              <p>Oui, EPI Tchach est un outil d'entraide et de communication 100% gratuit, mis à disposition par l'école pour accompagner tous les étudiants vers la réussite de leurs examens.</p>
            </div>
          </div>

          <div class="faq-item reveal" style="--delay: 0.2s">
            <button type="button" class="faq-question">
              <span>Comment les cours et annales sont-ils organisés ?</span>
              <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
            </button>
            <div class="faq-answer">
              <p>Chaque filière possède sa propre zone avec des catégories claires : Cours & TP, Projets de classe, Examens & Révisions. Des tags précis permettent de filtrer rapidement les publications par matière (ex: #laravel, #gestion, #geniecivil).</p>
            </div>
          </div>

          <div class="faq-item reveal" style="--delay: 0.3s">
            <button type="button" class="faq-question">
              <span>Les échanges sont-ils modérés ?</span>
              <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
            </button>
            <div class="faq-answer">
              <p>Absolument. Des modérateurs, délégués et enseignants veillent au respect de la charte de bonne conduite pour garantir un espace bienveillant, constructif et sécurisé pour chacun.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== 8. Section Appel à l'action Immersive ===== -->
    <section class="cta-section">
      <div class="container-fluid">
        <div class="cta-box reveal">
          <h2>L'école des Leaders et des Entrepreneurs</h2>
          <p>Rejoignez dès aujourd'hui la communauté de l'École Polytechnique Internationale de Yamoussoukro sur EPI Tchach.</p>
          <div class="cta-actions">
            <a href="<?= $base ?>/register" class="btn btn-light">Créer mon compte membre</a>
            <a href="<?= $base ?>/login" class="btn btn-outline" style="border-color:#ffffff; color:#ffffff;">Se connecter</a>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- ===== 9. Pied de page Complet ===== -->
  <footer class="footer">
    <div class="container-fluid">
      <div class="footer-top">
        <div>
          <div class="brand" style="margin-bottom: 0.75rem;">
            <div class="brand-logo-wrapper" style="width:40px; height:40px;">
              <img src="<?= htmlspecialchars($logoFile) ?>" alt="Logo EPI" class="brand-logo-img" onerror="this.src='public/assets/images/logo.jpeg'" />
            </div>
            <div class="brand-text">
              <span class="brand-title" style="font-size:1.15rem;">EPI <span class="brand-accent">Tchach</span></span>
              <span class="brand-subtitle">Yamoussoukro</span>
            </div>
          </div>
          <p class="footer-brand-desc">
            Plateforme officielle de discussion, d'entraide et de partage de l'École Polytechnique Internationale (EPI Yamoussoukro).
          </p>
        </div>

        <div>
          <h4 class="footer-heading">Navigation</h4>
          <ul class="footer-list">
            <li><a href="#apercu">Aperçu du forum</a></li>
            <li><a href="#filieres">Nos filières</a></li>
            <li><a href="#comment-ca-marche">Comment ça marche ?</a></li>
            <li><a href="#temoignages">Témoignages</a></li>
            <li><a href="#faq">Foire aux questions</a></li>
          </ul>
        </div>

        <div>
          <h4 class="footer-heading">Espace Membre</h4>
          <ul class="footer-list">
            <li><a href="<?= $base ?>/login">Connexion étudiant / prof</a></li>
            <li><a href="<?= $base ?>/register">Créer un compte</a></li>
            <li><a href="<?= $base ?>/forgot-password">Mot de passe oublié</a></li>
          </ul>
        </div>

        <div>
          <h4 class="footer-heading">Campus & Contact</h4>
          <ul class="footer-list" style="font-size:0.875rem; color:var(--muted-foreground); line-height:1.6;">
            <li>📍 Yamoussoukro, Quartier Millionnaire</li>
            <li>🏨 En face de l'Hôtel Concorde</li>
            <li>📬 BP 1912 Yamoussoukro</li>
            <li>🌐 www.epiglobalgroup.com</li>
          </ul>
        </div>
      </div>

      <div class="footer-bottom">
        <p>© 2026 École Polytechnique Internationale (EPI) — Tous droits réservés.</p>
        <p>Projet Forum Académique MVC · EPI Tchach</p>
      </div>
    </div>
  </footer>

  <div class="tricolor-bar"></div>

  <!-- Scripts JavaScript Desktop -->
  <script src="<?= $base ?>/public/assets/js/desktop/landing.js" defer></script>
  <script src="<?= $base ?>/assets/js/desktop/landing.js" defer></script>
</body>
</html>
