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
    require_once ROOT_PATH . '/models/Attachment.php';
    require_once ROOT_PATH . '/models/Salon.php';
    require_once ROOT_PATH . '/models/Social.php';
    require_once ROOT_PATH . '/models/Notification.php';
    require_once ROOT_PATH . '/config/database.php';
} else {
    require_once dirname(__DIR__) . '/models/ModerationService.php';
    require_once dirname(__DIR__) . '/models/Attachment.php';
    require_once dirname(__DIR__) . '/models/Salon.php';
    require_once dirname(__DIR__) . '/models/Social.php';
    require_once dirname(__DIR__) . '/models/Notification.php';
    require_once dirname(__DIR__) . '/config/database.php';
}

class PublicationController {
    private ?PDO $pdo;
    private ModerationService $moderationService;
    private Attachment $attachmentModel;
    private Salon $salonModel;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
        $this->moderationService = new ModerationService($this->pdo);
        $this->attachmentModel = new Attachment();
        $this->salonModel = new Salon($this->pdo);
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
        $returnUrl = $this->publicationReturnUrl();
        try {
            $uploadedFiles = $this->attachmentModel->validate($_FILES['pieces_jointes'] ?? []);
        } catch (InvalidArgumentException $exception) {
            $this->respondError($exception->getMessage(), $returnUrl);
            return;
        }

        if (empty($titre) || empty($contenu) || $categorieId <= 0) {
            $this->respondError("Tous les champs (titre, contenu, catégorie) sont obligatoires.", $returnUrl);
            return;
        }

        if (!in_array($typePublication, ['discussion', 'question', 'annonce'], true)) {
            $this->respondError("Le type de publication sélectionné n'est pas valide.", $returnUrl);
            return;
        }

        $submittedOptions = is_array($_POST['options'] ?? null) ? $_POST['options'] : [];
        $pollOptions = array_values(array_unique(array_filter(
            array_map(static fn ($option): string => is_scalar($option) ? trim((string)$option) : '', $submittedOptions),
            static fn (string $option): bool => $option !== ''
        )));
        if ($typePublication === 'question' && (count($pollOptions) < 2 || count($pollOptions) > 10)) {
            $this->respondError('Un sondage doit contenir entre 2 et 10 choix.', $returnUrl);
            return;
        }
        if ($typePublication === 'question'
            && count(array_filter($pollOptions, static fn (string $option): bool => mb_strlen($option) > 255)) > 0) {
            $this->respondError('Chaque choix du sondage doit contenir au maximum 255 caractères.', $returnUrl);
            return;
        }

        if (!$this->pdo) {
            $this->respondError("Erreur de connexion à la base de données.", $returnUrl);
            return;
        }

        $roleStatement = $this->pdo->prepare('SELECT role FROM utilisateurs WHERE id = :user_id LIMIT 1');
        $roleStatement->execute(['user_id' => $userId]);
        $userRole = $roleStatement->fetchColumn();
        if (!is_string($userRole) || $userRole === '') {
            $this->respondError("Votre compte n'est plus disponible. Veuillez vous reconnecter.", $returnUrl);
            return;
        }
        $userRole = strtolower(trim($userRole));
        $_SESSION['user_role'] = $userRole;

        if (!$this->salonModel->canAccessCategory(
            $userId,
            $categorieId,
            $userRole
        )) {
            $this->respondError("Vous n'avez pas accès au salon sélectionné.", $returnUrl);
            return;
        }

        // 1. Analyse du titre et du contenu par ModerationService
        $analyseTitre = $this->moderationService->analyser($titre);
        $analyseContenu = $this->moderationService->analyser($contenu);

        if ($analyseTitre['status'] === 'bloquer' || $analyseContenu['status'] === 'bloquer') {
            $this->respondError("Publication refusée : le texte contient des termes non autorisés.", $returnUrl);
            return;
        }

