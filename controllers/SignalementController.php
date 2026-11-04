<?php
/**
 * =============================================================
 * controllers/SignalementController.php – Contrôleur de Signalement
 * =============================================================
 * Permet à tout utilisateur connecté de signaler une publication
 * ou un commentaire non conforme à la charte.
 * Gère les réponses AJAX (JSON) et les soumissions classiques.
 * =============================================================
 */

if (defined('ROOT_PATH')) {
    require_once ROOT_PATH . '/config/database.php';
    require_once ROOT_PATH . '/models/Signalement.php';
} else {
    require_once dirname(__DIR__) . '/config/database.php';
    require_once dirname(__DIR__) . '/models/Signalement.php';
}

class SignalementController {
    private ?PDO $pdo;
    private Signalement $signalementModel;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
        $this->signalementModel = new Signalement($this->pdo);
    }

    /**
     * Traitement du signalement d'un contenu
     */
    public function create(): void {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
               || (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);

        // 1. Vérification de l'authentification
        if (empty($_SESSION['user_id'])) {
            $msg = "Vous devez être connecté pour signaler une publication.";
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

        // 2. Extraction des données (support JSON body ou formulaire $_POST)
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
        $motif = trim($input['motif'] ?? '');

        if ($cibleId <= 0 || empty($motif)) {
            $msg = "Veuillez sélectionner un contenu et spécifier le motif du signalement.";
            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8', true, 400);
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            $_SESSION['flash_error'] = $msg;
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? url('salon')));
            exit;
        }

        // 3. Exécution du signalement avec contrôle du seuil automatique
        $result = $this->signalementModel->signaler($userId, $typeCible, $cibleId, $motif);

        if ($isAjax) {
            header('Content-Type: application/json; charset=UTF-8', true, $result['success'] ? 200 : 400);
            echo json_encode($result);
            exit;
        }

        if ($result['success']) {
            $_SESSION['flash_success'] = $result['message'];
        } else {
            $_SESSION['flash_error'] = $result['message'];
        }

        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? url('salon')));
        exit;
    }
}
