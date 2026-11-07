<?php
/**
 * =============================================================
 * controllers/ProfileController.php – Contrôleur du Profil Membre
 * =============================================================
 * Gère l'affichage du profil et la mise à jour asynchrone (AJAX)
 * sans rechargement de page pour une fluidité totale.
 * =============================================================
 */

if (defined('ROOT_PATH')) {
    require_once ROOT_PATH . '/models/User.php';
    require_once ROOT_PATH . '/models/Notification.php';
} else {
    require_once dirname(__DIR__) . '/models/User.php';
    require_once dirname(__DIR__) . '/models/Notification.php';
}

class ProfileController {
    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    /**
     * Affiche la page de profil
     */
    public function index(): void {
        $this->requireAuthentication();
        global $baseUrl, $base, $assetBase, $asset;

        $currentUserId = $_SESSION['user_id'] ?? null;
        $targetUserId = !empty($_GET['id']) ? (int)$_GET['id'] : $currentUserId;
        $user = null;

        if ($targetUserId) {
            $user = $this->userModel->findById((int) $targetUserId);
        }

        // Si l'utilisateur consulte son propre profil, on synchronise la session
        if ($user && $currentUserId && $targetUserId == $currentUserId) {
            $_SESSION['user_nom'] = $user['nom'];
            $_SESSION['user_prenom'] = $user['prenom'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_bio'] = $user['biographie'];
            $_SESSION['user_filiere'] = $user['filiere'];
            $_SESSION['user_modules'] = $user['modules_enseignes'];
        }

        $isOwnProfile = ($currentUserId && $targetUserId == $currentUserId);
        global $pdo;
        $unreadCount = (new Notification($pdo))->countUnread((int)$currentUserId);

        if (!$user) {
            http_response_code(404);
            exit('Profil introuvable.');
        }

        $profilePosts = [];
        $profileZones = [];
        $profileCategories = [];
        $profileDocuments = [];
        $profileStats = ['posts' => 0, 'comments' => 0, 'documents' => 0, 'zones' => 0];

        if ($pdo instanceof PDO) {
            $postsStatement = $pdo->prepare("
                SELECT publication.id, publication.titre, publication.contenu,
                       publication.type_publication, publication.statut, publication.date_creation,
                       category.id AS category_id, category.nom AS category_name,
                       zone.nom AS zone_name,
                       (SELECT COUNT(*) FROM commentaires comment
                        WHERE comment.publication_id = publication.id AND comment.est_signale = 0) AS comments_count,
                       (SELECT COUNT(*) FROM reactions reaction
                        WHERE reaction.type_cible = 'publication'
                          AND reaction.type_reaction = 'like'
                          AND reaction.cible_id = publication.id) AS likes_count
                FROM publications publication
                JOIN categories category ON category.id = publication.categorie_id
                JOIN zones zone ON zone.id = category.zone_id
                WHERE publication.utilisateur_id = :user_id
                  AND publication.est_signale = 0
                ORDER BY publication.date_creation DESC
                LIMIT 30
            ");
            $postsStatement->execute(['user_id' => (int)$targetUserId]);
            $profilePosts = $postsStatement->fetchAll(PDO::FETCH_ASSOC);

            $zonesStatement = $pdo->prepare("
                SELECT zone.id, zone.nom, zone.type_zone, zone.description,
                       membership.role_dans_zone,
                       (SELECT COUNT(*) FROM appartenance_zones members
                        WHERE members.zone_id = zone.id) AS membres_count
                FROM appartenance_zones membership
                JOIN zones zone ON zone.id = membership.zone_id
                WHERE membership.utilisateur_id = :user_id
                  AND zone.est_active = 1
                ORDER BY FIELD(zone.type_zone, 'generale', 'filiere', 'professeurs', 'delegation'),
                         zone.nom
            ");
            $zonesStatement->execute(['user_id' => (int)$targetUserId]);
            $profileZones = $zonesStatement->fetchAll(PDO::FETCH_ASSOC);

            $categoriesStatement = $pdo->prepare("
                SELECT category.id, category.nom, zone.nom AS zone_name
                FROM categories category
                JOIN zones zone ON zone.id = category.zone_id
                JOIN appartenance_zones membership ON membership.zone_id = zone.id
                WHERE membership.utilisateur_id = :user_id
                  AND category.est_active = 1
                  AND zone.est_active = 1
                ORDER BY zone.nom, category.ordre, category.nom
            ");
            $categoriesStatement->execute(['user_id' => (int)$targetUserId]);
            $profileCategories = $categoriesStatement->fetchAll(PDO::FETCH_ASSOC);

            $documentsStatement = $pdo->prepare("
                SELECT attachment.id, attachment.nom_fichier, attachment.type_mime,
                       attachment.taille, attachment.date_upload,
                       publication.titre AS publication_title,
                       category.nom AS category_name
                FROM pieces_jointes attachment
                JOIN publications publication
                  ON attachment.type_cible = 'publication'
                 AND publication.id = attachment.cible_id
                JOIN categories category ON category.id = publication.categorie_id
                WHERE publication.utilisateur_id = :publication_user
                  AND publication.est_signale = 0
                UNION ALL
                SELECT attachment.id, attachment.nom_fichier, attachment.type_mime,
                       attachment.taille, attachment.date_upload,
                       publication.titre AS publication_title,
                       category.nom AS category_name
                FROM pieces_jointes attachment
                JOIN commentaires comment
                  ON attachment.type_cible = 'commentaire'
                 AND comment.id = attachment.cible_id
                JOIN publications publication ON publication.id = comment.publication_id
                JOIN categories category ON category.id = publication.categorie_id
                WHERE comment.utilisateur_id = :comment_user
                  AND comment.est_signale = 0
                  AND publication.est_signale = 0
                ORDER BY date_upload DESC
                LIMIT 30
            ");
            $documentsStatement->execute([
                'publication_user' => (int)$targetUserId,
                'comment_user' => (int)$targetUserId,
            ]);
            $profileDocuments = $documentsStatement->fetchAll(PDO::FETCH_ASSOC);

            $commentsStatement = $pdo->prepare("
                SELECT COUNT(*) FROM commentaires
                WHERE utilisateur_id = :user_id AND est_signale = 0
            ");
            $commentsStatement->execute(['user_id' => (int)$targetUserId]);
            $postCountStatement = $pdo->prepare("
                SELECT COUNT(*) FROM publications
                WHERE utilisateur_id = :user_id AND est_signale = 0
            ");
            $postCountStatement->execute(['user_id' => (int)$targetUserId]);
            $documentCountStatement = $pdo->prepare("
                SELECT COUNT(*)
                FROM pieces_jointes attachment
                LEFT JOIN publications publication
                  ON attachment.type_cible = 'publication'
                 AND publication.id = attachment.cible_id
                LEFT JOIN commentaires comment
                  ON attachment.type_cible = 'commentaire'
                 AND comment.id = attachment.cible_id
                WHERE (attachment.type_cible = 'publication'
                       AND publication.utilisateur_id = :publication_user
                       AND publication.est_signale = 0)
                   OR (attachment.type_cible = 'commentaire'
                       AND comment.utilisateur_id = :comment_user
                       AND comment.est_signale = 0
                       AND publication.est_signale = 0)
            ");
            $documentCountStatement->execute([
                'publication_user' => (int)$targetUserId,
                'comment_user' => (int)$targetUserId,
            ]);

            $profileStats = [
                'posts' => (int)$postCountStatement->fetchColumn(),
                'comments' => (int)$commentsStatement->fetchColumn(),
                'documents' => (int)$documentCountStatement->fetchColumn(),
                'zones' => count($profileZones),
            ];
        }

        require_once ROOT_PATH . '/views/desktop/profile/index.php';
    }

    /**
     * Traitement de mise à jour du profil (AJAX Popup / POST)
     */
    public function update(): void {
        $this->requireAuthentication();
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
               || isset($_POST['ajax']);

        $userId = $_SESSION['user_id'] ?? null;

        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $biographie = trim($_POST['biographie'] ?? '');
        $nouveauMotDePasse = $_POST['nouveau_mot_de_passe'] ?? '';

        $updateError = '';
        $errorStatus = 422;
        if ($nom === '' || $prenom === '') {
            $updateError = 'Le nom et le prénom sont obligatoires.';
        } elseif ($nouveauMotDePasse !== '' && mb_strlen($nouveauMotDePasse, 'UTF-8') < 8) {
            $updateError = 'Le nouveau mot de passe doit contenir au moins 8 caractères.';
        }

        if ($updateError === '' && $userId) {
            if (!$this->userModel->updateProfile((int)$userId, $nom, $prenom, $biographie)) {
                $updateError = 'La mise à jour du profil a échoué.';
                $errorStatus = 500;
            } elseif ($nouveauMotDePasse !== '' && !$this->userModel->updatePassword((int)$userId, $nouveauMotDePasse)) {
                $updateError = 'La mise à jour du mot de passe a échoué.';
                $errorStatus = 500;
            }
        }

        if ($updateError !== '') {
            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8', true, $errorStatus);
                echo json_encode(['success' => false, 'message' => $updateError]);
                exit;
            }
            $_SESSION['flash_error'] = $updateError;
            header('Location: ' . url('profile'));
            exit;
        }

        // Mise à jour systématique de la session
        $_SESSION['user_nom'] = $nom;
        $_SESSION['user_prenom'] = $prenom;
        $_SESSION['user_bio'] = $biographie;

        $initials = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));

        if ($isAjax) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'success' => true,
                'message' => 'Profil mis à jour avec succès !',
                'user' => [
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'fullname' => $prenom . ' ' . $nom,
                    'biographie' => $biographie,
                    'initials' => $initials
                ]
            ]);
            exit;
        }

        header('Location: ' . url('profile'));
        exit;
    }

    private function requireAuthentication(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        if (empty($_SESSION['user_id'])) {
            header('Location: ' . url('login'));
            exit;
        }
    }
}
