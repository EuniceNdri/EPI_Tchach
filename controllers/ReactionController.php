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
    require_once ROOT_PATH . '/models/Reaction.php';
    require_once ROOT_PATH . '/models/Salon.php';
} else {
    require_once dirname(__DIR__) . '/config/database.php';
    require_once dirname(__DIR__) . '/models/Reaction.php';
    require_once dirname(__DIR__) . '/models/Salon.php';
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
            ? $this->pdo->prepare('SELECT type_publication, categorie_id, est_signale FROM publications WHERE id = :id LIMIT 1')
            : $this->pdo->prepare("
                SELECT publication.type_publication, publication.categorie_id, publication.est_signale,
                       comment.est_signale AS comment_signale
                FROM commentaires comment
                JOIN publications publication ON publication.id = comment.publication_id
                WHERE comment.id = :id
                LIMIT 1
            ");
        $targetStatement->execute(['id' => $cibleId]);
        $target = $targetStatement->fetch(PDO::FETCH_ASSOC);

        $isAllowed = is_string($storedRole) && $storedRole !== '' && $target
            && !(bool)$target['est_signale']
            && $this->salonModel->canAccessCategory($userId, (int)$target['categorie_id'], strtolower(trim($storedRole)));
        if ($typeCible === 'publication') {
            $isAllowed = $isAllowed && in_array($target['type_publication'], ['discussion', 'annonce'], true);
            if ($target && $target['type_publication'] === 'annonce' && $typeReaction !== 'like') {
                $isAllowed = false;
            }
        } else {
            $isAllowed = $isAllowed
                && $target['type_publication'] === 'discussion'
                && !(bool)($target['comment_signale'] ?? false)
                && $typeReaction === 'like';
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
