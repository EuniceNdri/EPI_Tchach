<?php
/**
 * =============================================================
 * controllers/ModerationController.php – Contrôleur de Modération
 * =============================================================
 * Espace réservé aux administrateurs et modérateurs pour examiner
 * les signalements et appliquer les décisions (avertir l'auteur,
 * supprimer le contenu, bloquer la publication, rejeter).
 * =============================================================
 */

if (defined('ROOT_PATH')) {
    require_once ROOT_PATH . '/config/database.php';
    require_once ROOT_PATH . '/models/ModerationService.php';
    require_once ROOT_PATH . '/models/Signalement.php';
} else {
    require_once dirname(__DIR__) . '/config/database.php';
    require_once dirname(__DIR__) . '/models/ModerationService.php';
    require_once dirname(__DIR__) . '/models/Signalement.php';
}

class ModerationController {
    private ?PDO $pdo;
    private ModerationService $moderationService;
    private Signalement $signalementModel;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
        $this->moderationService = new ModerationService($this->pdo);
        $this->signalementModel = new Signalement($this->pdo);

        $this->checkAdminOrModerator();
    }

    /**
     * Garde de sécurité : seuls les administrateurs et modérateurs peuvent agir
     */
    private function checkAdminOrModerator(): void {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $userId = (int)($_SESSION['user_id'] ?? 0);
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        if ($userId > 0 && !($this->pdo instanceof PDO)) {
            http_response_code(503);
            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => 'Modération indisponible : la base de données ne répond pas.']);
                exit;
            }

            exit('Modération indisponible : la base de données ne répond pas.');
        }

        $role = strtolower(trim((string)($_SESSION['user_role'] ?? '')));
        if ($userId > 0 && $this->pdo instanceof PDO) {
            $statement = $this->pdo->prepare('SELECT role FROM utilisateurs WHERE id = :id LIMIT 1');
            $statement->execute(['id' => $userId]);
            $role = strtolower(trim((string)$statement->fetchColumn()));
        }
        $allowed = ['administrateur', 'moderateur'];

        if ($userId <= 0 || !in_array($role, $allowed, true)) {
            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8', true, 403);
                echo json_encode(['success' => false, 'message' => "Accès refusé. Privilèges modérateur requis."]);
                exit;
            }

            $_SESSION['flash_error'] = "Accès refusé. Zone réservée à la modération.";
            header('Location: ' . url('login'));
            exit;
        }
    }

    /**
     * Affiche le centre de modération avec la liste des signalements
     */
    public function index(): void {
        global $baseUrl, $base, $assetBase, $asset;

        $statut = $_GET['statut'] ?? null;
        $reports = $this->signalementModel->getAllReports($statut);

        require_once ROOT_PATH . '/views/desktop/admin/moderation.php';
    }

    /**
     * Prise de décision sur un signalement
     * Actions supportées : 'avertir', 'supprimer', 'bloquer', 'rejeter'
     */
    public function decision(): void {
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Location: ' . url('admin/moderation'));
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

        $adminId = (int)$_SESSION['user_id'];
        $reportId = (int)($input['report_id'] ?? 0);
        $action = trim($input['action'] ?? '');
        $message = trim($input['message'] ?? $input['motif'] ?? '');

        if ($reportId <= 0) {
            $msg = "Identifiant de signalement manquant.";
            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8', true, 400);
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            $_SESSION['flash_error'] = $msg;
            header('Location: ' . url('admin/moderation'));
            exit;
        }

        $success = false;
        $respMsg = "";

        switch ($action) {
            case 'avertir':
            case 'avertiAuteur':
            case 'avertirAuteur':
                $motifAvertissement = !empty($message) 
                    ? $message 
                    : "Votre publication enfreint les règles de courtoisie et de bienséance du forum.";
                $success = $this->moderationService->avertirAuteur($reportId, $adminId, $motifAvertissement);
                $respMsg = "Un avertissement a été envoyé à l'auteur et le signalement a été clôturé.";
                break;

            case 'supprimer':
            case 'supprimer_contenu':
            case 'supprimerContenu':
                $motifSuppression = !empty($message) ? $message : "Non-respect de la charte de l'école.";
                $success = $this->moderationService->supprimerContenu($reportId, $adminId, $motifSuppression);
                $respMsg = "La publication a été supprimée définitivement en base de données, l'auteur a été notifié du motif et tous les signalements liés ont été clôturés.";
                break;

            case 'bloquer':
            case 'bloquer_publication':
            case 'bloquerPublication':
                $success = $this->moderationService->bloquerPublication($reportId, $adminId);
                $respMsg = "La publication a été verrouillée (fermée aux réponses), l'auteur a été notifié et le signalement a été clôturé.";
                break;

            case 'rejeter':
            case 'classer':
            case 'rejeterSignalement':
                $success = $this->moderationService->rejeterSignalement($reportId, $adminId);
                $respMsg = "Le signalement a été marqué comme rejeté (publication jugée conforme).";
                break;

            default:
                $respMsg = "Action de modération non reconnue.";
                break;
        }

        if ($isAjax) {
            header('Content-Type: application/json; charset=UTF-8', true, $success ? 200 : 400);
            echo json_encode([
                'success' => $success,
                'message' => $respMsg,
                'action'  => $action,
                'report_id' => $reportId
            ]);
            exit;
        }

        if ($success) {
            $_SESSION['flash_success'] = $respMsg;
        } else {
            $_SESSION['flash_error'] = "Erreur lors du traitement de l'action de modération.";
        }

        header('Location: ' . url('admin/moderation'));
        exit;
    }
}
