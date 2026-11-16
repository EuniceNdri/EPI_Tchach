<?php
$feedbackError = (string)($error ?? $flashError ?? $_SESSION['flash_error'] ?? '');
$feedbackSuccess = (string)($success ?? $flashSuccess ?? $_SESSION['flash_success'] ?? '');
$feedbackMessage = $feedbackError !== '' ? $feedbackError : $feedbackSuccess;
$feedbackType = $feedbackError !== '' ? 'error' : 'success';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<?php if ($feedbackMessage !== ''): ?>
  <div class="feedback-toast feedback-toast-<?= $feedbackType ?>" data-feedback-toast data-dismiss-after="<?= $feedbackType === 'success' ? '6500' : '0' ?>" role="<?= $feedbackType === 'error' ? 'alert' : 'status' ?>" aria-live="<?= $feedbackType === 'error' ? 'assertive' : 'polite' ?>">
    <span class="feedback-toast-message"><?= htmlspecialchars($feedbackMessage, ENT_QUOTES, 'UTF-8') ?></span>
    <button class="feedback-toast-close" type="button" aria-label="Fermer la notification">×</button>
  </div>
<?php endif; ?>
