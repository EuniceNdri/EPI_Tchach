<?php
/**
 =============================================================
 controllers/AdminController.php – Contrôleur d'Administration
 =============================================================
 Gère toutes les actions du panneau d'administration :
 - Tableau de bord & KPIs en temps réel (dashboard)
 - Gestion & Édition des utilisateurs en modal AJAX (users, editUser)
 - Modération des signalements (moderation, handleReport)
 - Gestion des filières et salons (categories, createZone, createCategory via le modèle Salon)
 - Paramètres de la plateforme (settings)
 =============================================================
 */

if (defined('ROOT_PATH')) {
    require_once ROOT_PATH . '/models/Admin.php';
    require_once ROOT_PATH . '/models/Salon.php';
    require_once ROOT_PATH . '/models/LandingContent.php';
    require_once ROOT_PATH . '/models/PublicationMetadata.php';
} else {
    require_once dirname(__DIR__) . '/models/Admin.php';
    require_once dirname(__DIR__) . '/models/Salon.php';
    require_once dirname(__DIR__) . '/models/LandingContent.php';
    require_once dirname(__DIR__) . '/models/PublicationMetadata.php';
}

class AdminController {
    private Admin $adminModel;
    private Salon $salonModel;

    public function __construct() {
        $this->adminModel = new Admin();
        // Récupération de la connexion PDO du modèle Admin pour l'injecter dans Salon
        $pdo = method_exists($this->adminModel, 'getPdo') ? $this->adminModel->getPdo() : null;
        $this->salonModel = new Salon($pdo);
        
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

        // Prefer the current database role so revoked privileges do not persist in a stale session.
        $userEmail = $_SESSION['user_email'] ?? '';
        $pdo = $this->adminModel->getPdo();

        if (!($pdo instanceof PDO)) {
            http_response_code(503);
            exit('Espace administration indisponible : la base de données ne répond pas.');
        }

        $userRole = strtolower(trim((string)$this->adminModel->chargeRole($userEmail)));
        $isAuthorized = in_array($userRole, ['administrateur', 'admin'], true);

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
        
        $recentUsers = $this->adminModel->getRecentUsers(8) ?? [];
        $filiereActivity = $this->salonModel->getFiliereActivity() ?? [];
        
        $recentReports = $this->adminModel->getReports('en_attente') ?? [];
        $zones = $this->salonModel->getZonesWithCategories() ?? [];

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
        $responsibilityZones = $this->adminModel->getResponsibilityZones();

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

        $responsibilities = $this->adminModel->getUserZoneResponsibilities($userId);
        echo json_encode([
            'success' => true,
            'user'    => $user,
            'zone_roles' => $responsibilities,
            'zone_exceptions' => $this->adminModel->getUserZoneExceptions($userId),
        ]);
        exit;
    }

    /**
     * Mise à jour des informations d'un utilisateur
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

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $msg = "Veuillez saisir une adresse email valide.";
            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8', true, 422);
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            $_SESSION['flash_error'] = $msg;
            header('Location: ' . url('admin/users'));
            exit;
        }
        $email = strtolower($email);

        if (!in_array($role, ['etudiant', 'professeur', 'administrateur'], true)) {
            $msg = 'Le rôle de compte sélectionné n’est pas valide.';
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
        $zoneRoles = $_POST['zone_roles'] ?? [];
        $zoneExceptions = $_POST['zone_exceptions'] ?? [];
        if (!is_array($zoneRoles) || !is_array($zoneExceptions)) {
            $msg = 'Les responsabilités par zone sont invalides.';
            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8', true, 422);
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            $_SESSION['flash_error'] = $msg;
            header('Location: ' . url('admin/users'));
            exit;
        }
        $updateData['zone_roles'] = $zoneRoles;
        $updateData['zone_exceptions'] = $zoneExceptions;

        try {
            $success = $this->adminModel->updateUser($userId, $updateData);
        } catch (InvalidArgumentException $exception) {
            $msg = $exception->getMessage();
            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8', true, 422);
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            $_SESSION['flash_error'] = $msg;
            header('Location: ' . url('admin/users'));
            exit;
        }

        if ($success) {
            $updatedUser = $this->adminModel->getUserById($userId);
            $updatedUser['responsabilites'] = implode(', ', array_map(
                static fn (array $zone): string => $zone['nom'] . ' — ' . (
                    $zone['role_dans_zone'] === 'delegue_de_classe' ? 'Délégué de classe' : 'Délégué général'
                ),
                $this->adminModel->getUserResponsibilities($userId)
            ));
            $msg = "Utilisateur {$prenom} {$nom} mis à jour avec succès.";
            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode([
                    'success' => true,
                    'message' => $msg,
                    'user'    => $updatedUser
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
     * Traitement d'un signalement
     */
    public function handleReport(): void {
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        $reportId = (int)($_POST['report_id'] ?? 0);
        $action = trim($_POST['action'] ?? 'traiter');

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

        $zones = $this->salonModel->getZonesWithCategories();
        $publicationMetadata = new PublicationMetadata($this->adminModel->getPdo());
        $publicationTags = $publicationMetadata->getAdminTags();
        if (empty($_SESSION['admin_tag_csrf'])) {
            $_SESSION['admin_tag_csrf'] = bin2hex(random_bytes(32));
        }
        $adminTagCsrf = $_SESSION['admin_tag_csrf'];

        require_once ROOT_PATH . '/views/desktop/admin/categories.php';
    }

