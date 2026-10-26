<?php
$base = isset($baseUrl) ? $baseUrl : '';
$asset = isset($assetBase) ? $assetBase : (isset($baseUrl) ? $baseUrl . '/public/assets' : '/public/assets');
$logoFile = $asset . '/images/logo.jpeg';

$sessionNom = $_SESSION['user_nom'] ?? 'Kouassi';
$sessionPrenom = $_SESSION['user_prenom'] ?? 'Momo';
$sessionEmail = $_SESSION['user_email'] ?? 'momo.kouassi@epi-yakro.ci';
$initials = strtoupper(substr($sessionPrenom, 0, 1) . substr($sessionNom, 0, 1));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Modifier mon Profil — <?= htmlspecialchars($sessionPrenom . ' ' . $sessionNom) ?> · EPI Tchach</title>
  <meta name="description" content="Modifier les informations de profil et paramètres du compte sur EPI Tchach" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="<?= $asset ?>/css/desktop/profile.css" />
</head>
<body>
  <div class="tricolor-bar"></div>

  <!-- Barre de navigation -->
  <header class="profile-header">
    <div class="profile-nav">
      <a href="<?= $base ?>/landing" class="brand-group">
        <img src="<?= htmlspecialchars($logoFile) ?>" alt="Logo EPI" class="brand-logo-img" onerror="this.src='<?= $asset ?>/images/logo.jpeg'" />
        <div>
          <span class="brand-title">EPI <span class="brand-accent">Tchach</span></span>
          <div class="brand-subtitle">Édition Profil</div>
        </div>
      </a>

      <div class="nav-user-actions">
        <a href="<?= $base ?>/profile" class="btn btn-outline btn-sm">← Retour au Profil</a>
        <a href="<?= $base ?>/landing" class="btn btn-outline btn-sm">Accueil</a>
      </div>
    </div>
  </header>

  <main class="profile-container" style="max-width: 60rem;">
    <div style="margin-bottom: 2rem;">
      <h1 style="font-size: 2rem; font-weight: 800;">Paramètres & Profil</h1>
      <p style="color: var(--muted-foreground); margin-top: 0.35rem;">Gérez vos informations académiques, vos coordonnées et la sécurité de votre compte.</p>
    </div>

    <form id="profileEditForm" class="profile-tabs-card" style="padding: 2.25rem;">
      <!-- Photo de profil -->
      <div style="display:flex; align-items:center; gap:1.75rem; margin-bottom:2.25rem; padding-bottom:2rem; border-bottom:1px solid var(--border);">
        <div class="profile-avatar-wrapper" style="width:90px; height:90px;">
          <div class="profile-avatar" id="avatarInitials" style="font-size:1.85rem;"><?= htmlspecialchars($initials) ?></div>
          <img id="avatarPreviewImg" style="display:none; width:100%; height:100%; border-radius:50%; object-fit:cover;" alt="Avatar" />
        </div>
        <div>
          <h3 style="font-size:1.1rem; font-weight:800; margin-bottom:0.25rem;">Photo de profil</h3>
          <p style="font-size:0.85rem; color:var(--muted-foreground); margin-bottom:0.85rem;">Format JPG, PNG ou WEBP. Résolution conseillée 400x400px.</p>
          <div style="display:flex; gap:0.75rem;">
            <label for="avatarUpload" class="btn btn-primary btn-sm" style="cursor:pointer;">
              <span>Changer la photo</span>
              <input type="file" id="avatarUpload" accept="image/*" style="display:none;" />
            </label>
            <button type="button" class="btn btn-outline btn-sm" onclick="alert('Photo réinitialisée par défaut.');">Supprimer</button>
          </div>
        </div>
      </div>

      <!-- Informations Personnelles & Académiques -->
      <h3 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 1.25rem; color: var(--primary);">
        1. Informations Académiques
      </h3>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label" for="nom">Nom de famille</label>
          <input type="text" id="nom" class="form-control" value="<?= htmlspecialchars($sessionNom) ?>" required />
        </div>

        <div class="form-group">
          <label class="form-label" for="prenom">Prénom</label>
          <input type="text" id="prenom" class="form-control" value="<?= htmlspecialchars($sessionPrenom) ?>" required />
        </div>

        <div class="form-group">
          <label class="form-label" for="email">Adresse Email Institutionnelle</label>
          <input type="email" id="email" class="form-control" value="<?= htmlspecialchars($sessionEmail) ?>" required />
        </div>

        <div class="form-group">
          <label class="form-label" for="email">Email Institutionnel</label>
          <input type="email" id="email" class="form-control" value="momo.kouassi@epi-yakro.ci" readonly style="background:#f8fafc; cursor:not-allowed;" title="L'adresse institutionnelle ne peut être modifiée que par l'administration" />
        </div>

        <div class="form-group">
          <label class="form-label" for="matricule">Matricule Étudiant</label>
          <input type="text" id="matricule" class="form-control" value="EPI-2024-IDA-042" readonly style="background:#f8fafc; cursor:not-allowed;" />
        </div>

        <div class="form-group">
          <label class="form-label" for="filiere">Filière / Spécialité</label>
          <select id="filiere" class="form-control">
            <option value="ida" selected>BTS IDA (Informatique Développeur d'Applications)</option>
            <option value="batiment">BTS Génie Civil - Bâtiment</option>
            <option value="tp">BTS Génie Civil - Travaux Publics</option>
            <option value="mines">BTS Mines & Géologie</option>
            <option value="fcge">BTS Finance Comptabilité (FCGE)</option>
            <option value="rhcom">BTS Ressources Humaines & Communication</option>
            <option value="licence-info">Licence Informatique & Réseaux</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="niveau">Niveau d'étude</label>
          <select id="niveau" class="form-control">
            <option value="1">1ère Année</option>
            <option value="2" selected>2ème Année</option>
            <option value="3">3ème Année (Licence)</option>
            <option value="master">Master</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="telephone">Numéro WhatsApp / Téléphone</label>
          <input type="tel" id="telephone" class="form-control" value="+225 07 00 00 00 00" placeholder="+225 07..." />
        </div>

        <div class="form-group">
          <label class="form-label" for="role_display">Rôle déclaré</label>
          <input type="text" id="role_display" class="form-control" value="Délégué de promotion (BTS IDA 2)" readonly style="background:#f8fafc; cursor:not-allowed;" />
        </div>

        <div class="form-group form-group-full">
          <label class="form-label" for="bio">Biographie / Présentation</label>
          <textarea id="bio" class="form-control" rows="3">Passionné de développement web fullstack, base de données et architecture MVC. Prêt à s'entraider pour réussir les partiels et examens du BTS 2026 !</textarea>
        </div>
      </div>

      <!-- Sécurité & Mot de passe -->
      <h3 style="font-size: 1.25rem; font-weight: 800; margin-top: 2.5rem; margin-bottom: 1.25rem; color: var(--accent);">
        2. Sécurité du Compte & Mot de Passe
      </h3>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label" for="old_pass">Mot de passe actuel</label>
          <input type="password" id="old_pass" class="form-control" placeholder="••••••••" />
        </div>

        <div class="form-group">
          <label class="form-label" for="new_pass">Nouveau mot de passe</label>
          <input type="password" id="new_pass" class="form-control" placeholder="Au moins 8 caractères" />
        </div>

        <div class="form-group form-group-full">
          <label class="form-label" for="confirm_pass">Confirmer le nouveau mot de passe</label>
          <input type="password" id="confirm_pass" class="form-control" placeholder="Retapez le nouveau mot de passe" />
        </div>
      </div>

      <!-- Préférences de notification -->
      <h3 style="font-size: 1.25rem; font-weight: 800; margin-top: 2.5rem; margin-bottom: 1.25rem; color: var(--foreground);">
        3. Notifications & Alertes
      </h3>

      <div style="display:flex; flex-direction:column; gap:0.85rem; margin-bottom:2rem;">
        <label style="display:flex; align-items:center; gap:0.75rem; font-size:0.95rem; cursor:pointer;">
          <input type="checkbox" checked style="width:1.2rem; height:1.2rem; accent-color:var(--primary);" />
          <span>Recevoir un email lorsqu'un enseignant ou camarade répond à mes sujets.</span>
        </label>
        <label style="display:flex; align-items:center; gap:0.75rem; font-size:0.95rem; cursor:pointer;">
          <input type="checkbox" checked style="width:1.2rem; height:1.2rem; accent-color:var(--primary);" />
          <span>M'alerter des annonces officielles et dates d'examens publiées par la direction de l'EPI.</span>
        </label>
        <label style="display:flex; align-items:center; gap:0.75rem; font-size:0.95rem; cursor:pointer;">
          <input type="checkbox" checked style="width:1.2rem; height:1.2rem; accent-color:var(--primary);" />
          <span>Afficher mon statut "En ligne" aux membres de ma promotion.</span>
        </label>
      </div>

      <div style="display:flex; align-items:center; justify-content:flex-end; gap:1rem; border-top:1px solid var(--border); padding-top:1.5rem;">
        <a href="<?= $base ?>/profile" class="btn btn-outline">Annuler</a>
        <button type="submit" class="btn btn-primary">
          <span>Enregistrer les modifications</span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        </button>
      </div>
    </form>
  </main>

  <footer class="profile-footer">
    <p>© 2026 École Polytechnique Internationale de Yamoussoukro — EPI Tchach</p>
  </footer>

  <script src="<?= $asset ?>/js/desktop/profile.js"></script>
</body>
</html>

