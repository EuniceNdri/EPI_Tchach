<?php
/**
 * =============================================================
 * views/desktop/binomages/index.php – Espace Binômage & Mentorat
 * =============================================================
 * Rôles séparés strictement :
 * - Mentoré (1ère année) : Mon mentor | Demandes envoyées | Trouver un mentor
 * - Mentor (2ème année+) : Mes mentorés | Demandes reçues
 * =============================================================
 */

$base = is_string($baseUrl ?? null) ? $baseUrl : '';
$asset = is_string($assetBase ?? null) ? $assetBase : ($base !== '' ? $base . '/public/assets' : '/public/assets');
$csrfToken = is_string($csrfToken ?? null) ? $csrfToken : '';
$userRole = is_array($userRole ?? null) ? $userRole : ['type' => 'none', 'annee' => 0, 'filiere_nom' => ''];
$mentors = is_array($mentors ?? null) ? $mentors : [];
$incoming = is_array($incoming ?? null) ? $incoming : [];
$outgoing = is_array($outgoing ?? null) ? $outgoing : [];
$mentees = is_array($mentees ?? null) ? $mentees : [];
$mentor = is_array($mentor ?? null) ? $mentor : null;
$isMentore = ($userRole['type'] === 'mentore');
$isMentor = ($userRole['type'] === 'mentor');

