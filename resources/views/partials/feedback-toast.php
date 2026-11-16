<?php
$feedbackError = (string)($error ?? $flashError ?? $_SESSION['flash_error'] ?? '');
$feedbackSuccess = (string)($success ?? $flashSuccess ?? $_SESSION['flash_success'] ?? '');
$feedbackMessage = $feedbackError !== '' ? $feedbackError : $feedbackSuccess;
$feedbackType = $feedbackError !== '' ? 'error' : 'success';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<?php if ($feedbackMessage !== ''): ?>
  <div class="feedback-toast feedback-toast-<?= $feedbackType ?>" data-feedback-toast data-dismiss-after="<?= $feedbackType === 'success' ? '5500' : '8000' ?>" role="<?= $feedbackType === 'error' ? 'alert' : 'status' ?>" aria-live="<?= $feedbackType === 'error' ? 'assertive' : 'polite' ?>">
    <div class="feedback-toast-icon" aria-hidden="true">
      <?php if ($feedbackType === 'error'): ?>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <?php else: ?>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
      <?php endif; ?>
    </div>
    <div class="feedback-toast-body">
      <span class="feedback-toast-message"><?= htmlspecialchars($feedbackMessage, ENT_QUOTES, 'UTF-8') ?></span>
    </div>
    <button class="feedback-toast-close" type="button" aria-label="Fermer la notification">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
  </div>
<?php endif; ?>
