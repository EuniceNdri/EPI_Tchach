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
     * @return array<int, array{id: int, name: string, size: int, type: string, download_url: string}>
     */
    public function store(PDO $pdo, string $targetType, int $targetId, array $files): array
    {
        if ($files === []) {
            return [];
        }

        $uploadDirectory = ROOT_PATH . '/public/uploads';
        if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0750, true) && !is_dir($uploadDirectory)) {
            throw new RuntimeException('Le dossier de stockage des fichiers est indisponible.');
        }

        $storedPaths = [];
        $attachments = [];
        try {
            $statement = $pdo->prepare(
                'INSERT INTO pieces_jointes (type_cible, cible_id, nom_fichier, chemin_fichier, type_mime, taille)
                 VALUES (:type_cible, :cible_id, :nom_fichier, :chemin_fichier, :type_mime, :taille)'
            );

            foreach ($files as $file) {
                $storedName = bin2hex(random_bytes(16)) . '.' . $file['extension'];
                $destination = $uploadDirectory . '/' . $storedName;
                if (!move_uploaded_file($file['tmp_name'], $destination)) {
                    throw new RuntimeException("Impossible de stocker le fichier « {$file['name']} ».");
                }

                $storedPaths[] = $destination;
                $relativePath = '/uploads/' . $storedName;
                $statement->execute([
                    'type_cible' => $targetType,
                    'cible_id' => $targetId,
                    'nom_fichier' => $file['name'],
                    'chemin_fichier' => $relativePath,
                    'type_mime' => $file['type'],
                    'taille' => $file['size'],
                ]);

                $attachments[] = [
                    'id' => (int)$pdo->lastInsertId(),
                    'name' => $file['name'],
                    'size' => $file['size'],
                    'type' => $file['type'],
                    'download_url' => url('attachment/download/' . $pdo->lastInsertId()),
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
}
