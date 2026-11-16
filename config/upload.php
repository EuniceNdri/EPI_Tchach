<?php

$projectRoot = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__);

return [
    'directory' => $projectRoot . '/public/uploads',
    'max_size' => 10 * 1024 * 1024,
    'allowed_mimes' => [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'application/pdf' => 'pdf',
    ],
];
