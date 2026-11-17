<?php
$base = isset($baseUrl) ? $baseUrl : '';
$asset = isset($assetBase) ? $assetBase : (isset($baseUrl) ? $baseUrl . '/public/assets' : '/public/assets');
$isAuthenticated = !empty($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots" content="noindex,follow" />
  <title>Page introuvable — EPI Tchach</title>
  <link rel="icon" type="image/svg+xml" href="<?= htmlspecialchars($asset, ENT_QUOTES, 'UTF-8') ?>/images/logo_epi.svg" />
  <link rel="stylesheet" href="<?= htmlspecialchars($asset, ENT_QUOTES, 'UTF-8') ?>/css/feedback.css" />
  <style>
    :root { color-scheme:light; font-family:"Plus Jakarta Sans",system-ui,sans-serif; color:#1e293b; background:#f4f6f9; }
    * { box-sizing:border-box; }
    body { min-height:100vh; margin:0; display:grid; place-items:center; padding:1.5rem; background:radial-gradient(circle at 15% 15%,#ccfbf1,transparent 35%),radial-gradient(circle at 85% 85%,#ffedd5,transparent 35%),#f4f6f9; }
    main { width:min(38rem,100%); padding:clamp(2rem,7vw,4rem); border:1px solid #e2e8f0; border-radius:1.5rem; background:#fff; box-shadow:0 24px 70px rgb(15 118 110 / 10%); text-align:center; }
    img { width:4rem; height:4rem; border-radius:1rem; object-fit:cover; }
    .code { margin:1.25rem 0 0; color:#0d9488; font-size:clamp(4rem,16vw,7rem); font-weight:900; letter-spacing:-.08em; line-height:1; }
    h1 { margin:.75rem 0; font-size:clamp(1.5rem,5vw,2.1rem); }
    p { margin:0 auto 1.75rem; max-width:30rem; color:#64748b; line-height:1.7; }
    nav { display:flex; flex-wrap:wrap; justify-content:center; gap:.75rem; }
    a { display:inline-flex; align-items:center; justify-content:center; min-height:2.9rem; padding:.7rem 1.1rem; border:1px solid #0d9488; border-radius:.75rem; color:#0d766e; font-weight:700; text-decoration:none; }
    a:first-child { background:#0d9488; color:#fff; }
    a:focus-visible { outline:3px solid #5eead4; outline-offset:3px; }
  </style>
</head>
<body>
  <main>
    <img src="<?= htmlspecialchars($asset, ENT_QUOTES, 'UTF-8') ?>/images/logo.jpeg" alt="Logo EPI" />
    <div class="code" aria-hidden="true">404</div>
    <h1>Page introuvable</h1>
    <p>L’adresse demandée n’existe pas ou a été déplacée. Choisissez un espace pour reprendre votre navigation.</p>
    <nav aria-label="Actions">
      <?php if ($isAuthenticated): ?>
        <a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/salon">Retour au fil public</a>
        <a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/messages">Messagerie</a>
        <a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/profile">Mon profil</a>
      <?php else: ?>
        <a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/landing">Retour à l’accueil</a>
        <a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/login">Se connecter</a>
      <?php endif; ?>
    </nav>
  </main>
</body>
</html>