$defaultTab = $isMentore ? ($mentor ? 'mentor' : 'find') : 'mentees';
$activeTab = $_GET['tab'] ?? $defaultTab;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Espace Binômage & Mentorat · EPI Tchach</title>
  <link rel="icon" type="image/svg+xml" href="<?= htmlspecialchars($asset) ?>/images/logo_epi.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= htmlspecialchars($asset) ?>/css/desktop/community-shell.css">
  <link rel="stylesheet" href="<?= htmlspecialchars($asset) ?>/css/desktop/salon.css">
  <link rel="stylesheet" href="<?= htmlspecialchars($asset) ?>/css/desktop/profile.css">
  <?php include ROOT_PATH . '/resources/views/partials/theme-head.php'; ?>
  <style>
    .binomage-container {
      max-width: 1180px;
      margin: 1.5rem auto 3rem;
      padding: 0 1.25rem;
    }
    .binomage-hero {
      background: linear-gradient(135deg, #065f46 0%, #0d9488 100%);
      color: #fff;
      border-radius: 18px;
      padding: 2rem 2.25rem;
      margin-bottom: 1.75rem;
      box-shadow: 0 10px 25px -5px rgba(13, 148, 136, 0.3);
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 1.25rem;
    }
    .binomage-hero h1 {
      margin: 0 0 0.4rem;
      font-size: 1.75rem;
      font-weight: 800;
      letter-spacing: -0.02em;
    }
    .binomage-hero p {
      margin: 0;
      color: rgba(255, 255, 255, 0.9);
      font-size: 0.95rem;
      max-width: 650px;
      line-height: 1.5;
    }
    .binomage-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.4rem 0.85rem;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 9999px;
      font-size: 0.8rem;
      font-weight: 700;
      letter-spacing: 0.04em;
      text-transform: uppercase;
      margin-bottom: 0.65rem;
    }
    .binomage-tabs {
      display: flex;
      gap: 0.5rem;
      border-bottom: 2px solid var(--profile-border, #e2e8f0);
      margin-bottom: 1.5rem;
    }
    .binomage-tab-btn {
      padding: 0.75rem 1.25rem;
      background: transparent;
      border: none;
      border-bottom: 3px solid transparent;
      font-weight: 700;
      font-size: 0.925rem;
      color: var(--profile-text-muted, #64748b);
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-bottom: -2px;
      transition: all 0.2s ease;
      text-decoration: none;
    }
    .binomage-tab-btn:hover {
      color: #0d9488;
    }
    .binomage-tab-btn.is-active {
      color: #0d9488;
      border-bottom-color: #0d9488;
    }
    .binomage-counter-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 0.15rem 0.5rem;
      border-radius: 9999px;
      background: rgba(13, 148, 136, 0.15);
      color: #0d9488;
      font-size: 0.75rem;
      font-weight: 800;
    }
    .binomage-card-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
      gap: 1.25rem;
    }
    .binomage-user-card {
      background: var(--profile-card-bg, #ffffff);
      border: 1px solid var(--profile-border, #e2e8f0);
      border-radius: 16px;
      padding: 1.25rem;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      gap: 1rem;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .binomage-user-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
    }
    .binomage-user-header {
      display: flex;
      align-items: center;
      gap: 0.85rem;
    }
    .binomage-avatar {
      width: 52px;
      height: 52px;
      border-radius: 50%;
      background: #0d9488;
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
      font-size: 1.15rem;
      flex-shrink: 0;
      overflow: hidden;
    }
    .binomage-avatar img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .binomage-user-info h3 {
      margin: 0;
      font-size: 1rem;
      font-weight: 700;
      color: var(--profile-text, #1e293b);
    }
    .binomage-user-sub {
      font-size: 0.8rem;
      color: var(--profile-text-muted, #64748b);
      margin-top: 0.2rem;
    }
    .binomage-message-quote {
      background: var(--profile-surface, #f8fafc);
      border-left: 3px solid #0d9488;
      padding: 0.65rem 0.85rem;
      border-radius: 0 8px 8px 0;
      font-size: 0.85rem;
      color: var(--profile-text, #334155);
      font-style: italic;
    }
  </style>
</head>
<body class="community-shell community-salon" data-base-url="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>">
  <?php include ROOT_PATH . '/resources/views/partials/theme-toggle.php'; ?>
  <?php include ROOT_PATH . '/resources/views/partials/feedback-toast.php'; ?>
  <div class="tricolor-bar"></div>

  <!-- Topbar Header -->
  <header class="epi-topbar">
    <div class="epi-topbar-inner">
      <div class="epi-topbar-left">
        <a class="epi-brand" href="<?= htmlspecialchars($base) ?>/salon" aria-label="Accueil EPI Tchach">
          <div class="epi-brand-icon"><img src="<?= htmlspecialchars($asset) ?>/images/logo.jpeg" alt="EPI"></div>
          <span class="epi-brand-title">EPI <span class="accent">Tchach</span></span>
        </a>
        <a class="epi-community-pill" href="<?= htmlspecialchars($base) ?>/binomage">
          <span>🎓 Mentorat Polytech</span>
        </a>
      </div>
      <div class="epi-topbar-center">
        <span class="epi-topbar-brand-title">Programme de Binômage Académique</span>
      </div>
      <div class="epi-topbar-right">
        <a class="epi-icon-action" href="<?= htmlspecialchars($base) ?>/salon" title="Retour au fil campus">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>
        </a>
        <a class="epi-icon-action" href="<?= htmlspecialchars($base) ?>/friends" title="Mes camarades">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </a>
        <a class="epi-icon-action" href="<?= htmlspecialchars($base) ?>/profile" title="Mon profil">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </a>
      </div>
    </div>
  </header>

  <main class="binomage-container">

    <!-- HERO HEADER DU BINÔMAGE -->
    <section class="binomage-hero">
      <div>
        <span class="binomage-badge">
          <?php if ($isMentore): ?>
            🌱 Profil Mentoré · 1ère Année <?= htmlspecialchars($userRole['filiere_nom']) ?>
          <?php elseif ($isMentor): ?>
            🎓 Profil Mentor · <?= (int)$userRole['annee'] ?>ème Année <?= htmlspecialchars($userRole['filiere_nom']) ?>
          <?php else: ?>
            ℹ️ Programme de Binômage Solidaire
          <?php endif; ?>
        </span>
        <h1>Binômage & Mentorat d'Excellence</h1>
        <p>
          <?php if ($isMentore): ?>
            En 1ère année, choisissez un aîné de votre propre filière pour vous accompagner, partager ses conseils de méthodologie et vous guider tout au long de l'année.
          <?php elseif ($isMentor): ?>
            En tant qu'étudiant de <?= (int)$userRole['annee'] ?>ème année en <?= htmlspecialchars($userRole['filiere_nom']) ?>, transmettez votre expérience aux nouveaux arrivants de 1ère année pour assurer leur réussite.
          <?php else: ?>
            Le mentorat met en relation les étudiants de 1ère année (mentorés) avec les étudiants de 2ème année ou plus (mentors) d'une même filière.
          <?php endif; ?>
        </p>
      </div>
      <div>
        <a href="<?= $base ?>/profile" class="campus-profile-btn secondary" style="background:rgba(255,255,255,0.15); color:#fff; border-color:rgba(255,255,255,0.3);">
          Mon Profil Académique
        </a>
      </div>
    </section>

    <!-- ONGLETS PAR RÔLE STRICT -->
    <?php if ($isMentore): ?>
      <!-- ================= VUE MENTORÉ (1ère ANNÉE) ================= -->
      <nav class="binomage-tabs" role="tablist">
        <a href="<?= $base ?>/binomage?tab=mentor" class="binomage-tab-btn <?= $activeTab === 'mentor' ? 'is-active' : '' ?>">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
          <span>Mon mentor</span>
          <?php if ($mentor): ?><span class="binomage-counter-badge">1</span><?php endif; ?>
        </a>
        <a href="<?= $base ?>/binomage?tab=sent" class="binomage-tab-btn <?= $activeTab === 'sent' ? 'is-active' : '' ?>">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
          <span>Demandes envoyées</span>
          <?php if (count($outgoing) > 0): ?><span class="binomage-counter-badge"><?= count($outgoing) ?></span><?php endif; ?>
        </a>
        <a href="<?= $base ?>/binomage?tab=find" class="binomage-tab-btn <?= $activeTab === 'find' ? 'is-active' : '' ?>">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <span>Trouver un mentor</span>
          <span class="binomage-counter-badge"><?= count($mentors) ?></span>
        </a>
      </nav>

      <!-- CONTENU ONGLET 1: MON MENTOR -->
      <?php if ($activeTab === 'mentor'): ?>
        <?php if ($mentor): ?>
          <div class="binomage-user-card" style="max-width:680px; border:2px solid #0d9488;">
            <div class="binomage-user-header">
              <div class="binomage-avatar">
                <?php if (!empty($mentor['mentor_avatar'])): ?>
                  <img src="<?= htmlspecialchars(url('public/' . ltrim($mentor['mentor_avatar'], '/')), ENT_QUOTES, 'UTF-8') ?>" alt="">
                <?php else: ?>
                  <?= htmlspecialchars(mb_substr($mentor['mentor_prenom'], 0, 1) . mb_substr($mentor['mentor_nom'], 0, 1)) ?>
                <?php endif; ?>
              </div>
              <div class="binomage-user-info">
                <h3><?= htmlspecialchars($mentor['mentor_prenom'] . ' ' . $mentor['mentor_nom']) ?></h3>
                <div class="binomage-user-sub">
                  <?= htmlspecialchars($mentor['filiere'] ?? '') ?> · <?= (int)$mentor['mentor_annee'] ?>ème année · Mentor Certifié
                </div>
              </div>
            </div>

            <div style="background:var(--profile-surface); padding:1rem; border-radius:12px; font-size:0.875rem; color:var(--profile-text-muted); line-height:1.5;">
              🤝 <strong>Binômage Actif :</strong> Votre mentor est à votre disposition pour vous conseiller et vous prêter main-forte. N'hésitez pas à lui envoyer un message privé.
            </div>

            <div style="display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
              <a href="<?= $base ?>/messages?user=<?= (int)$mentor['mentor_id'] ?>" class="campus-profile-btn primary" style="background:#0d9488; border-color:#0d9488; text-decoration:none;">
                💬 Discuter avec mon mentor
              </a>
              <a href="<?= $base ?>/profile?id=<?= (int)$mentor['mentor_id'] ?>" class="campus-profile-btn secondary" style="text-decoration:none;">
                Consulter son profil
              </a>
              <form method="post" action="<?= $base ?>/binomage/action" style="display:inline; margin-left:auto;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="action" value="terminate">
                <input type="hidden" name="relation_id" value="<?= (int)$mentor['id'] ?>">
                <button type="submit" class="campus-profile-btn secondary" style="color:#ef4444;" onclick="return confirm('Voulez-vous vraiment rompre ce binômage ?');">
                  Rompre le binômage
                </button>
              </form>
            </div>
          </div>
        <?php else: ?>
          <div class="campus-empty-box" style="padding:3rem 1.5rem; text-align:center;">
            <div style="font-size:3rem; margin-bottom:1rem;">🎓</div>
            <h3 style="margin:0 0 0.5rem;">Vous n'avez pas encore de mentor</h3>
            <p style="color:var(--profile-text-muted); max-width:500px; margin:0 auto 1.25rem;">
              En tant qu'étudiant de 1ère année, choisissez un étudiant de 2ème ou 3ème année de votre filière pour être guidé.
            </p>
            <a href="<?= $base ?>/binomage?tab=find" class="campus-profile-btn primary" style="background:#0d9488; border-color:#0d9488; text-decoration:none;">
              Consulter les mentors disponibles →
            </a>
          </div>
        <?php endif; ?>

      <!-- CONTENU ONGLET 2: DEMANDES ENVOYÉES -->
      <?php elseif ($activeTab === 'sent'): ?>
        <?php if (empty($outgoing)): ?>
          <div class="campus-empty-box" style="padding:3rem 1.5rem; text-align:center;">
            <div style="font-size:3rem; margin-bottom:1rem;">📤</div>
            <h3 style="margin:0 0 0.5rem;">Aucune demande de mentorat en attente</h3>
            <p style="color:var(--profile-text-muted); max-width:500px; margin:0 auto 1.25rem;">
              Vos demandes de mentorat envoyées apparaîtront ici avec leur état d'avancement.
            </p>
            <a href="<?= $base ?>/binomage?tab=find" class="campus-profile-btn primary" style="background:#0d9488; border-color:#0d9488; text-decoration:none;">
              Trouver un mentor
            </a>
          </div>
        <?php else: ?>
          <div class="binomage-card-grid">
            <?php foreach ($outgoing as $req): ?>
              <article class="binomage-user-card">
                <div class="binomage-user-header">
                  <div class="binomage-avatar">
                    <?= htmlspecialchars(mb_substr($req['mentor_prenom'], 0, 1) . mb_substr($req['mentor_nom'], 0, 1)) ?>
                  </div>
                  <div class="binomage-user-info">
                    <h3><?= htmlspecialchars($req['mentor_prenom'] . ' ' . $req['mentor_nom']) ?></h3>
                    <div class="binomage-user-sub">
                      <?= htmlspecialchars($req['filiere'] ?? '') ?> · <?= (int)$req['mentor_annee'] ?>ème année
                    </div>
                  </div>
                </div>

                <?php if (!empty($req['message_demande'])): ?>
                  <div class="binomage-message-quote">
                    « <?= htmlspecialchars($req['message_demande']) ?> »
                  </div>
                <?php endif; ?>

                <div style="display:flex; justify-content:space-between; align-items:center;">
                  <span style="font-size:0.8rem; font-weight:700; color:#d97706; background:rgba(217,119,6,0.1); padding:0.25rem 0.65rem; border-radius:9999px;">
                    ⏳ En attente de réponse
                  </span>

                  <form method="post" action="<?= $base ?>/binomage/action">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="action" value="cancel">
                    <input type="hidden" name="relation_id" value="<?= (int)$req['id'] ?>">
                    <button type="submit" class="campus-profile-btn secondary" style="font-size:0.8rem;">
                      Annuler la demande
                    </button>
                  </form>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

      <!-- CONTENU ONGLET 3: TROUVER UN MENTOR -->
      <?php elseif ($activeTab === 'find'): ?>
        <?php if ($mentor): ?>
          <div style="background:rgba(13,148,136,0.1); border:1px solid rgba(13,148,136,0.25); border-radius:14px; padding:1.25rem; margin-bottom:1.5rem; display:flex; align-items:center; justify-content:space-between; gap:1rem;">
            <div>
              <strong>Vous êtes déjà binômé avec <?= htmlspecialchars($mentor['mentor_prenom'] . ' ' . $mentor['mentor_nom']) ?>.</strong>
              <div style="font-size:0.85rem; color:var(--profile-text-muted); margin-top:0.25rem;">
                Le règlement académique prévoit 1 mentor principal par étudiant.
              </div>
            </div>
            <a href="<?= $base ?>/binomage?tab=mentor" class="campus-profile-btn primary" style="background:#0d9488; text-decoration:none;">
              Voir mon mentor
            </a>
          </div>
        <?php endif; ?>

        <?php if (empty($mentors)): ?>
          <div class="campus-empty-box" style="padding:3rem 1.5rem; text-align:center;">
            <div style="font-size:3rem; margin-bottom:1rem;">🔍</div>
            <h3 style="margin:0 0 0.5rem;">Aucun mentor disponible actuellement</h3>
            <p style="color:var(--profile-text-muted); max-width:500px; margin:0 auto;">
              Les aînés de votre filière (<?= htmlspecialchars($userRole['filiere_nom']) ?>) en 2ème ou 3ème année accompagnent déjà leur quota maximum d'étudiants ou n'ont pas encore activé leur profil.
            </p>
          </div>
        <?php else: ?>
          <div class="binomage-card-grid">
            <?php foreach ($mentors as $candidate): ?>
              <article class="binomage-user-card">
                <div>
                  <div class="binomage-user-header">
                    <div class="binomage-avatar">
                      <?php if (!empty($candidate['avatar_url'])): ?>
                        <img src="<?= htmlspecialchars(url('public/' . ltrim($candidate['avatar_url'], '/')), ENT_QUOTES, 'UTF-8') ?>" alt="">
                      <?php else: ?>
                        <?= htmlspecialchars(mb_substr($candidate['prenom'], 0, 1) . mb_substr($candidate['nom'], 0, 1)) ?>
                      <?php endif; ?>
                    </div>
                    <div class="binomage-user-info">
                      <h3><?= htmlspecialchars($candidate['prenom'] . ' ' . $candidate['nom']) ?></h3>
                      <div class="binomage-user-sub">
                        <?= htmlspecialchars($candidate['filiere'] ?? '') ?> · <?= (int)$candidate['annee'] ?>ème année
                      </div>
                    </div>
                  </div>

                  <div style="margin-top:0.75rem; font-size:0.8rem; color:var(--profile-text-muted); display:flex; align-items:center; gap:0.5rem;">
                    <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#10b981;"></span>
                    <span>Accompagne actuellement : <?= (int)$candidate['mentor_count'] ?>/3 mentoré(s)</span>
                  </div>
                </div>

                <div style="display:flex; gap:0.5rem; align-items:center; margin-top:0.5rem;">
                  <a href="<?= $base ?>/profile?id=<?= (int)$candidate['id'] ?>" class="campus-profile-btn secondary" style="flex:1; text-align:center; text-decoration:none; font-size:0.8rem;">
                    Profil
                  </a>

                  <?php if (!$mentor): ?>
                    <button type="button" class="campus-profile-btn primary" onclick="openRequestModal(<?= (int)$candidate['id'] ?>, '<?= htmlspecialchars(addslashes($candidate['prenom'] . ' ' . $candidate['nom'])) ?>')" style="flex:1.6; background:#0d9488; border-color:#0d9488; font-size:0.8rem; justify-content:center;">
                      + Demander
                    </button>
                  <?php endif; ?>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

      <?php endif; ?>

    <?php elseif ($isMentor): ?>
      <!-- ================= VUE MENTOR (2ème ANNÉE ET PLUS) ================= -->
      <nav class="binomage-tabs" role="tablist">
        <a href="<?= $base ?>/binomage?tab=mentees" class="binomage-tab-btn <?= $activeTab === 'mentees' ? 'is-active' : '' ?>">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          <span>Mes mentorés</span>
          <span class="binomage-counter-badge"><?= count($mentees) ?>/3</span>
        </a>
        <a href="<?= $base ?>/binomage?tab=requests" class="binomage-tab-btn <?= $activeTab === 'requests' ? 'is-active' : '' ?>">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
          <span>Demandes reçues</span>
          <?php if (count($incoming) > 0): ?><span class="binomage-counter-badge" style="background:#ef4444; color:#fff;"><?= count($incoming) ?></span><?php endif; ?>
        </a>
      </nav>

      <!-- CONTENU ONGLET 1: MES MENTORÉS -->
      <?php if ($activeTab === 'mentees'): ?>
        <?php if (empty($mentees)): ?>
          <div class="campus-empty-box" style="padding:3rem 1.5rem; text-align:center;">
            <div style="font-size:3rem; margin-bottom:1rem;">🌱</div>
            <h3 style="margin:0 0 0.5rem;">Vous n'accompagnez aucun mentoré pour l'instant</h3>
            <p style="color:var(--profile-text-muted); max-width:500px; margin:0 auto 1.25rem;">
              Les étudiants de 1ère année de votre filière peuvent vous solliciter. Lorsqu'une demande arrive, vous pourrez l'accepter ici.
            </p>
            <?php if (count($incoming) > 0): ?>
              <a href="<?= $base ?>/binomage?tab=requests" class="campus-profile-btn primary" style="background:#0d9488; text-decoration:none;">
                Voir les demandes reçues (<?= count($incoming) ?>) →
              </a>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <div class="binomage-card-grid">
            <?php foreach ($mentees as $mentee): ?>
              <article class="binomage-user-card" style="border:1.5px solid #0d9488;">
                <div>
                  <div class="binomage-user-header">
                    <div class="binomage-avatar">
                      <?php if (!empty($mentee['mentore_avatar'])): ?>
                        <img src="<?= htmlspecialchars(url('public/' . ltrim($mentee['mentore_avatar'], '/')), ENT_QUOTES, 'UTF-8') ?>" alt="">
                      <?php else: ?>
                        <?= htmlspecialchars(mb_substr($mentee['mentore_prenom'], 0, 1) . mb_substr($mentee['mentore_nom'], 0, 1)) ?>
                      <?php endif; ?>
                    </div>
                    <div class="binomage-user-info">
                      <h3><?= htmlspecialchars($mentee['mentore_prenom'] . ' ' . $mentee['mentore_nom']) ?></h3>
                      <div class="binomage-user-sub">
                        1ère année · <?= htmlspecialchars($mentee['filiere'] ?? '') ?>
                      </div>
                    </div>
                  </div>

                  <div style="background:var(--profile-surface); padding:0.75rem; border-radius:10px; font-size:0.825rem; color:var(--profile-text-muted); margin-top:0.75rem;">
                    📅 Binôme actif depuis le <?= htmlspecialchars(date('d/m/Y', strtotime($mentee['date_reponse'] ?: $mentee['date_demande']))) ?>
                  </div>
                </div>

                <div style="display:flex; gap:0.5rem; align-items:center; flex-wrap:wrap;">
                  <a href="<?= $base ?>/messages?user=<?= (int)$mentee['mentore_id'] ?>" class="campus-profile-btn primary" style="flex:1; background:#0d9488; border-color:#0d9488; text-decoration:none; font-size:0.8rem; justify-content:center;">
                    💬 Écrire
                  </a>
                  <a href="<?= $base ?>/profile?id=<?= (int)$mentee['mentore_id'] ?>" class="campus-profile-btn secondary" style="flex:1; text-decoration:none; font-size:0.8rem; text-align:center;">
                    Profil
                  </a>
                  <form method="post" action="<?= $base ?>/binomage/action" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="action" value="terminate">
                    <input type="hidden" name="relation_id" value="<?= (int)$mentee['id'] ?>">
                    <button type="submit" class="campus-profile-btn secondary" style="color:#ef4444; font-size:0.8rem;" onclick="return confirm('Mettre fin au mentorat de cet étudiant ?');">
                      Rompre
                    </button>
                  </form>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

      <!-- CONTENU ONGLET 2: DEMANDES REÇUES -->
      <?php elseif ($activeTab === 'requests'): ?>
        <?php if (empty($incoming)): ?>
          <div class="campus-empty-box" style="padding:3rem 1.5rem; text-align:center;">
            <div style="font-size:3rem; margin-bottom:1rem;">📬</div>
            <h3 style="margin:0 0 0.5rem;">Aucune nouvelle demande de binômage</h3>
            <p style="color:var(--profile-text-muted); max-width:500px; margin:0 auto;">
              Les demandes envoyées par les étudiants de 1ère année s'afficheront ici.
            </p>
          </div>
        <?php else: ?>
          <div class="binomage-card-grid">
            <?php foreach ($incoming as $request): ?>
              <article class="binomage-user-card" style="border:2px solid #38bdf8;">
                <div>
                  <div class="binomage-user-header">
                    <div class="binomage-avatar">
                      <?= htmlspecialchars(mb_substr($request['mentore_prenom'], 0, 1) . mb_substr($request['mentore_nom'], 0, 1)) ?>
                    </div>
                    <div class="binomage-user-info">
                      <h3><?= htmlspecialchars($request['mentore_prenom'] . ' ' . $request['mentore_nom']) ?></h3>
                      <div class="binomage-user-sub">
                        1ère année · <?= htmlspecialchars($request['filiere'] ?? '') ?>
                      </div>
                    </div>
                  </div>

                  <?php if (!empty($request['message_demande'])): ?>
                    <div class="binomage-message-quote" style="margin-top:0.75rem;">
                      « <?= htmlspecialchars($request['message_demande']) ?> »
                    </div>
                  <?php endif; ?>
                </div>

                <div style="display:flex; gap:0.5rem; align-items:center;">
                  <form method="post" action="<?= $base ?>/binomage/action" style="flex:1;">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="action" value="accept">
                    <input type="hidden" name="relation_id" value="<?= (int)$request['id'] ?>">
                    <button type="submit" class="campus-profile-btn primary" style="width:100%; background:#0d9488; border-color:#0d9488; justify-content:center;">
                      ✓ Accepter
                    </button>
                  </form>
                  <form method="post" action="<?= $base ?>/binomage/action" style="flex:1;">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="action" value="refuse">
                    <input type="hidden" name="relation_id" value="<?= (int)$request['id'] ?>">
                    <button type="submit" class="campus-profile-btn secondary" style="width:100%; justify-content:center;">
                      Refuser
                    </button>
                  </form>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>

    <?php else: ?>
      <!-- AUTRE PROFIL (PROFESSEUR, ADMIN OU ÉTUDIANT SANS FILIÈRE) -->
      <div class="campus-empty-box" style="padding:3rem 1.5rem; text-align:center;">
        <div style="font-size:3rem; margin-bottom:1rem;">ℹ️</div>
        <h3 style="margin:0 0 0.5rem;">Programme de Binômage Étudiant</h3>
        <p style="color:var(--profile-text-muted); max-width:550px; margin:0 auto 1.25rem;">
          Le binômage s'adresse aux élèves de l'EPI : les étudiants de 1ère année (mentorés) sont accompagnés par les aînés de 2ème ou 3ème année (mentors) de leur filière respective.
        </p>
        <?php if ($userRole['annee'] === 0 || empty($userRole['filiere_nom'])): ?>
          <p style="color:#0d9488; font-weight:700;">
            Veuillez renseigner votre année et votre filière sur votre profil pour participer au programme.
          </p>
          <a href="<?= $base ?>/profile" class="campus-profile-btn primary" style="background:#0d9488; text-decoration:none;">
            Compléter mon profil
          </a>
        <?php endif; ?>
      </div>
    <?php endif; ?>

  </main>

  <!-- MODALE DE DEMANDE DE MENTORAT (Mentoré -> Mentor) -->
  <div id="quickMentorModal" class="modal-backdrop-profile" role="dialog" aria-modal="true" style="display:none; align-items:center; justify-content:center;">
    <div class="modal-dialog-profile" style="max-width:500px; width:92%; margin:auto;">
      <div class="modal-header-profile">
        <div>
          <h3 style="margin:0 0 4px; font-size:1.15rem; color:var(--profile-text);">Demander comme Mentor</h3>
          <div id="quickMentorCandidateName" style="font-size:0.78rem; color:var(--profile-text-muted);"></div>
        </div>
        <button type="button" class="epi-modal-close" onclick="closeRequestModal();">&times;</button>
      </div>
      <form method="post" action="<?= $base ?>/binomage/action">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
        <input type="hidden" name="action" value="send">
        <input type="hidden" name="mentor_id" id="quickMentorIdInput" value="">
        
        <div class="modal-body-profile">
          <div class="form-group-profile">
            <label for="quickMentorMessage">Message de présentation ou motivation (optionnel) :</label>
            <textarea id="quickMentorMessage" name="message" class="form-control" rows="4" placeholder="Bonjour, je suis en 1ère année et j'aimerais bénéficier de ton mentorat..."></textarea>
          </div>
        </div>
        
        <div class="modal-footer-profile" style="display:flex; justify-content:flex-end; gap:0.5rem; padding:0.75rem 1rem;">
          <button type="button" class="campus-profile-btn secondary" onclick="closeRequestModal();">Annuler</button>
          <button type="submit" class="campus-profile-btn primary" style="background:#0d9488; border-color:#0d9488;">Envoyer ma demande</button>
        </div>
      </form>
    </div>
  </div>

  <script src="<?= htmlspecialchars($asset) ?>/js/theme.js?v=<?= (int)filemtime(ROOT_PATH . '/public/assets/js/theme.js') ?>"></script>
  <script src="<?= htmlspecialchars($asset) ?>/js/feedback.js"></script>
  <script>
    function openRequestModal(mentorId, mentorName) {
      document.getElementById('quickMentorIdInput').value = mentorId;
      document.getElementById('quickMentorCandidateName').textContent = 'Demande adressée à ' + mentorName;
      document.getElementById('quickMentorModal').style.display = 'flex';
    }
    function closeRequestModal() {
      document.getElementById('quickMentorModal').style.display = 'none';
    }
  </script>
</body>
</html>
