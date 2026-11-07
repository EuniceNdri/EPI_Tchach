<?php
$base = isset($baseUrl) ? $baseUrl : '';
$asset = isset($assetBase) ? $assetBase : (isset($baseUrl) ? $baseUrl . '/public/assets' : '/public/assets');
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
    :root { color-scheme:light; font-family:"Plus Jakarta Sans",system-ui,sans-serif; color:#0f172a; background:#f8fafc; }
    * { box-sizing:border-box; }
    body { min-height:100vh; margin:0; display:grid; place-items:center; padding:1.5rem; background:radial-gradient(circle at 15% 15%,#dbeafe,transparent 35%),radial-gradient(circle at 85% 85%,#fee2e2,transparent 35%),#f8fafc; }
    main { width:min(38rem,100%); padding:clamp(2rem,7vw,4rem); border:1px solid #e2e8f0; border-radius:1.5rem; background:#fff; box-shadow:0 24px 70px rgb(30 58 138 / 12%); text-align:center; }
    img { width:4rem; height:4rem; border-radius:1rem; object-fit:cover; }
    .code { margin:1.25rem 0 0; color:#dc2626; font-size:clamp(4rem,16vw,7rem); font-weight:900; letter-spacing:-.08em; line-height:1; }
    h1 { margin:.75rem 0; font-size:clamp(1.5rem,5vw,2.1rem); }
    p { margin:0 auto 1.75rem; max-width:30rem; color:#64748b; line-height:1.7; }
    nav { display:flex; flex-wrap:wrap; justify-content:center; gap:.75rem; }
    a { display:inline-flex; align-items:center; justify-content:center; min-height:2.9rem; padding:.7rem 1.1rem; border:1px solid #1e3a8a; border-radius:.75rem; color:#1e3a8a; font-weight:700; text-decoration:none; }
    a:first-child { background:#1e3a8a; color:#fff; }
    a:focus-visible { outline:3px solid #93c5fd; outline-offset:3px; }
  </style>
</head>
<body>
  <main>
    <img src="<?= htmlspecialchars($asset, ENT_QUOTES, 'UTF-8') ?>/images/logo.jpeg" alt="Logo EPI" />
    <div class="code" aria-hidden="true">404</div>
    <h1>Cette page s’est égarée</h1>
    <p>L’adresse demandée n’existe pas ou a été déplacée. Retournez à l’accueil ou connectez-vous pour retrouver les espaces de l’EPI.</p>
    <nav aria-label="Actions">
      <a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/landing">Retour à l’accueil</a>
      <a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/login">Se connecter</a>
    </nav>
  </main>
</body>
</html>
