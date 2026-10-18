<!-- ============================================================
     POPUP MODAL : Charte d'Utilisation & Politique de Confidentialité
     ============================================================ -->
<div id="policyModal" class="modal-backdrop" aria-hidden="true" role="dialog" aria-labelledby="policyModalTitle">
  <div class="modal-dialog">
    <div class="modal-content">
      
      <!-- En-tête de la modale -->
      <div class="modal-header">
        <div class="modal-title-group">
          <span class="modal-badge">Règlement Officiel</span>
          <h3 id="policyModalTitle">Charte d'Utilisation & Confidentialité</h3>
          <p class="modal-subtitle">École Polytechnique Internationale de Yamoussoukro — EPI Tchach</p>
        </div>
        <button type="button" class="modal-close-btn" id="closePolicyModal" aria-label="Fermer la fenêtre">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
        </button>
      </div>

      <!-- Corps du règlement défilant -->
      <div class="modal-body">
        <div class="policy-alert">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <span>Tout membre inscrit sur EPI Tchach s'engage à respecter les règles académiques et éthiques suivantes.</span>
        </div>

        <section class="policy-section">
          <h4>1. Respect mutuel & Éthique académique</h4>
          <p>EPI Tchach est un espace d'entraide, de travail et de bienveillance. Tout propos haineux, diffamatoire, discriminatoire ou irrespectueux envers un camarade, un délégué ou un professeur est strictement interdit et entraîne l'exclusion immédiate.</p>
        </section>

        <section class="policy-section">
          <h4>2. Utilisation de l'Email Institutionnel</h4>
          <p>L'inscription est réservée aux titulaires d'une adresse officielle de l'école (<code>@epi-yakro.ci</code>). Chaque compte est strictement personnel : vous êtes responsable de toute publication ou commentaire émis depuis votre profil.</p>
        </section>

        <section class="policy-section">
          <h4>3. Partage de Documents & Propriété Intellectuelle</h4>
          <p>Le partage d'annales, de sujets de TP et de fiches de révision est encouragé. Cependant, il est formellement interdit de diffuser des documents sous secret d'examen ou des contenus protégés sans autorisation des enseignants.</p>
        </section>

        <section class="policy-section">
          <h4>4. Confidentialité & Protection des Données</h4>
          <p>Vos informations personnelles (nom, filière, adresse email) ne sont jamais cédées ni vendues à des tiers. Elles sont uniquement utilisées pour l'organisation pédagogique et le fonctionnement des salons d'entraide de l'EPI.</p>
        </section>

        <section class="policy-section">
          <h4>5. Modération & Système de Signalement</h4>
          <p>L'équipe des modérateurs, délégués et enseignants vérifie régulièrement les échanges. Tout utilisateur a la possibilité de signaler un message inapproprié via le bouton de signalement pour traitement immédiat.</p>
        </section>
      </div>

      <!-- Pied de la modale avec accord obligatoire -->
      <div class="modal-footer">
        <label class="modal-checkbox-label">
          <input type="checkbox" id="policyAgreeCheckbox" class="modal-checkbox" />
          <span>J'ai lu et j'accepte l'intégralité de la charte de bonne conduite et de la politique de confidentialité de l'EPI.</span>
        </label>

        <div class="modal-actions">
          <button type="button" class="btn btn-outline btn-sm" id="cancelPolicyModal">Fermer</button>
          <button type="button" class="btn btn-primary btn-sm" id="confirmPolicyAgree" disabled>
            <span>J'accepte et je continue</span>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          </button>
        </div>
      </div>

    </div>
  </div>
</div>
