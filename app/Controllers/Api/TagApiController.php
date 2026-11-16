<?php

require_once ROOT_PATH . '/app/Controllers/Api/ApiController.php';
require_once ROOT_PATH . '/app/Models/Publications/PublicationMetadata.php';

final class TagApiController extends ApiController
{
    public function search(): never
    {
        global $pdo;
        $this->requireUser();
        if (!$pdo instanceof PDO) {
            $this->respond(['success' => false, 'message' => 'Base de données indisponible.'], 503);
        }

        $query = trim((string)($_GET['q'] ?? ''));
        $query = ltrim($query, '#');

        if ($query === '') {
            $statement = $pdo->query(
                "SELECT id, nom, 
                        (SELECT COUNT(*) FROM publications_tags pt WHERE pt.tag_id = t.id) AS usage_count
                 FROM tags_publication t
                 WHERE statut = 'actif'
                 ORDER BY usage_count DESC, nom ASC
                 LIMIT 30"
            );
            $tags = $statement ? $statement->fetchAll(PDO::FETCH_ASSOC) : [];
        } else {
            $statement = $pdo->prepare(
                "SELECT id, nom,
                        (SELECT COUNT(*) FROM publications_tags pt WHERE pt.tag_id = t.id) AS usage_count
                 FROM tags_publication t
                 WHERE statut = 'actif' AND nom LIKE :search
                 ORDER BY usage_count DESC, nom ASC
                 LIMIT 20"
            );
            $statement->execute(['search' => '%' . $query . '%']);
            $tags = $statement->fetchAll(PDO::FETCH_ASSOC);
        }

        $this->respond([
            'success' => true,
            'query' => $query,
            'tags' => $tags
        ]);
    }
}
