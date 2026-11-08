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
?>
<div id="epiPostModal" class="epi-modal-backdrop" hidden aria-hidden="true" style="display:none;">
  <div class="epi-modal-container" role="dialog" aria-modal="true" aria-labelledby="modalComposerHeading">
    <div class="epi-modal-header">
      <div class="epi-modal-title-group">
        <h2 id="modalComposerHeading">Créer une publication</h2>
        <div style="font-size:0.78rem; color:var(--community-muted);">Partagez vos questions, ressources et idées avec l'école</div>
      </div>
      <button type="button" class="epi-modal-close" id="closeCreatePostModal" aria-label="Fermer la boîte de dialogue">&times;</button>
    </div>

    <div class="epi-modal-type-tabs">
      <button type="button" class="epi-type-tab is-active" data-type-target="discussion">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        <span>Discussion</span>
      </button>
      <button type="button" class="epi-type-tab" data-type-target="question">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        <span>Question / Sondage</span>
      </button>
      <button type="button" class="epi-type-tab" data-type-target="annonce">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        <span>Annonce</span>
      </button>
    </div>

    <form id="salonPostForm" action="<?= $base ?>/publication/create" method="post" enctype="multipart/form-data" class="epi-modal-form">
      <input type="hidden" name="ajax" value="1">
      <input type="hidden" name="return_view" value="<?= htmlspecialchars($composerReturnView, ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="return_zone" value="<?= $composerReturnZone ?>">
      <input type="hidden" name="return_category" value="<?= $composerReturnCategory ?>">

      <select class="epi-form-control" id="publicationType" name="type_publication" style="display:none;" aria-label="Type de publication">
        <option value="discussion" selected>Discussion</option>
        <option value="question">Question / sondage</option>
        <option value="annonce">Annonce</option>
      </select>

      <div>
        <label style="display:block; font-size:0.8rem; font-weight:700; color:var(--community-muted); margin-bottom:0.35rem;" for="modalCategorySelect">Choisir un salon de destination</label>
        <select class="epi-form-control" id="modalCategorySelect" name="categorie_id" required>
          <?php foreach ($postCategories as $postCategory): ?>
            <option value="<?= (int)$postCategory['id'] ?>" data-zone-id="<?= (int)$postCategory['zone_id'] ?>"
              <?= (int)$postCategory['id'] === $defaultPostCategoryId ? 'selected' : '' ?>>
              <?= htmlspecialchars($postCategory['zone_name'] . ' · ' . $postCategory['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label style="display:block; font-size:0.8rem; font-weight:700; color:var(--community-muted); margin-bottom:0.35rem;" for="salonPostTitle">Titre de la publication</label>
        <input class="epi-form-control" id="salonPostTitle" name="titre" type="text" maxlength="255" required placeholder="Titre descriptif et clair">
      </div>

      <div>
        <label style="display:block; font-size:0.8rem; font-weight:700; color:var(--community-muted); margin-bottom:0.35rem;" id="salonContentLabel" for="salonPostContent">Votre message</label>
        <textarea class="epi-form-control" id="salonPostContent" name="contenu" rows="5" required placeholder="Partagez une question, une idée ou une ressource avec la communauté..."></textarea>
      </div>

      <div id="pollOptionsFields" class="poll-options-fields" hidden style="background:var(--community-raised); border:1px solid var(--community-line); padding:1rem; border-radius:var(--community-radius-md);">
        <strong style="display:block; font-size:0.85rem; margin-bottom:0.5rem;">Choix du sondage</strong>
        <div id="pollOptionsList" style="display:flex; flex-direction:column; gap:0.5rem;">
          <input class="epi-form-control" name="options[]" maxlength="255" placeholder="Choix 1" aria-label="Choix 1">
          <input class="epi-form-control" name="options[]" maxlength="255" placeholder="Choix 2" aria-label="Choix 2">
        </div>
        <button class="epi-btn-cancel" type="button" id="addPollOption" style="margin-top:0.6rem; font-size:0.8rem; padding:0.35rem 0.85rem;">+ Ajouter un choix</button>
        <div style="font-size:0.75rem; color:var(--community-muted); margin-top:0.4rem;">De 2 à 10 choix. Chaque membre vote une seule fois.</div>
      </div>

      <div>
        <label style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.6rem 1rem; border-radius:var(--community-radius-pill); border:1px dashed var(--community-line); cursor:pointer; background:var(--community-raised); font-size:0.85rem;" for="salonPostAttachments">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
          <span>Ajouter des fichiers ou des images (PDF, photos, etc.)</span>
        </label>
        <input id="salonPostAttachments" class="salon-attachment-input" type="file" name="pieces_jointes[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.png,.jpg,.jpeg,.gif" style="display:none;">
        <div id="salonAttachmentPreview" class="salon-attachment-preview" aria-live="polite" hidden style="margin-top:0.75rem; display:flex; flex-wrap:wrap; gap:0.5rem;"></div>
      </div>

      <p id="salonPostFeedback" class="salon-composer-feedback" role="status" aria-live="polite" style="font-size:0.85rem; color:var(--community-orange); margin:0;"></p>
      <div class="epi-modal-footer">
        <button type="button" class="epi-btn-cancel" id="cancelPostBtn">Annuler</button>
        <button type="submit" class="epi-btn-submit" id="submitPostBtn">Publier</button>
      </div>
    </form>
  </div>
</div>
