<?php

$emptyStateTitle = (string) ($emptyStateTitle ?? 'Aucun élément');
$emptyStateMessage = (string) ($emptyStateMessage ?? '');
$emptyStateLink = (string) ($emptyStateLink ?? '');
$emptyStateLinkLabel = (string) ($emptyStateLinkLabel ?? '');
?>
<div class="notif-empty-state">
  <div class="notif-empty-icon" aria-hidden="true">
    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
  </div>
  <h3><?= htmlspecialchars($emptyStateTitle, ENT_QUOTES, 'UTF-8') ?></h3>
  <p><?= htmlspecialchars($emptyStateMessage, ENT_QUOTES, 'UTF-8') ?></p>
  <?php if ($emptyStateLink !== '' && $emptyStateLinkLabel !== ''): ?>
    <a href="<?= htmlspecialchars($emptyStateLink, ENT_QUOTES, 'UTF-8') ?>" class="notif-btn-nav primary">
      <?= htmlspecialchars($emptyStateLinkLabel, ENT_QUOTES, 'UTF-8') ?>
    </a>
  <?php endif; ?>
</div>
