<?php
$composerZones = is_array($composerZones ?? null) ? $composerZones : [];
$postCategories = is_array($postCategories ?? null) ? $postCategories : [];
if (!$postCategories) {
  foreach ($composerZones as $composerZone) {
    foreach (($composerZone['categories'] ?? []) as $composerCategory) {
      $postCategories[] = [
        'id' => (int)$composerCategory['id'],
        'zone_id' => (int)$composerZone['id'],
        'zone_name' => (string)$composerZone['nom'],
        'name' => (string)$composerCategory['nom'],
      ];
    }
  }
}
$defaultPostCategoryId = (int)($defaultPostCategoryId ?? $categoryId ?? $postCategories[0]['id'] ?? 0);
$composerReturnView = is_string($feedView ?? null) ? $feedView : 'latest';
$composerReturnZone = (int)($selectedZone['id'] ?? 0);
$composerReturnCategory = (int)($categoryId ?? 0);
$composerName = trim((string)($_SESSION['user_prenom'] ?? '') . ' ' . (string)($_SESSION['user_nom'] ?? ''));
$composerAvatar = !empty($_SESSION['user_avatar'])
  ? url('public/' . ltrim((string)$_SESSION['user_avatar'], '/'))
  : '';
$composerInitial = mb_substr((string)($_SESSION['user_prenom'] ?? 'M'), 0, 1, 'UTF-8');
?>
<div id="epiPostModal" class="epi-modal-backdrop" hidden aria-hidden="true" style="display:none;">
  <div class="epi-modal-container epi-create-post-dialog" role="dialog" aria-modal="true" aria-labelledby="modalComposerHeading">
    <div class="epi-modal-header epi-create-post-header">
      <h2 id="modalComposerHeading">Créer une publication</h2>
      <button type="button" class="epi-modal-close" id="closeCreatePostModal" aria-label="Fermer la boîte de dialogue">&times;</button>
    </div>

    <form id="salonPostForm" action="<?= $base ?>/publication/create" method="post" enctype="multipart/form-data" class="epi-modal-form epi-create-post-form">
      <input type="hidden" name="ajax" value="1">
      <input type="hidden" name="return_view" value="<?= htmlspecialchars($composerReturnView, ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="return_zone" value="<?= $composerReturnZone ?>">
      <input type="hidden" name="return_category" value="<?= $composerReturnCategory ?>">
      <select id="publicationType" name="type_publication" aria-label="Type de publication" tabindex="-1" hidden>
        <option value="discussion" selected>Discussion</option>
        <option value="question">Question / sondage</option>
        <option value="annonce">Annonce</option>
      </select>

      <div class="epi-composer-author">
        <span class="epi-composer-avatar" aria-hidden="true">
          <span><?= htmlspecialchars($composerInitial, ENT_QUOTES, 'UTF-8') ?></span>
          <?php if ($composerAvatar !== ''): ?>
            <img src="<?= htmlspecialchars($composerAvatar, ENT_QUOTES, 'UTF-8') ?>" alt="">
          <?php endif; ?>
        </span>
        <div class="epi-composer-author-info">
          <strong><?= htmlspecialchars($composerName !== '' ? $composerName : 'Membre', ENT_QUOTES, 'UTF-8') ?></strong>
          <label class="epi-composer-audience" for="modalCategorySelect" title="La publication sera visible par tous">
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
            <span>Fil public · visible par tous</span>
          </label>
        </div>
      </div>

      <div class="epi-composer-fields">
        <label class="sr-only" for="salonPostTitle">Titre de la publication</label>
        <input class="epi-composer-title" id="salonPostTitle" name="titre" type="text" maxlength="255" required placeholder="Titre de votre publication">
        <label class="sr-only" id="salonContentLabel" for="salonPostContent">Votre message</label>
        <textarea class="epi-composer-content" id="salonPostContent" name="contenu" rows="5" required placeholder="Quoi de neuf<?= $composerName !== '' ? ', ' . htmlspecialchars(explode(' ', $composerName)[0], ENT_QUOTES, 'UTF-8') : '' ?> ?"></textarea>
      </div>

      <div class="epi-composer-metadata">
        <div class="epi-composer-picker" data-composer-picker="tags">
          <label for="publicationTagSearch">Ajouter des tags</label>
          <input id="publicationTagSearch" class="epi-form-control" type="search" maxlength="50" autocomplete="off" placeholder="Rechercher ou proposer un tag…">
          <div class="epi-composer-suggestions" role="listbox" aria-label="Tags disponibles" hidden></div>
          <div class="epi-composer-selected" data-selected-tags aria-live="polite"></div>
          <p class="epi-composer-picker-status" data-tag-status role="status" aria-live="polite"></p>
        </div>
        <div class="epi-composer-picker" data-composer-picker="mentions">
          <label for="publicationMentionSearch">Associer des personnes</label>
          <input id="publicationMentionSearch" class="epi-form-control" type="search" autocomplete="off" placeholder="Rechercher un professeur ou un étudiant…">
          <div class="epi-composer-suggestions" role="listbox" aria-label="Personnes à mentionner" hidden></div>
          <div class="epi-composer-selected" data-selected-mentions aria-live="polite"></div>
          <p class="epi-composer-picker-status" data-mention-status role="status" aria-live="polite"></p>
        </div>
      </div>

      <div class="epi-modal-type-tabs epi-composer-type-tabs" role="group" aria-label="Type de publication">
        <button type="button" class="epi-type-tab is-active" data-type-target="discussion">
          <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          <span>Discussion</span>
        </button>
        <button type="button" class="epi-type-tab" data-type-target="question">
          <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
          <span>Sondage</span>
        </button>
        <button type="button" class="epi-type-tab" data-type-target="annonce">
          <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <span>Annonce</span>
        </button>
      </div>

      <div id="pollOptionsFields" class="poll-options-fields epi-composer-poll" hidden>
        <strong>Choix du sondage</strong>
        <div id="pollOptionsList">
          <input class="epi-form-control" name="options[]" maxlength="255" placeholder="Choix 1" aria-label="Choix 1">
          <input class="epi-form-control" name="options[]" maxlength="255" placeholder="Choix 2" aria-label="Choix 2">
        </div>
        <button class="epi-btn-cancel" type="button" id="addPollOption">+ Ajouter un choix</button>
        <small>De 2 à 10 choix. Chaque membre vote une seule fois.</small>
      </div>

      <div class="epi-composer-tools">
        <strong>Ajouter à votre publication</strong>
        <div class="epi-composer-tool-actions">
          <label class="epi-composer-tool" for="salonPostAttachments" title="Ajouter des photos ou des fichiers">
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
            <span>Photo ou fichier</span>
          </label>
          <label class="epi-composer-tool epi-composer-category" for="modalCategorySelect" title="Choisir une catégorie">
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41 11 3.83V3H4v7h.83l9.58 9.59a2 2 0 0 0 2.83 0l3.35-3.35a2 2 0 0 0 0-2.83Z"/><circle cx="7.5" cy="6.5" r=".5"/></svg>
            <span>Catégorie</span>
            <select id="modalCategorySelect" name="categorie_id" required aria-label="Catégorie de la publication">
              <?php foreach ($postCategories as $postCategory): ?>
                <option value="<?= (int)$postCategory['id'] ?>" data-zone-id="<?= (int)$postCategory['zone_id'] ?>"
                  <?= (int)$postCategory['id'] === $defaultPostCategoryId ? 'selected' : '' ?>>
                  <?= htmlspecialchars($postCategory['name'], ENT_QUOTES, 'UTF-8') ?>
                </option>
              <?php endforeach; ?>
            </select>
          </label>
        </div>
        <input id="salonPostAttachments" class="salon-attachment-input sr-only" type="file" name="pieces_jointes[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.png,.jpg,.jpeg,.gif">
      </div>
      <div class="epi-composer-metadata-inputs" data-composer-metadata-inputs></div>
      <div id="salonAttachmentPreview" class="salon-attachment-preview epi-composer-attachment-preview" aria-live="polite" hidden></div>
      <p id="salonPostFeedback" class="salon-composer-feedback epi-composer-feedback" role="status" aria-live="polite"></p>

      <div class="epi-modal-footer epi-composer-footer">
        <button type="button" class="epi-btn-cancel" id="cancelPostBtn">Annuler</button>
        <button type="submit" class="epi-btn-submit" id="submitPostBtn" disabled>Publier</button>
      </div>
    </form>
  </div>
</div>
<script src="<?= htmlspecialchars($asset, ENT_QUOTES, 'UTF-8') ?>/js/desktop/post-composer.js?v=<?= (int)filemtime(ROOT_PATH . '/public/assets/js/desktop/post-composer.js') ?>" defer></script>
