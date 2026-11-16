<?php

$publicRoot = realpath(__DIR__ . '/public');
$requestPath = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');

// Vérifie si la requête pointe directement dans /public ou s'il s'agit d'un alias comme /uploads ou /assets
$requestedFile = realpath(__DIR__ . $requestPath);
if ($requestedFile === false && $publicRoot !== false) {
    $requestedFile = realpath($publicRoot . $requestPath);
}

if (
    $publicRoot !== false
    && $requestedFile !== false
    && str_starts_with($requestedFile, $publicRoot . DIRECTORY_SEPARATOR)
    && is_file($requestedFile)
) {
    return false;
}

require __DIR__ . '/index.php';
