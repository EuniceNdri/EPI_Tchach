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
    require_once ROOT_PATH . '/models/Notification.php';
    require_once ROOT_PATH . '/models/PublicationMetadata.php';
    require_once ROOT_PATH . '/models/RoleAccess.php';
} else {
    require_once dirname(__DIR__) . '/models/Salon.php';
    require_once dirname(__DIR__) . '/models/User.php';
    require_once dirname(__DIR__) . '/models/Notification.php';
    require_once dirname(__DIR__) . '/models/PublicationMetadata.php';
    require_once dirname(__DIR__) . '/models/RoleAccess.php';
}

class SalonController {
    private Salon $salonModel;
    private PublicationMetadata $publicationMetadata;
    private ?PDO $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo instanceof PDO ? $pdo : null;
        $this->salonModel = new Salon($this->pdo);
        $this->publicationMetadata = new PublicationMetadata($this->pdo);

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

        $userId = (int)$_SESSION['user_id'];
        if (!($this->pdo instanceof PDO)) {
            http_response_code(503);
            exit('Salon indisponible : la base de données ne répond pas.');
        }

        $roleStatement = $this->pdo->prepare('SELECT role FROM utilisateurs WHERE id = :user_id LIMIT 1');
        $roleStatement->execute(['user_id' => $userId]);
        $storedRole = $roleStatement->fetchColumn();
        if (!is_string($storedRole) || $storedRole === '') {
            http_response_code(401);
            exit('Votre compte n’est plus disponible. Veuillez vous reconnecter.');
        }

        $userRole = strtolower(trim($storedRole));
        $_SESSION['user_role'] = $userRole;
        $this->salonModel->touchActivity($userId);
        $zones = $this->salonModel->getAccessibleZones($userId, $userRole);
        $requestedZoneId = isset($_GET['zone']) ? max(0, (int)$_GET['zone']) : 0;
        $selectedZone = null;
        if ($requestedZoneId > 0) {
            foreach ($zones as $zone) {
                if ((int)$zone['id'] === $requestedZoneId) {
                    $selectedZone = $zone;
                    break;
                }
            }
            if ($selectedZone === null) {
                http_response_code(403);
                exit('Vous n’avez pas accès à ce salon.');
            }
        }