    public function tagAction(): void {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Allow: POST');
            http_response_code(405);
            exit('Méthode non autorisée.');
        }

        $token = (string)($_POST['csrf_token'] ?? '');
        if (empty($_SESSION['admin_tag_csrf']) || !hash_equals($_SESSION['admin_tag_csrf'], $token)) {
            $_SESSION['flash_error'] = 'Votre session a expiré. Rechargez la page et réessayez.';
            header('Location: ' . url('admin/categories#tags'));
            exit;
        }

        $tagId = (int)($_POST['tag_id'] ?? 0);
        $action = (string)($_POST['action'] ?? '');
        $metadata = new PublicationMetadata($this->adminModel->getPdo());

        try {
            if ($action === 'create') {
                $metadata->createAdminTag((string)($_POST['nom'] ?? ''), (int)$_SESSION['user_id']);
                $_SESSION['flash_success'] = 'Le tag a été créé et activé.';
            } elseif ($tagId <= 0) {
                throw new InvalidArgumentException('Tag introuvable.');
            } elseif ($action === 'rename') {
                $metadata->renameTag($tagId, (string)($_POST['nom'] ?? ''));
                $_SESSION['flash_success'] = 'Le tag a été renommé.';
            } elseif ($action === 'approve') {
                $metadata->setTagStatus($tagId, 'actif', (int)$_SESSION['user_id']);
                $_SESSION['flash_success'] = 'La proposition de tag est maintenant active.';
            } elseif ($action === 'deactivate') {
                $metadata->setTagStatus($tagId, 'inactif', (int)$_SESSION['user_id']);
                $_SESSION['flash_success'] = 'Le tag a été désactivé.';
            } elseif ($action === 'activate') {
                $metadata->setTagStatus($tagId, 'actif', (int)$_SESSION['user_id']);
                $_SESSION['flash_success'] = 'Le tag a été réactivé.';
            } elseif ($action === 'delete') {
                $metadata->deleteTag($tagId);
                $_SESSION['flash_success'] = 'Le tag a été supprimé.';
            } else {
                throw new InvalidArgumentException('Action sur le tag invalide.');
            }
        } catch (InvalidArgumentException $exception) {
            $_SESSION['flash_error'] = $exception->getMessage();
        } catch (PDOException $exception) {
            error_log('Gestion des tags impossible : ' . $exception->getMessage());
            $_SESSION['flash_error'] = 'Le tag n’a pas pu être enregistré. Vérifiez qu’aucun tag du même nom n’existe.';
        }

