<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>EPI Tchach — Plateforme de Discussion & Entraide</title>
  <meta name="description" content="Espace officiel d'échange et d'entraide pour les étudiants, professeurs et délégués de l'EPI." />
  
  <!-- Polices Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  
  <!-- Feuille de style Desktop dédiée -->
  <link rel="stylesheet" href="assets/css/desktop/home.css" />
</head>
<body>
  <!-- Bande tricolore décorative -->
  <div class="tricolor-bar"></div>

  <!-- ===== Barre de navigation fixe ===== -->
  <header class="header-wrapper">
    <nav class="nav container animate-fade-down">
      <a href="home" class="brand">
        <div class="brand-icon-wrapper">
          <svg class="icon brand-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z"/>
            <path d="M22 10v6"/><path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5"/>
          </svg>
        </div>
        <span>EPI <span class="brand-accent">Tchach</span></span>
      </a>

      <div class="nav-actions">
        <?php if (isset($_SESSION['user_id'])): ?>
          <a href="logout" class="btn btn-outline btn-sm">Se déconnecter</a>
        <?php else: ?>
          <a href="login" class="btn btn-outline btn-sm">Se connecter</a>
          <a href="register" class="btn btn-primary btn-sm">S'inscrire</a>
        <?php endif; ?>
      </div>
    </nav>
  </header>

  <!-- ===== Section Héros ===== -->
  <main>
    <section class="hero container">
      <div class="blob blob-left"></div>
      <div class="blob blob-right"></div>

      <div class="hero-text animate-fade-up">
        <span class="badge">Plateforme Officielle</span>
        <h1>Toute l'école, <span class="text-gradient">une seule conversation.</span></h1>
        <p class="lead">Étudiants, professeurs et délégués échangent, s'entraident et restent informés en temps réel dans un espace moderne et bienveillant.</p>
        
        <div class="hero-actions">
          <a href="register" class="btn btn-accent group">
            Commencer
            <svg class="icon arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
            </svg>
          </a>
          <a href="#features" class="btn btn-outline">Découvrir les zones</a>
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

    <!-- ===== Section Fonctionnalités & Zones ===== -->
    <section id="features" class="features">
      <div class="container">
        <div class="features-header reveal">
          <h2>Pensé pour <span class="text-primary">toute</span> la <span class="text-accent">communauté</span></h2>
          <p>Un environnement pensé pour faciliter la réussite scolaire et la vie de campus.</p>
        </div>

        <div class="features-grid">
          <article class="feature-card reveal" style="--delay: 0s">
            <div class="feature-icon icon-blue">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
              </svg>
            </div>
            <h3>Zones par Filière</h3>
            <p>Des espaces réservés à chaque niveau (BTS IDA, Licence Info, etc.) pour partager cours et projets tuteurés.</p>
          </article>

          <article class="feature-card reveal" style="--delay: 0.15s">
            <div class="feature-icon icon-red">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
              </svg>
            </div>
            <h3>Entraide & Professeurs</h3>
            <p>Posez vos questions sur les TP et bénéficiez de réponses rapides et certifiées par vos professeurs et délégués.</p>
          </article>

          <article class="feature-card reveal" style="--delay: 0.3s">
            <div class="feature-icon icon-blue">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/>
              </svg>
            </div>
            <h3>Annonces & Actualités</h3>
            <p>Emploi du temps, dates d'examens, événements du campus et opportunités de stage réunis au même endroit.</p>
          </article>

          <article class="feature-card reveal" style="--delay: 0.45s">
            <div class="feature-icon icon-red">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1 1 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/>
              </svg>
            </div>
            <h3>Espace Sécurisé</h3>
            <p>Accès strictement réservé aux membres de l'école avec authentification institutionnelle et modération active.</p>
          </article>
        </div>
      </div>
    </section>

    <!-- ===== Section Appel à l'action ===== -->
    <section class="cta container">
      <div class="cta-box reveal">
        <h2>Prêt à rejoindre EPI Tchach ?</h2>
        <p>Inscrivez-vous dès maintenant avec votre adresse institutionnelle pour participer aux discussions.</p>
        <a href="register" class="btn btn-light">Créer mon compte</a>
      </div>
    </section>
  </main>

  <!-- ===== Pied de page ===== -->
  <footer class="footer">
    <div class="container">
      <div class="footer-links">
        <a href="login">Connexion</a>
        <a href="register">Inscription</a>
        <a href="#features">Fonctionnalités</a>
      </div>
      <p>© 2026 EPI Tchach — Ensemble, on apprend mieux.</p>
    </div>
  </footer>

  <div class="tricolor-bar"></div>

  <!-- Script JavaScript Desktop dédié -->
  <script src="assets/js/desktop/home.js" defer></script>
</body>
</html>
