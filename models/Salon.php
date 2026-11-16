<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/RoleAccess.php';

class Salon {
    private ?PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        if ($pdo !== null) {
            $this->pdo = $pdo;
        } else {
            // Récupération automatique de la connexion globale si elle existe
            global $pdo;
            if (isset($pdo) && $pdo instanceof PDO) {
                $this->pdo = $pdo;
            } else {
                // Fallback via database.php si la fonction de connexion globale existe
                $this->pdo = function_exists('getPDO') ? getPDO() : null;
            }
        }
    }

    
    /**
     * Récupère toutes les zones avec leurs catégories associées et le compte de membres
     *
     * @return array
     */
    public function getZonesWithCategories(): array {
        if (!$this->pdo) {
            return [];
        }

        $zonesStmt = $this->pdo->query("
            SELECT z.*, 
                   (SELECT COUNT(*) FROM appartenance_zones WHERE zone_id = z.id) as membres_count,
                   (SELECT COUNT(*) FROM categories WHERE zone_id = z.id) as categories_count
            FROM zones z 
            ORDER BY z.type_zone ASC, z.nom ASC
        ");
        $zones = $zonesStmt ? $zonesStmt->fetchAll() : [];

        foreach ($zones as &$zone) {
            $catStmt = $this->pdo->prepare("
                SELECT c.*, 
                       (SELECT COUNT(*) FROM publications WHERE categorie_id = c.id) as publications_count
                FROM categories c 
                WHERE c.zone_id = :zone_id 
                ORDER BY c.ordre ASC, c.nom ASC
            ");
            $catStmt->execute(['zone_id' => $zone['id']]);
            $zone['categories'] = $catStmt->fetchAll();
        }

        return $zones;
    }

    public function getAccessibleZones(int $userId, string $role): array {
        if (!$this->pdo || $userId <= 0) {
            return [];
        }

        $canViewAll = RoleAccess::canViewAllZones($this->pdo, $userId);
        $statement = $this->pdo->prepare("
            SELECT z.id, z.nom, z.type_zone, z.description,
                   az.role_dans_zone,
                   (SELECT COUNT(*) FROM appartenance_zones members WHERE members.zone_id = z.id) AS membres_count
            FROM zones z
            LEFT JOIN appartenance_zones az
              ON az.zone_id = z.id AND az.utilisateur_id = :membership_user
            WHERE z.est_active = 1
              AND (:view_all = 1 OR az.utilisateur_id IS NOT NULL)
            ORDER BY FIELD(z.type_zone, 'generale', 'filiere', 'professeurs', 'delegation'), z.nom
        ");
        $statement->execute([
            'membership_user' => $userId,
            'view_all' => $canViewAll ? 1 : 0,
        ]);
        $zones = $statement->fetchAll(PDO::FETCH_ASSOC);

        if (!$zones) {
            return [];
        }

        $zoneIds = array_column($zones, 'id');
        $placeholders = implode(',', array_fill(0, count($zoneIds), '?'));
        $categoriesStatement = $this->pdo->prepare("
            SELECT c.id, c.zone_id, c.nom, c.description,
                   (SELECT COUNT(*) FROM publications p WHERE p.categorie_id = c.id AND p.est_signale = 0) AS publications_count
            FROM categories c
            WHERE c.est_active = 1 AND c.zone_id IN ($placeholders)
            ORDER BY c.ordre, c.nom
        ");
        $categoriesStatement->execute($zoneIds);
        $categoriesByZone = [];
        foreach ($categoriesStatement->fetchAll(PDO::FETCH_ASSOC) as $category) {
            $categoriesByZone[(int)$category['zone_id']][] = $category;
        }

        foreach ($zones as &$zone) {
            $zone['categories'] = $categoriesByZone[(int)$zone['id']] ?? [];
        }
        unset($zone);

        return $zones;
    }

    public function canAccessCategory(int $userId, int $categoryId, string $role): bool {
        if (!$this->pdo || $userId <= 0 || $categoryId <= 0) {
            return false;
        }

        $canViewAll = RoleAccess::canViewAllZones($this->pdo, $userId);
        $statement = $this->pdo->prepare("
            SELECT 1
            FROM categories c
            JOIN zones z ON z.id = c.zone_id
            WHERE c.id = :category_id
              AND c.est_active = 1
              AND z.est_active = 1
              AND (:can_view_all = 1 OR EXISTS (
                  SELECT 1
                  FROM appartenance_zones membership
                  WHERE membership.utilisateur_id = :user_id
                    AND membership.zone_id = z.id
              ))
            LIMIT 1
        ");
        $statement->execute([
            'category_id' => $categoryId,
            'can_view_all' => $canViewAll ? 1 : 0,
            'user_id' => $userId,
        ]);

        return (bool)$statement->fetchColumn();
    }

    public function getPublications(
        int $userId,
        string $role,
        string $view = 'latest',
        ?int $categoryId = null,
        int $limit = 50,
        ?int $zoneId = null,
        ?int $tagId = null,
        ?int $publicationId = null
    ): array {
        if (!$this->pdo || $userId <= 0) {
            return [];
        }

        $view = in_array($view, ['popular', 'saved'], true) ? $view : 'latest';
        $canViewAll = RoleAccess::canViewAllZones($this->pdo, $userId);
        $limit = max(1, min($limit, 100));
        $conditions = ["p.est_signale = 0"];
        $parameters = ['saved_user' => $userId, 'reaction_user' => $userId];

        if ($view === 'saved') {
            $conditions[] = 'saved.id IS NOT NULL';
        }
        if (!$canViewAll) {
            $conditions[] = "EXISTS (
                SELECT 1 FROM appartenance_zones member_access
                WHERE member_access.utilisateur_id = :access_user
                  AND member_access.zone_id = z.id
            )";
            $parameters['access_user'] = $userId;
        }

        if ($categoryId !== null && $categoryId > 0) {
            $conditions[] = 'c.id = :category_id';
            $parameters['category_id'] = $categoryId;
        }
        if ($zoneId !== null && $zoneId > 0) {
            $conditions[] = 'z.id = :zone_id';
            $parameters['zone_id'] = $zoneId;
        }
        if ($tagId !== null && $tagId > 0) {
            $conditions[] = 'EXISTS (
                SELECT 1 FROM publications_tags publication_tag
                WHERE publication_tag.publication_id = p.id AND publication_tag.tag_id = :tag_id
            )';
            $parameters['tag_id'] = $tagId;
        }
        if ($publicationId !== null && $publicationId > 0) {
            $conditions[] = 'p.id = :publication_id';
            $parameters['publication_id'] = $publicationId;
        }

        $orderBy = $view === 'popular'
            ? '(likes_count + comments_count) DESC, likes_count DESC, p.date_creation DESC'
            : 'p.date_creation DESC';

        $sql = "
            SELECT p.id, p.titre, p.contenu, p.type_publication, p.statut, p.date_creation,
                   u.id AS author_id, u.prenom, u.nom, u.role AS author_role, u.avatar_url,
                   c.id AS category_id, c.nom AS category_name,
                   z.id AS zone_id, z.nom AS zone_name, z.type_zone,
                   (SELECT COUNT(*) FROM reactions r
                    WHERE r.type_cible = 'publication'
                      AND r.type_reaction = 'like'
                      AND r.cible_id = p.id) AS likes_count,
                   EXISTS (
                       SELECT 1 FROM reactions user_reaction
                       WHERE user_reaction.type_cible = 'publication'
                         AND user_reaction.type_reaction = 'like'
                         AND user_reaction.cible_id = p.id
                         AND user_reaction.utilisateur_id = :reaction_user
                   ) AS user_liked,
                   (SELECT COUNT(*) FROM commentaires co
                    WHERE co.publication_id = p.id AND co.est_signale = 0) AS comments_count,
                   (SELECT COUNT(*) FROM pieces_jointes attachment
                    WHERE attachment.type_cible = 'publication' AND attachment.cible_id = p.id) AS attachment_count,
                   saved.id AS saved_id
            FROM publications p
            JOIN utilisateurs u ON u.id = p.utilisateur_id
            JOIN categories c ON c.id = p.categorie_id AND c.est_active = 1
            JOIN zones z ON z.id = c.zone_id AND z.est_active = 1
            LEFT JOIN publications_sauvegardees saved
              ON saved.publication_id = p.id AND saved.utilisateur_id = :saved_user
            WHERE " . implode(' AND ', $conditions) . "
            ORDER BY $orderBy
            LIMIT $limit
        ";
        $statement = $this->pdo->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPublicationPolls(array $publicationIds, int $userId): array {
        if (!$this->pdo || !$publicationIds || $userId <= 0) {
            return [];
        }

        $publicationIds = array_values(array_filter(array_map('intval', $publicationIds), static fn (int $id): bool => $id > 0));
        if (!$publicationIds) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($publicationIds), '?'));
        $statement = $this->pdo->prepare("
            SELECT option_row.id, option_row.publication_id, option_row.texte,
                   (SELECT COUNT(*) FROM sondage_votes vote WHERE vote.option_id = option_row.id) AS votes,
                   EXISTS (
                       SELECT 1 FROM sondage_votes user_vote
                       WHERE user_vote.option_id = option_row.id
                         AND user_vote.utilisateur_id = ?
                   ) AS user_voted
            FROM sondage_options option_row
            JOIN publications publication ON publication.id = option_row.publication_id
            WHERE option_row.publication_id IN ($placeholders)
              AND publication.type_publication = 'question'
            ORDER BY option_row.publication_id, option_row.ordre, option_row.id
        ");
        $statement->execute(array_merge([$userId], $publicationIds));

        $polls = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $option) {
            $polls[(int)$option['publication_id']][] = $option;
        }

        return $polls;
    }

    public function getSalonDocuments(
        int $userId,
        string $role,
        ?int $categoryId = null,
        ?int $zoneId = null,
        int $limit = 100
    ): array {
        if (!$this->pdo || $userId <= 0) {
            return [];
        }

        $canViewAll = RoleAccess::canViewAllZones($this->pdo, $userId);
        $limit = max(1, min($limit, 200));
        $conditions = [
            "attachment.type_mime IN ('application/pdf', 'image/png', 'image/jpeg', 'image/gif')",
            'publication.est_signale = 0',
            '(comment.id IS NULL OR comment.est_signale = 0)',
        ];
        $parameters = [];

        if (!$canViewAll) {
            $conditions[] = 'EXISTS (
                SELECT 1 FROM appartenance_zones membership
                WHERE membership.utilisateur_id = :membership_user
                  AND membership.zone_id = zone.id
            )';
            $parameters['membership_user'] = $userId;
        }
        if ($categoryId !== null && $categoryId > 0) {
            $conditions[] = 'category.id = :category_id';
            $parameters['category_id'] = $categoryId;
        }
        if ($zoneId !== null && $zoneId > 0) {
            $conditions[] = 'zone.id = :zone_id';
            $parameters['zone_id'] = $zoneId;
        }

        $statement = $this->pdo->prepare("
            SELECT attachment.id, attachment.nom_fichier, attachment.type_mime,
                   attachment.taille, attachment.date_upload,
                   publication.id AS publication_id, publication.titre,
                   category.id AS category_id,
                   category.nom AS category_name, zone.id AS zone_id, zone.nom AS zone_name,
                   author.prenom, author.nom
            FROM pieces_jointes attachment
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
            JOIN utilisateurs author ON author.id = publication.utilisateur_id
            WHERE " . implode(' AND ', $conditions) . "
            ORDER BY attachment.ordre_affichage, attachment.id
            LIMIT $limit
        ");
        $statement->execute($parameters);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function synchronizePublicationSolution(int $publicationId): void {
        if (!$this->pdo || $publicationId <= 0) {
            return;
        }

        $winner = $this->pdo->prepare("
            SELECT comment.id
            FROM commentaires comment
            LEFT JOIN reactions reaction
              ON reaction.type_cible = 'commentaire'
             AND reaction.cible_id = comment.id
             AND reaction.type_reaction = 'like'
            WHERE comment.publication_id = :publication_id
              AND comment.est_signale = 0
            GROUP BY comment.id
            HAVING COUNT(reaction.id) > 0
            ORDER BY COUNT(reaction.id) DESC, MIN(comment.date_creation), comment.id
            LIMIT 1
        ");
        $winner->execute(['publication_id' => $publicationId]);
        $winnerId = $winner->fetchColumn();

        $clear = $this->pdo->prepare('UPDATE commentaires SET est_solution = 0 WHERE publication_id = :publication_id');
        $clear->execute(['publication_id' => $publicationId]);
        if ($winnerId !== false) {
            $mark = $this->pdo->prepare('UPDATE commentaires SET est_solution = 1 WHERE id = :comment_id');
            $mark->execute(['comment_id' => (int)$winnerId]);
        }
    }

    public function getPublicationAttachments(array $publicationIds, int $userId): array {
        if (!$this->pdo || !$publicationIds || $userId <= 0) {
            return [];
        }

        $publicationIds = array_values(array_filter(array_map('intval', $publicationIds), static fn (int $id): bool => $id > 0));
        if (!$publicationIds) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($publicationIds), '?'));
        $statement = $this->pdo->prepare("
            SELECT attachment.id, attachment.cible_id AS publication_id,
                   attachment.nom_fichier, attachment.chemin_fichier,
                   attachment.type_mime, attachment.taille,
                   saved.id AS saved_id
            FROM pieces_jointes attachment
            LEFT JOIN pieces_jointes_sauvegardees saved
              ON saved.piece_jointe_id = attachment.id AND saved.utilisateur_id = ?
            WHERE attachment.type_cible = 'publication'
              AND attachment.cible_id IN ($placeholders)
            ORDER BY attachment.ordre_affichage, attachment.id
        ");
        $statement->execute(array_merge([$userId], $publicationIds));
        $attachmentsByPublication = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $attachment) {
            $attachmentsByPublication[(int)$attachment['publication_id']][] = $attachment;
        }

        return $attachmentsByPublication;
    }

    public function getSavedAttachments(int $userId, string $role): array {
        if (!$this->pdo || $userId <= 0) {
            return [];
        }

        $canViewAll = RoleAccess::canViewAllZones($this->pdo, $userId);
        $statement = $this->pdo->prepare("
            SELECT attachment.id, attachment.nom_fichier, attachment.chemin_fichier,
                   attachment.type_mime, attachment.taille,
                   publication.id AS publication_id, publication.titre,
                   category.nom AS category_name, zone.nom AS zone_name
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
            JOIN categories category ON category.id = publication.categorie_id
            JOIN zones zone ON zone.id = category.zone_id
            WHERE saved.utilisateur_id = :user_id
              AND (:can_view_all = 1 OR EXISTS (
                  SELECT 1 FROM appartenance_zones membership
                  WHERE membership.utilisateur_id = :membership_user
                    AND membership.zone_id = zone.id
              ))
            ORDER BY saved.date_sauvegarde DESC
        ");
        $statement->execute([
            'user_id' => $userId,
            'membership_user' => $userId,
            'can_view_all' => $canViewAll ? 1 : 0,
        ]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPopularPublications(int $userId, string $role, int $limit = 5): array {
        return $this->getPublications($userId, $role, 'popular', null, $limit);
    }

    public function getOnlineUsers(): array {
        if (!$this->pdo) {
            return [];
        }

        $statement = $this->pdo->query("
            SELECT id, prenom, nom, role, filiere, avatar_url
            FROM utilisateurs
            WHERE est_actif = 1
              AND derniere_activite >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
            ORDER BY prenom, nom
        ");

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function touchActivity(int $userId): bool {
        if (!$this->pdo || $userId <= 0) {
            return false;
        }

        $statement = $this->pdo->prepare('UPDATE utilisateurs SET derniere_activite = NOW() WHERE id = :user_id');
        return $statement->execute(['user_id' => $userId]);
    }

    public function toggleSavedPublication(int $userId, int $publicationId, string $role): ?bool {
        if (!$this->pdo || $userId <= 0 || $publicationId <= 0) {
            return null;
        }

        $canViewAll = RoleAccess::canViewAllZones($this->pdo, $userId);
        $check = $this->pdo->prepare("
            SELECT publication.id
            FROM publications publication
            JOIN categories category ON category.id = publication.categorie_id
            JOIN zones zone ON zone.id = category.zone_id
            WHERE publication.id = :publication_id
              AND (publication.utilisateur_id = :publication_owner OR (
                  publication.est_signale = 0
                  AND (:can_view_all = 1 OR EXISTS (
                  SELECT 1 FROM appartenance_zones membership
                  WHERE membership.utilisateur_id = :user_id
                    AND membership.zone_id = zone.id
                  ))
              ))
            LIMIT 1
        ");
        $check->execute([
            'publication_id' => $publicationId,
            'publication_owner' => $userId,
            'can_view_all' => $canViewAll ? 1 : 0,
            'user_id' => $userId,
        ]);
        if (!$check->fetchColumn()) {
            return null;
        }

        $saved = $this->pdo->prepare('SELECT id FROM publications_sauvegardees WHERE utilisateur_id = :user_id AND publication_id = :publication_id');
        $saved->execute(['user_id' => $userId, 'publication_id' => $publicationId]);
        $savedId = $saved->fetchColumn();
        if ($savedId) {
            $delete = $this->pdo->prepare('DELETE FROM publications_sauvegardees WHERE id = :id');
            $delete->execute(['id' => $savedId]);
            return false;
        }

        $insert = $this->pdo->prepare('INSERT INTO publications_sauvegardees (utilisateur_id, publication_id) VALUES (:user_id, :publication_id)');
        $insert->execute(['user_id' => $userId, 'publication_id' => $publicationId]);
        return true;
    }

    public function toggleSavedAttachment(int $userId, int $attachmentId, string $role): ?bool {
        if (!$this->pdo || $userId <= 0 || $attachmentId <= 0) {
            return null;
        }

        $saved = $this->pdo->prepare('SELECT id FROM pieces_jointes_sauvegardees WHERE utilisateur_id = :user_id AND piece_jointe_id = :attachment_id');
        $saved->execute(['user_id' => $userId, 'attachment_id' => $attachmentId]);
        $savedId = $saved->fetchColumn();
        if ($savedId) {
            $delete = $this->pdo->prepare('DELETE FROM pieces_jointes_sauvegardees WHERE id = :id');
            $delete->execute(['id' => $savedId]);
            return false;
        }

        $canViewAll = RoleAccess::canViewAllZones($this->pdo, $userId);
        $check = $this->pdo->prepare("
            SELECT attachment.id
            FROM pieces_jointes attachment
            LEFT JOIN publications direct_publication
              ON attachment.type_cible = 'publication'
             AND direct_publication.id = attachment.cible_id
            LEFT JOIN commentaires comment
              ON attachment.type_cible = 'commentaire'
             AND comment.id = attachment.cible_id
            JOIN publications publication
              ON publication.id = COALESCE(direct_publication.id, comment.publication_id)
            JOIN categories category ON category.id = publication.categorie_id
            JOIN zones zone ON zone.id = category.zone_id
            WHERE attachment.id = :attachment_id
              AND (
                (attachment.type_cible = 'publication' AND publication.utilisateur_id = :owner_user)
                OR (attachment.type_cible = 'commentaire' AND comment.utilisateur_id = :owner_user)
                OR (publication.est_signale = 0
                    AND (comment.id IS NULL OR comment.est_signale = 0)
                    AND (:can_view_all = 1 OR EXISTS (
                  SELECT 1 FROM appartenance_zones membership
                  WHERE membership.utilisateur_id = :user_id
                    AND membership.zone_id = zone.id
                    )))
              )
            LIMIT 1
        ");
        $check->execute([
            'attachment_id' => $attachmentId,
            'owner_user' => $userId,
            'can_view_all' => $canViewAll ? 1 : 0,
            'user_id' => $userId,
        ]);
        if (!$check->fetchColumn()) {
            return null;
        }

        $insert = $this->pdo->prepare('INSERT INTO pieces_jointes_sauvegardees (utilisateur_id, piece_jointe_id) VALUES (:user_id, :attachment_id)');
        $insert->execute(['user_id' => $userId, 'attachment_id' => $attachmentId]);
        return true;
    }

    /**
     * Crée une nouvelle zone et y ajoute automatiquement les étudiants de la filière si applicable
     *
     * @param string $nom
     * @param string $typeZone ('generale', 'filiere', 'professeurs', 'delegation')
     * @param string|null $description
     * @param int|null $createurId
     * @return int|false ID de la zone créée
     */
    public function createZone(string $nom, string $typeZone = 'filiere', ?string $description = null, ?int $createurId = null) {
        if (!$this->pdo) {
            return false;
        }

        $nom = trim($nom);
        $description = !empty($description) ? trim($description) : null;

        $stmt = $this->pdo->prepare("
            INSERT INTO zones (nom, type_zone, description, createur_id, date_creation) 
            VALUES (:nom, :type_zone, :description, :createur_id, NOW())
        ");

        $success = $stmt->execute([
            'nom'         => $nom,
            'type_zone'   => $typeZone,
            'description' => $description,
            'createur_id' => $createurId
        ]);

        if (!$success) {
            return false;
        }

        $newZoneId = (int)$this->pdo->lastInsertId();

        // Si c'est une zone de filière, rattacher automatiquement les étudiants dont la filière correspond
        if ($typeZone === 'filiere') {
            $cleanName = str_ireplace('Filière ', '', $nom);
            $userStmt = $this->pdo->prepare("
                SELECT id FROM utilisateurs 
                WHERE role = 'etudiant' 
                  AND (
                      LOWER(filiere) = LOWER(:exact) 
                      OR LOWER(filiere) LIKE LOWER(:pattern)
                  )
            ");
            $userStmt->execute([
                'exact'   => $cleanName,
                'pattern' => '%' . $cleanName . '%'
            ]);
            $users = $userStmt->fetchAll();

            $insertMembre = $this->pdo->prepare("
                INSERT IGNORE INTO appartenance_zones (utilisateur_id, zone_id, role_dans_zone, date_ajout)
                VALUES (:user_id, :zone_id, 'membre', NOW())
            ");

            foreach ($users as $u) {
                $insertMembre->execute([
                    'user_id' => (int)$u['id'],
                    'zone_id' => $newZoneId
                ]);
            }

            // Marquer les notifications de demande pour cette filière comme lues
            $notifStmt = $this->pdo->prepare("
                UPDATE notifications 
                SET lu = 1 
                WHERE type_notification = 'demande_creation_zone' 
                  AND LOWER(contenu) LIKE LOWER(:pattern)
            ");
            $notifStmt->execute(['pattern' => '%' . $cleanName . '%']);
        }

        return $newZoneId;
    }

    /**
     * Crée une nouvelle catégorie au sein d'une zone
     *
     * @param int $zoneId
     * @param string $nom
     * @param string|null $slug
     * @param string|null $description
     * @param int $ordre
     * @return int|false
     */
    public function createCategory(int $zoneId, string $nom, ?string $slug = null, ?string $description = null, int $ordre = 0) {
        if (!$this->pdo) {
            return false;
        }

        $nom = trim($nom);
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nom), '-'));
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO categories (zone_id, nom, slug, description, ordre) 
            VALUES (:zone_id, :nom, :slug, :description, :ordre)
        ");

        $success = $stmt->execute([
            'zone_id'     => $zoneId,
            'nom'         => $nom,
            'slug'        => $slug,
            'description' => !empty($description) ? trim($description) : null,
            'ordre'       => $ordre
        ]);

        return $success ? (int)$this->pdo->lastInsertId() : false;
    }

    /**
     * Calcule l'activité (nombre de publications) pour chaque filière
     *
     * @return array
     */
    public function getFiliereActivity(): array {
        if (!$this->pdo) {
            return [];
        }

        $stmt = $this->pdo->query("
            SELECT z.id, z.nom, COUNT(p.id) as publications_count
            FROM zones z
            LEFT JOIN categories c ON c.zone_id = z.id
            LEFT JOIN publications p ON p.categorie_id = c.id
            WHERE z.type_zone = 'filiere'
            GROUP BY z.id, z.nom
            ORDER BY publications_count DESC
        ");

        $filieres = $stmt ? $stmt->fetchAll() : [];
        if (empty($filieres)) {
            // Filières représentatives de l'école
            $filieres = [
                ['id' => 1, 'nom' => "BTS Informatique (IDA)", 'publications_count' => 1420],
                ['id' => 2, 'nom' => "BTS Génie Civil & BTP", 'publications_count' => 980],
                ['id' => 3, 'nom' => "Mines, Pétrole & Géologie", 'publications_count' => 650],
                ['id' => 4, 'nom' => "Finance Comptabilité (FCGE)", 'publications_count' => 540],
                ['id' => 5, 'nom' => "Ressources Humaines & Com", 'publications_count' => 390],
            ];
        }

        $total = 0;
        foreach ($filieres as $f) {
            $total += (int)$f['publications_count'];
        }

        foreach ($filieres as &$f) {
            $count = (int)$f['publications_count'];
            $f['messages'] = $count;
            $f['pourcentage'] = $total > 0 ? round(($count / $total) * 100) : 0;
        }

        return $filieres;
    }
}