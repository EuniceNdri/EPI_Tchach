<?php

require_once dirname(__DIR__) . '/models/Salon.php';

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

        $userId = (int)$_SESSION['user_id'];
        $roleStatement = $pdo->prepare('SELECT role FROM utilisateurs WHERE id = ? LIMIT 1');
        $roleStatement->execute([$userId]);
        $role = $roleStatement->fetchColumn();
        if (!is_string($role) || $role === '') {
            $this->respond(['success' => false, 'message' => 'Compte utilisateur introuvable.'], 401);
            return;
        }

        $salonModel = new Salon($pdo);
        $accessibleZones = $salonModel->getAccessibleZones($userId, strtolower(trim($role)));
        $accessibleZoneIds = array_map(
            static fn (array $zone): int => (int)$zone['id'],
            $accessibleZones
        );
        $parseIdFilter = static function (string $key): int|false|null {
            if (!isset($_GET[$key])) {
                return null;
            }
            if (!is_string($_GET[$key]) || !ctype_digit($_GET[$key])) {
                return false;
            }

            $value = filter_var($_GET[$key], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            return $value === false ? false : $value;
        };
        $zoneId = $parseIdFilter('zone');
        $categoryId = $parseIdFilter('category');
        if ($zoneId === false || $categoryId === false) {
            $this->respond(['success' => false, 'message' => 'Filtre de salon invalide.'], 400);
            return;
        }
        if ($zoneId !== null && !in_array($zoneId, $accessibleZoneIds, true)) {
            $this->respond(['success' => false, 'message' => 'Vous n’avez pas accès à ce salon.'], 403);
            return;
        }
        if ($categoryId !== null && !$salonModel->canAccessCategory(
            $userId,
            $categoryId,
            strtolower(trim($role))
        )) {
            $this->respond(['success' => false, 'message' => 'Vous n’avez pas accès à cette catégorie.'], 403);
            return;
        }

        $term = trim((string)($_GET['q'] ?? ''));
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        $like = '%' . $term . '%';

        $profiles = [];
        if ($term !== '') {
            $profileStatement = $pdo->prepare(
                "SELECT id, nom, prenom, role, filiere, avatar_url
                 FROM utilisateurs
                 WHERE est_actif = 1
                   AND id <> ?
                   AND (prenom LIKE ? OR nom LIKE ? OR CONCAT(prenom, ' ', nom) LIKE ?)
                 ORDER BY prenom ASC, nom ASC
                 LIMIT 8"
            );
            $profileStatement->execute([$userId, $like, $like, $like]);
            $profiles = array_map(
                static fn (array $profile): array => [
                    'id' => (int)$profile['id'],
                    'name' => trim($profile['prenom'] . ' ' . $profile['nom']),
                    'role' => $profile['role'],
                    'filiere' => $profile['filiere'],
                    'avatar_url' => $profile['avatar_url'],
                ],
                $profileStatement->fetchAll(PDO::FETCH_ASSOC)
            );
        }

        $conditions = ['p.est_signale = 0', 'c.est_active = 1', 'z.est_active = 1'];
        $parameters = [];
        if ($accessibleZoneIds) {
            $conditions[] = 'z.id IN (' . implode(',', array_fill(0, count($accessibleZoneIds), '?')) . ')';
            $parameters = array_merge($parameters, $accessibleZoneIds);
        } else {
            $conditions[] = '1 = 0';
        }
        if ($zoneId !== null) {
            $conditions[] = 'z.id = ?';
            $parameters[] = $zoneId;
        }
        if ($categoryId !== null) {
            $conditions[] = 'c.id = ?';
            $parameters[] = $categoryId;
        }
        if ($term !== '') {
            $conditions[] = '(p.titre LIKE ? OR p.contenu LIKE ? OR u.nom LIKE ? OR u.prenom LIKE ?
                      OR c.nom LIKE ? OR t.nom LIKE ? OR pj.nom_fichier LIKE ?)';
            $parameters = array_merge($parameters, array_fill(0, 7, $like));
        }
        $where = 'WHERE ' . implode(' AND ', $conditions);

        $countStatement = $pdo->prepare(
            "SELECT COUNT(DISTINCT p.id)
             FROM publications p
             INNER JOIN utilisateurs u ON u.id = p.utilisateur_id
             INNER JOIN categories c ON c.id = p.categorie_id
             INNER JOIN zones z ON z.id = c.zone_id
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
                    GROUP_CONCAT(DISTINCT CONCAT(pj.id, '::', pj.nom_fichier, '::', pj.type_mime)
                                 ORDER BY pj.nom_fichier SEPARATOR '||') AS fichiers
             FROM publications p
             INNER JOIN utilisateurs u ON u.id = p.utilisateur_id
             INNER JOIN categories c ON c.id = p.categorie_id
             INNER JOIN zones z ON z.id = c.zone_id
             LEFT JOIN commentaires co ON co.publication_id = p.id AND co.est_signale = 0
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
            'profiles' => $profiles,
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
            [$id, $name, $type] = array_pad(explode('::', $file, 3), 3, '');
            if ((int)$id <= 0 || $name === '') {
                continue;
            }
            $formatted[] = [
                'name' => $name,
                'type' => $type,
                'download_url' => url('attachment/download/' . (int)$id),
            ];
        }
        return $formatted;
    }
}
