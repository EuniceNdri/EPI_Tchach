<?php
/**
 * Barre latérale d'administration commune
 * Attend $activeAdminPage (ex: 'dashboard', 'users', 'moderation', 'categories', 'settings')
 */
$base = isset($baseUrl) ? $baseUrl : '';
$logoFile = file_exists(ROOT_PATH . '/public/assets/images/logo.jpeg') 
    ? ($base . '/public/assets/images/logo.jpeg') 
    : ($base . '/assets/images/logo.jpeg');
$currentPage = $activeAdminPage ?? 'dashboard';
?>
<aside class="admin-sidebar">
  <a href="<?= $base ?>/admin" class="sidebar-brand-block">
    <img src="<?= htmlspecialchars($logoFile) ?>" alt="Logo EPI" class="admin-logo-img" onerror="this.src='public/assets/images/logo.jpeg'" />
    <div class="admin-brand-text">
      <div class="admin-brand-title">EPI <span>Tchach</span></div>
      <div class="admin-badge-sub">Espace Administration</div>
    </div>
  </a>

  <nav class="sidebar-menu-nav">
    <div class="nav-section-title">Général</div>
    <a href="<?= $base ?>/admin" class="sidebar-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
      <span>Tableau de Bord</span>
    </a>

    <div class="nav-section-title">Gestion de l'École</div>
    <a href="<?= $base ?>/admin/users" class="sidebar-link <?= $currentPage === 'users' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      <span>Utilisateurs</span>
      <span class="link-counter" style="background:rgba(56,189,248,0.2); color:#38bdf8;">1 248</span>
    </a>

    <a href="<?= $base ?>/admin/moderation" class="sidebar-link <?= $currentPage === 'moderation' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      <span>Modération</span>
      <span class="link-counter">3</span>
    </a>

    <a href="<?= $base ?>/admin/categories" class="sidebar-link <?= $currentPage === 'categories' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      <span>Filières & Salons</span>
    </a>

    <div class="nav-section-title">Plateforme</div>
    <a href="<?= $base ?>/admin/settings" class="sidebar-link <?= $currentPage === 'settings' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
      <span>Paramètres</span>
    </a>

    <a href="<?= $base ?>/landing" class="sidebar-link" target="_blank">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
      <span>Voir le Forum</span>
    </a>

    <a href="<?= $base ?>/profile" class="sidebar-link">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      <span>Mon Profil</span>
    </a>
  </nav>

  <div class="sidebar-footer-card">
    <div class="admin-avatar">AD</div>
    <div class="admin-user-info">
      <div class="admin-user-name">Directeur Scolarité</div>
      <div class="admin-user-role">Super Administrateur</div>
    </div>
  </div>
</aside>

