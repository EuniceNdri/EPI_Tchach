<?php

final class UploadService
{
    private array $configuration;

    public function __construct(?array $configuration = null)
    {
        $this->configuration = $configuration
            ?? require (defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__, 2)) . '/config/upload.php';
    }

    public function store(array $file, string $subdirectory = 'temp'): string
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('Le fichier n’a pas pu être téléversé.');
        }

        $temporaryPath = (string) ($file['tmp_name'] ?? '');
        if ($temporaryPath === '' || !is_uploaded_file($temporaryPath)) {
            throw new InvalidArgumentException('Le fichier téléversé est invalide.');
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > $this->configuration['max_size']) {
            throw new InvalidArgumentException('La taille du fichier est invalide ou dépasse la limite autorisée.');
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($temporaryPath);
        if (!is_string($mime) || !isset($this->configuration['allowed_mimes'][$mime])) {
            throw new InvalidArgumentException('Le type de fichier n’est pas autorisé.');
        }

        $safeDirectory = trim($subdirectory, '/');
        if ($safeDirectory === '' || str_contains($safeDirectory, '..')) {
            throw new InvalidArgumentException('Le dossier de destination est invalide.');
        }

        $directory = $this->configuration['directory'] . '/' . $safeDirectory;
        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
            throw new RuntimeException('Impossible de créer le dossier de téléversement.');
        }

        $extension = $this->configuration['allowed_mimes'][$mime];
        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $destination = $directory . '/' . $filename;
        if (!move_uploaded_file($temporaryPath, $destination)) {
            throw new RuntimeException('Impossible d’enregistrer le fichier téléversé.');
        }

        chmod($destination, 0600);
        return 'uploads/' . $safeDirectory . '/' . $filename;
    }

    public function delete(string $relativePath): void
    {
        $normalized = ltrim($relativePath, '/');
        $root = realpath($this->configuration['directory']);
        $path = realpath(ROOT_PATH . '/public/' . $normalized);
        if ($root === false || $path === false || !str_starts_with($path, $root . DIRECTORY_SEPARATOR)) {
            throw new InvalidArgumentException('Le fichier à supprimer est invalide.');
        }

        if (is_file($path) && !unlink($path)) {
            throw new RuntimeException('Impossible de supprimer le fichier téléversé.');
        }
    }
}
