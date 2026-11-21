<?php
/**
 * resources/views/desktop/partials/report-modal.php
 * Modal interactif universel de signalement (Publication, Commentaire, Profil).
 */
$reportModalBase = $base ?? ($baseUrl ?? '');
?>
<!-- Modale de Signalement Universelle -->
<div id="universalReportModal" class="campus-modal-backdrop" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(15, 23, 42, 0.65); backdrop-filter:blur(4px); align-items:center; justify-content:center; padding:1rem;" aria-hidden="true" role="dialog" aria-labelledby="reportModalTitle">
  <div class="campus-modal-dialog" style="background:var(--admin-card, #ffffff); color:var(--admin-text, #1e293b); width:100%; max-width:520px; border-radius:16px; box-shadow:0 25px 50px -12px rgba(0, 0, 0, 0.25); border:1px solid rgba(226, 232, 240, 0.8); overflow:hidden; animation:reportModalPop 0.25s cubic-bezier(0.16, 1, 0.3, 1);">
    
    <!-- En-tête -->
    <div style="padding:1.25rem 1.5rem; border-bottom:1px solid rgba(226, 232, 240, 0.8); display:flex; align-items:center; justify-content:space-between; background:linear-gradient(to right, rgba(239, 68, 68, 0.04), transparent);">
      <div style="display:flex; align-items:center; gap:0.75rem;">
        <div style="width:38px; height:38px; border-radius:10px; background:#fee2e2; color:#ef4444; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
          <span class="material-symbols-outlined" style="font-size:22px;">flag</span>
        </div>
        <div>
          <h3 id="reportModalTitle" style="margin:0; font-size:1.1rem; font-weight:800; color:#0f172a;">Signaler un contenu</h3>
          <p style="margin:0; font-size:0.8rem; color:#64748b;">Aidez-nous à préserver le respect sur le campus.</p>
        </div>
      </div>
      <button type="button" id="closeReportModalBtn" style="background:none; border:none; color:#94a3b8; cursor:pointer; padding:6px; border-radius:8px; display:flex; align-items:center; justify-content:center; transition:background 0.2s;" aria-label="Fermer">
        <span class="material-symbols-outlined" style="font-size:20px;">close</span>
      </button>
    </div>

    <!-- Formulaire -->
    <form id="universalReportForm" method="post" action="<?= $reportModalBase ?>/signalement/create" style="padding:1.5rem;">
      <input type="hidden" name="cible_id" id="reportCibleId" value="0">
      <input type="hidden" name="type_cible" id="reportTypeCible" value="publication">
      <input type="hidden" name="zone_id" id="reportZoneId" value="">

      <!-- Badge de contexte -->
      <div style="margin-bottom:1.25rem; padding:0.65rem 0.85rem; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; display:flex; align-items:center; gap:0.5rem; font-size:0.85rem;">
        <span style="font-weight:700; color:#475569;" id="reportTargetTypeLabel">Publication :</span>
        <span style="color:#0f172a; font-weight:600; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" id="reportTargetTitleLabel">Contenu</span>
      </div>

      <!-- Motif -->
      <div style="margin-bottom:1.25rem;">
        <label for="reportMotifSelect" style="display:block; font-size:0.85rem; font-weight:700; color:#334155; margin-bottom:0.4rem;">
          Motif du problème <span style="color:#ef4444;">*</span>
        </label>
        <select name="motif" id="reportMotifSelect" required style="width:100%; padding:0.65rem 0.85rem; border:1.5px solid #cbd5e1; border-radius:10px; font-size:0.9rem; font-family:inherit; background:#ffffff; color:#0f172a; outline:none; transition:border-color 0.2s;">
          <option value="" disabled selected>Sélectionnez une raison...</option>
          <option value="Harcèlement / agressivité">Harcèlement / agressivité</option>
          <option value="Spam / publicité">Spam / publicité</option>
          <option value="Contenu inapproprié">Contenu inapproprié</option>
          <option value="Hors sujet / perturbation">Hors sujet / perturbation</option>
          <option value="Usurpation / faux compte">Usurpation / faux compte</option>
          <option value="Autre">Autre problème</option>
        </select>
      </div>

      <!-- Détail complémentaire -->
      <div style="margin-bottom:1.5rem;">
        <label for="reportDetailInput" style="display:block; font-size:0.85rem; font-weight:700; color:#334155; margin-bottom:0.4rem;">
          Détails complémentaires <span style="font-size:0.75rem; font-weight:500; color:#94a3b8;">(optionnel)</span>
        </label>
        <textarea name="detail" id="reportDetailInput" rows="3" placeholder="Expliquez brièvement le problème aux modérateurs..." style="width:100%; padding:0.65rem 0.85rem; border:1.5px solid #cbd5e1; border-radius:10px; font-size:0.88rem; font-family:inherit; color:#0f172a; resize:vertical; outline:none; transition:border-color 0.2s; box-sizing:border-box;"></textarea>
      </div>

      <!-- Note anti-abus -->
      <div style="margin-bottom:1.25rem; font-size:0.75rem; color:#64748b; line-height:1.4;">
        ℹ️ Les signalements abusifs ou répétés sont sanctionnés. Limite de 5 signalements par jour.
      </div>

      <!-- Boutons -->
      <div style="display:flex; align-items:center; justify-content:flex-end; gap:0.75rem;">
        <button type="button" id="cancelReportModalBtn" style="padding:0.6rem 1.1rem; border-radius:10px; font-size:0.88rem; font-weight:700; background:#f1f5f9; color:#475569; border:none; cursor:pointer; transition:background 0.2s;">
          Annuler
        </button>
        <button type="submit" id="submitReportModalBtn" style="padding:0.6rem 1.25rem; border-radius:10px; font-size:0.88rem; font-weight:700; background:#ef4444; color:#ffffff; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:0.5rem; box-shadow:0 4px 12px rgba(239, 68, 68, 0.25); transition:transform 0.15s, background 0.2s;">
          <span class="material-symbols-outlined" style="font-size:18px;">flag</span>
          <span id="submitReportModalBtnText">Envoyer le signalement</span>
        </button>
      </div>
    </form>
  </div>
