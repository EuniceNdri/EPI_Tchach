<?php

/**
 * Validation et stockage des pièces jointes associées aux contenus.
 */
class Attachment
{
    private const MAX_FILE_SIZE = 10485760;
    private const MAX_FILES = 5;

    private const ALLOWED_TYPES = [
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'xls' => ['application/vnd.ms-excel'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        'ppt' => ['application/vnd.ms-powerpoint'],
        'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation'],
        'txt' => ['text/plain'],
        'zip' => ['application/zip', 'application/x-zip-compressed', 'multipart/x-zip'],
        'png' => ['image/png'],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'gif' => ['image/gif'],
        'webp' => ['image/webp'],
    ];

    /**
     * @return array<int, array{name: string, tmp_name: string, size: int, type: string}>
     */
    public function validate(array $files): array
    {
        if (empty($files) || !isset($files['name'])) {
            return [];
        }

        $names = is_array($files['name']) ? $files['name'] : [$files['name']];
        $tmpNames = is_array($files['tmp_name']) ? $files['tmp_name'] : [$files['tmp_name']];
        $sizes = is_array($files['size']) ? $files['size'] : [$files['size']];
        $errors = is_array($files['error']) ? $files['error'] : [$files['error']];

        $uploadedFiles = [];
        foreach ($names as $index => $originalName) {
            $error = (int)($errors[$index] ?? UPLOAD_ERR_NO_FILE);
            if ($error === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            if ($error !== UPLOAD_ERR_OK) {
                throw new InvalidArgumentException("Le fichier « {$originalName} » n'a pas pu être envoyé.");
            }
            if (count($uploadedFiles) >= self::MAX_FILES) {
                throw new InvalidArgumentException('Vous pouvez joindre au maximum 5 fichiers.');
            }

            $name = basename((string)$originalName);
            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $size = (int)($sizes[$index] ?? 0);
            $tmpName = (string)($tmpNames[$index] ?? '');

            if (!isset(self::ALLOWED_TYPES[$extension])) {
                throw new InvalidArgumentException("Le format du fichier « {$name} » n'est pas autorisé.");
            }
            if ($size <= 0 || $size > self::MAX_FILE_SIZE) {
                throw new InvalidArgumentException("Le fichier « {$name} » dépasse la taille maximale de 10 Mo.");
            }
            if ($tmpName === '' || !is_uploaded_file($tmpName)) {
                throw new InvalidArgumentException("Le fichier « {$name} » est invalide.");
            }

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($tmpName);
            if (!is_string($mime) || !in_array($mime, self::ALLOWED_TYPES[$extension], true)) {
                throw new InvalidArgumentException("Le type réel du fichier « {$name} » ne correspond pas à son extension.");
            }

            $uploadedFiles[] = [
                'name' => $name,
                'tmp_name' => $tmpName,
                'size' => $size,
                'type' => $mime,
                'extension' => $extension,
            ];
        }

        return $uploadedFiles;
    }

    /**
     * @param array<int, array{name: string, tmp_name: string, size: int, type: string, extension: string}> $files
     * @return array<int, array{id: int, name: string, size: int, type: string, download_url: string, storage_path: string}>
     */
    public function store(PDO $pdo, string $targetType, int $targetId, array $files): array
    {
        if ($files === []) {
            return [];
        }

        $uploadDirectory = self::storageDirectory();
        if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0700, true) && !is_dir($uploadDirectory)) {
            throw new RuntimeException('Le dossier de stockage des fichiers est indisponible.');
        }
        $resolvedUploadDirectory = realpath($uploadDirectory);
        $publicDirectory = realpath(ROOT_PATH . '/public');
        if ($resolvedUploadDirectory === false || $publicDirectory === false
            || $resolvedUploadDirectory === $publicDirectory
            || str_starts_with($resolvedUploadDirectory, $publicDirectory . DIRECTORY_SEPARATOR)) {
            throw new RuntimeException('Le stockage des pièces jointes doit rester hors du dossier public.');
        }

        $storedPaths = [];
        $attachments = [];
        try {
            $orderStatement = $pdo->prepare(
                'SELECT COALESCE(MAX(ordre_affichage) + 1, 0)
                 FROM pieces_jointes
                 WHERE type_cible = :type_cible AND cible_id = :cible_id'
            );
            $orderStatement->execute(['type_cible' => $targetType, 'cible_id' => $targetId]);
            $displayOrder = (int)$orderStatement->fetchColumn();
            $statement = $pdo->prepare(
                'INSERT INTO pieces_jointes (
                    type_cible, cible_id, nom_fichier, chemin_fichier, type_mime, type_media, taille, ordre_affichage
                 ) VALUES (
                    :type_cible, :cible_id, :nom_fichier, :chemin_fichier, :type_mime, :type_media, :taille, :ordre_affichage
                 )'
            );

