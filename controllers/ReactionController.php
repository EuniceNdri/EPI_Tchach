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
} else {
    require_once dirname(__DIR__) . '/config/database.php';
    require_once dirname(__DIR__) . '/models/Reaction.php';
}

class ReactionController {
    private ?PDO $pdo;
    private Reaction $reactionModel;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
        $this->reactionModel = new Reaction($this->pdo);
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

        if ($cibleId <= 0) {
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

        $result = $this->reactionModel->toggle($userId, $typeCible, $cibleId, $typeReaction);

        if ($isAjax) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode($result);
            exit;
        }

        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? url('salon')));
        exit;
    }
}