        $targetPublicationIdRaw = $_GET['pub'] ?? 0;
        $targetPublicationId = is_scalar($targetPublicationIdRaw) ? max(0, (int)$targetPublicationIdRaw) : 0;
        $feedView = $targetPublicationId > 0 ? 'latest' : ($_GET['view'] ?? 'latest');
        if (!in_array($feedView, ['latest', 'popular', 'saved', 'documents'], true)) {
            $feedView = 'latest';
        }
        $categoryId = isset($_GET['category']) ? max(0, (int)$_GET['category']) : null;
        if ($categoryId !== null && $categoryId > 0) {
            $categoryIsInSelectedZone = $selectedZone === null
                || in_array($categoryId, array_map(
                    static fn (array $category): int => (int)$category['id'],
                    $selectedZone['categories']
                ), true);
            if (!$categoryIsInSelectedZone
                || !$this->salonModel->canAccessCategory($userId, $categoryId, $userRole)) {
                http_response_code(403);
                exit('Vous n’avez pas accès à cette catégorie.');
            }
        }
        $composerZones = $selectedZone !== null ? [$selectedZone] : $zones;
        $announcementCategoryPermissions = [];
        foreach ($composerZones as $composerZone) {
            foreach ($composerZone['categories'] as $composerCategory) {
                $composerCategoryId = (int)$composerCategory['id'];
                $announcementCategoryPermissions[$composerCategoryId] = RoleAccess::canPublishAnnouncement(
                    $this->pdo,
                    $userId,
                    $composerCategoryId
                );
            }
        }
        $activeTags = $this->publicationMetadata->getActiveTags();
        $requestedTagRaw = $_GET['tag'] ?? 0;
        $requestedTagId = is_scalar($requestedTagRaw) ? max(0, (int)$requestedTagRaw) : 0;
        $selectedTag = $requestedTagId > 0 ? $this->publicationMetadata->getTag($requestedTagId) : null;
        $tagId = $selectedTag ? (int)$selectedTag['id'] : null;
        $feedPosts = $feedView === 'documents' ? [] : $this->salonModel->getPublications(
            $userId,
            $userRole,
            $feedView,
            $categoryId,
            50,
            $selectedZone === null ? null : (int)$selectedZone['id'],
            $targetPublicationId > 0 ? null : $tagId,
            $targetPublicationId > 0 ? $targetPublicationId : null
        );
        $publicationTags = $this->publicationMetadata->getPublicationTags(array_column($feedPosts, 'id'));
        $publicationMentions = $this->publicationMetadata->getPublicationMentions(array_column($feedPosts, 'id'));
        $pollOptions = $this->salonModel->getPublicationPolls(
            array_column($feedPosts, 'id'),
            $userId
        );
        $postAttachments = $this->salonModel->getPublicationAttachments(
            array_column($feedPosts, 'id'),
            $userId
        );
        $savedAttachments = $feedView === 'saved'
            ? $this->salonModel->getSavedAttachments($userId, $userRole)
            : [];
        $salonDocuments = $feedView === 'documents'
            ? $this->salonModel->getSalonDocuments(
                $userId,
                $userRole,
                $categoryId,
                $selectedZone === null ? null : (int)$selectedZone['id']
            )
            : [];
        $popularPosts = $this->salonModel->getPopularPublications($userId, $userRole, 5);
        $onlineUsers = $this->salonModel->getOnlineUsers();
        $unreadCount = (new Notification($this->pdo))->countUnread($userId);
        $flashSuccess = $_SESSION['flash_success'] ?? '';
        $flashError = $_SESSION['flash_error'] ?? '';
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require_once ROOT_PATH . '/views/desktop/salon/salon_generale.php';
    }

    public function mentionableUsers(): void {
        header('Content-Type: application/json; charset=UTF-8');
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
            header('Allow: GET');
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
            exit;
        }

        if (!($this->pdo instanceof PDO)) {
            http_response_code(503);
            echo json_encode(['success' => false, 'message' => 'La base de données est indisponible.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $categoryIdRaw = $_GET['category_id'] ?? 0;
        $queryRaw = $_GET['q'] ?? '';
        $categoryId = is_scalar($categoryIdRaw) ? max(0, (int)$categoryIdRaw) : 0;
        $query = is_scalar($queryRaw) ? mb_substr(trim((string)$queryRaw), 0, 100) : '';
        if ($categoryId <= 0 || mb_strlen($query) < 2) {
            echo json_encode(['success' => true, 'users' => []], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $roleStatement = $this->pdo->prepare('SELECT role FROM utilisateurs WHERE id = :user_id LIMIT 1');
        $roleStatement->execute(['user_id' => $userId]);
        $role = $roleStatement->fetchColumn();
        if (!is_string($role) || !$this->salonModel->canAccessCategory($userId, $categoryId, strtolower(trim($role)))) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Vous ne pouvez pas mentionner des personnes dans ce salon.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $users = $this->publicationMetadata->findMentionableUsers($userId, $categoryId, $query);
        echo json_encode(['success' => true, 'users' => $users], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function toggleSaved(): void {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Allow: POST');
            http_response_code(405);
            exit('Méthode non autorisée.');
        }

        $userId = (int)$_SESSION['user_id'];
        global $pdo;
        if ($pdo instanceof PDO) {
            $roleStatement = $pdo->prepare('SELECT role FROM utilisateurs WHERE id = :user_id LIMIT 1');
            $roleStatement->execute(['user_id' => $userId]);
            $storedRole = $roleStatement->fetchColumn();
            if (!is_string($storedRole) || $storedRole === '') {
                http_response_code(401);
                exit('Votre compte n’est plus disponible. Veuillez vous reconnecter.');
            }
            $userRole = strtolower(trim($storedRole));
            $_SESSION['user_role'] = $userRole;
        } else {
            http_response_code(503);
            exit('La base de données est indisponible.');
        }
        $targetType = $_POST['target_type'] ?? '';
        $targetId = (int)($_POST['target_id'] ?? 0);

        if ($targetType === 'publication') {
            $isSaved = $this->salonModel->toggleSavedPublication($userId, $targetId, $userRole);
        } elseif ($targetType === 'attachment') {
            $isSaved = $this->salonModel->toggleSavedAttachment($userId, $targetId, $userRole);
        } else {
            $isSaved = null;
        }

        if ($isSaved === null) {
            $_SESSION['flash_error'] = 'Cet élément ne peut pas être sauvegardé depuis votre espace.';
        } else {
            $_SESSION['flash_success'] = $isSaved
                ? 'Élément ajouté à vos sauvegardes.'
                : 'Élément retiré de vos sauvegardes.';
        }

        $returnPage = $_POST['return_page'] ?? '';
        if ($returnPage === 'profile') {
            $tab = in_array($_POST['return_tab'] ?? '', ['posts', 'channels', 'docs', 'saved', 'about'], true)
                ? $_POST['return_tab']
                : 'saved';
            header('Location: ' . url('profile') . '?tab=' . rawurlencode($tab));
            exit;
        }

        $view = in_array($_POST['return_view'] ?? '', ['latest', 'popular', 'saved'], true)
            ? $_POST['return_view']
            : 'latest';
        $categoryId = max(0, (int)($_POST['return_category'] ?? 0));
        $returnUrl = url('salon') . '?view=' . rawurlencode($view);
        if ($categoryId > 0) {
            $returnUrl .= '&category=' . $categoryId;
        }
        header('Location: ' . $returnUrl);
        exit;
    }

    public function presence(): void {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Allow: POST');
            header('Content-Type: application/json; charset=UTF-8', true, 405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
            exit;
        }

        $success = $this->salonModel->touchActivity((int)$_SESSION['user_id']);
        header('Content-Type: application/json; charset=UTF-8', true, $success ? 200 : 500);
        echo json_encode(['success' => $success]);
        exit;
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