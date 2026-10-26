<?php
$pageTitle = "Inscription — EPI Tchach";
$base = isset($baseUrl) ? $baseUrl : '';
$asset = isset($assetBase) ? $assetBase : (isset($baseUrl) ? $baseUrl . '/public/assets' : '/public/assets');
$logoPath = $asset . '/images/logo.jpeg';
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
  
  <link rel="stylesheet" href="<?= $asset ?>/css/desktop/auth.css" />
</head>
<body>
  <!-- Bande tricolore -->
  <div class="tricolor-bar"></div>

  <!-- En-tête -->
  <header class="auth-header">
    <a href="<?= $base ?>/landing" class="brand-link">
      <img src="<?= htmlspecialchars($logoPath) ?>" alt="Logo EPI" class="brand-logo-img" onerror="this.src='<?= $asset ?>/images/logo.jpeg'" />
      <span class="brand-title">EPI <span class="brand-accent">Tchach</span></span>
    </a>
    <a href="<?= $base ?>/landing" class="back-link">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
      Retour à l'accueil
    </a>
  </header>

  <!-- Contenu Principal -->
  <main class="auth-main">
    <div class="blob blob-left"></div>
    <div class="blob blob-right"></div>

    <section class="auth-card auth-card-wide">
      <div class="auth-card-header">
        <span class="auth-badge">Inscription Membre</span>
        <h1>Créer un compte</h1>
        <p>Rejoignez la communauté de l'École Polytechnique Internationale</p>
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

      <form action="<?= $base ?>/register" method="POST" class="auth-form">
        <!-- Ligne Nom & Prénom -->
        <div class="form-row">
          <div class="form-group">
            <label for="nom" class="form-label">Nom de famille</label>
            <div class="input-wrapper">
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              <input type="text" id="nom" name="nom" class="form-input" placeholder="Kouassi" required />
            </div>
          </div>

          <div class="form-group">
            <label for="prenom" class="form-label">Prénom</label>
            <div class="input-wrapper">
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              <input type="text" id="prenom" name="prenom" class="form-input" placeholder="Momo" required />
            </div>
          </div>
        </div>

        <!-- Email Institutionnel -->
        <div class="form-group">
          <label for="email" class="form-label">Adresse Email Institutionnelle</label>
          <div class="input-wrapper">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
            <input type="email" id="email" name="email" class="form-input" placeholder="momo.kouassi@epi-yakro.ci" required />
          </div>
        </div>

        <!-- Statut / Rôle dans l'école -->
        <div class="form-group">
          <label for="role" class="form-label">Rôle au sein de l'EPI</label>
          <div class="input-wrapper">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6"/><path d="M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
            <select id="role" name="role" class="form-select" required>
              <option value="etudiant" <?= (($_POST['role'] ?? '') === 'etudiant') ? 'selected' : '' ?>>Étudiant(e)</option>
              <option value="professeur" <?= (($_POST['role'] ?? '') === 'professeur') ? 'selected' : '' ?>>Professeur / Enseignant(e)</option>
            </select>
          </div>
        </div>

        <!-- ========================================== -->
        <!-- BLOC ÉTUDIANT : 1 seule filière obligatoire -->
        <!-- ========================================== -->
        <div id="section-etudiant" class="role-specific-section" style="background: rgba(30, 58, 138, 0.03); border: 1px solid rgba(30, 58, 138, 0.12); border-radius: 12px; padding: 1.1rem; margin-bottom: 1.25rem;">
          <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/><path d="M6 6h10"/><path d="M6 10h10"/></svg>
            <span style="font-size: 0.95rem; font-weight: 700; color: var(--text-dark);">Filière d'études (Obligatoire)</span>
          </div>

          <div class="form-group" style="margin-bottom: 0.75rem;">
            <label for="filiere-select" class="form-label" style="font-size: 0.85rem;">Sélectionnez votre filière</label>
            <div class="input-wrapper">
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
              <select id="filiere-select" name="filiere" class="form-select">
                <option value="">-- Choisir une filière --</option>
                <?php
                // Liste de base avec filières existantes en BDD
                $predefined = ['BTS IDA', 'Licence Informatique'];
                if (!empty($availableFilieres)) {
                    foreach ($availableFilieres as $zf) {
                        $fName = str_ireplace('Filière ', '', $zf['nom']);
                        if (!in_array($fName, $predefined)) {
                            $predefined[] = $fName;
                        }
                    }
                }
                // Ajout de filières représentatives
                $autresSuggestions = ['Master MBDS', 'Génie Civil', 'Génie Électrique & Énergétique', 'Finance & Comptabilité', 'Gestion des Entreprises'];
                foreach ($autresSuggestions as $asg) {
                    if (!in_array($asg, $predefined)) $predefined[] = $asg;
                }

                $selectedFiliere = $_POST['filiere'] ?? '';
                foreach ($predefined as $fil):
                ?>
                  <option value="<?= htmlspecialchars($fil) ?>" <?= ($selectedFiliere === $fil) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($fil) ?>
                  </option>
                <?php endforeach; ?>
                <option value="autre" <?= ($selectedFiliere === 'autre') ? 'selected' : '' ?>>Autre filière (Non listée)...</option>
              </select>
            </div>
          </div>

          <!-- Champ texte si "Autre filière" -->
          <div class="form-group" id="autre-filiere-group" style="display: none; margin-bottom: 0.75rem;">
            <label for="autre_filiere" class="form-label" style="font-size: 0.85rem;">Précisez le nom complet de votre filière</label>
            <div class="input-wrapper">
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              <input type="text" id="autre_filiere" name="autre_filiere" class="form-input" placeholder="Ex: Master Big Data & IA" value="<?= htmlspecialchars($_POST['autre_filiere'] ?? '') ?>" />
            </div>
          </div>

          <p style="font-size: 0.8rem; color: var(--text-muted); margin: 0; line-height: 1.4;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="m9 12 2 2 4-4"/></svg>
            <strong>Attribution automatique :</strong> Si le salon de votre filière existe déjà, vous y entrerez directement. Dans le cas contraire, un signalement est automatiquement envoyé à l'administration pour ouvrir la zone.
          </p>
        </div>

        <!-- ============================================== -->
        <!-- BLOC PROFESSEUR : Plusieurs filières & modules -->
        <!-- ============================================== -->
        <div id="section-professeur" class="role-specific-section" style="display: none; background: rgba(234, 88, 12, 0.04); border: 1px solid rgba(234, 88, 12, 0.15); border-radius: 12px; padding: 1.1rem; margin-bottom: 1.25rem;">
          <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2"><path d="M22 10v6"/><path d="M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
            <span style="font-size: 0.95rem; font-weight: 700; color: var(--text-dark);">Filières & Modules enseignés</span>
          </div>

          <!-- Choix multiple de filières -->
          <div class="form-group" style="margin-bottom: 0.85rem;">
            <label class="form-label" style="font-size: 0.85rem;">Filières dans lesquelles vous dispensez des cours :</label>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.5rem; margin-top: 0.35rem;">
              <?php
              $profFilieresDispo = ['BTS IDA', 'Licence Informatique', 'Master MBDS', 'Génie Civil', 'Finance & Comptabilité'];
              $postProfFilieres = $_POST['prof_filieres'] ?? [];
              foreach ($profFilieresDispo as $pfil):
                  $checked = in_array($pfil, $postProfFilieres) ? 'checked' : '';
              ?>
                <label style="display: flex; align-items: center; gap: 0.45rem; font-size: 0.85rem; font-weight: 500; cursor: pointer; background: #fff; padding: 0.45rem 0.65rem; border-radius: 6px; border: 1px solid #e2e8f0;">
                  <input type="checkbox" name="prof_filieres[]" value="<?= htmlspecialchars($pfil) ?>" <?= $checked ?> />
                  <span><?= htmlspecialchars($pfil) ?></span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Modules enseignés -->
          <div class="form-group" style="margin-bottom: 0.5rem;">
            <label for="modules_enseignes" class="form-label" style="font-size: 0.85rem;">Modules ou matières enseignées</label>
            <div class="input-wrapper">
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
              <input type="text" id="modules_enseignes" name="modules_enseignes" class="form-input" placeholder="Ex: Développement Web, Base de données, Réseaux..." value="<?= htmlspecialchars($_POST['modules_enseignes'] ?? '') ?>" />
            </div>
          </div>

          <p style="font-size: 0.8rem; color: var(--text-muted); margin: 0; line-height: 1.4;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Vous serez automatiquement rattaché à l'<strong>Espace Professeurs</strong> ainsi qu'aux salons de toutes les filières cochées.
          </p>
        </div>

        <!-- Mot de Passe & Confirmation -->
        <div class="form-row">
          <div class="form-group">
            <label for="mot_de_passe" class="form-label">Mot de passe</label>
            <div class="input-wrapper">
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              <input type="password" id="mot_de_passe" name="mot_de_passe" class="form-input" placeholder="8+ caractères" required minlength="8" />
              <button type="button" class="toggle-password" data-target="mot_de_passe" aria-label="Afficher ou masquer">
                <svg class="icon-eye" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                <svg class="icon-eye-off" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" y1="2" x2="22" y2="22"/></svg>
              </button>
            </div>
          </div>

          <div class="form-group">
            <label for="confirmer_mot_de_passe" class="form-label">Confirmer le mot de passe</label>
            <div class="input-wrapper">
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              <input type="password" id="confirmer_mot_de_passe" name="confirmer_mot_de_passe" class="form-input" placeholder="Retapez le mot de passe" required minlength="8" />
              <button type="button" class="toggle-password" data-target="confirmer_mot_de_passe" aria-label="Afficher ou masquer">
                <svg class="icon-eye" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                <svg class="icon-eye-off" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" y1="2" x2="22" y2="22"/></svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Case Règlement -->
        <label class="remember-me" style="margin-top: 0.25rem;">
          <input type="checkbox" name="cgu" id="cguCheckbox" required />
          <span>J'accepte la <a href="#policyModal" class="open-policy-modal" onclick="window.openPolicyModal && window.openPolicyModal(event)" style="color:var(--primary); font-weight:700; text-decoration:underline;">charte d'utilisation</a> et la <a href="#policyModal" class="open-policy-modal" onclick="window.openPolicyModal && window.openPolicyModal(event)" style="color:var(--primary); font-weight:700; text-decoration:underline;">politique de confidentialité</a> de l'EPI.</span>
        </label>

        <button type="submit" class="btn-submit">Créer mon compte</button>
      </form>

      <div class="auth-card-footer">
        <span>Vous avez déjà un compte ?</span>
        <a href="<?= $base ?>/login" class="auth-link">Se connecter</a>
      </div>
    </section>
  </main>

  <footer class="auth-footer">
    © 2026 École Polytechnique Internationale de Yamoussoukro — EPI Tchach
  </footer>

  <!-- Modal Règlement & Charte -->
  <?php include ROOT_PATH . '/views/desktop/partials/policy-modal.php'; ?>

  <script src="<?= $asset ?>/js/desktop/auth.js" defer></script>
</body>
</html>
