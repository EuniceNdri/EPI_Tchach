<?php
/**
 * =============================================================
 * controllers/PublicationController.php – Contrôleur des Publications
 * =============================================================
 * Gère la création des publications et commentaires avec
 * intégration du service de modération automatique (ModerationService).
 * =============================================================
 */

if (defined('ROOT_PATH')) {
    require_once ROOT_PATH . '/models/ModerationService.php';
    require_once ROOT_PATH . '/config/database.php';
} else {
    require_once dirname(__DIR__) . '/models/ModerationService.php';
    require_once dirname(__DIR__) . '/config/database.php';
}

class PublicationController {
    private ?PDO $pdo;
    private ModerationService $moderationService;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
        $this->moderationService = new ModerationService($this->pdo);
    }

    /**
     * Création d'une publication avec modération automatique
     */
    public function create(): void {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        if (empty($_SESSION['user_id'])) {
            $_SESSION['flash_error'] = "Vous devez être connecté pour publier.";
            header('Location: ' . url('login'));
            exit;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Location: ' . url());
            exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $categorieId = (int)($_POST['categorie_id'] ?? 0);
        $titre = trim($_POST['titre'] ?? '');
        $contenu = trim($_POST['contenu'] ?? '');
        $typePublication = trim($_POST['type_publication'] ?? 'discussion');

        if (empty($titre) || empty($contenu) || $categorieId <= 0) {
            $_SESSION['flash_error'] = "Tous les champs (titre, contenu, catégorie) sont obligatoires.";
            header('Location: ' . url());
            exit;
        }

        // 1. Analyse du titre et du contenu par ModerationService
        $analyseTitre = $this->moderationService->analyser($titre);
        $analyseContenu = $this->moderationService->analyser($contenu);

        if ($analyseTitre['status'] === 'bloquer' || $analyseContenu['status'] === 'bloquer') {
            $_SESSION['flash_error'] = "Publication refusée : le texte contient des termes non autorisés.";
            header('Location: ' . url());
            exit;
        }

        $titreFinal = $analyseTitre['content'];
        $contenuFinal = $analyseContenu['content'];

        $estSignale = ($analyseTitre['status'] === 'signaler' || $analyseContenu['status'] === 'signaler') ? 1 : 0;

        // 2. Insertion en base de données
        if (!$this->pdo) {
            $_SESSION['flash_error'] = "Erreur de connexion à la base de données.";
            header('Location: ' . url());
            exit;
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO publications (utilisateur_id, categorie_id, titre, contenu, type_publication, statut, est_signale, date_creation)
            VALUES (:user_id, :cat_id, :titre, :contenu, :type_pub, 'ouvert', :est_signale, NOW())
        ");

        $success = $stmt->execute([
            'user_id'     => $userId,
            'cat_id'      => $categorieId,
            'titre'       => $titreFinal,
            'contenu'     => $contenuFinal,
            'type_pub'    => $typePublication,
            'est_signale' => $estSignale
        ]);

        if ($success) {
            $pubId = (int)$this->pdo->lastInsertId();

            // 3. Si le contenu est signalé, créer un signalement automatique dans la table signalements
            if ($estSignale) {
                $this->moderationService->creerSignalementAutomatique('publication', $pubId);
            }

            $_SESSION['flash_success'] = $estSignale
                ? "Votre publication a été enregistrée mais fait l'objet d'une vérification de modération."
                : "Publication créée avec succès !";
        } else {
            $_SESSION['flash_error'] = "Une erreur est survenue lors de l'enregistrement de la publication.";
        }

        header('Location: ' . url());
        exit;
    }

    /**
     * Création d'un commentaire avec modération automatique
     */
    public function createComment(): void {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        if (empty($_SESSION['user_id'])) {
            $_SESSION['flash_error'] = "Vous devez être connecté pour commenter.";
            header('Location: ' . url('login'));
            exit;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Location: ' . url());
            exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $publicationId = (int)($_POST['publication_id'] ?? 0);
        $contenu = trim($_POST['contenu'] ?? '');

        if (empty($contenu) || $publicationId <= 0) {
            $_SESSION['flash_error'] = "Le contenu du commentaire est obligatoire.";
            header('Location: ' . url());
            exit;
        }

        // 1. Analyse du commentaire par ModerationService
        $analyse = $this->moderationService->analyser($contenu);

        if ($analyse['status'] === 'bloquer') {
            $_SESSION['flash_error'] = "Commentaire refusé : termes inappropriés détectés.";
            header('Location: ' . url());
            exit;
        }

        $contenuFinal = $analyse['content'];
        $estSignale = ($analyse['status'] === 'signaler') ? 1 : 0;

        // 2. Insertion en base de données
        if (!$this->pdo) {
            $_SESSION['flash_error'] = "Erreur de connexion à la base de données.";
            header('Location: ' . url());
            exit;
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO commentaires (publication_id, utilisateur_id, contenu, est_signale, date_creation)
            VALUES (:pub_id, :user_id, :contenu, :est_signale, NOW())
        ");

        $success = $stmt->execute([
            'pub_id'      => $publicationId,
            'user_id'     => $userId,
            'contenu'     => $contenuFinal,
            'est_signale' => $estSignale
        ]);

        if ($success) {
            $commentId = (int)$this->pdo->lastInsertId();

            if ($estSignale) {
                $this->moderationService->creerSignalementAutomatique('commentaire', $commentId);
            }

            $_SESSION['flash_success'] = "Commentaire ajouté avec succès.";
        } else {
            $_SESSION['flash_error'] = "Erreur lors de l'ajout du commentaire.";
        }

        header('Location: ' . url());
        exit;
    }
}
