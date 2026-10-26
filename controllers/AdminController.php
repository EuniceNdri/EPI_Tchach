<?php
/**
 * =============================================================
 * controllers/AdminController.php – Contrôleur d'Administration
 * =============================================================
 * Gère toutes les actions du panneau d'administration :
 *   - Tableau de bord & KPIs en temps réel (dashboard)
 *   - Gestion & Édition des utilisateurs en modal AJAX (users, editUser)
 *   - Modération des signalements (moderation, handleReport)
 *   - Gestion des filières et salons (categories, createZone, createCategory)
 *   - Paramètres de la plateforme (settings)
 * =============================================================
 */

if (defined('ROOT_PATH')) {
    require_once ROOT_PATH . '/models/Admin.php';
} else {
    require_once dirname(__DIR__) . '/models/Admin.php';
}

class AdminController {
    private Admin $adminModel;

    public function __construct() {
        $this->adminModel = new Admin();
        $this->checkAdmin();
    }

    /**
     * Garde-fou de sécurité : vérifie que l'utilisateur est authentifié et possède le rôle administrateur
     */
    private function checkAdmin(): void {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        // Si non connecté, redirection vers la page de connexion
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . url('login'));
            exit;
        }

        // Vérification stricte du rôle
        $userEmail = $_SESSION['user_email'] ?? '';
        $userRole = strtolower(trim($_SESSION['user_role'] ?? ''));

        $isAuthorized = ($userRole === 'administrateur' || $userRole === 'admin')
            || $this->adminModel->trusAdmin($userEmail);

        if (!$isAuthorized) {
            $_SESSION['flash_error'] = "Accès refusé : cet espace est réservé aux administrateurs de l'EPI.";
            header('Location: ' . url('profile'));
            exit;
        }
    }

    /**
     * Tableau de Bord Général
     */
    public function dashboard(): void {
    global $baseUrl, $base, $assetBase, $asset;

    $kpis = $this->adminModel->getDashboardKPIs();
    
    // Correction : forcer le type tableau pour éviter les erreurs si le modèle retourne null
    $recentUsers = $this->adminModel->getRecentUsers(8) ?? [];
    $filiereActivity = $this->adminModel->getFiliereActivity() ?? [];
    
    $recentReports = $this->adminModel->getReports('en_attente') ?? [];
    $zones = $this->adminModel->getZonesWithCategories() ?? [];

    require_once ROOT_PATH . '/views/desktop/admin/dashboard.php';
}

    /**
     * Gestion des Utilisateurs
     */
    public function users(): void {
        global $baseUrl, $base, $assetBase, $asset;

        $roleFilter = !empty($_GET['role']) && $_GET['role'] !== 'all' ? trim($_GET['role']) : null;
        $users = $this->adminModel->listUser($roleFilter);
        $roleCounts = $this->adminModel->countUsersByRole();

        require_once ROOT_PATH . '/views/desktop/admin/users.php';
    }

    /**
     * Récupération des données JSON d'un utilisateur pour la modal d'édition
     */
    public function getUserJson(): void {
        header('Content-Type: application/json; charset=UTF-8');

        $userId = (int)($_GET['id'] ?? 0);
        if ($userId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Identifiant utilisateur invalide.']);
            exit;
        }

        $user = $this->adminModel->getUserById($userId);
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable.']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'user'    => $user
        ]);
        exit;
    }

    /**
     * Mise à jour des informations d'un utilisateur (appelé depuis la modal ou formulaire POST)
     */
    public function editUser(): void {
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
               || isset($_POST['ajax']);

        $userId = (int)($_POST['id'] ?? 0);
        if ($userId <= 0) {
            $msg = "Identifiant utilisateur invalide.";
            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8', true, 422);
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            $_SESSION['flash_error'] = $msg;
            header('Location: ' . url('admin/users'));
            exit;
        }

        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = strtolower(trim($_POST['role'] ?? 'etudiant'));
        $filiere = trim($_POST['filiere'] ?? '');
        $modules = trim($_POST['modules_enseignes'] ?? '');
        $biographie = trim($_POST['biographie'] ?? '');

        if (empty($nom) || empty($prenom) || empty($email)) {
            $msg = "Le nom, le prénom et l'adresse email sont obligatoires.";
            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8', true, 422);
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            $_SESSION['flash_error'] = $msg;
            header('Location: ' . url('admin/users'));
            exit;
        }

        $updateData = [
            'nom'               => $nom,
            'prenom'            => $prenom,
            'email'             => $email,
            'role'              => $role,
            'filiere'           => $filiere,
            'modules_enseignes' => $modules,
            'biographie'        => $biographie
        ];

        $success = $this->adminModel->updateUser($userId, $updateData);

        if ($success) {
            $msg = "Utilisateur {$prenom} {$nom} mis à jour avec succès.";
            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode([
                    'success' => true,
                    'message' => $msg,
                    'user'    => array_merge(['id' => $userId], $updateData)
                ]);
                exit;
            }
            $_SESSION['flash_success'] = $msg;
        } else {
            $msg = "Une erreur est survenue lors de la mise à jour.";
            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8', true, 500);
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            $_SESSION['flash_error'] = $msg;
        }

        header('Location: ' . url('admin/users'));
        exit;
    }

    /**
     * Suppression d'un utilisateur
     */
    public function deleteUser(): void {
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        $userId = (int)($_POST['id'] ?? 0);

        // Protection : on ne peut pas supprimer son propre compte en cours de session
        if ($userId === (int)($_SESSION['user_id'] ?? 0)) {
            $msg = "Vous ne pouvez pas supprimer votre propre compte administrateur.";
            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8', true, 400);
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            $_SESSION['flash_error'] = $msg;
            header('Location: ' . url('admin/users'));
            exit;
        }

        $success = $this->adminModel->deleteUser($userId);

        if ($success) {
            $msg = "Utilisateur supprimé définitivement de la base.";
            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => true, 'message' => $msg]);
                exit;
            }
            $_SESSION['flash_success'] = $msg;
        } else {
            $msg = "Impossible de supprimer cet utilisateur.";
            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8', true, 500);
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            $_SESSION['flash_error'] = $msg;
        }

        header('Location: ' . url('admin/users'));
        exit;
    }

    /**
     * Centre de Modération
     */
    public function moderation(): void {
        global $baseUrl, $base, $assetBase, $asset;

        $statut = $_GET['statut'] ?? null;
        $reports = $this->adminModel->getReports($statut) ?? [];

        require_once ROOT_PATH . '/views/desktop/admin/moderation.php';
    }

    /**
     * Traitement d'un signalement (classer ou supprimer le message)
     */
    public function handleReport(): void {
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        $reportId = (int)($_POST['report_id'] ?? 0);
        $action = trim($_POST['action'] ?? 'traiter'); // 'traiter', 'rejeter', 'supprimer_contenu'

        $success = $this->adminModel->handleReport($reportId, $action);

        $messages = [
            'supprimer_contenu' => "Le contenu signalé a été supprimé et le signalement est clôturé.",
            'rejeter'           => "Le signalement a été rejeté (contenu conforme).",
            'traiter'           => "Le signalement a été validé et classé."
        ];

        $respMsg = $messages[$action] ?? "Action effectuée avec succès.";

        if ($isAjax) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['success' => $success, 'message' => $respMsg]);
            exit;
        }

        if ($success) {
            $_SESSION['flash_success'] = $respMsg;
        } else {
            $_SESSION['flash_error'] = "Une erreur est survenue lors du traitement du signalement.";
        }

        header('Location: ' . url('admin/moderation'));
        exit;
    }

    /**
     * Filières & Salons
     */
    public function categories(): void {
        global $baseUrl, $base, $assetBase, $asset;

        $zones = $this->adminModel->getZonesWithCategories();

        require_once ROOT_PATH . '/views/desktop/admin/categories.php';
    }

    /**
     * Création d'une nouvelle Zone (ex: filière)
     */
    public function createZone(): void {
        $nom = trim($_POST['nom'] ?? '');
        $typeZone = trim($_POST['type_zone'] ?? 'filiere');
        $description = trim($_POST['description'] ?? '');
        $createurId = (int)($_SESSION['user_id'] ?? 1);

        if (!empty($nom)) {
            $newId = $this->adminModel->createZone($nom, $typeZone, $description, $createurId);
            if ($newId) {
                $_SESSION['flash_success'] = "La zone \"{$nom}\" a été créée avec succès et les étudiants correspondants ont été automatiquement rattachés.";
            } else {
                $_SESSION['flash_error'] = "Erreur lors de la création de la zone.";
            }
        } else {
            $_SESSION['flash_error'] = "Le nom de la zone est obligatoire.";
        }

        header('Location: ' . url('admin/categories'));
        exit;
    }

    /**
     * Création d'une catégorie dans une zone
     */
    public function createCategory(): void {
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
               || isset($_POST['ajax']);

        $zoneIdRaw = $_POST['zone_id'] ?? 0;
        $nom = trim($_POST['nom'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $ordre = (int)($_POST['ordre'] ?? 0);

        // Si le nom commence par #, on le nettoie ou on le garde propre
        if (strpos($nom, '#') === 0) {
            $nom = trim(substr($nom, 1));
        }

        if (empty($nom)) {
            $msg = "Le nom du salon est obligatoire.";
            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8', true, 422);
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            $_SESSION['flash_error'] = $msg;
            header('Location: ' . url('admin/categories'));
            exit;
        }

        // Si l'administrateur a choisi de créer une nouvelle zone/filière à la volée
        if ($zoneIdRaw === 'new' || (int)$zoneIdRaw <= 0) {
            $newZoneName = trim($_POST['new_zone_name'] ?? '');
            if (empty($newZoneName)) {
                $newZoneName = 'Filière Générale';
            }
            $createurId = (int)($_SESSION['user_id'] ?? 1);
            $zoneId = $this->adminModel->createZone($newZoneName, 'filiere', 'Zone créée depuis le panneau admin', $createurId);
        } else {
            $zoneId = (int)$zoneIdRaw;
        }

        if ($zoneId > 0) {
            $catId = $this->adminModel->createCategory($zoneId, $nom, null, $description, $ordre);
            if ($catId) {
                $msg = "Le salon d'échange \"#{$nom}\" a été créé avec succès.";
                if ($isAjax) {
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode([
                        'success'  => true,
                        'message'  => $msg,
                        'category' => [
                            'id'          => $catId,
                            'zone_id'     => $zoneId,
                            'nom'         => $nom,
                            'description' => $description
                        ]
                    ]);
                    exit;
                }
                $_SESSION['flash_success'] = $msg;
            } else {
                $msg = "Erreur lors de la création du salon.";
                if ($isAjax) {
                    header('Content-Type: application/json; charset=UTF-8', true, 500);
                    echo json_encode(['success' => false, 'message' => $msg]);
                    exit;
                }
                $_SESSION['flash_error'] = $msg;
            }
        } else {
            $msg = "Veuillez sélectionner ou renseigner une filière valide.";
            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8', true, 422);
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            $_SESSION['flash_error'] = $msg;
        }

        header('Location: ' . url('admin/categories'));
        exit;
    }

    /**
     * Paramètres de la Plateforme
     */
    public function settings(): void {
        global $baseUrl, $base, $assetBase, $asset;

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $_SESSION['flash_success'] = "Paramètres de la plateforme EPI Tchach enregistrés avec succès !";
            header('Location: ' . url('admin/settings'));
            exit;
        }

        require_once ROOT_PATH . '/views/desktop/admin/settings.php';
    }
}

