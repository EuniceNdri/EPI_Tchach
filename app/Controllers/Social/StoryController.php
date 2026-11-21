<?php

require_once ROOT_PATH . '/app/Models/Social/Story.php';
require_once ROOT_PATH . '/app/Services/UploadService.php';

final class StoryController
{
    private PDO $pdo;
    private Story $stories;

    public function __construct()
    {
        global $pdo;
        if (!$pdo instanceof PDO) {
            throw new RuntimeException('La base de données est indisponible.');
        }
        $this->pdo = $pdo;
        $this->stories = new Story($pdo);
    }

    public function create(): void
    {
        $userId = $this->requireAuthentication();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Allow: POST');
            http_response_code(405);
            return;
        }
        if (!$this->hasValidCsrf()) {
            $this->redirectWithError('Session invalide. Veuillez réessayer.');
        }

        $text = trim((string)($_POST['texte'] ?? ''));
        $visibility = (string)($_POST['visibilite'] ?? 'amis');
        $file = $_FILES['image'] ?? null;
        $imageUrl = null;
        $uploadService = new UploadService();

        try {
            if (mb_strlen($text) > 300) {
                throw new InvalidArgumentException('Le texte ne peut pas dépasser 300 caractères.');
            }
            if ($visibility !== 'public' && $visibility !== 'amis') {
                throw new InvalidArgumentException('La visibilité choisie est invalide.');
            }

            if (is_array($file) && (int)($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $imageUrl = $this->storeStoryImage($file, $uploadService);
            }

            if ($text === '' && $imageUrl === null) {
                throw new InvalidArgumentException('Ajoutez un texte ou une image à votre story.');
            }

            $this->stories->create(
                $userId,
                $text === '' ? null : $text,
                $imageUrl,
                $visibility
            );
            $_SESSION['flash_success'] = 'Votre story a été publiée pour 24 heures.';
        } catch (InvalidArgumentException $exception) {
            if ($imageUrl !== null) {
                $this->deleteStoryImage($imageUrl);
            }
            $_SESSION['flash_error'] = $exception->getMessage();
        } catch (Throwable $exception) {
            if ($imageUrl !== null) {
                $this->deleteStoryImage($imageUrl);
            }
            error_log('Publication de story impossible : ' . $exception->getMessage());
            $_SESSION['flash_error'] = 'La story n’a pas pu être publiée. Veuillez réessayer.';
        }

        header('Location: ' . url('salon'));
        exit;
    }

    public function image(): void
    {
        $userId = $this->requireAuthentication();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
            header('Allow: GET');
            http_response_code(405);
            return;
        }
        $storyId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($storyId === false || $storyId === null) {
            http_response_code(404);
            return;
        }