</div>

<style>
@keyframes reportModalPop {
  0% { opacity:0; transform:scale(0.95) translateY(10px); }
  100% { opacity:1; transform:scale(1) translateY(0); }
}
#universalReportModal button:hover {
  filter: brightness(0.96);
}
</style>

<script>
(function() {
  const modal = document.getElementById('universalReportModal');
  const form = document.getElementById('universalReportForm');
  const closeBtn = document.getElementById('closeReportModalBtn');
  const cancelBtn = document.getElementById('cancelReportModalBtn');
  const submitBtn = document.getElementById('submitReportModalBtn');
  const submitBtnText = document.getElementById('submitReportModalBtnText');

  const cibleIdInput = document.getElementById('reportCibleId');
  const typeCibleInput = document.getElementById('reportTypeCible');
  const zoneIdInput = document.getElementById('reportZoneId');
  const motifSelect = document.getElementById('reportMotifSelect');
  const detailInput = document.getElementById('reportDetailInput');
  const modalTitle = document.getElementById('reportModalTitle');
  const targetTypeLabel = document.getElementById('reportTargetTypeLabel');
  const targetTitleLabel = document.getElementById('reportTargetTitleLabel');

  const openModal = (type, id, zoneId, title) => {
    if (!modal) return;
    type = type || 'publication';
    cibleIdInput.value = id || 0;
    typeCibleInput.value = type;
    zoneIdInput.value = zoneId || '';
    motifSelect.value = '';
    detailInput.value = '';

    const labels = {
      'publication': 'Publication :',
      'commentaire': 'Commentaire :',
      'utilisateur': 'Profil utilisateur :'
    };
    const titles = {
      'publication': 'Signaler une publication',
      'commentaire': 'Signaler un commentaire',
      'utilisateur': 'Signaler ce profil'
    };

    modalTitle.textContent = titles[type] || 'Signaler un contenu';
    targetTypeLabel.textContent = labels[type] || 'Contenu :';
    targetTitleLabel.textContent = title ? (title.length > 50 ? title.substring(0, 47) + '...' : title) : '#' + id;

    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden', 'false');
    motifSelect.focus();
  };

  const closeModal = () => {
    if (!modal) return;
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
  };

  if (closeBtn) closeBtn.addEventListener('click', closeModal);
  if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
  if (modal) {
    modal.addEventListener('click', function(e) {
      if (e.target === modal) closeModal();
    });
  }

  // Écouteur global pour ouvrir la modale depuis n'importe quel bouton déclencheur
  document.addEventListener('click', function(e) {
    const trigger = e.target.closest('.btn-report-trigger, [data-open-report-modal]');
    if (!trigger) return;
    e.preventDefault();
    e.stopPropagation();

    const type = trigger.getAttribute('data-report-type') || 'publication';
    const id = trigger.getAttribute('data-report-id') || 0;
    const zone = trigger.getAttribute('data-report-zone') || '';
    const title = trigger.getAttribute('data-report-title') || '';

    openModal(type, id, zone, title);
  });

  // Soumission AJAX du signalement
  if (form) {
    form.addEventListener('submit', async function(e) {
      e.preventDefault();

      if (!motifSelect.value) {
        if (typeof window.showToast === 'function') {
          window.showToast("Veuillez sélectionner un motif de signalement.", "error");
        } else {
          alert("Veuillez sélectionner un motif de signalement.");
        }
        return;
      }

      submitBtn.disabled = true;
      submitBtnText.textContent = "Transmission...";

      const formData = new FormData(form);
      const postUrl = form.getAttribute('action') || '<?= $reportModalBase ?>/signalement/create';

      try {
        const response = await fetch(postUrl, {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        });

        const data = await response.json();

        if (data.success) {
          if (typeof window.showToast === 'function') {
            window.showToast(data.message || "Signalement transmis à l'équipe de modération.", "success");
          } else {
            alert(data.message || "Signalement transmis.");
          }
          closeModal();
        } else {
          if (typeof window.showToast === 'function') {
            window.showToast(data.message || "Impossible d'envoyer le signalement.", "error");
          } else {
            alert(data.message || "Erreur de signalement.");
          }
        }
      } catch (err) {
        if (typeof window.showToast === 'function') {
          window.showToast("Erreur de communication avec le serveur.", "error");
        } else {
          alert("Erreur réseau.");
        }
      } finally {
        submitBtn.disabled = false;
        submitBtnText.textContent = "Envoyer le signalement";
      }
    });
  }

  // Export pour usage programmatique
  window.openReportModal = openModal;
  window.closeReportModal = closeModal;
})();
</script>
