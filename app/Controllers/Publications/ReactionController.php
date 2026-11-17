<?php
/**
 * =============================================================
 * controllers/ReactionController.php – Contrôleur des Réactions
 * =============================================================
 * Gère l'ajout, la modification et la suppression de réactions
 * (Like, J'adore, Utile) avec notification immédiate à l'auteur.
 * =============================================================
 */

if (defined('ROOT_PATH')) {
    require_once ROOT_PATH . '/config/database.php';
    require_once ROOT_PATH . '/app/Models/Publications/Reaction.php';
    require_once ROOT_PATH . '/app/Models/Salon/Salon.php';
} else {
    require_once dirname(__DIR__, 3) . '/config/database.php';
    require_once dirname(__DIR__, 2) . '/Models/Publications/Reaction.php';
    require_once dirname(__DIR__, 2) . '/Models/Salon/Salon.php';
}

class ReactionController {
    private ?PDO $pdo;
    private Reaction $reactionModel;
    private Salon $salonModel;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
        $this->reactionModel = new Reaction($this->pdo);
        $this->salonModel = new Salon($this->pdo);
    }

    public function liveStats(): void {
        header('Content-Type: application/json; charset=UTF-8');

        $respondError = static function (string $message, int $status): void {
            http_response_code($status);
            echo json_encode(['success' => false, 'message' => $message], JSON_UNESCAPED_UNICODE);
        };
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
            header('Allow: GET');
            $respondError('Méthode non autorisée.', 405);
            return;
        }

        $userId = (int)($_SESSION['user_id'] ?? 0);
        if ($userId <= 0) {
            $respondError('Connexion requise.', 401);
            return;
        }
        if (!$this->pdo) {
            $respondError('La base de données est indisponible.', 503);
            return;
        }

        $parseIds = static function ($value, int $limit): ?array {
            if (!is_string($value) || trim($value) === '') {
                return null;
            }
            $parts = explode(',', $value);
            if (count($parts) > $limit) {
                return null;
            }

            $ids = [];
            foreach ($parts as $part) {
                if (!preg_match('/^[1-9][0-9]*$/D', $part)) {
                    return null;
                }
                $id = filter_var($part, FILTER_VALIDATE_INT);
                if ($id === false) {
                    return null;
                }
                $ids[] = $id;
            }

            return array_values(array_unique($ids));
        };
        $publicationIds = $parseIds($_GET['ids'] ?? null, 50);
        $commentIds = empty($_GET['comment_ids'])
            ? []
            : $parseIds($_GET['comment_ids'], 200);
        if ($publicationIds === null || $commentIds === null) {
            $respondError('Liste de publications ou de commentaires invalide.', 400);
            return;
        }

        $roleStatement = $this->pdo->prepare('SELECT role FROM utilisateurs WHERE id = :user_id LIMIT 1');
        $roleStatement->execute(['user_id' => $userId]);
        $storedRole = $roleStatement->fetchColumn();
        if (!is_string($storedRole) || trim($storedRole) === '') {
            $respondError('Compte utilisateur introuvable.', 401);
            return;
        }
        $userRole = strtolower(trim($storedRole));
        $accessibleZoneIds = [];
        $accessibleCategoryIds = [];
        foreach ($this->salonModel->getAccessibleZones($userId, $userRole) as $zone) {
            $accessibleZoneIds[(int)$zone['id']] = true;
            foreach ($zone['categories'] as $category) {
                $accessibleCategoryIds[(int)$category['id']] = true;
            }
        }

        $publicationPlaceholders = [];
        $parameters = ['user_id' => $userId];
        foreach ($publicationIds as $index => $publicationId) {
            $placeholder = ':publication_' . $index;
            $publicationPlaceholders[] = $placeholder;
            $parameters['publication_' . $index] = $publicationId;
        }

        $publicationStatement = $this->pdo->prepare("
            SELECT publication.id, publication.zone_id, publication.categorie_id,
                   publication.type_publication, publication.est_signale,
                   (SELECT COUNT(*) FROM reactions reaction
                    WHERE reaction.type_cible = 'publication'
                      AND reaction.cible_id = publication.id) AS likes_count,
                   (SELECT reaction.type_reaction FROM reactions reaction
                    WHERE reaction.type_cible = 'publication'
                      AND reaction.cible_id = publication.id
                      AND reaction.utilisateur_id = :user_id
                    LIMIT 1) AS user_reaction,
                   (SELECT COUNT(*) FROM commentaires comment
                    WHERE comment.publication_id = publication.id
                      AND comment.est_signale = 0) AS comments_count
            FROM publications publication
            WHERE publication.id IN (" . implode(', ', $publicationPlaceholders) . ")
        ");
        $publicationStatement->execute($parameters);

        $validPublicationTypes = ['discussion', 'question', 'annonce', 'sondage'];
        $publications = [];
        foreach ($publicationStatement->fetchAll(PDO::FETCH_ASSOC) as $publication) {
            $publicationId = (int)$publication['id'];
            if ((bool)$publication['est_signale']
                || !in_array($publication['type_publication'], $validPublicationTypes, true)) {
                continue;
            }

            $canAccess = !empty($publication['categorie_id'])
                ? isset($accessibleCategoryIds[(int)$publication['categorie_id']])
                : isset($accessibleZoneIds[(int)$publication['zone_id']]);
            if (!$canAccess) {
                continue;
            }

            $publications[(string)$publicationId] = [
                'likes_count' => (int)$publication['likes_count'],
                'comments_count' => (int)$publication['comments_count'],
                'user_reaction' => $publication['user_reaction'] ?: null,
                'user_liked' => !empty($publication['user_reaction']),
            ];
        }

        $comments = [];
        if ($commentIds && $publications) {
            $commentPlaceholders = [];
            $commentParameters = [];
            foreach ($commentIds as $index => $commentId) {
                $placeholder = ':comment_' . $index;
                $commentPlaceholders[] = $placeholder;
                $commentParameters['comment_' . $index] = $commentId;
            }
            $visiblePublicationPlaceholders = [];
            foreach (array_keys($publications) as $index => $publicationId) {
                $placeholder = ':visible_publication_' . $index;
                $visiblePublicationPlaceholders[] = $placeholder;
                $commentParameters['visible_publication_' . $index] = (int)$publicationId;
            }
            $commentParameters['user_id'] = $userId;
            $commentStatement = $this->pdo->prepare("
                SELECT comment.id,
                       (SELECT COUNT(*) FROM reactions reaction
                        WHERE reaction.type_cible = 'commentaire'
                          AND reaction.cible_id = comment.id) AS likes_count,
                       (SELECT COUNT(*) FROM reactions reaction
                        WHERE reaction.type_cible = 'commentaire'
                          AND reaction.cible_id = comment.id
                          AND reaction.utilisateur_id = :user_id) > 0 AS user_liked
                FROM commentaires comment
                WHERE comment.id IN (" . implode(', ', $commentPlaceholders) . ")
                  AND comment.publication_id IN (" . implode(', ', $visiblePublicationPlaceholders) . ")
                  AND comment.est_signale = 0
            ");
            $commentStatement->execute($commentParameters);
            foreach ($commentStatement->fetchAll(PDO::FETCH_ASSOC) as $comment) {
                $comments[(string)$comment['id']] = [
                    'likes_count' => (int)$comment['likes_count'],
                    'user_liked' => (bool)$comment['user_liked'],
                ];
            }
        }

        echo json_encode([
            'success' => true,
            'publications' => $publications,
            'comments' => $comments,
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Bascule d'une réaction (Ajout / Modification / Retrait)
     */
    public function toggle(): void {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
               || (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);

        if (empty($_SESSION['user_id'])) {
            $msg = "Veuillez vous connecter pour réagir.";
            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8', true, 401);
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            $_SESSION['flash_error'] = $msg;
            header('Location: ' . url('login'));
            exit;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Location: ' . url());
            exit;
        }

        $input = $_POST;
        if (empty($input)) {
            $raw = file_get_contents('php://input');
            if ($raw) {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $input = $decoded;
                }
            }
        }

        $userId = (int)$_SESSION['user_id'];
        $cibleId = (int)($input['cible_id'] ?? $input['publication_id'] ?? 0);
        $typeCible = trim($input['type_cible'] ?? 'publication');
        $typeReaction = trim($input['type_reaction'] ?? 'like');

        if ($cibleId <= 0 || !in_array($typeCible, ['publication', 'commentaire'], true)) {
            $msg = "Cible de réaction invalide.";
            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8', true, 400);
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            $_SESSION['flash_error'] = $msg;
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? url('salon')));
            exit;
        }

        if (!$this->pdo) {
            http_response_code(503);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['success' => false, 'message' => 'La base de données est indisponible.']);
            exit;
        }

        $roleStatement = $this->pdo->prepare('SELECT role FROM utilisateurs WHERE id = :user_id LIMIT 1');
        $roleStatement->execute(['user_id' => $userId]);
        $storedRole = $roleStatement->fetchColumn();
        $targetStatement = $typeCible === 'publication'
            ? $this->pdo->prepare('SELECT type_publication, zone_id, categorie_id, est_signale FROM publications WHERE id = :id LIMIT 1')
            : $this->pdo->prepare("
                SELECT publication.type_publication, publication.zone_id, publication.categorie_id, publication.est_signale,
                       comment.est_signale AS comment_signale
                FROM commentaires comment
                JOIN publications publication ON publication.id = comment.publication_id
                WHERE comment.id = :id
                LIMIT 1
            ");
        $targetStatement->execute(['id' => $cibleId]);
        $target = $targetStatement->fetch(PDO::FETCH_ASSOC);

        $userRole = is_string($storedRole) ? strtolower(trim($storedRole)) : '';
        $canAccess = $target && $userRole !== '' && (
            !empty($target['categorie_id'])
                ? $this->salonModel->canAccessCategory($userId, (int)$target['categorie_id'], $userRole)
                : $this->salonModel->canAccessZone($userId, (int)$target['zone_id'], $userRole)
        );
        $validPubTypes = ['discussion', 'question', 'annonce', 'sondage'];
        $isAllowed = $canAccess && !(bool)$target['est_signale'];
        if ($typeCible === 'publication') {
            $isAllowed = $isAllowed && in_array($target['type_publication'], $validPubTypes, true);
        } else {
            $isAllowed = $isAllowed
                && in_array($target['type_publication'], $validPubTypes, true)
                && !(bool)($target['comment_signale'] ?? false);
        }
        if (!$isAllowed) {
            http_response_code(403);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['success' => false, 'message' => 'Cette réaction n’est pas autorisée sur cette publication.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $result = $this->reactionModel->toggle($userId, $typeCible, $cibleId, $typeReaction);
        if ($typeCible === 'commentaire' && !empty($result['success'])) {
            $publicationIdStatement = $this->pdo->prepare('SELECT publication_id FROM commentaires WHERE id = :id');
            $publicationIdStatement->execute(['id' => $cibleId]);
            $publicationId = (int)$publicationIdStatement->fetchColumn();
            $this->salonModel->synchronizePublicationSolution($publicationId);
        }

        if ($isAjax) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode($result);
            exit;
        }

        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? url('salon')));
        exit;
    }
}
