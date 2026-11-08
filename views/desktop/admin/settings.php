<?php
$activeAdminPage = 'settings';
$base = isset($baseUrl) ? $baseUrl : '';
$asset = isset($assetBase) ? $assetBase : (isset($baseUrl) ? $baseUrl . '/public/assets' : '/public/assets');
$heroSlides = $landingSettings['hero_slides'] ?? LandingContent::defaultHeroSlides();
$stats = $landingSettings['stats'] ?? LandingContent::defaultStats();
$filiereImages = [
  'IDA' => 'images/filiere-industrielle.jpg',
  'LIC-INFO' => 'images/filiere-industrielle.jpg',
  'GM' => 'images/filiere-industrielle.jpg',
  'GC' => 'images/filiere-industrielle.jpg',
  'FCGE' => 'images/filiere-tertiaire.jpg',
];
$imageUrl = static function (?string $path) use ($asset): string {
  $path = ltrim((string)$path, '/');
  if (strpos($path, 'uploads/') === 0) {
    return rtrim((string)preg_replace('#/assets$#', '', $asset), '/') . '/' . $path;
  }
  return rtrim($asset, '/') . '/' . $path;
};
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Administration — Contenu de la landing · EPI Tchach</title>
  <link rel="icon" type="image/svg+xml" href="<?= htmlspecialchars($asset, ENT_QUOTES, 'UTF-8') ?>/images/logo_epi.svg" />
  <link rel="apple-touch-icon" href="<?= htmlspecialchars($asset, ENT_QUOTES, 'UTF-8') ?>/images/logo.jpeg" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="<?= htmlspecialchars($asset, ENT_QUOTES, 'UTF-8') ?>/css/desktop/admin.css" />
  <link rel="stylesheet" href="<?= htmlspecialchars($asset, ENT_QUOTES, 'UTF-8') ?>/css/feedback.css" />
  <style>
    .landing-editor-list { display:grid; gap:1rem; }
    .landing-editor-row { display:grid; grid-template-columns:150px minmax(0,1fr); gap:1rem; padding:1rem; border:1px solid var(--admin-border); border-radius:var(--radius-sm); }
    .landing-editor-row img { width:100%; height:110px; object-fit:cover; border-radius:8px; background:#f1f5f9; }
    .landing-editor-fields { display:grid; grid-template-columns:1fr 1fr; gap:.75rem; }
    .landing-editor-fields label { display:grid; gap:.35rem; font-size:.8rem; font-weight:700; }
    .landing-editor-fields input, .landing-editor-fields textarea { width:100%; box-sizing:border-box; padding:.65rem .75rem; border:1px solid var(--admin-border); border-radius:8px; font:inherit; }
    .landing-editor-fields .full-width { grid-column:1 / -1; }
    .landing-editor-help { color:var(--admin-muted); font-size:.82rem; }
    .landing-stats-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:1rem; }
    .landing-stat-editor { display:grid; gap:.75rem; padding:1rem; border:1px solid var(--admin-border); border-radius:var(--radius-sm); }
    .landing-stat-editor label { display:grid; gap:.35rem; font-size:.8rem; font-weight:700; }
    .landing-stat-editor input { width:100%; box-sizing:border-box; padding:.65rem .75rem; border:1px solid var(--admin-border); border-radius:8px; font:inherit; }
    @media (max-width:760px) {
      .landing-editor-row { grid-template-columns:1fr; }
      .landing-editor-row img { max-width:240px; }
      .landing-editor-fields, .landing-stats-grid { grid-template-columns:1fr; }
    }
  </style>
  <?php include ROOT_PATH . '/views/partials/theme-head.php'; ?>
