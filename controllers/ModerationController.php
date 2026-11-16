<?php
/**
 * =============================================================
 * controllers/ModerationController.php – Contrôleur de Modération
 * =============================================================
 * Espace réservé aux administrateurs et délégués de classe pour examiner
 * les signalements et appliquer les décisions (avertir l'auteur,
 * supprimer le contenu, bloquer la publication, rejeter).
 * =============================================================
 */

if (defined('ROOT_PATH')) {
    require_once ROOT_PATH . '/config/database.php';
    require_once ROOT_PATH . '/models/ModerationService.php';
    require_once ROOT_PATH . '/models/Signalement.php';
    require_once ROOT_PATH . '/models/RoleAccess.php';
} else {
    require_once dirname(__DIR__) . '/config/database.php';
    require_once dirname(__DIR__) . '/models/ModerationService.php';
    require_once dirname(__DIR__) . '/models/Signalement.php';
    require_once dirname(__DIR__) . '/models/RoleAccess.php';
}

class ModerationController {
    private ?PDO $pdo;
    private ModerationService $moderationService;
    private Signalement $signalementModel;
    private bool $isAdministrator = false;
    /** @var int[] */
    private array $classDelegateZones = [];

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
        $this->moderationService = new ModerationService($this->pdo);
        $this->signalementModel = new Signalement($this->pdo);

        $this->checkModerationAccess();
    }

    /**
     * L'administration voit toute la plateforme; un délégué de classe reste limité à ses filières.
     */
    private function checkModerationAccess(): void {
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

        if ($userId > 0 && $this->pdo instanceof PDO) {
            $this->isAdministrator = RoleAccess::isAdministrator($this->pdo, $userId);
            $this->classDelegateZones = RoleAccess::classDelegateZoneIds($this->pdo, $userId);
        }

        if ($userId <= 0 || (!$this->isAdministrator && !$this->classDelegateZones)) {
            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8', true, 403);
                echo json_encode(['success' => false, 'message' => 'Accès réservé à l’administration et aux délégués de classe.']);
                exit;
            }

            $_SESSION['flash_error'] = 'Accès réservé à l’administration et aux délégués de classe.';
            header('Location: ' . url('profile'));
            exit;
        }
    }

    /**
     * Affiche le centre de modération avec la liste des signalements
     */
    public function index(): void {
        global $baseUrl, $base, $assetBase, $asset;

        $statut = $_GET['statut'] ?? null;
        $reports = $this->signalementModel->getAllReports(
            $statut,
            $this->isAdministrator ? null : $this->classDelegateZones
        );
        $activeAdminPage = 'moderation';
        $showAdminLinks = $this->isAdministrator;
        $showModerationLink = true;
        $canModerateReports = true;

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
            $msg = 'Identifiant de signalement invalide.';
            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8', true, 400);
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            $_SESSION['flash_error'] = $msg;
            header('Location: ' . url('moderation'));
            exit;
        }

        if (!($this->pdo instanceof PDO)
            || !RoleAccess::canModerateReport($this->pdo, $adminId, $reportId)) {
            $msg = 'Vous ne pouvez pas traiter ce signalement en dehors de votre périmètre.';
            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8', true, 403);
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            $_SESSION['flash_error'] = $msg;
            header('Location: ' . url('moderation'));
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
