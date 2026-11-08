<?php

class Avatar
{
    private const MAX_FILE_SIZE = 5242880;
    private const MAX_IMAGE_PIXELS = 12000000;
    private const OUTPUT_SIZE = 400;
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
        $width = (int)$imageInfo[0];
        $height = (int)$imageInfo[1];
        if ($width <= 0 || $height <= 0 || $width * $height > self::MAX_IMAGE_PIXELS) {
            throw new InvalidArgumentException('La photo de profil est trop grande en résolution.');
        }
        if (!extension_loaded('gd')) {
            throw new RuntimeException("L’extension PHP GD est nécessaire pour traiter les photos de profil.");
        }
        if ($mimeType === 'image/webp'
            && (!function_exists('imagecreatefromwebp') || !function_exists('imagewebp'))) {
            throw new RuntimeException("La version de PHP GD installée ne prend pas en charge les images WEBP.");
        }

        $directory = ROOT_PATH . '/public/uploads/avatars';
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException("Le dossier de stockage des photos de profil est indisponible.");
        }

        $source = match ($mimeType) {
            'image/jpeg' => imagecreatefromjpeg($tmpName),
            'image/png' => imagecreatefrompng($tmpName),
            'image/webp' => imagecreatefromwebp($tmpName),
        };
        if ($source === false) {
            throw new InvalidArgumentException('Le fichier image ne peut pas être décodé.');
        }

        $storedName = bin2hex(random_bytes(16)) . '.' . self::ALLOWED_TYPES[$mimeType];
        $destination = imagecreatetruecolor(self::OUTPUT_SIZE, self::OUTPUT_SIZE);
        if ($destination === false) {
            imagedestroy($source);
            throw new RuntimeException("Impossible de préparer la photo de profil.");
        }

        if ($mimeType !== 'image/jpeg') {
            imagealphablending($destination, false);
            imagesavealpha($destination, true);
            $transparent = imagecolorallocatealpha($destination, 0, 0, 0, 127);
            imagefill($destination, 0, 0, $transparent);
        }

        $cropSize = min($width, $height);
        $sourceX = (int)(($width - $cropSize) / 2);
        $sourceY = (int)(($height - $cropSize) / 2);
        imagecopyresampled(
            $destination,
            $source,
            0,
            0,
            $sourceX,
            $sourceY,
            self::OUTPUT_SIZE,
            self::OUTPUT_SIZE,
            $cropSize,
            $cropSize
        );
        imagedestroy($source);

        $absolutePath = $directory . '/' . $storedName;
        $saved = match ($mimeType) {
            'image/jpeg' => imagejpeg($destination, $absolutePath, 85),
            'image/png' => imagepng($destination, $absolutePath, 7),
            'image/webp' => imagewebp($destination, $absolutePath, 85),
        };
        imagedestroy($destination);
        if (!$saved) {
            if (is_file($absolutePath)) {
                unlink($absolutePath);
            }
            throw new RuntimeException("Impossible d'enregistrer la photo de profil.");
        }
        if (!chmod($absolutePath, 0644)) {
            unlink($absolutePath);
            throw new RuntimeException("Impossible de définir les permissions de la photo de profil.");
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
