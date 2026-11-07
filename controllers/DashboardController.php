<?php
/**
 * =============================================================
 * controllers/DashboardController.php – Tableau de bord par rôle
 * =============================================================
 * Centralise la logique d'affichage du dashboard selon le profil :
 * - administrateur : vue globale avec tous les modules
 * - délégué : vue filtrée par filière
 * - chef de classe : vue filtrée par classe
 * =============================================================
 */

if (defined('ROOT_PATH')) {
    require_once ROOT_PATH . '/models/Admin.php';
} else {
    require_once dirname(__DIR__) . '/models/Admin.php';
}

class DashboardController {
    private Admin $adminModel;

    public function __construct() {
        $this->adminModel = new Admin();
        $this->checkAccess();
    }

    /**
     * Vérifie que l'utilisateur est connecté.
     * L'accès admin/délégué/chef de classe est autorisé via le rôle.
     */
    private function checkAccess(): void {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        if (empty($_SESSION['user_id'])) {
            $_SESSION['flash_error'] = 'Vous devez être connecté pour accéder au tableau de bord.';
            header('Location: ' . url('login'));
            exit;
        }
    }

    /**
     * Point d'entrée du dashboard.
     */
    public function index(): void {
        $role = strtolower(trim($_SESSION['user_role'] ?? ''));

        switch ($role) {
            case 'administrateur':
            case 'admin':
                $this->adminDashboard();
                return;

            case 'delegue':
            case 'délégué':
                $this->delegueDashboard();
                return;

            case 'chef de classe':
            case 'chef_classe':
            case 'chefclasse':
                $this->chefClasseDashboard();
                return;

            default:
                $this->userDashboard();
                return;
        }
    }

    /**
     * Dashboard admin : vue complète avec gestion utilisateurs, filières et signalements.
     */
    public function adminDashboard(): void {
        global $baseUrl, $base, $assetBase, $asset;

        $kpis = $this->adminModel->getDashboardKPIs();
        $users = $this->adminModel->listUser() ?? [];
        $recentReports = $this->adminModel->getReports('en_attente') ?? [];

        $roleLabel = 'Administrateur';
        $title = 'Dashboard Administrateur';
        $activeAdminPage = 'dashboard';

        require_once ROOT_PATH . '/views/desktop/dashboard/admin.php';
    }

    /**
     * Dashboard délégué : uniquement la filière liée à l'utilisateur.
     */
    public function delegueDashboard(): void {
        global $baseUrl, $base, $assetBase, $asset;

        $userId = (int)($_SESSION['user_id'] ?? 0);
        $userFiliere = trim($_SESSION['user_filiere'] ?? '');

        $kpis = $this->adminModel->getDashboardKPIs();
        $users = $this->adminModel->listUser();
        $filteredUsers = array_values(array_filter($users, function ($user) use ($userFiliere) {
            $userFiliereValue = strtolower(trim((string)($user['filiere'] ?? '')));
            return $userFiliereValue === strtolower($userFiliere);
        }));

        $recentReports = $this->adminModel->getReports('en_attente') ?? [];
        $recentReports = array_values(array_filter($recentReports, function ($report) use ($userFiliere) {
            $reportFiliere = strtolower(trim((string)($report['filiere'] ?? '')));
            return $reportFiliere === strtolower($userFiliere) || empty($reportFiliere);
        }));

        $roleLabel = 'Délégué';
        $title = 'Dashboard Délégué';

        require_once ROOT_PATH . '/views/desktop/dashboard/delegue.php';
    }

    /**
     * Dashboard chef de classe : filtres sur la classe/groupe de l'utilisateur.
     */
    public function chefClasseDashboard(): void {
        global $baseUrl, $base, $assetBase, $asset;

        $userId = (int)($_SESSION['user_id'] ?? 0);
        $userFiliere = trim($_SESSION['user_filiere'] ?? '');
        $userClasse = trim($_SESSION['user_classe'] ?? '');

        $kpis = $this->adminModel->getDashboardKPIs();
        $users = $this->adminModel->listUser();
        $filteredUsers = array_values(array_filter($users, function ($user) use ($userFiliere, $userClasse) {
            $sameFiliere = strtolower(trim((string)($user['filiere'] ?? ''))) === strtolower($userFiliere);
            $sameClasse = !empty($userClasse) && strtolower(trim((string)($user['classe'] ?? ''))) === strtolower($userClasse);
            return $sameFiliere && ($sameClasse || empty($userClasse));
        }));

        $recentReports = $this->adminModel->getReports('en_attente') ?? [];
        $recentReports = array_values(array_filter($recentReports, function ($report) use ($userFiliere, $userClasse) {
            $reportFiliere = strtolower(trim((string)($report['filiere'] ?? '')));
            $reportClasse = strtolower(trim((string)($report['classe'] ?? '')));
            return ($reportFiliere === strtolower($userFiliere) || empty($reportFiliere))
                && ($reportClasse === strtolower($userClasse) || empty($reportClasse));
        }));

        $roleLabel = 'Chef de classe';
        $title = 'Dashboard Chef de classe';

        require_once ROOT_PATH . '/views/desktop/dashboard/chef_classe.php';
    }

    /**
     * Dashboard utilisateur standard.
     */
    public function userDashboard(): void {
        global $baseUrl, $base, $assetBase, $asset;

        $kpis = $this->adminModel->getDashboardKPIs();
        $users = $this->adminModel->listUser();
        $roleLabel = 'Étudiant';
        $title = 'Mes informations';

        require_once ROOT_PATH . '/views/desktop/dashboard/user.php';
    }
}