        $path = $this->stories->getImagePath((int)$storyId, $userId);
        if ($path === null) {
            http_response_code(404);
            return;
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($path);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            http_response_code(404);
            return;
        }
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . (string)filesize($path));
        header('Cache-Control: private, no-store');
        header('X-Content-Type-Options: nosniff');
        readfile($path);
        exit;
    }

    public function markViewed(): void
    {
        $userId = $this->requireAuthentication();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Allow: POST');
            $this->jsonResponse(405, ['success' => false, 'message' => 'Méthode non autorisée.']);
        }
        if (!$this->hasValidCsrf()) {
            $this->jsonResponse(403, ['success' => false, 'message' => 'Session invalide.']);
        }

        $storyId = filter_var($_POST['story_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($storyId === false || $storyId === null) {
            $this->jsonResponse(400, ['success' => false, 'message' => 'Story invalide.']);
        }

        if (!$this->stories->markViewed((int)$storyId, $userId)) {
            $this->jsonResponse(404, ['success' => false, 'message' => 'Cette story est expirée ou inaccessible.']);
        }
        $this->jsonResponse(200, ['success' => true]);
    }

    public function viewers(): void
    {
        $userId = $this->requireAuthentication();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
            header('Allow: GET');
            $this->jsonResponse(405, ['success' => false, 'message' => 'Méthode non autorisée.']);
        }
        $storyId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($storyId === false || $storyId === null) {
            $this->jsonResponse(400, ['success' => false, 'message' => 'Story invalide.']);
        }

        $viewers = $this->stories->getViewers((int)$storyId, $userId);
        if ($viewers === null) {
            $this->jsonResponse(404, ['success' => false, 'message' => 'Cette story est introuvable ou expirée.']);
        }
        $this->jsonResponse(200, ['success' => true, 'viewers' => $viewers]);
    }

    public function delete(): void
    {
        $userId = $this->requireAuthentication();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Allow: POST');
            $this->jsonResponse(405, ['success' => false, 'message' => 'Méthode non autorisée.']);
        }
        if (!$this->hasValidCsrf()) {
            $this->jsonResponse(403, ['success' => false, 'message' => 'Session invalide.']);
        }
        $storyId = filter_var($_POST['story_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($storyId === false || $storyId === null) {
            $this->jsonResponse(400, ['success' => false, 'message' => 'Story invalide.']);
        }

        try {
            $imageUrl = $this->stories->delete((int)$storyId, $userId);
            if ($imageUrl === null) {
                $this->jsonResponse(404, ['success' => false, 'message' => 'Cette story ne vous appartient pas ou est introuvable.']);
            }
            if ($imageUrl !== '') {
                $this->deleteStoryImage($imageUrl);
            }
            $this->jsonResponse(200, ['success' => true]);
        } catch (Throwable $exception) {
            error_log('Suppression de story impossible : ' . $exception->getMessage());
            $this->jsonResponse(500, ['success' => false, 'message' => 'La suppression de la story a échoué.']);
        }
    }

    private function requireAuthentication(): int
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . url('login'));
            exit;
        }
        return (int)$_SESSION['user_id'];
    }

    private function hasValidCsrf(): bool
    {
        $token = (string)($_POST['csrf_token'] ?? '');
        return !empty($_SESSION['story_csrf'])
            && hash_equals((string)$_SESSION['story_csrf'], $token);
    }

    private function storeStoryImage(array $file, UploadService $uploadService): string
    {
        $temporaryUrl = $uploadService->store($file, 'stories');
        if (!in_array(pathinfo($temporaryUrl, PATHINFO_EXTENSION), ['jpg', 'png', 'webp'], true)) {
            $uploadService->delete($temporaryUrl);
            throw new InvalidArgumentException('Seules les images JPG, PNG ou WEBP sont autorisées.');
        }

        $source = ROOT_PATH . '/public/' . $temporaryUrl;
        $directory = ROOT_PATH . '/storage/stories';
        try {
            if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
                throw new RuntimeException('Impossible de créer le dossier privé des stories.');
            }
            $filename = basename($temporaryUrl);
            $destination = $directory . '/' . $filename;
            if (!rename($source, $destination)) {
                throw new RuntimeException('Impossible de déplacer l’image dans le stockage privé.');
            }
            chmod($destination, 0600);
            return 'stories/' . $filename;
        } catch (Throwable $exception) {
            if (is_file($source)) {
                $uploadService->delete($temporaryUrl);
            }
            throw $exception;
        }
    }

    private function deleteStoryImage(string $imageUrl): void
    {
        if (!preg_match('/\Astories\/[a-f0-9]{32}\.(?:jpg|png|webp)\z/', $imageUrl)) {
            throw new InvalidArgumentException('Le chemin de l’image de story est invalide.');
        }
        $directory = realpath(ROOT_PATH . '/storage/stories');
        $path = realpath(ROOT_PATH . '/storage/' . $imageUrl);
        if ($directory === false || $path === false || !str_starts_with($path, $directory . DIRECTORY_SEPARATOR)) {
            throw new InvalidArgumentException('Le chemin de l’image de story est invalide.');
        }
        if (is_file($path) && !unlink($path)) {
            throw new RuntimeException('Impossible de supprimer l’image de story.');
        }
    }

    private function redirectWithError(string $message): never
    {
        $_SESSION['flash_error'] = $message;
        header('Location: ' . url('salon'));
        exit;
    }

    private function jsonResponse(int $status, array $payload): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