</head>
<body>
  <?php include ROOT_PATH . '/views/partials/theme-toggle.php'; ?>
  <?php include ROOT_PATH . '/views/partials/feedback-toast.php'; ?>
  <div class="admin-wrapper">
    <?php include ROOT_PATH . '/views/desktop/partials/admin-sidebar.php'; ?>

    <main class="admin-main-area">
      <header class="admin-topbar">
        <div class="topbar-left"><h1 class="page-title">Contenu de la page d’accueil</h1></div>
        <div class="topbar-right"><a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/landing" class="btn-table-action">Voir la landing</a></div>
      </header>

      <div class="admin-content" style="max-width:76rem;">
        <form id="adminSettingsForm" action="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/admin/settings" method="POST" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:1.5rem;">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_settings_csrf'], ENT_QUOTES, 'UTF-8') ?>" />

          <section class="admin-card">
            <div class="card-header-flex">
              <div>
                <h2 class="card-title">Filières affichées sur la landing</h2>
                <p class="landing-editor-help">Les modifications sont visibles sur ordinateur et téléphone. Images JPG, PNG ou WebP, 5 Mo maximum.</p>
              </div>
            </div>
            <div class="card-body landing-editor-list">
              <?php if (!$filieres): ?>
                <p class="landing-editor-help">Aucune filière active à configurer. Vérifiez que la table des filières contient des filières actives.</p>
              <?php endif; ?>
              <?php foreach ($filieres as $filiere): ?>
                <?php
                  $filiereId = (int)$filiere['id'];
                  $code = strtoupper(trim((string)($filiere['code'] ?? '')));
                  $currentImage = trim((string)($filiere['image_url'] ?? ''));
                  if ($currentImage === '') {
                    $currentImage = $filiereImages[$code] ?? 'images/filiere-entraide.jpg';
                  }
                ?>
                <article class="landing-editor-row">
                  <div>
                    <img src="<?= htmlspecialchars($imageUrl($currentImage), ENT_QUOTES, 'UTF-8') ?>" alt="Visuel actuel de <?= htmlspecialchars($filiere['nom'], ENT_QUOTES, 'UTF-8') ?>" data-image-preview />
                    <label class="landing-editor-help" style="display:block;margin-top:.5rem;">
                      Remplacer la photo
                      <input type="file" name="filiere_images[<?= $filiereId ?>]" accept="image/jpeg,image/png,image/webp" data-image-input />
                    </label>
                  </div>
                  <div class="landing-editor-fields">
                    <strong class="full-width"><?= htmlspecialchars($filiere['nom'], ENT_QUOTES, 'UTF-8') ?><?= !empty($filiere['code']) ? ' · ' . htmlspecialchars($filiere['code'], ENT_QUOTES, 'UTF-8') : '' ?></strong>
                    <label class="full-width">Slogan
                      <input type="text" name="filieres[<?= $filiereId ?>][slogan]" maxlength="180" value="<?= htmlspecialchars((string)($filiere['slogan'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Ex. Construire les idées de demain" />
                    </label>
                    <label class="full-width">Description
                      <textarea name="filieres[<?= $filiereId ?>][description]" rows="3" maxlength="2000"><?= htmlspecialchars((string)($filiere['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </label>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
          </section>

          <section class="admin-card">
            <div class="card-header-flex">
              <div>
                <h2 class="card-title">Photos et légendes du bandeau principal</h2>
                <p class="landing-editor-help">Ajoutez, retirez ou modifiez jusqu’à 5 diapositives du carrousel affiché sur ordinateur et mobile.</p>
              </div>
              <button type="button" class="btn-table-action" id="addHeroSlide">Ajouter une photo</button>
            </div>
            <div class="card-body landing-editor-list" id="heroSlides">
              <?php foreach ($heroSlides as $index => $slide): ?>
                <?php $slideImage = (string)($slide['image'] ?? ''); ?>
                <article class="landing-editor-row" data-slide-row>
                  <div>
                    <img src="<?= htmlspecialchars($imageUrl($slideImage), ENT_QUOTES, 'UTF-8') ?>" alt="Aperçu de la photo du bandeau" data-image-preview />
                    <label class="landing-editor-help" style="display:block;margin-top:.5rem;">
                      Remplacer la photo
                      <input type="file" name="hero_images[<?= (int)$index ?>]" accept="image/jpeg,image/png,image/webp" data-image-input />
                    </label>
                    <input type="hidden" name="hero_slides[<?= (int)$index ?>][image]" value="<?= htmlspecialchars($slideImage, ENT_QUOTES, 'UTF-8') ?>" />
                  </div>
                  <div class="landing-editor-fields">
                    <label class="full-width">Titre
                      <input type="text" name="hero_slides[<?= (int)$index ?>][title]" maxlength="120" required value="<?= htmlspecialchars((string)($slide['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
                    </label>
                    <label class="full-width">Légende
                      <input type="text" name="hero_slides[<?= (int)$index ?>][caption]" maxlength="180" value="<?= htmlspecialchars((string)($slide['caption'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
                    </label>
                    <button type="button" class="btn-table-action full-width" data-remove-slide>Retirer cette photo</button>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
          </section>

          <section class="admin-card">
            <div class="card-header-flex">
              <div>
                <h2 class="card-title">Statistiques de la landing</h2>
                <p class="landing-editor-help">Ces quatre indicateurs sont affichés dans les versions ordinateur et mobile.</p>
              </div>
            </div>
            <div class="card-body landing-stats-grid">
              <?php foreach ($stats as $index => $stat): ?>
                <div class="landing-stat-editor">
                  <strong>Indicateur <?= (int)$index + 1 ?></strong>
                  <label>Valeur numérique
                    <input type="number" name="stats[<?= (int)$index ?>][value]" min="0" max="999999999" required value="<?= htmlspecialchars((string)($stat['value'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
                  </label>
                  <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                    <label>Préfixe (optionnel)
                      <input type="text" name="stats[<?= (int)$index ?>][prefix]" maxlength="8" value="<?= htmlspecialchars((string)($stat['prefix'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
                    </label>
                    <label>Suffixe (optionnel)
                      <input type="text" name="stats[<?= (int)$index ?>][suffix]" maxlength="8" value="<?= htmlspecialchars((string)($stat['suffix'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
                    </label>
                  </div>
                  <label>Libellé
                    <input type="text" name="stats[<?= (int)$index ?>][label]" maxlength="80" required value="<?= htmlspecialchars((string)($stat['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
                  </label>
                  <label>Description
                    <input type="text" name="stats[<?= (int)$index ?>][description]" maxlength="150" value="<?= htmlspecialchars((string)($stat['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
                  </label>
                </div>
              <?php endforeach; ?>
            </div>
          </section>

          <div style="display:flex;justify-content:flex-end;">
            <button type="submit" class="btn-table-action" style="background:var(--admin-primary);color:#fff;padding:.85rem 1.5rem;font-size:.95rem;border-radius:var(--radius-sm);">
              Enregistrer le contenu de la landing
            </button>
          </div>
        </form>

        <template id="heroSlideTemplate">
          <article class="landing-editor-row" data-slide-row>
            <div>
              <img src="" alt="Aperçu de la nouvelle photo" data-image-preview />
              <label class="landing-editor-help" style="display:block;margin-top:.5rem;">
                Photo (obligatoire)
                <input type="file" name="hero_images[__INDEX__]" accept="image/jpeg,image/png,image/webp" data-image-input required />
              </label>
              <input type="hidden" name="hero_slides[__INDEX__][image]" value="" />
            </div>
            <div class="landing-editor-fields">
              <label class="full-width">Titre
                <input type="text" name="hero_slides[__INDEX__][title]" maxlength="120" required />
              </label>
              <label class="full-width">Légende
                <input type="text" name="hero_slides[__INDEX__][caption]" maxlength="180" />
              </label>
              <button type="button" class="btn-table-action full-width" data-remove-slide>Retirer cette photo</button>
            </div>
          </article>
        </template>
      </div>
    </main>
  </div>

  <script>
    (() => {
      const slides = document.getElementById("heroSlides");
      const template = document.getElementById("heroSlideTemplate");
      let nextIndex = Math.max(-1, ...Array.from(slides.querySelectorAll('input[name^="hero_slides["]'))
        .map((input) => Number(input.name.match(/\[(\d+)\]/)?.[1] ?? -1))) + 1;

      document.getElementById("addHeroSlide").addEventListener("click", () => {
        if (slides.querySelectorAll("[data-slide-row]").length >= 5) {
          window.alert("Le carrousel peut contenir au maximum 5 photos.");
          return;
        }
        slides.insertAdjacentHTML("beforeend", template.innerHTML.replaceAll("__INDEX__", String(nextIndex++)));
      });

      slides.addEventListener("click", (event) => {
        const removeButton = event.target.closest("[data-remove-slide]");
        if (removeButton && slides.querySelectorAll("[data-slide-row]").length > 1) {
          removeButton.closest("[data-slide-row]").remove();
        }
      });

      slides.addEventListener("change", (event) => {
        const input = event.target.closest("[data-image-input]");
        if (!input || !input.files?.[0]) return;
        const preview = input.closest("[data-slide-row]").querySelector("[data-image-preview]");
        preview.src = URL.createObjectURL(input.files[0]);
      });

      document.querySelectorAll('input[data-image-input][name^="filiere_images"]').forEach((input) => {
        input.addEventListener("change", () => {
          if (input.files?.[0]) {
            input.closest(".landing-editor-row").querySelector("[data-image-preview]").src = URL.createObjectURL(input.files[0]);
          }
        });
      });
    })();
  </script>
  <script src="<?= htmlspecialchars($asset, ENT_QUOTES, 'UTF-8') ?>/js/desktop/admin.js"></script>
  <script src="<?= htmlspecialchars($asset, ENT_QUOTES, 'UTF-8') ?>/js/feedback.js" defer></script>
</body>
</html>
