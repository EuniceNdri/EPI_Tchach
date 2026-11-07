<?php

class Avatar
{
    private const MAX_FILE_SIZE = 5242880;
    private const ALLOWED_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public static function storeUploadedFile(array $file): ?string
    {
        $error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($error !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException("La photo de profil n'a pas pu être envoyée.");
        }

        $tmpName = (string)($file['tmp_name'] ?? '');
        $size = (int)($file['size'] ?? 0);
        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            throw new InvalidArgumentException("Le fichier de la photo de profil est invalide.");
        }
        if ($size <= 0 || $size > self::MAX_FILE_SIZE) {
            throw new InvalidArgumentException('La photo de profil doit peser au maximum 5 Mo.');
        }

        $fileInfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $fileInfo->file($tmpName);
        $imageInfo = getimagesize($tmpName);
        if (!is_string($mimeType) || !isset(self::ALLOWED_TYPES[$mimeType]) || $imageInfo === false) {
            throw new InvalidArgumentException('Choisissez une image JPG, PNG ou WEBP valide.');
        }

        $directory = ROOT_PATH . '/public/uploads/avatars';
        if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
            throw new RuntimeException("Le dossier de stockage des photos de profil est indisponible.");
        }

        $storedName = bin2hex(random_bytes(16)) . '.' . self::ALLOWED_TYPES[$mimeType];
        if (!move_uploaded_file($tmpName, $directory . '/' . $storedName)) {
            throw new RuntimeException("Impossible d'enregistrer la photo de profil.");
        }

        return 'uploads/avatars/' . $storedName;
    }

    public static function removeStoredFile(?string $path): void
    {
        if (!is_string($path) || !preg_match('#^uploads/avatars/[a-f0-9]{32}\.(?:jpg|png|webp)$#', $path)) {
            return;
        }

        $absolutePath = ROOT_PATH . '/public/' . $path;
        if (is_file($absolutePath)) {
            unlink($absolutePath);
        }
    }
}
