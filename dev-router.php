<?php

$publicRoot = realpath(__DIR__ . '/public');
$requestPath = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$requestedFile = realpath(__DIR__ . $requestPath);

if (
    $publicRoot !== false
    && $requestedFile !== false
    && str_starts_with($requestedFile, $publicRoot . DIRECTORY_SEPARATOR)
    && is_file($requestedFile)
) {
    return false;
}

error_log(sprintf(
    '[HTTP] %s %s',
    $_SERVER['REQUEST_METHOD'] ?? 'GET',
    $requestPath
));

require __DIR__ . '/index.php';
