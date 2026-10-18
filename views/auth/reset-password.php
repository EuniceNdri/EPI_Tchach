<?php
$pageTitle = "Réinitialiser le mot de passe — EPI Tchach";
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
        <span class="auth-badge">Nouveau Mot de Passe</span>
        <h1>Réinitialisation</h1>
        <p>Veuillez définir votre nouveau mot de passe sécurisé.</p>
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

      <form action="<?= $base ?>/reset-password" method="POST" class="auth-form">
        <!-- Nouveau mot de passe -->
        <div class="form-group">
          <label for="nouveau_mot_de_passe" class="form-label">Nouveau mot de passe</label>
          <div class="input-wrapper">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <input type="password" id="nouveau_mot_de_passe" name="nouveau_mot_de_passe" class="form-input" placeholder="8+ caractères" required minlength="8" />
            <button type="button" class="toggle-password" data-target="nouveau_mot_de_passe" aria-label="Afficher ou masquer">
              <svg class="icon-eye" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg class="icon-eye-off" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" y1="2" x2="22" y2="22"/></svg>
            </button>
          </div>
        </div>

        <!-- Confirmation du mot de passe -->
        <div class="form-group">
          <label for="confirmer_mot_de_passe" class="form-label">Confirmer le nouveau mot de passe</label>
          <div class="input-wrapper">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <input type="password" id="confirmer_mot_de_passe" name="confirmer_mot_de_passe" class="form-input" placeholder="Retapez le mot de passe" required minlength="8" />
            <button type="button" class="toggle-password" data-target="confirmer_mot_de_passe" aria-label="Afficher ou masquer">
              <svg class="icon-eye" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg class="icon-eye-off" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" y1="2" x2="22" y2="22"/></svg>
            </button>
          </div>
        </div>

        <button type="submit" class="btn-submit">Mettre à jour le mot de passe</button>
      </form>

      <div class="auth-card-footer">
        <a href="<?= $base ?>/login" class="auth-link">← Retour à la page de connexion</a>
      </div>
    </section>
  </main>

  <footer class="auth-footer">
    © 2026 École Polytechnique Internationale de Yamoussoukro — EPI Tchach
  </footer>

  <script src="<?= $base ?>/public/assets/js/desktop/auth.js" defer></script>
  <script src="<?= $base ?>/assets/js/desktop/auth.js" defer></script>
</body>
</html>