        header('Location: ' . url('admin/categories#tags'));
        exit;
    }

    /**
     * Création d'une nouvelle Zone via Salon
     */
    public function createZone(): void {
        $nom = trim($_POST['nom'] ?? '');
        $typeZone = trim($_POST['type_zone'] ?? 'filiere');
        $description = trim($_POST['description'] ?? '');
        $createurId = (int)($_SESSION['user_id'] ?? 1);

        if (!empty($nom)) {
            $newId = $this->salonModel->createZone($nom, $typeZone, $description, $createurId);
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
     * Création d'une catégorie dans une zone via Salon
     */
    public function createCategory(): void {
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
               || isset($_POST['ajax']);

        $zoneIdRaw = $_POST['zone_id'] ?? 0;
        $nom = trim($_POST['nom'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $ordre = (int)($_POST['ordre'] ?? 0);

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

        if ($zoneIdRaw === 'new' || (int)$zoneIdRaw <= 0) {
            $newZoneName = trim($_POST['new_zone_name'] ?? '');
            if (empty($newZoneName)) {
                $newZoneName = 'Filière Générale';
            }
            $createurId = (int)($_SESSION['user_id'] ?? 1);
            $zoneId = $this->salonModel->createZone($newZoneName, 'filiere', 'Zone créée depuis le panneau admin', $createurId);
        } else {
            $zoneId = (int)$zoneIdRaw;
        }

        if ($zoneId > 0) {
            $catId = $this->salonModel->createCategory($zoneId, $nom, null, $description, $ordre);
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
            $this->saveLandingSettings();
            header('Location: ' . url('admin/settings'));
            exit;
        }

        if (empty($_SESSION['admin_settings_csrf'])) {
            $_SESSION['admin_settings_csrf'] = bin2hex(random_bytes(32));
        }

        $pdo = $this->adminModel->getPdo();
        $filieres = [];
        if ($pdo instanceof PDO) {
            try {
                $statement = $pdo->query(
                    'SELECT id, nom, code, description, image_url, slogan
                     FROM filieres
                     WHERE est_active = 1
                     ORDER BY nom ASC'
                );
                $filieres = $statement->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $exception) {
                error_log('Chargement des filières pour les réglages impossible : ' . $exception->getMessage());
                try {
                    $statement = $pdo->query(
                        'SELECT id, nom, code, description, NULL AS image_url, NULL AS slogan
                         FROM filieres
                         WHERE est_active = 1
                         ORDER BY nom ASC'
                    );
                    $filieres = $statement->fetchAll(PDO::FETCH_ASSOC);
                    $_SESSION['flash_error'] = 'La migration de gestion de la landing doit être exécutée avant de pouvoir enregistrer ces réglages.';
                } catch (PDOException $fallbackException) {
                    error_log('Chargement des filières de secours impossible : ' . $fallbackException->getMessage());
                    $_SESSION['flash_error'] = 'Les filières ne peuvent pas être chargées. Vérifiez la connexion et les migrations de la base.';
                }
            }
        } else {
            $_SESSION['flash_error'] = 'La base de données est indisponible.';
        }

        $landingSettings = (new LandingContent($pdo))->getSettings();
        require_once ROOT_PATH . '/views/desktop/admin/settings.php';
    }

    private function saveLandingSettings(): void {
        $token = $_POST['csrf_token'] ?? '';
        if (!is_string($token)) {
            $token = '';
        }
        if (empty($_SESSION['admin_settings_csrf']) || !hash_equals($_SESSION['admin_settings_csrf'], $token)) {
            http_response_code(403);
            $_SESSION['flash_error'] = 'Votre session a expiré. Rechargez la page puis réessayez.';
            return;
        }

        $pdo = $this->adminModel->getPdo();
        if (!$pdo instanceof PDO) {
            $_SESSION['flash_error'] = 'La base de données est indisponible ; les réglages n’ont pas été enregistrés.';
            return;
        }

        $uploadedFiles = [];
        try {
            $existingStatement = $pdo->query('SELECT id, image_url FROM filieres WHERE est_active = 1');
            $existingFilieres = [];
            foreach ($existingStatement->fetchAll(PDO::FETCH_ASSOC) as $filiere) {
                $existingFilieres[(int)$filiere['id']] = $filiere;
            }

            $submittedFilieres = $_POST['filieres'] ?? [];
            if (!is_array($submittedFilieres) || count($submittedFilieres) !== count($existingFilieres)) {
                throw new InvalidArgumentException('La liste des filières reçue est invalide. Rechargez la page puis réessayez.');
            }
            $filiereUploads = $_FILES['filiere_images'] ?? null;
            $heroUploads = $_FILES['hero_images'] ?? null;
            if (($filiereUploads !== null && !is_array($filiereUploads))
                || ($heroUploads !== null && !is_array($heroUploads))) {
                throw new InvalidArgumentException('Les images téléversées sont invalides.');
            }

            $filiereUpdates = [];
            foreach ($submittedFilieres as $id => $fields) {
                $filiereId = filter_var($id, FILTER_VALIDATE_INT);
                if (!$filiereId || !isset($existingFilieres[$filiereId]) || !is_array($fields)) {
                    throw new InvalidArgumentException('Une filière reçue est invalide. Rechargez la page puis réessayez.');
                }

                $slogan = $this->getPostedText($fields, 'slogan');
                $description = $this->getPostedText($fields, 'description');
                if (strlen($slogan) > 180 || strlen($description) > 65535) {
                    throw new InvalidArgumentException('Un slogan ou une description dépasse la longueur autorisée.');
                }

                $imagePath = $existingFilieres[$filiereId]['image_url'];
                $upload = $this->getUploadedFileAt($filiereUploads, $filiereId);
                $newImage = $this->storeLandingImage($upload, $uploadedFiles);
                if ($newImage !== null) {
                    $imagePath = $newImage;
                }
                $filiereUpdates[] = [
                    'id' => $filiereId,
                    'slogan' => $slogan !== '' ? $slogan : null,
                    'description' => $description !== '' ? $description : null,
                    'image_url' => $imagePath,
                ];
            }

            $rawSlides = $_POST['hero_slides'] ?? [];
            if (!is_array($rawSlides) || count($rawSlides) < 1 || count($rawSlides) > 5) {
                throw new InvalidArgumentException('Le carrousel doit contenir entre 1 et 5 photos.');
            }
            $heroSlides = [];
            foreach ($rawSlides as $index => $fields) {
                if (!is_array($fields)) {
                    throw new InvalidArgumentException('Une diapositive du carrousel est invalide.');
                }
                $title = $this->getPostedText($fields, 'title');
                $caption = $this->getPostedText($fields, 'caption');
                $imagePath = $this->validateLandingImagePath($this->getPostedText($fields, 'image'));
                if ($title === '' || strlen($title) > 120 || strlen($caption) > 180) {
                    throw new InvalidArgumentException('Chaque photo du bandeau doit avoir un titre et une légende valides.');
                }
                $newImage = $this->storeLandingImage(
                    $this->getUploadedFileAt($heroUploads, (int)$index),
                    $uploadedFiles
                );
                if ($newImage !== null) {
                    $imagePath = $newImage;
                }
                if ($imagePath === '') {
                    throw new InvalidArgumentException('Ajoutez une image à chaque nouvelle diapositive du bandeau.');
                }
                $heroSlides[] = ['image' => $imagePath, 'title' => $title, 'caption' => $caption];
            }

            $rawStats = $_POST['stats'] ?? [];
            if (!is_array($rawStats) || count($rawStats) !== 4) {
                throw new InvalidArgumentException('Les quatre statistiques de la page d’accueil sont requises.');
            }
            $stats = [];
            foreach ($rawStats as $fields) {
                if (!is_array($fields)) {
                    throw new InvalidArgumentException('Une statistique reçue est invalide.');
                }
                $value = trim($this->getPostedText($fields, 'value'));
                $prefix = trim($this->getPostedText($fields, 'prefix'));
                $suffix = trim($this->getPostedText($fields, 'suffix'));
                $label = trim($this->getPostedText($fields, 'label'));
                $description = trim($this->getPostedText($fields, 'description'));
                if (!preg_match('/^\d{1,9}$/', $value) || $label === '' || strlen($prefix) > 8
                    || strlen($suffix) > 8 || strlen($label) > 80 || strlen($description) > 150) {
                    throw new InvalidArgumentException('Vérifiez les valeurs, libellés et descriptions des statistiques.');
                }
                $stats[] = [
                    'value' => $value,
                    'prefix' => $prefix,
                    'suffix' => $suffix,
                    'label' => $label,
                    'description' => $description,
                ];
            }

            $pdo->beginTransaction();
            $updateFiliere = $pdo->prepare(
                'UPDATE filieres
                 SET image_url = :image_url, slogan = :slogan, description = :description
                 WHERE id = :id AND est_active = 1'
            );
            foreach ($filiereUpdates as $filiere) {
                $updateFiliere->execute($filiere);
            }
            (new LandingContent($pdo))->saveSettings($heroSlides, $stats);
            $pdo->commit();
            $_SESSION['flash_success'] = 'Les visuels des filières, le bandeau et les statistiques ont été enregistrés.';
        } catch (InvalidArgumentException $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $this->removeUploadedFiles($uploadedFiles);
            $_SESSION['flash_error'] = $exception->getMessage();
        } catch (PDOException $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $this->removeUploadedFiles($uploadedFiles);
            error_log('Enregistrement des réglages de la landing impossible : ' . $exception->getMessage());
            $databaseErrorCode = (int)($exception->errorInfo[1] ?? 0);
            $_SESSION['flash_error'] = in_array($databaseErrorCode, [1054, 1146], true)
                ? 'La migration 20261107_add_landing_management.sql doit être exécutée avant d’enregistrer ces réglages.'
                : 'Une erreur de base de données a empêché l’enregistrement des réglages.';
        } catch (RuntimeException $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $this->removeUploadedFiles($uploadedFiles);
            $_SESSION['flash_error'] = $exception->getMessage();
        }
    }

    private function getPostedText(array $fields, string $key): string {
        $value = $fields[$key] ?? '';
        if (!is_string($value)) {
            throw new InvalidArgumentException('Un champ de contenu reçu est invalide.');
        }
        return trim($value);
    }

    private function getUploadedFileAt(?array $files, int $index): array {
        if (!$files || !isset($files['error'][$index])) {
            return ['error' => UPLOAD_ERR_NO_FILE];
        }

        return [
            'error' => $files['error'][$index],
            'name' => $files['name'][$index] ?? '',
            'tmp_name' => $files['tmp_name'][$index] ?? '',
            'size' => $files['size'][$index] ?? 0,
        ];
    }

    private function storeLandingImage(array $file, array &$uploadedFiles): ?string {
        $error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($error !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('Le téléversement d’une image a échoué. Vérifiez la taille du fichier.');
        }
        if ((int)($file['size'] ?? 0) > 5 * 1024 * 1024 || !is_uploaded_file((string)($file['tmp_name'] ?? ''))) {
            throw new InvalidArgumentException('Chaque image doit être un fichier téléversé valide de 5 Mo maximum.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $imageInfo = @getimagesize($file['tmp_name']);
        if (!isset($extensions[$mime]) || !$imageInfo || $imageInfo['mime'] !== $mime) {
            throw new InvalidArgumentException('Format d’image non accepté. Utilisez une image JPG, PNG ou WebP.');
        }

        $directory = ROOT_PATH . '/public/uploads/landing';
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('Le dossier de téléversement des images ne peut pas être créé.');
        }
        $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
        $destination = $directory . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new RuntimeException('Impossible d’enregistrer une image téléversée.');
        }
        $uploadedFiles[] = $destination;

        return 'uploads/landing/' . $filename;
    }

    private function validateLandingImagePath(string $imagePath): string {
        if ($imagePath === '') {
            return '';
        }
        if (preg_match('#^images/[A-Za-z0-9._-]+$#', $imagePath)
            || preg_match('#^uploads/landing/[a-f0-9]{32}\.(?:jpg|png|webp)$#', $imagePath)) {
            return $imagePath;
        }
        throw new InvalidArgumentException('Le chemin d’une image du bandeau est invalide.');
    }

    private function removeUploadedFiles(array $uploadedFiles): void {
        foreach ($uploadedFiles as $path) {
            if (is_file($path) && !unlink($path)) {
                error_log('Impossible de supprimer une image téléversée après échec : ' . $path);
            }
        }
    }
}