<?php
/**
 * =============================================================
 * controllers/DashboardController.php – Tableau de bord par rôle
 * =============================================================
 * Centralise la logique d'affichage du dashboard selon le profil :
 * - administrateur : vue globale avec tous les modules
 * - délégué : vue limitée aux zones de délégation attribuées
 * - chef de classe : vue limitée aux zones de filière attribuées
 * =============================================================
 */

if (defined('ROOT_PATH')) {
    require_once ROOT_PATH . '/models/Admin.php';
    require_once ROOT_PATH . '/models/RoleAccess.php';
} else {
    require_once dirname(__DIR__) . '/models/Admin.php';
    require_once dirname(__DIR__) . '/models/RoleAccess.php';
}

class DashboardController {
    private Admin $adminModel;

    public function __construct() {
        $this->adminModel = new Admin();
        $this->checkAccess();
    }

    /**
     * Vérifie que l'utilisateur est connecté.
     * L'accès aux rôles de zone est vérifié dans appartenance_zones.
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
        if ($this->isAdministrator()) {
            $this->adminDashboard();
            return;
        }

        $userId = (int)$_SESSION['user_id'];
        if ($this->adminModel->getPdo() instanceof PDO
            && RoleAccess::isGlobalDelegate($this->adminModel->getPdo(), $userId)) {
            $this->delegueDashboard();
            return;
        }
        if ($this->adminModel->getDashboardZonesForUser($userId, 'delegue_de_classe')) {
            $this->chefClasseDashboard();
            return;
        }

        $this->userDashboard();
    }

    private function isAdministrator(): bool {
        $email = trim((string)($_SESSION['user_email'] ?? ''));
        if ($this->adminModel->getPdo() instanceof PDO) {
            if ($email === '') {
                return false;
            }

            $databaseRole = $this->adminModel->chargeRole($email);
            return in_array($databaseRole, ['administrateur', 'admin'], true);
        }

        $sessionRole = strtolower(trim((string)($_SESSION['user_role'] ?? '')));
        return in_array($sessionRole, ['administrateur', 'admin'], true);
    }

    private function requireDashboardZones(string $scope): array {
        $this->requireDatabaseConnection();

        $zones = $this->adminModel->getDashboardZonesForUser(
            (int)($_SESSION['user_id'] ?? 0),
            $scope
        );

        if (!$zones) {
            http_response_code(403);
            exit('Accès refusé : vous ne possédez pas les droits requis pour ce tableau de bord.');
        }

        return $zones;
    }

    private function requireDatabaseConnection(): void {
        if (!($this->adminModel->getPdo() instanceof PDO)) {
            http_response_code(503);
            exit('Tableau de bord indisponible : la base de données ne répond pas.');
        }
    }

    private function zoneIds(array $zones): array {
        return array_map(static fn (array $zone): int => (int)$zone['id'], $zones);
    }

    private function zoneLabel(array $zones): string {
        return implode(', ', array_column($zones, 'nom'));
    }

    /**
     * Dashboard admin : vue complète avec gestion utilisateurs, filières et signalements.
     */
    public function adminDashboard(): void {
        global $baseUrl, $base, $assetBase, $asset;

        if (!$this->isAdministrator()) {
            http_response_code(403);
            exit('Accès refusé : ce tableau de bord est réservé aux administrateurs.');
        }

        $this->requireDatabaseConnection();

        $kpis = $this->adminModel->getDashboardKPIs();
        $users = $this->adminModel->getRecentUsers(6);
        $recentReports = $this->adminModel->getReports('en_attente', null, 25);

        $roleLabel = 'Administrateur';
        $title = 'Dashboard Administrateur';
        $activeAdminPage = 'dashboard';
        $showAdminLinks = true;

        require_once ROOT_PATH . '/views/desktop/dashboard/admin.php';
    }

    /**
     * Dashboard du délégué général : statistiques et signalements globaux en lecture seule.
     */
    public function delegueDashboard(): void {
        global $baseUrl, $base, $assetBase, $asset;

        $this->requireDashboardZones('delegue');
        $globalKPIs = $this->adminModel->getDashboardKPIs();
        $schoolActivity = $this->adminModel->getSchoolActivityByZone();
        $recentReports = $this->adminModel->getReports('en_attente', null, 8);

        $roleLabel = 'Délégué général';
        $title = 'Vue globale de l’école';
        $showAdminLinks = false;
        $showModerationLink = false;

        require_once ROOT_PATH . '/views/desktop/dashboard/delegue.php';
    }

    /**
     * Dashboard chef de classe : membres des zones de filière attribuées.
     */
    public function chefClasseDashboard(): void {
        global $baseUrl, $base, $assetBase, $asset;

        $zones = $this->requireDashboardZones('delegue_de_classe');
        $zoneIds = $this->zoneIds($zones);
        $filteredUsers = $this->adminModel->getUsersInZones($zoneIds);
        $recentReports = $this->adminModel->getReports('en_attente', $zoneIds);
        $scopeLabel = $this->zoneLabel($zones);

        $roleLabel = 'Délégué de classe';
        $title = 'Dashboard Délégué de classe';
        $showAdminLinks = false;
        $showModerationLink = true;

        require_once ROOT_PATH . '/views/desktop/dashboard/chef_classe.php';
    }

    /**
     * Dashboard utilisateur standard.
     */
    public function userDashboard(): void {
        global $baseUrl, $base, $assetBase, $asset;

        $roleLabel = (string)($_SESSION['user_role'] ?? 'Utilisateur');
        $title = 'Mes informations';
        $showAdminLinks = false;

        require_once ROOT_PATH . '/views/desktop/dashboard/user.php';
    }
}
