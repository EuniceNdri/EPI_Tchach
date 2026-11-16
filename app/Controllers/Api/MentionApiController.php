<?php

require_once ROOT_PATH . '/app/Controllers/Api/ApiController.php';
require_once ROOT_PATH . '/app/Services/MentionService.php';

final class MentionApiController extends ApiController
{
    public function search(): never
    {
        global $pdo;
        $userId = $this->requireUser();
        if (!$pdo instanceof PDO) {
            $this->respond(['success' => false, 'message' => 'Base de données indisponible.'], 503);
        }

        $categoryId = (int)($_GET['category_id'] ?? 0);
        $query = trim((string)($_GET['q'] ?? ''));
        if ($categoryId <= 0) {
            $this->respond(['success' => false, 'message' => 'Catégorie invalide.'], 422);
        }

        $users = (new MentionService($pdo))->search($userId, $categoryId, $query);
        $this->respond(['success' => true, 'users' => $users]);
    }
}
