<?php
$base = isset($baseUrl) ? $baseUrl : '';
$asset = isset($assetBase) ? $assetBase : (isset($baseUrl) ? $baseUrl . '/public/assets' : '/public/assets');
$logoFile = $asset . '/images/logo.jpeg';
$filieres = isset($filieres) && is_array($filieres) ? $filieres : [];
$filieresLoadError = $filieresLoadError ?? false;
$filiereImages = [
  'IDA' => 'filiere-industrielle.jpg',
  'LIC-INFO' => 'filiere-industrielle.jpg',
  'GM' => 'filiere-industrielle.jpg',
  'GC' => 'filiere-industrielle.jpg',
  'FCGE' => 'filiere-tertiaire.jpg',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>EPI Tchach — La communauté de l'EPI</title>
  <meta name="description" content="Échangez vos cours, entraidez-vous et restez informés avec la communauté de l'École Polytechnique Internationale de Yamoussoukro." />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="<?= htmlspecialchars($asset, ENT_QUOTES, 'UTF-8') ?>/css/smartphone/landing.css" />
</head>
<body class="phone-page">
  <header class="phone-header">
    <a class="phone-brand" href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/landing">
      <img src="<?= htmlspecialchars($logoFile, ENT_QUOTES, 'UTF-8') ?>" alt="Logo EPI" />
      <span><strong>EPI <em>Tchach</em></strong><small>Yamoussoukro</small></span>
    </a>
    <button class="phone-menu-toggle" type="button" aria-label="Ouvrir la navigation" aria-expanded="false" aria-controls="phoneNavigation">
      <span></span><span></span><span></span>
    </button>
    <nav class="phone-navigation" id="phoneNavigation">
      <a href="#apercu">Aperçu</a>
      <a href="#filieres">Filières</a>
      <a href="#fonctionnement">Comment ça marche</a>
      <a href="#questions">Questions fréquentes</a>
      <a href="#charte">Charte & confidentialité</a>
      <div class="phone-nav-actions">
        <a class="phone-button phone-button-quiet" href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/login">Se connecter</a>
        <a class="phone-button phone-button-primary" href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/register">Créer un compte</a>
      </div>
    </nav>
  </header>

  <main>
    <section class="phone-hero">
      <div class="phone-hero-copy">
        <span class="phone-eyebrow"><span></span> En direct de Yamoussoukro</span>
        <h1>Toute l'école,<br /><strong>une seule conversation.</strong></h1>
        <p>Étudiants, professeurs et délégués : partagez vos cours, résolvez vos exercices et restez informés au sein de l'EPI.</p>
        <div class="phone-hero-actions">
          <a class="phone-button phone-button-primary" href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/register">Commencer</a>
          <a class="phone-text-link" href="#apercu">Découvrir la plateforme <span aria-hidden="true">↓</span></a>
        </div>
      </div>
      <div class="phone-photo-carousel" aria-label="La vie à l'EPI">
        <figure class="phone-photo is-active" data-phone-slide>
          <img src="<?= htmlspecialchars($asset, ENT_QUOTES, 'UTF-8') ?>/images/landing-students.jpg" alt="Deux étudiants révisent ensemble" fetchpriority="high" />
          <figcaption>On avance ensemble.</figcaption>
        </figure>
        <figure class="phone-photo" data-phone-slide>
          <img src="<?= htmlspecialchars($asset, ENT_QUOTES, 'UTF-8') ?>/images/filiere-entraide.jpg" alt="Étudiants qui travaillent en équipe" />
          <figcaption>L'entraide au quotidien.</figcaption>
        </figure>
        <figure class="phone-photo" data-phone-slide>
          <img src="<?= htmlspecialchars($asset, ENT_QUOTES, 'UTF-8') ?>/images/filiere-industrielle.jpg" alt="Une formation tournée vers la pratique" />
          <figcaption>Apprendre en construisant.</figcaption>
        </figure>
        <div class="phone-photo-controls" aria-label="Choisir une photo">
          <button type="button" data-phone-prev aria-label="Photo précédente">←</button>
          <div>
            <button type="button" class="is-active" data-phone-dot="0" aria-label="Photo 1" aria-current="true"></button>
            <button type="button" data-phone-dot="1" aria-label="Photo 2"></button>
            <button type="button" data-phone-dot="2" aria-label="Photo 3"></button>
          </div>
          <button type="button" data-phone-next aria-label="Photo suivante">→</button>
        </div>
      </div>
    </section>

    <section class="phone-stats" aria-label="EPI Tchach en chiffres">
      <div><strong>+1 250</strong><span>Membres actifs</span></div>
      <div><strong>12</strong><span>Filières représentées</span></div>
      <div><strong>+850</strong><span>Ressources partagées</span></div>
      <div><strong>98%</strong><span>Questions avec solution</span></div>
    </section>

    <section class="phone-section" id="apercu">
      <div class="phone-section-heading">
        <span class="phone-kicker">Interface intuitive</span>
        <h2>Le forum pensé pour votre réussite</h2>
        <p>Les discussions de classe, les documents et les annonces importantes réunis au même endroit.</p>
      </div>
      <div class="phone-forum-card">
        <div class="phone-forum-heading"><span class="phone-status-dot"></span> Discussions de la communauté <span>En ligne</span></div>
        <article>
          <span class="phone-post-tag">Ressource · Épinglé</span>
          <h3>Annales corrigées de Base de Données 2025</h3>
          <p>Le corrigé détaillé du partiel blanc et la grille d'évaluation sont disponibles.</p>
          <small>Dr. Ibrahim Koné · 6 réponses</small>
        </article>
        <article>
          <span class="phone-post-tag phone-post-solved">Question · Résolue</span>
          <h3>Comment corriger une erreur SQL dans le TP ?</h3>
          <p>Une solution partagée et validée par la communauté.</p>
          <small>Momo Kouassi · 4 solutions</small>
        </article>
      </div>
    </section>

    <section class="phone-section phone-section-tinted" id="filieres">
      <div class="phone-section-heading">
        <span class="phone-kicker">Formations d'excellence</span>
        <h2>Une place pour chaque filière</h2>
        <p>Retrouvez les étudiants de votre promotion et progressez ensemble.</p>
      </div>
      <?php if ($filieres): ?>
        <div class="phone-filiere-list">
          <?php foreach ($filieres as $filiere): ?>
            <?php
              $code = strtoupper(trim($filiere['code'] ?? ''));
              $image = $filiereImages[$code] ?? 'filiere-entraide.jpg';
              $name = trim($filiere['nom'] ?? '');
              $description = trim($filiere['description'] ?? '');
              if ($description === '') {
                $description = 'Échangez et progressez avec les étudiants de la filière ' . $name . '.';
              }
            ?>
            <article class="phone-filiere-card">
              <img src="<?= htmlspecialchars($asset . '/images/' . $image, ENT_QUOTES, 'UTF-8') ?>" alt="" loading="lazy" />
              <div>
                <span><?= htmlspecialchars($filiere['code'] ?: 'Filière', ENT_QUOTES, 'UTF-8') ?></span>
                <h3><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></h3>
                <p><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></p>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="phone-empty-state" role="status">
          <?= $filieresLoadError
            ? 'Les filières ne sont pas disponibles pour le moment.'
            : 'Aucune filière active n’est disponible pour le moment.' ?>
        </p>
      <?php endif; ?>
    </section>

    <section class="phone-section" id="fonctionnement">
      <div class="phone-section-heading">
        <span class="phone-kicker">Prise en main rapide</span>
        <h2>Rejoignez la communauté en trois étapes</h2>
      </div>
      <ol class="phone-steps">
        <li><span>01</span><div><h3>Créez votre profil</h3><p>Inscrivez-vous avec votre adresse email institutionnelle <code>@epi-yakro.ci</code>.</p></div></li>
        <li><span>02</span><div><h3>Retrouvez votre filière</h3><p>Accédez aux échanges et ressources de votre communauté académique.</p></div></li>
        <li><span>03</span><div><h3>Partagez et progressez</h3><p>Posez vos questions, aidez vos camarades et partagez vos ressources.</p></div></li>
      </ol>
    </section>

    <section class="phone-section phone-section-tinted" id="questions">
      <div class="phone-section-heading">
        <span class="phone-kicker">Questions fréquentes</span>
        <h2>Vous avez des questions ?</h2>
      </div>
      <div class="phone-faq">
        <details><summary>Qui peut rejoindre EPI Tchach ?</summary><p>Les étudiants, professeurs et délégués de l'École Polytechnique Internationale de Yamoussoukro.</p></details>
        <details><summary>L'inscription est-elle gratuite ?</summary><p>Oui, la création d'un compte et l'accès à la communauté sont gratuits.</p></details>
        <details><summary>Comment accéder aux ressources de ma filière ?</summary><p>Après inscription et connexion, vous pourrez rejoindre les espaces de discussion correspondant à votre filière.</p></details>
        <details><summary>Comment les échanges sont-ils modérés ?</summary><p>Des modérateurs, délégués et enseignants veillent au respect de la charte et au bon déroulement des échanges.</p></details>
      </div>
    </section>

    <section class="phone-cta">
      <span class="phone-kicker">La communauté EPI vous attend</span>
      <h2>Faisons avancer l'école ensemble.</h2>
      <p>Rejoignez les étudiants, délégués et professeurs sur EPI Tchach.</p>
      <a class="phone-button phone-button-light" href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/register">Créer mon compte</a>
      <a class="phone-cta-login" href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/login">J'ai déjà un compte</a>
    </section>

    <section class="phone-charter" id="charte">
      <details>
        <summary>Charte d'utilisation & confidentialité</summary>
        <p>EPI Tchach est un espace d'entraide et de bienveillance. Les propos haineux, diffamatoires, discriminatoires ou irrespectueux sont interdits.</p>
        <p>L'inscription est réservée aux titulaires d'une adresse officielle de l'école (<code>@epi-yakro.ci</code>). Chaque compte est personnel.</p>
        <p>Le partage de ressources est encouragé, dans le respect des droits d'auteur et des consignes des enseignants.</p>
        <p>Vos informations personnelles sont utilisées uniquement pour l'organisation pédagogique et le fonctionnement de la plateforme.</p>
        <p>Les échanges sont modérés. Signalez tout contenu inapproprié à l'équipe de modération.</p>
      </details>
    </section>
  </main>

  <footer class="phone-footer">
    <a class="phone-brand" href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/landing">
      <img src="<?= htmlspecialchars($logoFile, ENT_QUOTES, 'UTF-8') ?>" alt="" />
      <span><strong>EPI <em>Tchach</em></strong><small>Yamoussoukro</small></span>
    </a>
    <p>La communauté d'entraide de l'École Polytechnique Internationale.</p>
    <div><a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/login">Connexion</a><a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/register">Inscription</a><a href="#charte">Charte</a></div>
    <small>© 2026 École Polytechnique Internationale — Tous droits réservés.</small>
  </footer>
  <script src="<?= htmlspecialchars($asset, ENT_QUOTES, 'UTF-8') ?>/js/smartphone/landing.js" defer></script>
</body>
</html>