        $titreFinal = $analyseTitre['content'];
        $contenuFinal = $analyseContenu['content'];

        $estSignale = ($analyseTitre['status'] === 'signaler' || $analyseContenu['status'] === 'signaler') ? 1 : 0;

        // 2. Insertion en base de données
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare("
            INSERT INTO publications (utilisateur_id, categorie_id, titre, contenu, type_publication, statut, est_signale, date_creation)
            VALUES (:user_id, :cat_id, :titre, :contenu, :type_pub, 'ouvert', :est_signale, NOW())
            ");

            $stmt->execute([
            'user_id'     => $userId,
            'cat_id'      => $categorieId,
            'titre'       => $titreFinal,
            'contenu'     => $contenuFinal,
            'type_pub'    => $typePublication,
            'est_signale' => $estSignale
            ]);

            $pubId = (int)$this->pdo->lastInsertId();
            $attachments = $this->attachmentModel->store($this->pdo, 'publication', $pubId, $uploadedFiles);

            if ($typePublication === 'question') {
                $optionStatement = $this->pdo->prepare(
                    'INSERT INTO sondage_options (publication_id, texte, ordre) VALUES (:publication_id, :texte, :ordre)'
                );
                foreach ($pollOptions as $index => $option) {
                    $optionStatement->execute([
                        'publication_id' => $pubId,
                        'texte' => $option,
                        'ordre' => $index,
                    ]);
                }
            }

            if ($estSignale) {
                $this->moderationService->creerSignalementAutomatique('publication', $pubId);
            }
            $this->pdo->commit();

            $message = $estSignale
                ? "Votre publication a été enregistrée mais fait l'objet d'une vérification de modération."
                : "Publication créée avec succès !";
            $this->respondSuccess($message, [
                'id' => $pubId,
                'title' => $titreFinal,
                'content' => $contenuFinal,
                'tag' => trim($_POST['tag'] ?? 'Général'),
                'attachments' => $attachments,
                'pollOptions' => $typePublication === 'question' ? $pollOptions : [],
                'moderationPending' => (bool)$estSignale,
            ], $returnUrl);
        } catch (PDOException | RuntimeException $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            $this->respondError("La publication n'a pas pu être enregistrée.", $returnUrl);
        }
    }

    private function publicationReturnUrl(): string
    {
        $view = in_array($_POST['return_view'] ?? '', ['latest', 'popular', 'saved'], true)
            ? $_POST['return_view']
            : 'latest';
        $zoneId = max(0, (int)($_POST['return_zone'] ?? 0));
        $categoryId = max(0, (int)($_POST['return_category'] ?? 0));
        $query = ['view' => $view];

        if ($zoneId > 0) {
            $query['zone'] = $zoneId;
        }
        if ($categoryId > 0) {
            $query['category'] = $categoryId;
        }

        return url('salon') . '?' . http_build_query($query);
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
        $parentCommentId = filter_var($_POST['parent_comment_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (!$this->pdo || $publicationId <= 0) {
            $this->respondError('Publication introuvable.');
            return;
        }

        $publicationStatement = $this->pdo->prepare(
            'SELECT categorie_id, type_publication, commentaires_fermes, est_signale
             FROM publications WHERE id = :publication_id LIMIT 1'
        );
        $publicationStatement->execute(['publication_id' => $publicationId]);
        $publication = $publicationStatement->fetch(PDO::FETCH_ASSOC);
        $roleStatement = $this->pdo->prepare('SELECT role FROM utilisateurs WHERE id = :user_id LIMIT 1');
        $roleStatement->execute(['user_id' => $userId]);
        $storedRole = $roleStatement->fetchColumn();
        if (!$publication || !is_string($storedRole) || $storedRole === '') {
            $this->respondError('Publication ou compte utilisateur introuvable.');
            return;
        }
        $userRole = strtolower(trim($storedRole));
        if ((bool)$publication['commentaires_fermes']
            || (bool)$publication['est_signale']
            || !$this->salonModel->canAccessCategory($userId, (int)$publication['categorie_id'], $userRole)) {
            $this->respondError('Les commentaires ne sont pas disponibles pour cette publication.');
            return;
        }

        if ($parentCommentId !== false && $parentCommentId !== null) {
            $parentStatement = $this->pdo->prepare(
                'SELECT id, parent_comment_id
                 FROM commentaires
                 WHERE id = :comment_id AND publication_id = :publication_id AND est_signale = 0
                 LIMIT 1'
            );
            $parentStatement->execute([
                'comment_id' => $parentCommentId,
                'publication_id' => $publicationId,
            ]);
            $parent = $parentStatement->fetch(PDO::FETCH_ASSOC);
            if (!$parent) {
                $this->respondError('Le commentaire auquel vous répondez est introuvable.');
                return;
            }
            $parentCommentId = $parent['parent_comment_id'] !== null
                ? (int)$parent['parent_comment_id']
                : (int)$parent['id'];
        } elseif (array_key_exists('parent_comment_id', $_POST) && $_POST['parent_comment_id'] !== '') {
            $this->respondError('Le commentaire auquel vous répondez est invalide.');
            return;
        }

        try {
            $uploadedFiles = $this->attachmentModel->validate($_FILES['pieces_jointes'] ?? []);
        } catch (InvalidArgumentException $exception) {
            $this->respondError($exception->getMessage());
            return;
        }

        if (empty($contenu) || $publicationId <= 0) {
            $this->respondError("Le contenu du commentaire est obligatoire.");
            return;
        }

        // 1. Analyse du commentaire par ModerationService
        $analyse = $this->moderationService->analyser($contenu);

        if ($analyse['status'] === 'bloquer') {
            $this->respondError("Commentaire refusé : termes inappropriés détectés.");
            return;
        }

        $contenuFinal = $analyse['content'];
        $estSignale = ($analyse['status'] === 'signaler') ? 1 : 0;

        // 2. Insertion en base de données
        if (!$this->pdo) {
            $_SESSION['flash_error'] = "Erreur de connexion à la base de données.";
            header('Location: ' . url());
            exit;
        }

        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare("
            INSERT INTO commentaires (publication_id, utilisateur_id, parent_comment_id, contenu, est_signale, date_creation)
            VALUES (:pub_id, :user_id, :parent_comment_id, :contenu, :est_signale, NOW())
            ");

            $stmt->execute([
            'pub_id'      => $publicationId,
            'user_id'     => $userId,
            'parent_comment_id' => $parentCommentId ?: null,
            'contenu'     => $contenuFinal,
            'est_signale' => $estSignale
            ]);

            $commentId = (int)$this->pdo->lastInsertId();
            $attachments = $this->attachmentModel->store($this->pdo, 'commentaire', $commentId, $uploadedFiles);

            if ($estSignale) {
                $this->moderationService->creerSignalementAutomatique('commentaire', $commentId);
            }
            $this->salonModel->synchronizePublicationSolution($publicationId);
            $this->pdo->commit();

            $this->respondSuccess('Commentaire ajouté avec succès.', [
                'id' => $commentId,
                'attachments' => $attachments,
            ]);
        } catch (PDOException | RuntimeException $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            $this->respondError("Le commentaire n'a pas pu être enregistré.");
        }
    }

    public function liveStats(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store, private');

        $userId = (int)($_SESSION['user_id'] ?? 0);
        if ($userId <= 0) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Connexion requise.'], JSON_UNESCAPED_UNICODE);
            return;
        }
        if (!$this->pdo) {
            http_response_code(503);
            echo json_encode(['success' => false, 'message' => 'Base de données indisponible.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $publicationIds = $this->parseLiveIds($_GET['ids'] ?? '');
        $commentIds = $this->parseLiveIds($_GET['comment_ids'] ?? '');
        if ($publicationIds === [] || count($publicationIds) > 50 || count($commentIds) > 200) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Liste de publications invalide.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $roleStatement = $this->pdo->prepare('SELECT role FROM utilisateurs WHERE id = ? LIMIT 1');
        $roleStatement->execute([$userId]);
        $storedRole = $roleStatement->fetchColumn();
        if (!is_string($storedRole) || $storedRole === '') {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Compte utilisateur introuvable.'], JSON_UNESCAPED_UNICODE);
            return;
        }
        $userRole = strtolower(trim($storedRole));
        $placeholders = implode(',', array_fill(0, count($publicationIds), '?'));
        $publicationStatement = $this->pdo->prepare(
            "SELECT id, categorie_id, type_publication, est_signale
             FROM publications WHERE id IN ({$placeholders})"
        );
        $publicationStatement->execute($publicationIds);
        $visiblePublicationIds = [];
        foreach ($publicationStatement->fetchAll(PDO::FETCH_ASSOC) as $publication) {
            if (!(bool)$publication['est_signale']
                && $this->salonModel->canAccessCategory($userId, (int)$publication['categorie_id'], $userRole)) {
                $visiblePublicationIds[] = (int)$publication['id'];
            }
        }
        if ($visiblePublicationIds === []) {
            echo json_encode(['success' => true, 'publications' => [], 'comments' => []], JSON_UNESCAPED_UNICODE);
            return;
        }

        $visiblePlaceholders = implode(',', array_fill(0, count($visiblePublicationIds), '?'));
        $statsStatement = $this->pdo->prepare(
            "SELECT publication.id,
                    (SELECT COUNT(*) FROM reactions reaction
                     WHERE reaction.type_cible = 'publication'
                       AND reaction.cible_id = publication.id
                       AND reaction.type_reaction = 'like') AS likes_count,
                    EXISTS (
                        SELECT 1 FROM reactions user_reaction
                        WHERE user_reaction.type_cible = 'publication'
                          AND user_reaction.cible_id = publication.id
                          AND user_reaction.type_reaction = 'like'
                          AND user_reaction.utilisateur_id = ?
                    ) AS user_liked,
                    (SELECT COUNT(*) FROM commentaires comment
                     WHERE comment.publication_id = publication.id AND comment.est_signale = 0) AS comments_count
             FROM publications publication
             WHERE publication.id IN ({$visiblePlaceholders})"
        );
        $statsStatement->execute(array_merge([$userId], $visiblePublicationIds));
        $publicationStats = [];
        foreach ($statsStatement->fetchAll(PDO::FETCH_ASSOC) as $stats) {
            $publicationStats[(int)$stats['id']] = [
                'likes_count' => (int)$stats['likes_count'],
                'user_liked' => (bool)$stats['user_liked'],
                'comments_count' => (int)$stats['comments_count'],
            ];
        }

        $commentStats = [];
        if ($commentIds !== []) {
            $commentPlaceholders = implode(',', array_fill(0, count($commentIds), '?'));
            $commentStatement = $this->pdo->prepare(
                "SELECT comment.id,
                        COUNT(reaction.id) AS likes_count,
                        MAX(reaction.utilisateur_id = ?) AS user_liked
                 FROM commentaires comment
                 JOIN publications publication ON publication.id = comment.publication_id
                 LEFT JOIN reactions reaction
                   ON reaction.type_cible = 'commentaire'
                  AND reaction.cible_id = comment.id
                  AND reaction.type_reaction = 'like'
                 WHERE comment.id IN ({$commentPlaceholders})
                   AND publication.id IN ({$visiblePlaceholders})
                   AND comment.est_signale = 0
                 GROUP BY comment.id"
            );
            $commentStatement->execute(array_merge([$userId], $commentIds, $visiblePublicationIds));
            foreach ($commentStatement->fetchAll(PDO::FETCH_ASSOC) as $stats) {
                $commentStats[(int)$stats['id']] = [
                    'likes_count' => (int)$stats['likes_count'],
                    'user_liked' => (bool)$stats['user_liked'],
                ];
            }
        }

        echo json_encode([
            'success' => true,
            'publications' => $publicationStats,
            'comments' => $commentStats,
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return int[]
     */
    private function parseLiveIds(mixed $value): array
    {
        if (is_array($value)) {
            $values = $value;
        } elseif (is_string($value) && $value !== '') {
            $values = explode(',', $value);
        } else {
            return [];
        }

        $ids = [];
        foreach ($values as $candidate) {
            $id = filter_var($candidate, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($id !== false) {
                $ids[] = (int)$id;
            }
        }
        return array_values(array_unique($ids));
    }

    public function discussion(): void {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        header('Content-Type: application/json; charset=UTF-8');

        $userId = (int)($_SESSION['user_id'] ?? 0);
        $publicationId = (int)($_GET['id'] ?? 0);
        if ($userId <= 0) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Connexion requise.'], JSON_UNESCAPED_UNICODE);
            return;
        }
        if (!$this->pdo || $publicationId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Publication invalide.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $statement = $this->pdo->prepare("
            SELECT publication.id, publication.titre, publication.contenu, publication.date_creation,
                   publication.categorie_id, publication.est_signale, publication.commentaires_fermes,
                   author.prenom, author.nom, category.nom AS category_name, zone.nom AS zone_name
            FROM publications publication
            JOIN utilisateurs author ON author.id = publication.utilisateur_id
            JOIN categories category ON category.id = publication.categorie_id
            JOIN zones zone ON zone.id = category.zone_id
            WHERE publication.id = :publication_id
              AND publication.type_publication = 'discussion'
            LIMIT 1
        ");
        $statement->execute(['publication_id' => $publicationId]);
        $publication = $statement->fetch(PDO::FETCH_ASSOC);
        $roleStatement = $this->pdo->prepare('SELECT role FROM utilisateurs WHERE id = :user_id LIMIT 1');
        $roleStatement->execute(['user_id' => $userId]);
        $storedRole = $roleStatement->fetchColumn();
        if (!$publication || !is_string($storedRole) || $storedRole === '') {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Discussion introuvable.'], JSON_UNESCAPED_UNICODE);
            return;
        }
        $userRole = strtolower(trim($storedRole));
        if ((bool)$publication['est_signale']
            || !$this->salonModel->canAccessCategory($userId, (int)$publication['categorie_id'], $userRole)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Vous n’avez pas accès à cette discussion.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $comments = $this->pdo->prepare("
            SELECT comment.id, comment.parent_comment_id, comment.contenu, comment.date_creation, comment.est_solution,
                   author.prenom, author.nom,
                   COUNT(reaction.id) AS likes_count,
                   MAX(reaction.utilisateur_id = :user_id) AS user_liked
            FROM commentaires comment
            JOIN utilisateurs author ON author.id = comment.utilisateur_id
            LEFT JOIN reactions reaction
              ON reaction.type_cible = 'commentaire'
             AND reaction.cible_id = comment.id
             AND reaction.type_reaction = 'like'
            WHERE comment.publication_id = :publication_id
              AND comment.est_signale = 0
            GROUP BY comment.id, comment.parent_comment_id, comment.contenu, comment.date_creation, comment.est_solution,
                     author.prenom, author.nom
            ORDER BY (SELECT root_comment.est_solution
                      FROM commentaires root_comment
                      WHERE root_comment.id = COALESCE(comment.parent_comment_id, comment.id)) DESC,
                     COALESCE(comment.parent_comment_id, comment.id),
                     comment.parent_comment_id IS NOT NULL,
                     likes_count DESC, comment.date_creation ASC
        ");
        $comments->execute(['publication_id' => $publicationId, 'user_id' => $userId]);

        echo json_encode([
            'success' => true,
            'publication' => $publication,
            'comments' => $comments->fetchAll(PDO::FETCH_ASSOC),
        ], JSON_UNESCAPED_UNICODE);
    }

    public function show(int $publicationId): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        require_once ROOT_PATH . '/models/Notification.php';
        if (empty($_SESSION['user_id'])) {
            $_SESSION['flash_error'] = 'Connectez-vous pour consulter cette publication.';
            header('Location: ' . url('login'));
            exit;
        }
        if (!$this->pdo) {
            http_response_code(503);
            exit('La publication est temporairement indisponible.');
        }
        if ($publicationId <= 0) {
            http_response_code(404);
            exit('Publication introuvable.');
        }

        $userId = (int)$_SESSION['user_id'];
        $roleStatement = $this->pdo->prepare('SELECT role FROM utilisateurs WHERE id = :user_id LIMIT 1');
        $roleStatement->execute(['user_id' => $userId]);
        $storedRole = $roleStatement->fetchColumn();
        if (!is_string($storedRole) || $storedRole === '') {
            http_response_code(401);
            exit('Votre compte n’est plus disponible. Veuillez vous reconnecter.');
        }
        $userRole = strtolower(trim($storedRole));
        $_SESSION['user_role'] = $userRole;

        $statement = $this->pdo->prepare("
            SELECT publication.id, publication.titre, publication.contenu, publication.type_publication,
                   publication.statut, publication.date_creation, publication.categorie_id,
                   publication.est_signale, publication.commentaires_fermes,
                   author.id AS author_id, author.prenom, author.nom, author.role AS author_role, author.avatar_url,
                   category.nom AS category_name, zone.nom AS zone_name,
                   (SELECT COUNT(*) FROM reactions reaction
                    WHERE reaction.type_cible = 'publication'
                      AND reaction.cible_id = publication.id
                      AND reaction.type_reaction = 'like') AS likes_count,
                   EXISTS (
                       SELECT 1 FROM reactions user_reaction
                       WHERE user_reaction.type_cible = 'publication'
                         AND user_reaction.cible_id = publication.id
                         AND user_reaction.type_reaction = 'like'
                         AND user_reaction.utilisateur_id = :reaction_user
                   ) AS user_liked,
                   (SELECT saved.id FROM publications_sauvegardees saved
                    WHERE saved.publication_id = publication.id
                      AND saved.utilisateur_id = :saved_user
                    LIMIT 1) AS saved_id
            FROM publications publication
            JOIN utilisateurs author ON author.id = publication.utilisateur_id
            JOIN categories category ON category.id = publication.categorie_id AND category.est_active = 1
            JOIN zones zone ON zone.id = category.zone_id AND zone.est_active = 1
            WHERE publication.id = :publication_id
            LIMIT 1
        ");
        $statement->execute([
            'publication_id' => $publicationId,
            'reaction_user' => $userId,
            'saved_user' => $userId,
        ]);
        $publication = $statement->fetch(PDO::FETCH_ASSOC);
        if (!$publication || (bool)$publication['est_signale']
            || !$this->salonModel->canAccessCategory($userId, (int)$publication['categorie_id'], $userRole)) {
            http_response_code(404);
            exit('Publication introuvable.');
        }

        $commentsStatement = $this->pdo->prepare("
            SELECT comment.id, comment.parent_comment_id, comment.contenu, comment.date_creation, comment.est_solution,
                   author.id AS author_id, author.prenom, author.nom, author.avatar_url,
                   COUNT(reaction.id) AS likes_count,
                   MAX(reaction.utilisateur_id = :user_id) AS user_liked
            FROM commentaires comment
            JOIN utilisateurs author ON author.id = comment.utilisateur_id
            LEFT JOIN reactions reaction
              ON reaction.type_cible = 'commentaire'
             AND reaction.cible_id = comment.id
             AND reaction.type_reaction = 'like'
            WHERE comment.publication_id = :publication_id
              AND comment.est_signale = 0
            GROUP BY comment.id, comment.parent_comment_id, comment.contenu, comment.date_creation, comment.est_solution,
                     author.id, author.prenom, author.nom, author.avatar_url
            ORDER BY (SELECT root_comment.est_solution
                      FROM commentaires root_comment
                      WHERE root_comment.id = COALESCE(comment.parent_comment_id, comment.id)) DESC,
                     COALESCE(comment.parent_comment_id, comment.id),
                     comment.parent_comment_id IS NOT NULL,
                     comment.date_creation ASC
        ");
        $commentsStatement->execute(['publication_id' => $publicationId, 'user_id' => $userId]);
        $comments = $commentsStatement->fetchAll(PDO::FETCH_ASSOC);

        $attachments = $this->pdo->prepare("
            SELECT id, nom_fichier, type_mime, taille
            FROM pieces_jointes
            WHERE type_cible = 'publication' AND cible_id = :publication_id
            ORDER BY date_upload, id
        ");
        $attachments->execute(['publication_id' => $publicationId]);
        $publicationAttachments = $attachments->fetchAll(PDO::FETCH_ASSOC);

        $commentAttachments = [];
        if ($comments) {
            $commentIds = array_map(static fn (array $comment): int => (int)$comment['id'], $comments);
            $placeholders = implode(',', array_fill(0, count($commentIds), '?'));
            $commentAttachmentStatement = $this->pdo->prepare("
                SELECT id, cible_id, nom_fichier, type_mime, taille
                FROM pieces_jointes
                WHERE type_cible = 'commentaire' AND cible_id IN ($placeholders)
                ORDER BY date_upload, id
            ");
            $commentAttachmentStatement->execute($commentIds);
            foreach ($commentAttachmentStatement->fetchAll(PDO::FETCH_ASSOC) as $attachment) {
                $commentAttachments[(int)$attachment['cible_id']][] = $attachment;
            }
        }

        $pollOptions = [];
        if ($publication['type_publication'] === 'question') {
            $pollStatement = $this->pdo->prepare("
                SELECT option_row.id, option_row.texte,
                       COUNT(vote.utilisateur_id) AS votes,
                       MAX(vote.utilisateur_id = :user_id) AS user_voted
                FROM sondage_options option_row
                LEFT JOIN sondage_votes vote ON vote.option_id = option_row.id
                WHERE option_row.publication_id = :publication_id
                GROUP BY option_row.id, option_row.texte, option_row.ordre
                ORDER BY option_row.ordre, option_row.id
            ");
            $pollStatement->execute(['publication_id' => $publicationId, 'user_id' => $userId]);
            $pollOptions = $pollStatement->fetchAll(PDO::FETCH_ASSOC);
        }

        $pageBase = $GLOBALS['baseUrl'] ?? '';
        $pageAsset = $GLOBALS['assetBase'] ?? url('public/assets');
        $unreadCount = 0;
        if (class_exists('Notification')) {
            $unreadCount = (new Notification($this->pdo))->countUnread($userId);
        }
        $unreadMessagesCount = $this->pdo instanceof PDO
            ? (new Social($this->pdo))->getUnreadMessageCount($userId)
            : 0;
        require ROOT_PATH . '/views/desktop/publication/show.php';
    }

    public function vote(): void {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        header('Content-Type: application/json; charset=UTF-8');

        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Allow: POST');
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.'], JSON_UNESCAPED_UNICODE);
            return;
        }
        if ($userId <= 0) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Connexion requise.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $publicationId = (int)($_POST['publication_id'] ?? 0);
        $optionId = (int)($_POST['option_id'] ?? 0);
        if (!$this->pdo || $publicationId <= 0 || $optionId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Choix de sondage invalide.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $this->pdo->beginTransaction();
            $publicationStatement = $this->pdo->prepare("
                SELECT publication.categorie_id, publication.est_signale
                FROM publications publication
                WHERE publication.id = :publication_id
                  AND publication.type_publication = 'question'
                FOR UPDATE
            ");
            $publicationStatement->execute(['publication_id' => $publicationId]);
            $publication = $publicationStatement->fetch(PDO::FETCH_ASSOC);
            $roleStatement = $this->pdo->prepare('SELECT role FROM utilisateurs WHERE id = :user_id LIMIT 1');
            $roleStatement->execute(['user_id' => $userId]);
            $storedRole = $roleStatement->fetchColumn();
            if (!$publication || (bool)$publication['est_signale'] || !is_string($storedRole)
                || !$this->salonModel->canAccessCategory($userId, (int)$publication['categorie_id'], strtolower(trim($storedRole)))) {
                $this->pdo->rollBack();
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Ce sondage n’est pas accessible.'], JSON_UNESCAPED_UNICODE);
                return;
            }

            $optionCheck = $this->pdo->prepare(
                'SELECT id FROM sondage_options WHERE id = :option_id AND publication_id = :publication_id LIMIT 1'
            );
            $optionCheck->execute(['option_id' => $optionId, 'publication_id' => $publicationId]);
            if (!$optionCheck->fetchColumn()) {
                $this->pdo->rollBack();
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Ce choix ne correspond pas au sondage.'], JSON_UNESCAPED_UNICODE);
                return;
            }

            $deleteVote = $this->pdo->prepare("
                DELETE vote FROM sondage_votes vote
                JOIN sondage_options option_row ON option_row.id = vote.option_id
                WHERE option_row.publication_id = :publication_id
                  AND vote.utilisateur_id = :user_id
            ");
            $deleteVote->execute(['publication_id' => $publicationId, 'user_id' => $userId]);
            $insertVote = $this->pdo->prepare(
                'INSERT INTO sondage_votes (option_id, utilisateur_id) VALUES (:option_id, :user_id)'
            );
            $insertVote->execute(['option_id' => $optionId, 'user_id' => $userId]);
            $this->pdo->commit();

            $optionsStatement = $this->pdo->prepare("
                SELECT option_row.id, option_row.texte,
                       (SELECT COUNT(*) FROM sondage_votes vote WHERE vote.option_id = option_row.id) AS votes
                FROM sondage_options option_row
                WHERE option_row.publication_id = :publication_id
                ORDER BY option_row.ordre, option_row.id
            ");
            $optionsStatement->execute(['publication_id' => $publicationId]);
            echo json_encode([
                'success' => true,
                'message' => 'Votre vote a été enregistré.',
                'option_id' => $optionId,
                'options' => $optionsStatement->fetchAll(PDO::FETCH_ASSOC),
            ], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('Vote au sondage impossible : ' . $exception->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Votre vote n’a pas pu être enregistré.'], JSON_UNESCAPED_UNICODE);
        }
    }

    private function isAjaxRequest(): bool
    {
        return isset($_POST['ajax'])
            || strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
            || strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;
    }

    private function respondSuccess(string $message, array $data = [], ?string $fallbackUrl = null): void
    {
        if ($this->isAjaxRequest()) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['success' => true, 'message' => $message] + $data, JSON_UNESCAPED_UNICODE);
            exit;
        }

        $_SESSION['flash_success'] = $message;
        header('Location: ' . ($fallbackUrl ?? url()));
        exit;
    }

    private function respondError(string $message, ?string $fallbackUrl = null): void
    {
        if ($this->isAjaxRequest()) {
            header('Content-Type: application/json; charset=UTF-8', true, 422);
            echo json_encode(['success' => false, 'message' => $message], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $_SESSION['flash_error'] = $message;
        header('Location: ' . ($fallbackUrl ?? url()));
        exit;
    }
}
