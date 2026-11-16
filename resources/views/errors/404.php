<?php
$base = isset($baseUrl) ? $baseUrl : '';
$asset = isset($assetBase) ? $assetBase : (isset($baseUrl) ? $baseUrl . '/public/assets' : '/public/assets');
$isLoggedIn = !empty($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots" content="noindex,follow" />
  <title>Page introuvable (404) · EPI Tchach</title>
  <link rel="icon" type="image/svg+xml" href="<?= htmlspecialchars($asset, ENT_QUOTES, 'UTF-8') ?>/images/logo_epi.svg" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <?php include ROOT_PATH . '/resources/views/partials/theme-head.php'; ?>
  <style>
    :root {
      --bg-page: #f8fafc;
      --card-bg: #ffffff;
      --card-border: #e2e8f0;
      --text-main: #0f172a;
      --text-muted: #64748b;
      --primary: #1e3a8a;
      --primary-hover: #172554;
      --accent-danger: #e11d48;
      --radius: 16px;
    }
    [data-theme="dark"] {
      --bg-page: #0b0f19;
      --card-bg: #111827;
      --card-border: #1f2937;
      --text-main: #f8fafc;
      --text-muted: #94a3b8;
      --primary: #3b82f6;
      --primary-hover: #60a5fa;
    }
    * { box-sizing: border-box; }
    body {
      min-height: 100vh;
      margin: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 1.5rem;
      background: var(--bg-page);
      font-family: "Plus Jakarta Sans", system-ui, sans-serif;
      color: var(--text-main);
      position: relative;
    }
    .tricolor-bar {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #1e3a8a 0%, #1e3a8a 33.3%, #ffffff 33.3%, #ffffff 66.6%, #dc2626 66.6%, #dc2626 100%);
      z-index: 1000;
    }
    .error-card {
      width: min(34rem, 100%);
      padding: clamp(2rem, 6vw, 3.25rem);
      border: 1px solid var(--card-border);
      border-radius: var(--radius);
      background: var(--card-bg);
      box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
      text-align: center;
      animation: cardIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    @keyframes cardIn {
      from { opacity: 0; transform: translateY(12px) scale(0.97); }
      to { opacity: 1; transform: translateY(0) scale(1); }
    }
    .brand-avatar {
      width: 4.5rem;
      height: 4.5rem;
      border-radius: 1.25rem;
      margin: 0 auto 1.25rem;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(30, 58, 138, 0.08);
      border: 1px solid rgba(30, 58, 138, 0.15);
    }
    .brand-avatar img {
      width: 3.5rem;
      height: 3.5rem;
      object-fit: cover;
      border-radius: 1rem;
    }
    .error-tag {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      padding: 0.25rem 0.75rem;
      border-radius: 9999px;
      font-size: 0.8rem;
      font-weight: 700;
      background: rgba(225, 29, 72, 0.1);
      color: var(--accent-danger);
      margin-bottom: 0.75rem;
    }
    .error-code {
      font-size: clamp(3.5rem, 12vw, 5.5rem);
      font-weight: 900;
      letter-spacing: -0.06em;
      line-height: 1;
      margin: 0;
      color: var(--text-main);
    }
    .error-title {
      font-size: clamp(1.25rem, 4vw, 1.65rem);
      font-weight: 800;
      margin: 0.75rem 0 0.5rem;
    }
    .error-desc {
      color: var(--text-muted);
      line-height: 1.6;
      font-size: 0.92rem;
      margin: 0 auto 1.75rem;
      max-width: 26rem;
    }
    .actions-group {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 0.75rem;
    }
    .btn-action {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      min-height: 2.85rem;
      padding: 0.65rem 1.35rem;
      border-radius: 10px;
      font-size: 0.88rem;
      font-weight: 700;
      text-decoration: none;
      transition: all 0.18s ease;
      cursor: pointer;
    }
    .btn-primary {
      background: var(--primary);
      color: #ffffff;
      border: 1px solid transparent;
      box-shadow: 0 4px 14px rgba(30, 58, 138, 0.25);
    }
    .btn-primary:hover {
      background: var(--primary-hover);
      transform: translateY(-1px);
    }
    .btn-secondary {
      background: transparent;
      color: var(--text-main);
      border: 1px solid var(--card-border);
    }
    .btn-secondary:hover {
      background: rgba(15, 23, 42, 0.04);
      transform: translateY(-1px);
    }
    [data-theme="dark"] .btn-secondary:hover {
      background: rgba(255, 255, 255, 0.05);
    }
  </style>
</head>
<body>
  <div class="tricolor-bar" aria-hidden="true"></div>
  <?php include ROOT_PATH . '/resources/views/partials/theme-toggle.php'; ?>

  <main class="error-card">
    <div class="brand-avatar">
      <img src="<?= htmlspecialchars($asset, ENT_QUOTES, 'UTF-8') ?>/images/logo.jpeg" alt="EPI Tchach" />
    </div>

    <div class="error-tag">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <span>Erreur 404 · Introuvable</span>
    </div>

    <h1 class="error-code">404</h1>
    <h2 class="error-title">Cette page s’est égarée</h2>
    <p class="error-desc">
      L’adresse demandée n’existe pas, a été renommée ou vous n’avez pas les droits nécessaires pour y accéder.
    </p>

    <nav class="actions-group" aria-label="Navigation de secours">
      <?php if ($isLoggedIn): ?>
        <a class="btn-action btn-primary" href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/salon">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          <span>Retour aux salons</span>
        </a>
        <a class="btn-action btn-secondary" href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/profile">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          <span>Mon profil</span>
        </a>
      <?php else: ?>
        <a class="btn-action btn-primary" href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/landing">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          <span>Accueil EPI</span>
        </a>
        <a class="btn-action btn-secondary" href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/login">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
          <span>Connexion</span>
        </a>
      <?php endif; ?>
    </nav>
  </main>
</body>
</html>
