<?php

class SearchController
{
    public function search(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        if (empty($_SESSION['user_id'])) {
            $this->respond(['success' => false, 'message' => 'Connexion requise.'], 401);
            return;
        }

        global $pdo;
        if (!$pdo) {
            $this->respond(['success' => false, 'message' => 'La base de données est indisponible.'], 503);
            return;
        }

        $term = trim((string)($_GET['q'] ?? ''));
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        $like = '%' . $term . '%';

        $where = '';
        $parameters = [];
        if ($term !== '') {
            $where = 'WHERE p.titre LIKE ? OR p.contenu LIKE ? OR u.nom LIKE ? OR u.prenom LIKE ?
                      OR c.nom LIKE ? OR t.nom LIKE ? OR pj.nom_fichier LIKE ?';
            $parameters = array_fill(0, 7, $like);
        }

        $countStatement = $pdo->prepare(
            "SELECT COUNT(DISTINCT p.id)
             FROM publications p
             INNER JOIN utilisateurs u ON u.id = p.utilisateur_id
             INNER JOIN categories c ON c.id = p.categorie_id
             LEFT JOIN publication_tags pt ON pt.publication_id = p.id
             LEFT JOIN tags t ON t.id = pt.tag_id
             LEFT JOIN pieces_jointes pj ON pj.type_cible = 'publication' AND pj.cible_id = p.id
             {$where}"
        );
        $countStatement->execute($parameters);
        $total = (int)$countStatement->fetchColumn();

        $statement = $pdo->prepare(
            "SELECT p.id, p.titre, p.contenu, p.date_creation,
                    CONCAT(u.prenom, ' ', u.nom) AS auteur,
                    c.nom AS categorie,
                    COUNT(DISTINCT co.id) AS commentaires,
                    GROUP_CONCAT(DISTINCT CONCAT(pj.id, '::', pj.nom_fichier)
                                 ORDER BY pj.nom_fichier SEPARATOR '||') AS fichiers
             FROM publications p
             INNER JOIN utilisateurs u ON u.id = p.utilisateur_id
             INNER JOIN categories c ON c.id = p.categorie_id
             LEFT JOIN commentaires co ON co.publication_id = p.id
             LEFT JOIN publication_tags pt ON pt.publication_id = p.id
             LEFT JOIN tags t ON t.id = pt.tag_id
             LEFT JOIN pieces_jointes pj ON pj.type_cible = 'publication' AND pj.cible_id = p.id
             {$where}
             GROUP BY p.id, p.titre, p.contenu, p.date_creation, u.prenom, u.nom, c.nom
             ORDER BY p.date_creation DESC
             LIMIT {$perPage} OFFSET {$offset}"
        );
        $statement->execute($parameters);

        $results = [];
        foreach ($statement->fetchAll() as $row) {
            $results[] = [
                'id' => (int)$row['id'],
                'title' => $row['titre'],
                'excerpt' => mb_strimwidth(strip_tags($row['contenu']), 0, 180, '...'),
                'author' => $row['auteur'],
                'category' => $row['categorie'],
                'date' => $row['date_creation'],
                'comments' => (int)$row['commentaires'],
                'files' => $this->formatFiles($row['fichiers']),
            ];
        }

        $this->respond([
            'success' => true,
            'query' => $term,
            'results' => $results,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'pages' => $total > 0 ? (int)ceil($total / $perPage) : 0,
            ],
        ]);
    }

    private function respond(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function formatFiles(?string $files): array
    {
        if (!$files) {
            return [];
        }

        $formatted = [];
        foreach (explode('||', $files) as $file) {
            [$id, $name] = array_pad(explode('::', $file, 2), 2, '');
            if ((int)$id <= 0 || $name === '') {
                continue;
            }
            $formatted[] = [
                'name' => $name,
                'download_url' => url('attachment/download/' . (int)$id),
            ];
        }
        return $formatted;
    }
}
