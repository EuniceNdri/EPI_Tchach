<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>EPI Tchach — Plateforme de Discussion & Entraide</title>
  <meta name="description" content="Espace officiel d'échange et d'entraide pour les étudiants, professeurs et délégués de l'École Polytechnique Internationale de Yamoussoukro." />
  
  <!-- Polices Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  
  <!-- Feuille de style Desktop dédiée Landing -->
  <link rel="stylesheet" href="public/assets/css/desktop/landing.css" />
  <!-- Fallback chemin direct si public est la racine -->
  <link rel="stylesheet" href="assets/css/desktop/landing.css" />
</head>
<body>
  <!-- Bande tricolore décorative -->
  <div class="tricolor-bar"></div>

  <!-- ===== Barre de navigation fixe ===== -->
  <header class="header-wrapper">
    <nav class="nav container animate-fade-down">
      <a href="landing" class="brand">
        <div class="brand-logo-container">
          <!-- Logo officiel SVG de l'EPI Yamoussoukro -->
          <svg class="brand-logo-svg" viewBox="0 0 300 300" width="46" height="46">
            <circle cx="150" cy="150" r="142" fill="#ffffff" stroke="#1e3a8a" stroke-width="6"/>
            <circle cx="150" cy="150" r="134" fill="none" stroke="#1e3a8a" stroke-width="2" stroke-dasharray="4,4"/>
            <circle cx="150" cy="150" r="102" fill="none" stroke="#1e3a8a" stroke-width="4"/>
            <defs>
              <path id="nav-arc-top" d="M 32,150 A 118,118 0 1,1 268,150" fill="none"/>
              <path id="nav-arc-bottom" d="M 262,150 A 118,118 0 0,1 38,150" fill="none"/>
            </defs>
            <text fill="#1e3a8a" font-family="'Plus Jakarta Sans', Arial, sans-serif" font-size="14" font-weight="800" letter-spacing="1.4">
              <textPath href="#nav-arc-top" startOffset="50%" text-anchor="middle">
                ECOLE POLYTECHNIQUE INTERNATIONALE
              </textPath>
            </text>
            <text fill="#1e3a8a" font-family="'Plus Jakarta Sans', Arial, sans-serif" font-size="14" font-weight="800" letter-spacing="2.2">
              <textPath href="#nav-arc-bottom" startOffset="50%" text-anchor="middle">
                ★ YAMOUSSOUKRO ★
              </textPath>
            </text>
            <g transform="translate(150, 138) scale(1.15)">
              <path d="M -24,24 L -24,4 C -24,-12 -34,-14 -34,-24 C -34,-33 -23,-39 -13,-34 C -3,-28 -2,-13 0,-7 C 2,-13 3,-28 13,-34 C 23,-39 34,-33 34,-24 C 34,-14 24,-12 24,4 L 24,24 L 14,24 L 14,3 C 14,-7 22,-14 22,-22 C 22,-27 16,-30 10,-26 C 2,-21 0,-6 -1,0 L -1,24 Z M -14,24 L -14,3 C -14,-7 -22,-14 -22,-22 C -22,-27 -16,-30 -10,-26 C -2,-21 0,-6 1,0 L 1,24 Z" fill="#dc2626" />
              <path d="M -17,24 C -17,16 -17,-5 -19,-14 C -22,-22 -14,-29 -8,-25 C -2,-21 -2,-7 0,-1 C 2,-7 2,-21 8,-25 C 14,-29 22,-22 19,-14 C 17,-5 17,16 17,24 Z" fill="#dc2626" opacity="0.95"/>
            </g>
            <text x="150" y="196" text-anchor="middle" fill="#1e3a8a" font-family="'Plus Jakarta Sans', Arial, sans-serif" font-size="28" font-weight="900" letter-spacing="2">
              EPI
            </text>
          </svg>
        </div>
        <div class="brand-text">
          <span class="brand-title">EPI <span class="brand-accent">Tchach</span></span>
          <span class="brand-subtitle">Yamoussoukro</span>
        </div>
      </a>

      <div class="nav-actions">
        <a href="#features" class="btn btn-outline btn-sm">Découvrir</a>
        <a href="#commencer" class="btn btn-primary btn-sm">Accéder</a>
      </div>
    </nav>
  </header>

  <!-- ===== Section Héros ===== -->
  <main>
    <section class="hero container">
      <div class="blob blob-left"></div>
      <div class="blob blob-right"></div>

      <div class="hero-text animate-fade-up">
        <span class="badge">École Polytechnique Internationale</span>
        <h1>Toute l'école, <span class="text-gradient">une seule conversation.</span></h1>
        <p class="lead">Étudiants de BTS, Licence et Master, professeurs et délégués : échangez, collaborez et restez informés en temps réel au sein de l'EPI Yamoussoukro.</p>
        
        <div class="hero-actions" id="commencer">
          <a href="#features" class="btn btn-accent group">
            Commencer
            <svg class="icon arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
            </svg>
          </a>
          <a href="#features" class="btn btn-outline">Découvrir les filières</a>
        </div>
      </div>

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
    </section>

    <!-- ===== Section Filières & Zones ===== -->
    <section id="features" class="features">
      <div class="container">
        <div class="features-header reveal">
          <h2>Pensé pour <span class="text-primary">toutes</span> les <span class="text-accent">filières</span></h2>
          <p>Filières industrielles et tertiaires réunies sur une même plateforme d'entraide et de partage.</p>
        </div>

        <div class="features-grid">
          <article class="feature-card reveal" style="--delay: 0s">
            <div class="feature-icon icon-blue">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
              </svg>
            </div>
            <h3>Filières Industrielles</h3>
            <p>Salons d'échange pour Informatique (IDA), Génie Civil, Mines & Géologie, Électrotechnique, Télécoms et Énergies.</p>
          </article>

          <article class="feature-card reveal" style="--delay: 0.15s">
            <div class="feature-icon icon-red">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
              </svg>
            </div>
            <h3>Filières Tertiaires</h3>
            <p>Espaces dédiés pour Finance & Comptabilité, Gestion Commerciale, Ressources Humaines, Logistique et Assistanat.</p>
          </article>

          <article class="feature-card reveal" style="--delay: 0.3s">
            <div class="feature-icon icon-blue">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/>
              </svg>
            </div>
            <h3>Annonces & Campus</h3>
            <p>Emplois du temps, dates d'examens, convocations et vie étudiante de Yamoussoukro centralisés au même endroit.</p>
          </article>

          <article class="feature-card reveal" style="--delay: 0.45s">
            <div class="feature-icon icon-red">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1 1 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/>
              </svg>
            </div>
            <h3>Entraide Profs & Délégués</h3>
            <p>Un espace modéré où étudiants, délégués et professeurs communiquent en toute transparence et efficacité.</p>
          </article>
        </div>
      </div>
    </section>

    <!-- ===== Section Appel à l'action ===== -->
    <section class="cta container">
      <div class="cta-box reveal">
        <h2>L'école des Leaders et des Entrepreneurs</h2>
        <p>Rejoignez la communauté de l'École Polytechnique Internationale de Yamoussoukro sur EPI Tchach.</p>
        <a href="#features" class="btn btn-light">Découvrir la plateforme</a>
      </div>
    </section>
  </main>

  <!-- ===== Pied de page ===== -->
  <footer class="footer">
    <div class="container">
      <div class="footer-links">
        <a href="#features">Filières Industrielles</a>
        <a href="#features">Filières Tertiaires</a>
        <a href="#features">À propos</a>
      </div>
      <p>© 2026 École Polytechnique Internationale (EPI Yamoussoukro) — EPI Tchach.</p>
    </div>
  </footer>

  <div class="tricolor-bar"></div>

  <!-- Scripts JavaScript Desktop dédiés -->
  <script src="public/assets/js/desktop/landing.js" defer></script>
  <script src="assets/js/desktop/landing.js" defer></script>
</body>
</html>
