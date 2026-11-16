<?php

$frontendStyles = is_array($frontendStyles ?? null) ? $frontendStyles : [];
$frontendScripts = is_array($frontendScripts ?? null) ? $frontendScripts : [];
$frontendAssetRoot = is_string($asset ?? null) ? $asset : ($assetBase ?? '/public/assets');

$frontendFileUrl = static function (string $directory, string $file) use ($frontendAssetRoot): string {
    $relativePath = $directory . '/' . ltrim($file, '/');
    $absolutePath = ROOT_PATH . '/public/assets/' . $relativePath;
    $version = is_file($absolutePath) ? (string) filemtime($absolutePath) : '';
    $query = $version !== '' ? '?v=' . rawurlencode($version) : '';

    return htmlspecialchars($frontendAssetRoot . '/' . $relativePath . $query, ENT_QUOTES, 'UTF-8');
};
?>
<link rel="stylesheet" href="<?= $frontendFileUrl('css', 'theme.css') ?>">
<?php foreach ($frontendStyles as $style): ?>
  <link rel="stylesheet" href="<?= $frontendFileUrl('css', (string) $style) ?>">
<?php endforeach; ?>
<script src="<?= $frontendFileUrl('js', 'theme.js') ?>" defer></script>
<?php foreach ($frontendScripts as $script): ?>
  <script src="<?= $frontendFileUrl('js', (string) $script) ?>" defer></script>
<?php endforeach; ?>
