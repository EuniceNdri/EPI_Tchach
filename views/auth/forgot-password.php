<?php
$pageTitle = "Mot de passe oublié — EPI Tchach";
$base = isset($baseUrl) ? $baseUrl : '';
$logoPath = file_exists(ROOT_PATH . '/public/assets/images/logo.jpeg') 
    ? ($base . '/public/assets/images/logo.jpeg') 
    : ($base . '/assets/images/logo.jpeg');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  
  <link rel="stylesheet" href="<?= $base ?>/public/assets/css/desktop/auth.css" />
  <link rel="stylesheet" href="<?= $base ?>/assets/css/desktop/auth.css" />
</head>
<body>
  <!-- Bande tricolore -->
  <div class="tricolor-bar"></div>

  <!-- En-tête -->
  <header class="auth-header">
    <a href="<?= $base ?>/landing" class="brand-link">
      <img src="<?= htmlspecialchars($logoPath) ?>" alt="Logo EPI" class="brand-logo-img" onerror="this.src='public/assets/images/logo.jpeg'" />
      <span class="brand-title">EPI <span class="brand-accent">Tchach</span></span>
    </a>
    <a href="<?= $base ?>/login" class="back-link">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
      Retour à la connexion
    </a>
  </header>

  <!-- Contenu Principal -->
  <main class="auth-main">
    <div class="blob blob-left"></div>
    <div class="blob blob-right"></div>

    <section class="auth-card">
      <div class="auth-card-header">
        <span class="auth-badge">Sécurité</span>
        <h1>Mot de passe oublié ?</h1>
        <p>Entrez votre email académique. Nous vous enverrons un lien sécurisé pour réinitialiser votre mot de passe.</p>
      </div>

      <?php if (!empty($error)): ?>
        <div class="alert alert-error">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <span><?= htmlspecialchars($error) ?></span>
        </div>
      <?php endif; ?>

      <?php if (!empty($success)): ?>
        <div class="alert alert-success">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          <span><?= htmlspecialchars($success) ?></span>
        </div>
      <?php endif; ?>

      <form action="<?= $base ?>/forgot-password" method="POST" class="auth-form">
        <div class="form-group">
          <label for="email" class="form-label">Adresse Email Institutionnelle</label>
          <div class="input-wrapper">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
            <input type="email" id="email" name="email" class="form-input" placeholder="prenom.nom@epi-yakro.ci" required autocomplete="email" />
          </div>
        </div>

        <button type="submit" class="btn-submit">Envoyer le lien de réinitialisation</button>
      </form>

      <div class="auth-card-footer">
        <span>Vous vous souvenez de votre mot de passe ?</span>
        <a href="<?= $base ?>/login" class="auth-link">Se connecter</a>
      </div>
    </section>
  </main>

  <footer class="auth-footer">
    © 2026 École Polytechnique Internationale de Yamoussoukro — EPI Tchach
  </footer>

  <script src="<?= $base ?>/public/assets/js/desktop/auth.js" defer></script>
</body>
</html>