            foreach ($files as $index => $file) {
                $storedName = bin2hex(random_bytes(16)) . '.' . $file['extension'];
                $destination = $uploadDirectory . '/' . $storedName;
                if (!move_uploaded_file($file['tmp_name'], $destination)) {
                    throw new RuntimeException("Impossible de stocker le fichier « {$file['name']} ».");
                }
                if (!chmod($destination, 0600)) {
                    unlink($destination);
                    throw new RuntimeException("Impossible de sécuriser le fichier « {$file['name']} ».");
                }

                $storedPaths[] = $destination;
                $relativePath = 'private/attachments/' . $storedName;
                $statement->execute([
                    'type_cible' => $targetType,
                    'cible_id' => $targetId,
                    'nom_fichier' => $file['name'],
                    'chemin_fichier' => $relativePath,
                    'type_mime' => $file['type'],
                    'type_media' => str_starts_with($file['type'], 'image/') ? 'image' : 'document',
                    'taille' => $file['size'],
                    'ordre_affichage' => $displayOrder + $index,
                ]);

                $attachments[] = [
                    'id' => (int)$pdo->lastInsertId(),
                    'name' => $file['name'],
                    'size' => $file['size'],
                    'type' => $file['type'],
                    'download_url' => url('attachment/download/' . $pdo->lastInsertId()),
                    'storage_path' => $relativePath,
                ];
            }
        } catch (PDOException | RuntimeException $exception) {
            foreach ($storedPaths as $storedPath) {
                if (is_file($storedPath)) {
                    unlink($storedPath);
                }
            }
            throw $exception;
        }

        return $attachments;
    }

    /**
     * @param array<int, array<string, mixed>> $attachments
     * @return array<int, array<string, mixed>>
     */
    public static function responseMetadata(array $attachments): array
    {
        return array_map(static function (array $attachment): array {
            unset($attachment['storage_path']);
            return $attachment;
        }, $attachments);
    }

    /**
     * @param array<int, array<string, mixed>> $attachments
     */
    public static function removeNewFiles(array $attachments): void
    {
        foreach ($attachments as $attachment) {
            $relativePath = $attachment['storage_path'] ?? null;
            if (!is_string($relativePath)) {
                continue;
            }
            $filePath = self::resolveStoredPath($relativePath);
            if ($filePath !== null && !unlink($filePath)) {
                error_log('Impossible de supprimer un fichier après l’échec de son enregistrement.');
            }
        }
    }

    public static function storageDirectory(): string
    {
        $configuredPath = getenv('EPI_PRIVATE_STORAGE');
        $storageRoot = is_string($configuredPath) && trim($configuredPath) !== ''
            ? rtrim($configuredPath, DIRECTORY_SEPARATOR)
            : dirname(ROOT_PATH) . '/epi-tchach-storage';

        return $storageRoot . '/attachments';
    }

    public static function resolveStoredPath(string $relativePath): ?string
    {
        if (preg_match('#^private/attachments/([a-f0-9]{32}\.[a-z0-9]{1,5})$#', $relativePath, $matches)) {
            $directory = realpath(self::storageDirectory());
            $publicDirectory = realpath(ROOT_PATH . '/public');
            $filePath = $directory === false ? false : realpath($directory . DIRECTORY_SEPARATOR . $matches[1]);
            if ($directory !== false && $publicDirectory !== false
                && $directory !== $publicDirectory
                && !str_starts_with($directory, $publicDirectory . DIRECTORY_SEPARATOR)
                && $filePath !== false
                && str_starts_with($filePath, $directory . DIRECTORY_SEPARATOR)
                && is_file($filePath)) {
                return $filePath;
            }

            return null;
        }

        if (preg_match('#^/?uploads/([a-zA-Z0-9][a-zA-Z0-9._-]{0,254})$#', $relativePath, $matches)) {
            $directory = realpath(ROOT_PATH . '/public/uploads');
            $filePath = $directory === false ? false : realpath($directory . DIRECTORY_SEPARATOR . $matches[1]);
            if ($directory !== false && $filePath !== false
                && str_starts_with($filePath, $directory . DIRECTORY_SEPARATOR)
                && is_file($filePath)) {
                return $filePath;
            }
        }

        return null;
    }

    /**
     * @return array<int, array{id: int, chemin_fichier: string}>
     */
    public static function findForTarget(PDO $pdo, string $targetType, int $targetId): array
    {
        if ($targetType === 'publication') {
            $statement = $pdo->prepare(
                "SELECT attachment.id, attachment.chemin_fichier
                 FROM pieces_jointes attachment
                 WHERE attachment.type_cible = 'publication' AND attachment.cible_id = :target_id
                 UNION
                 SELECT attachment.id, attachment.chemin_fichier
                 FROM pieces_jointes attachment
                 JOIN commentaires comment ON comment.id = attachment.cible_id
                 WHERE attachment.type_cible = 'commentaire' AND comment.publication_id = :comment_publication_id"
            );
            $statement->execute([
                'target_id' => $targetId,
                'comment_publication_id' => $targetId,
            ]);
        } elseif ($targetType === 'commentaire') {
            $statement = $pdo->prepare(
                "SELECT id, chemin_fichier FROM pieces_jointes
                 WHERE type_cible = 'commentaire' AND cible_id = :target_id"
            );
            $statement->execute(['target_id' => $targetId]);
        } else {
            throw new InvalidArgumentException('Type de cible de pièce jointe invalide.');
        }

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param array<int, array{id: int, chemin_fichier: string}> $attachments
     */
    public static function removeStoredFiles(PDO $pdo, array $attachments): void
    {
        if ($attachments === []) {
            return;
        }

        $ids = array_values(array_unique(array_map(
            static fn (array $attachment): int => (int)$attachment['id'],
            $attachments
        )));
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $delete = $pdo->prepare("DELETE FROM pieces_jointes WHERE id IN ($placeholders)");
        $delete->execute($ids);

        foreach ($attachments as $attachment) {
            $filePath = self::resolveStoredPath((string)$attachment['chemin_fichier']);
            if ($filePath === null) {
                error_log('Pièce jointe absente ou chemin non autorisé lors du nettoyage : ' . (int)$attachment['id']);
                continue;
            }
            if (!unlink($filePath)) {
                error_log('Impossible de supprimer le fichier de la pièce jointe : ' . (int)$attachment['id']);
            }
        }
    }
}
