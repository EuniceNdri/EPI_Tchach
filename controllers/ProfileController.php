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
    require_once ROOT_PATH . '/models/Avatar.php';
    require_once ROOT_PATH . '/models/Salon.php';
    require_once ROOT_PATH . '/models/Social.php';
} else {
    require_once dirname(__DIR__) . '/models/User.php';
    require_once dirname(__DIR__) . '/models/Notification.php';
    require_once dirname(__DIR__) . '/models/Avatar.php';
    require_once dirname(__DIR__) . '/models/Salon.php';
    require_once dirname(__DIR__) . '/models/Social.php';
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
        $loggedInUser = $currentUserId
            ? $this->userModel->findById((int)$currentUserId)
            : false;

        // Si l'utilisateur consulte son propre profil, on synchronise la session
        if ($user && $currentUserId && $targetUserId == $currentUserId) {
            $_SESSION['user_nom'] = $user['nom'];
            $_SESSION['user_prenom'] = $user['prenom'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_bio'] = $user['biographie'];
            $_SESSION['user_filiere'] = $user['filiere'];
            $_SESSION['user_modules'] = $user['modules_enseignes'];
            $_SESSION['user_avatar'] = $user['avatar_url'] ?? null;
        }

        $isOwnProfile = ($currentUserId && $targetUserId == $currentUserId);
        global $pdo;
        $friendshipStatus = null;
        if ($pdo instanceof PDO && !$isOwnProfile) {
            $friendshipStatus = (new Social($pdo))->getFriendship((int)$currentUserId, (int)$targetUserId);
        }
        if (empty($_SESSION['social_csrf'])) {
            $_SESSION['social_csrf'] = bin2hex(random_bytes(32));
        }
        $socialCsrfToken = $_SESSION['social_csrf'];
        $unreadMessagesCount = $pdo instanceof PDO
            ? (new Social($pdo))->getUnreadMessageCount((int)$currentUserId)
            : 0;
        $composerZones = $pdo instanceof PDO
            ? (new Salon($pdo))->getAccessibleZones(
                (int)$currentUserId,
                strtolower((string)($loggedInUser['role'] ?? $_SESSION['user_role'] ?? 'etudiant'))
            )
            : [];
        $unreadCount = (new Notification($pdo))->countUnread((int)$currentUserId);

        if (!$user) {
            http_response_code(404);
            exit('Profil introuvable.');
        }

        $profilePosts = [];
        $profileZones = [];
        $profileCategories = [];
        $profilePostCategories = [];
        $profileDocuments = [];
        $savedPublications = [];
        $savedAttachments = [];
        $profileCategoryIndex = [];
        $profileStats = ['posts' => 0, 'comments' => 0, 'documents' => 0, 'zones' => 0];
        $allowedProfileTabs = ['posts', 'channels', 'docs', 'about'];
        if ($isOwnProfile) {
            $allowedProfileTabs[] = 'saved';
        }
        $activeProfileTab = in_array($_GET['tab'] ?? '', $allowedProfileTabs, true)
            ? $_GET['tab']
            : 'posts';

        if ($pdo instanceof PDO) {
            $publicPostFilter = $isOwnProfile ? '' : 'AND publication.est_signale = 0';
            $publicPublicationAttachmentFilter = $isOwnProfile ? '' : 'AND publication.est_signale = 0';
            $publicCommentAttachmentFilter = $isOwnProfile
                ? ''
                : 'AND comment.est_signale = 0 AND publication.est_signale = 0';
            $historyLimit = $isOwnProfile ? '' : 'LIMIT 30';
            $postsStatement = $pdo->prepare("
                SELECT publication.id, publication.titre, publication.contenu,
                       publication.type_publication, publication.statut, publication.est_signale,
                       publication.date_creation,
                       category.id AS category_id, category.nom AS category_name,
                       zone.nom AS zone_name,
                       (SELECT COUNT(*) FROM commentaires comment
                        WHERE comment.publication_id = publication.id AND comment.est_signale = 0) AS comments_count,
                       (SELECT COUNT(*) FROM reactions reaction
                        WHERE reaction.type_cible = 'publication'
                          AND reaction.type_reaction = 'like'
                          AND reaction.cible_id = publication.id) AS likes_count,
                       saved.id AS saved_id
                FROM publications publication
                JOIN categories category ON category.id = publication.categorie_id
                JOIN zones zone ON zone.id = category.zone_id
                LEFT JOIN publications_sauvegardees saved
                  ON saved.publication_id = publication.id AND saved.utilisateur_id = :saved_user
                WHERE publication.utilisateur_id = :user_id
                  $publicPostFilter
                ORDER BY publication.date_creation DESC
                $historyLimit
            ");
            $postsStatement->execute([
                'saved_user' => (int)$currentUserId,
                'user_id' => (int)$targetUserId,
            ]);
            $profilePosts = $postsStatement->fetchAll(PDO::FETCH_ASSOC);
            foreach ($profilePosts as $post) {
                $profilePostCategories[(int)$post['category_id']] = [
                    'id' => (int)$post['category_id'],
                    'name' => (string)$post['category_name'],
                    'zone_name' => (string)$post['zone_name'],
                ];
            }

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
                SELECT category.id, category.nom AS name, zone.nom AS zone_name
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
                       category.id AS category_id, category.nom AS category_name, zone.nom AS zone_name,
                       publication.est_signale, saved.id AS saved_id
                FROM pieces_jointes attachment
                JOIN publications publication
                  ON attachment.type_cible = 'publication'
                 AND publication.id = attachment.cible_id
                JOIN categories category ON category.id = publication.categorie_id
                JOIN zones zone ON zone.id = category.zone_id
                LEFT JOIN pieces_jointes_sauvegardees saved
                  ON saved.piece_jointe_id = attachment.id AND saved.utilisateur_id = :saved_user_publication
                WHERE publication.utilisateur_id = :publication_user
                  $publicPublicationAttachmentFilter
                UNION ALL
                SELECT attachment.id, attachment.nom_fichier, attachment.type_mime,
                       attachment.taille, attachment.date_upload,
                       publication.titre AS publication_title,
                       category.id AS category_id,
                       category.nom AS category_name, zone.nom AS zone_name,
                       publication.est_signale, saved.id AS saved_id
                FROM pieces_jointes attachment
                JOIN commentaires comment
                  ON attachment.type_cible = 'commentaire'
                 AND comment.id = attachment.cible_id
                JOIN publications publication ON publication.id = comment.publication_id
                JOIN categories category ON category.id = publication.categorie_id
                JOIN zones zone ON zone.id = category.zone_id
                LEFT JOIN pieces_jointes_sauvegardees saved
                  ON saved.piece_jointe_id = attachment.id AND saved.utilisateur_id = :saved_user_comment
                WHERE comment.utilisateur_id = :comment_user
                  $publicCommentAttachmentFilter
                ORDER BY date_upload DESC
                $historyLimit
            ");
            $documentsStatement->execute([
                'saved_user_publication' => (int)$currentUserId,
                'publication_user' => (int)$targetUserId,
                'saved_user_comment' => (int)$currentUserId,
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
                WHERE utilisateur_id = :user_id " . ($isOwnProfile ? '' : 'AND est_signale = 0') . "
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
                       " . ($isOwnProfile ? '' : 'AND publication.est_signale = 0') . ")
                   OR (attachment.type_cible = 'commentaire'
                       AND comment.utilisateur_id = :comment_user
                       " . ($isOwnProfile ? '' : 'AND comment.est_signale = 0 AND publication.est_signale = 0') . ")
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

            if ($isOwnProfile) {
                $currentRole = strtolower(trim((string)($user['role'] ?? 'etudiant')));
                $canViewAll = in_array($currentRole, ['administrateur', 'admin', 'moderateur'], true);
                $savedPublicationsStatement = $pdo->prepare("
                    SELECT publication.id, publication.titre, publication.contenu,
                           publication.type_publication, publication.statut,
                           publication.date_creation, category.id AS category_id,
                           category.nom AS category_name,
                           zone.id AS zone_id, zone.nom AS zone_name,
                           saved.date_sauvegarde,
                           (SELECT COUNT(*) FROM commentaires comment
                            WHERE comment.publication_id = publication.id AND comment.est_signale = 0) AS comments_count,
                           (SELECT COUNT(*) FROM reactions reaction
                            WHERE reaction.type_cible = 'publication'
                              AND reaction.type_reaction = 'like'
                              AND reaction.cible_id = publication.id) AS likes_count
                    FROM publications_sauvegardees saved
                    JOIN publications publication ON publication.id = saved.publication_id
                    JOIN categories category ON category.id = publication.categorie_id AND category.est_active = 1
                    JOIN zones zone ON zone.id = category.zone_id AND zone.est_active = 1
                    WHERE saved.utilisateur_id = :user_id
                      AND (publication.est_signale = 0 OR publication.utilisateur_id = :owner_user)
                      AND (:can_view_all = 1 OR EXISTS (
                          SELECT 1 FROM appartenance_zones membership
                          WHERE membership.utilisateur_id = :membership_user
                            AND membership.zone_id = zone.id
                      ))
                    ORDER BY saved.date_sauvegarde DESC
                ");
                $savedPublicationsStatement->execute([
                    'user_id' => (int)$currentUserId,
                    'owner_user' => (int)$currentUserId,
                    'can_view_all' => $canViewAll ? 1 : 0,
                    'membership_user' => (int)$currentUserId,
                ]);
                $savedPublications = $savedPublicationsStatement->fetchAll(PDO::FETCH_ASSOC);

                $savedAttachmentsStatement = $pdo->prepare("
                    SELECT attachment.id, attachment.nom_fichier, attachment.type_mime,
                           attachment.taille, attachment.date_upload,
                           publication.titre AS publication_title,
                           category.id AS category_id, category.nom AS category_name, zone.nom AS zone_name,
                           saved.date_sauvegarde
                    FROM pieces_jointes_sauvegardees saved
                    JOIN pieces_jointes attachment ON attachment.id = saved.piece_jointe_id
                    LEFT JOIN publications direct_publication
                      ON attachment.type_cible = 'publication'
                     AND direct_publication.id = attachment.cible_id
                    LEFT JOIN commentaires comment
                      ON attachment.type_cible = 'commentaire'
                     AND comment.id = attachment.cible_id
                    JOIN publications publication
                      ON publication.id = COALESCE(direct_publication.id, comment.publication_id)
                    JOIN categories category ON category.id = publication.categorie_id AND category.est_active = 1
                    JOIN zones zone ON zone.id = category.zone_id AND zone.est_active = 1
                    WHERE saved.utilisateur_id = :user_id
                      AND (publication.est_signale = 0 OR publication.utilisateur_id = :publication_owner
                           OR comment.utilisateur_id = :comment_report_owner)
                      AND (comment.id IS NULL OR comment.est_signale = 0 OR comment.utilisateur_id = :comment_owner)
                      AND (:can_view_all = 1 OR publication.utilisateur_id = :publication_access_owner
                           OR comment.utilisateur_id = :comment_access_owner OR EXISTS (
                          SELECT 1 FROM appartenance_zones membership
                          WHERE membership.utilisateur_id = :membership_user
                            AND membership.zone_id = zone.id
                      ))
                    ORDER BY saved.date_sauvegarde DESC
                ");
                $savedAttachmentsStatement->execute([
                    'user_id' => (int)$currentUserId,
                    'publication_owner' => (int)$currentUserId,
                    'comment_report_owner' => (int)$currentUserId,
                    'comment_owner' => (int)$currentUserId,
                    'publication_access_owner' => (int)$currentUserId,
                    'comment_access_owner' => (int)$currentUserId,
                    'can_view_all' => $canViewAll ? 1 : 0,
                    'membership_user' => (int)$currentUserId,
                ]);
                $savedAttachments = $savedAttachmentsStatement->fetchAll(PDO::FETCH_ASSOC);
            }

            $profileCategoryIndex = $profilePostCategories;
            foreach (array_merge($profileDocuments, $savedAttachments) as $document) {
                $profileCategoryIndex[(int)$document['category_id']] = [
                    'id' => (int)$document['category_id'],
                    'name' => (string)$document['category_name'],
                    'zone_name' => (string)$document['zone_name'],
                ];
            }
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
        $avatarUrl = null;
        if ($nom === '' || $prenom === '') {
            $updateError = 'Le nom et le prénom sont obligatoires.';
        } elseif ($nouveauMotDePasse !== '' && mb_strlen($nouveauMotDePasse, 'UTF-8') < 8) {
            $updateError = 'Le nouveau mot de passe doit contenir au moins 8 caractères.';
        }

        try {
            if ($updateError === '' && $userId) {
                $currentUser = $this->userModel->findById((int)$userId);
                $avatarUrl = Avatar::storeUploadedFile($_FILES['avatar'] ?? []);
                if (!$this->userModel->updateProfile((int)$userId, $nom, $prenom, $biographie, $avatarUrl)) {
                    Avatar::removeStoredFile($avatarUrl);
                    $updateError = 'La mise à jour du profil a échoué.';
                    $errorStatus = 500;
                } else {
                    if ($avatarUrl !== null) {
                        Avatar::removeStoredFile($currentUser['avatar_url'] ?? null);
                    }
                    $_SESSION['user_avatar'] = $avatarUrl ?? ($currentUser['avatar_url'] ?? null);
                    if ($nouveauMotDePasse !== '' && !$this->userModel->updatePassword((int)$userId, $nouveauMotDePasse)) {
                        $updateError = 'La mise à jour du mot de passe a échoué.';
                        $errorStatus = 500;
                    }
                }
            }
        } catch (InvalidArgumentException $exception) {
            $updateError = $exception->getMessage();
        } catch (PDOException $exception) {
            error_log('Mise à jour du profil impossible : ' . $exception->getMessage());
            $updateError = 'La mise à jour du profil a échoué.';
            $errorStatus = 500;
        } catch (RuntimeException $exception) {
            error_log('Stockage de la photo de profil impossible : ' . $exception->getMessage());
            $updateError = "La photo de profil n'a pas pu être enregistrée. Réessayez dans quelques instants.";
            $errorStatus = 503;
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
                    'initials' => $initials,
                    'avatar_url' => !empty($_SESSION['user_avatar'])
                        ? url('public/' . ltrim((string)$_SESSION['user_avatar'], '/'))
                        : null
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
