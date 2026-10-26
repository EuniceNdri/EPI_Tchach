<?php
/**
 =============================================================
 controllers/SalonController.php – Contrôleur des Salons et Zones
 =============================================================
 Gère l'affichage et la gestion des salons, zones et catégories pour EPI Tchach :
 - Listing des salons et catégories (index)
 - Création de zones / filières
 - Création de catégories / salons d'échange
 =============================================================
 */

if (defined('ROOT_PATH')) {
    require_once ROOT_PATH . '/models/Salon.php';
    require_once ROOT_PATH . '/models/User.php';
} else {
    require_once dirname(__DIR__) . '/models/Salon.php';
    require_once dirname(__DIR__) . '/models/User.php';
}

class SalonController {
    private Salon $salonModel;
    private ?PDO $pdo;

    public function __construct() {
        // Récupération de la connexion PDO via le modèle User parent ou similaire
        $userModel = new User();
        $this->pdo = method_exists($userModel, 'getPdo') ? $userModel->getPdo() : null;
        $this->salonModel = new Salon($this->pdo);

        $this->checkAuth();
    }

    /**
     * Garde-fou de sécurité : vérifie que l'utilisateur est authentifié
     */
    private function checkAuth(): void {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        if (empty($_SESSION['user_id'])) {
            header('Location: ' . url('login'));
            exit;
        }
    }

    /**
     * Affiche la liste des zones, filières et salons d'échange
     */
    public function index(): void {
        global $baseUrl, $base, $assetBase, $asset;

        $zones = $this->salonModel->getZonesWithCategories();
        $filiereActivity = $this->salonModel->getFiliereActivity();

        require_once ROOT_PATH . '/views/desktop/salon/salon_generale.php';
    }

    /**
     * Création d'une nouvelle Zone / Filière
     */
    public function createZone(): void {
        $nom = trim($_POST['nom'] ?? '');
        $typeZone = trim($_POST['type_zone'] ?? 'filiere');
        $description = trim($_POST['description'] ?? '');
        $createurId = (int)($_SESSION['user_id'] ?? 1);

        if (!empty($nom)) {
            $newId = $this->salonModel->createZone($nom, $typeZone, $description, $createurId);
            if ($newId) {
                $_SESSION['flash_success'] = "La zone \"{$nom}\" a été créée avec succès.";
            } else {
                $_SESSION['flash_error'] = "Erreur lors de la création de la zone.";
            }
        } else {
            $_SESSION['flash_error'] = "Le nom de la zone est obligatoire.";
        }

        header('Location: ' . url('salons'));
        exit;
    }

    /**
     * Création d'une catégorie / salon d'échange au sein d'une zone
     */
    public function createCategory(): void {
        $zoneId = (int)($_POST['zone_id'] ?? 0);
        $nom = trim($_POST['nom'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $ordre = (int)($_POST['ordre'] ?? 0);

        if (strpos($nom, '#') === 0) {
            $nom = trim(substr($nom, 1));
        }

        if ($zoneId > 0 && !empty($nom)) {
            $catId = $this->salonModel->createCategory($zoneId, $nom, null, $description, $ordre);
            if ($catId) {
                $_SESSION['flash_success'] = "Le salon d'échange \"#{$nom}\" a été créé avec succès.";
            } else {
                $_SESSION['flash_error'] = "Erreur lors de la création du salon.";
            }
        } else {
            $_SESSION['flash_error'] = "Veuillez sélectionner une zone valide et renseigner un nom de salon.";
        }

        header('Location: ' . url('salons'));
        exit;
    }
}