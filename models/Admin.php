<?php
/**
 =============================================================
 models/Admin.php – Modèle Administrateur pour EPI Tchach
 =============================================================
 Hérite du modèle User et fournit des méthodes spécifiques à l'administration :
 - Vérification de privilèges (trusAdmin)
 - Gestion et listing des utilisateurs (listUser)
 - Statistiques globales et modération
 =============================================================
 */

// Inclusion robuste du modèle parent User
if (defined('ROOT_PATH')) {
    require_once ROOT_PATH . '/models/User.php';
    require_once ROOT_PATH . '/models/Attachment.php';
    require_once ROOT_PATH . '/models/Avatar.php';
} else {
    require_once __DIR__ . '/User.php';
    require_once __DIR__ . '/Attachment.php';
    require_once __DIR__ . '/Avatar.php';
}

class Admin extends User {

    /**
     * Constructeur : réutilise la connexion PDO du parent User
     */
    public function __construct(?PDO $pdo = null) {
        parent::__construct($pdo);
    }

    /**
     * Retourne l'instance PDO courante
     */
    public function getPdo(): ?PDO {
        return $this->pdo ?? null;
    }
    
    /**
     * Vérifie si une adresse email correspond à un administrateur certifié
     * Utilise chargeRole($email) hérité de User
     *
     * @param string $email
     * @return bool True si l'utilisateur possède le rôle administrateur, False sinon
     */
    public function trusAdmin(string $email): bool {
        $role = $this->chargeRole($email);
        return in_array($role, ['administrateur', 'admin'], true);
    }

    /**
     * Récupère la liste de tous les utilisateurs inscrits sur la plateforme
     *
     * @param string|null $filterRole Filtre optionnel par rôle ('etudiant', 'professeur', etc.)
     * @return array Liste des utilisateurs
     */
    public function listUser(?string $filterRole = null): array {
        if (!$this->pdo) {
            return [];
        }

        if (!empty($filterRole)) {
            $stmt = $this->pdo->prepare("
                SELECT user.id, user.nom, user.prenom, user.email, user.role, user.filiere,
                       user.modules_enseignes, user.date_inscription,
                       (SELECT GROUP_CONCAT(
                           CONCAT(zone.nom, ' — ', CASE membership.role_dans_zone
                               WHEN 'delegue_de_classe' THEN 'Délégué de classe'
                               WHEN 'delegue_general' THEN 'Délégué général'
                               ELSE 'Membre'
                           END) SEPARATOR ', '
                       )
                        FROM appartenance_zones membership
                        JOIN zones zone ON zone.id = membership.zone_id
                        WHERE membership.utilisateur_id = user.id
                          AND membership.role_dans_zone IN ('delegue_de_classe', 'delegue_general')
                       ) AS responsabilites
                FROM utilisateurs user
                WHERE LOWER(user.role) = LOWER(:role)
                ORDER BY user.date_inscription DESC
            ");
            $stmt->execute(['role' => trim($filterRole)]);
        } else {
            $stmt = $this->pdo->query("
                SELECT user.id, user.nom, user.prenom, user.email, user.role, user.filiere,
                       user.modules_enseignes, user.date_inscription,
                       (SELECT GROUP_CONCAT(
                           CONCAT(zone.nom, ' — ', CASE membership.role_dans_zone
                               WHEN 'delegue_de_classe' THEN 'Délégué de classe'
                               WHEN 'delegue_general' THEN 'Délégué général'
                               ELSE 'Membre'
                           END) SEPARATOR ', '
                       )
                        FROM appartenance_zones membership
                        JOIN zones zone ON zone.id = membership.zone_id
                        WHERE membership.utilisateur_id = user.id
                          AND membership.role_dans_zone IN ('delegue_de_classe', 'delegue_general')
                       ) AS responsabilites
                FROM utilisateurs user
                ORDER BY user.date_inscription DESC
            ");
        }

        return $stmt ? $stmt->fetchAll() : [];
    }

    /**
     * Retourne les zones où l'utilisateur possède un rôle de responsable.
     *
     * @param int $userId
     * @param string $scope 'delegue' ou 'delegue_de_classe'
     * @return array
     */
    public function getDashboardZonesForUser(int $userId, string $scope): array {
        if (!$this->pdo || $userId <= 0) {
            return [];
        }

        $rolesByScope = [
            'delegue' => ['delegue_general'],
            'delegue_de_classe' => ['delegue_de_classe'],
        ];

        if (!isset($rolesByScope[$scope])) {
            return [];
        }

        $zoneType = $scope === 'delegue' ? 'delegation' : 'filiere';
        $rolePlaceholders = [];
        $params = [
            'user_id' => $userId,
            'zone_type' => $zoneType,
        ];

        foreach ($rolesByScope[$scope] as $index => $role) {
            $placeholder = 'role_' . $index;
            $rolePlaceholders[] = ':' . $placeholder;
            $params[$placeholder] = $role;
        }

        $statement = $this->pdo->prepare("
            SELECT z.id, z.nom, z.type_zone, az.role_dans_zone
            FROM appartenance_zones az
            JOIN zones z ON z.id = az.zone_id
            JOIN utilisateurs account ON account.id = az.utilisateur_id AND account.role = 'etudiant'
            WHERE az.utilisateur_id = :user_id
              AND az.role_dans_zone IN (" . implode(', ', $rolePlaceholders) . ")
              AND z.type_zone = :zone_type
              AND z.est_active = 1
            ORDER BY z.nom
        ");
        $statement->execute($params);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Liste les membres appartenant à l'une des zones autorisées.
     *
     * @param int[] $zoneIds
     * @return array
     */
    public function getUsersInZones(array $zoneIds): array {
        if (!$this->pdo) {
            return [];
        }

        $zoneIds = array_values(array_unique(array_filter(
            array_map('intval', $zoneIds),
            static fn (int $zoneId): bool => $zoneId > 0
        )));
        if (!$zoneIds) {
            return [];
        }

        $placeholders = [];
        $params = [];
        foreach ($zoneIds as $index => $zoneId) {
            $placeholder = 'zone_' . $index;
            $placeholders[] = ':' . $placeholder;
            $params[$placeholder] = $zoneId;
        }

        $statement = $this->pdo->prepare("
            SELECT u.id, u.nom, u.prenom, u.email, u.role, u.filiere, u.est_actif
            FROM utilisateurs u
            JOIN appartenance_zones az ON az.utilisateur_id = u.id
            JOIN zones z ON z.id = az.zone_id AND z.est_active = 1
            WHERE z.id IN (" . implode(', ', $placeholders) . ")
            ORDER BY u.nom, u.prenom
        ");
        $statement->execute($params);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSchoolActivityByZone(): array {
        if (!$this->pdo) {
            return [];
        }

        $statement = $this->pdo->query(
            "SELECT zone.id, zone.nom, zone.type_zone,
                    COUNT(DISTINCT membership.utilisateur_id) AS members_count,
                    COUNT(DISTINCT publication.id) AS publications_count
             FROM zones zone
             LEFT JOIN appartenance_zones membership ON membership.zone_id = zone.id
             LEFT JOIN categories category ON category.zone_id = zone.id AND category.est_active = 1
             LEFT JOIN publications publication
               ON publication.categorie_id = category.id AND publication.est_signale = 0
             WHERE zone.est_active = 1
             GROUP BY zone.id, zone.nom, zone.type_zone
             ORDER BY FIELD(zone.type_zone, 'generale', 'filiere', 'professeurs', 'delegation'), zone.nom"
        );
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Modifie le rôle de compte d'un utilisateur.
     *
     * @param int $userId
     * @param string $newRole
     * @return bool
     */
    public function changeUserRole(int $userId, string $newRole): bool {
        if (!$this->pdo) {
            return false;
        }

        $rolesAutorises = ['etudiant', 'professeur', 'administrateur'];
        $newRole = strtolower(trim($newRole));

        if (!in_array($newRole, $rolesAutorises, true)) {
            return false;
        }

        $stmt = $this->pdo->prepare("UPDATE utilisateurs SET role = :role WHERE id = :id");
        return $stmt->execute([
            'role' => $newRole,
            'id'   => $userId
        ]);
    }

    /**
     * Compte le nombre d'utilisateurs par rôle pour les statistiques du dashboard
     *
     * @return array
     */
    public function countUsersByRole(): array {
        if (!$this->pdo) {
            return [];
        }

        $stmt = $this->pdo->query("
            SELECT role, COUNT(*) as total 
            FROM utilisateurs 
            GROUP BY role
        ");

        $counts = [
            'etudiant'       => 0,
            'professeur'     => 0,
            'administrateur' => 0,
            'total'          => 0
        ];

        if ($stmt) {
            while ($row = $stmt->fetch()) {
                $counts[$row['role']] = (int) $row['total'];
                $counts['total'] += (int) $row['total'];
            }
        }

        return $counts;
    }

    /**
     * Retourne l'ensemble des indicateurs clés (KPIs) pour le tableau de bord
     *
     * @return array
     */
    public function getDashboardKPIs(): array {
        if (!$this->pdo) {
            return [
                'etudiants_count'    => 0,
                'professeurs_count'  => 0,
                'publications_count' => 0,
                'signalements_count' => 0,
                'zones_count'        => 0,
                'total_users'        => 0
            ];
        }

        $userCounts = $this->countUsersByRole();

        // Nombre de publications
        $pubStmt = $this->pdo->query("SELECT COUNT(*) FROM publications");
        $pubCount = $pubStmt ? (int)$pubStmt->fetchColumn() : 0;

        // Nombre de signalements en attente
        $sigStmt = $this->pdo->query("SELECT COUNT(*) FROM signalements WHERE statut = 'en_attente'");
        $sigCount = $sigStmt ? (int)$sigStmt->fetchColumn() : 0;

        // Nombre de zones de filières
        $zoneStmt = $this->pdo->query("SELECT COUNT(*) FROM zones WHERE type_zone = 'filiere'");
        $zoneCount = $zoneStmt ? (int)$zoneStmt->fetchColumn() : 0;

        $totalUsers = $userCounts['total'] ?? 0;

        return [
            'etudiants_count'    => $userCounts['etudiant'] ?? 0,
            'professeurs_count'  => $userCounts['professeur'] ?? 0,
            'publications_count' => $pubCount,
            'signalements_count' => $sigCount,
            'zones_count'        => $zoneCount,
            'total_users'        => $totalUsers
        ];
    }

    /**
     * Récupère les derniers utilisateurs inscrits
     *
     * @param int $limit
     * @return array
     */
    public function getRecentUsers(int $limit = 5): array {
        if (!$this->pdo) {
            return [];
        }

        $stmt = $this->pdo->prepare("
            SELECT id, nom, prenom, email, role, filiere, modules_enseignes, date_inscription 
            FROM utilisateurs 
            ORDER BY date_inscription DESC, id DESC 
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Récupère un utilisateur complet par son ID (pour modal d'édition)
     *
     * @param int $id
     * @return array|null
     */
    public function getUserById(int $id): ?array {
        if (!$this->pdo) {
            return null;
        }

        $stmt = $this->pdo->prepare("
            SELECT id, nom, prenom, email, role, filiere, modules_enseignes, biographie, date_inscription 
            FROM utilisateurs 
            WHERE id = :id 
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    /**
     * Met à jour les informations d'un utilisateur depuis le panneau admin
     *
     * @param int $id
     * @param array $data (nom, prenom, email, role, filiere, modules_enseignes, biographie)
     * @return bool
     */
    public function updateUser(int $id, array $data): bool {
        if (!$this->pdo) {
            return false;
        }

        $zoneRoles = $data['zone_roles'] ?? null;
        $zoneExceptions = $data['zone_exceptions'] ?? [];
        unset($data['zone_roles']);
        unset($data['zone_exceptions']);
        $fields = [];
        $params = ['id' => $id];

        $allowedFields = ['nom', 'prenom', 'email', 'role', 'filiere', 'modules_enseignes', 'biographie'];
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = :$field";
                $val = trim((string)$data[$field]);
                $params[$field] = ($val === '') ? null : $val;
            }
        }

        if (empty($fields) && $zoneRoles === null) {
            return false;
        }
        if ($zoneRoles !== null) {
            if (!is_array($zoneRoles)) {
                throw new InvalidArgumentException('Les responsabilités par zone sont invalides.');
            }
            if (!is_array($zoneExceptions)) {
                throw new InvalidArgumentException('Les exceptions de filière sont invalides.');
            }
            $knownZones = $this->getResponsibilityZones();
            $knownZoneIds = array_map(static fn (array $zone): int => (int)$zone['id'], $knownZones);
            foreach ($knownZoneIds as $zoneId) {
                if (!array_key_exists((string)$zoneId, $zoneRoles) && !array_key_exists($zoneId, $zoneRoles)) {
                    $zoneRoles[$zoneId] = 'membre';
                }
            }
        }

        $ownsTransaction = !$this->pdo->inTransaction();
        if ($ownsTransaction) {
            $this->pdo->beginTransaction();
        }

        try {
            if ($fields) {
                $sql = "UPDATE utilisateurs SET " . implode(', ', $fields) . " WHERE id = :id";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                if ($stmt->rowCount() === 0) {
                    $exists = $this->pdo->prepare('SELECT 1 FROM utilisateurs WHERE id = :id');
                    $exists->execute(['id' => $id]);
                    if (!$exists->fetchColumn()) {
                        if ($ownsTransaction) {
                            $this->pdo->rollBack();
                        }
                        return false;
                    }
                }
                if (array_key_exists('filiere', $data)) {
                    $syncFiliere = $this->pdo->prepare(
                        'UPDATE utilisateurs
                         SET filiere_id = (
                             SELECT filiere.id
                             FROM filieres filiere
                             WHERE LOWER(TRIM(filiere.nom)) = LOWER(TRIM(:filiere))
                             LIMIT 1
                         )
                         WHERE id = :id'
                    );
                    $syncFiliere->execute([
                        'filiere' => $params['filiere'],
                        'id' => $id,
                    ]);
                }
            }

            if ($zoneRoles !== null) {
                $zoneQuery = $this->pdo->prepare(
                    'SELECT type_zone FROM zones WHERE id = :zone_id AND est_active = 1 LIMIT 1'
                );
                $accountQuery = $this->pdo->prepare(
                    'SELECT user.role, user.filiere, user.filiere_id,
                            COALESCE(
                                (SELECT COALESCE(filiere.zone_id, zone.id)
                                 FROM filieres filiere
                                 LEFT JOIN zones zone
                                   ON zone.nom = CONCAT(\'Filière \', filiere.nom)
                                  AND zone.type_zone = \'filiere\'
                                 WHERE filiere.id = user.filiere_id),
                                (SELECT COALESCE(filiere.zone_id, zone.id)
                                 FROM filieres filiere
                                 LEFT JOIN zones zone
                                   ON zone.nom = CONCAT(\'Filière \', filiere.nom)
                                  AND zone.type_zone = \'filiere\'
                                 WHERE LOWER(TRIM(filiere.nom)) = LOWER(TRIM(user.filiere))
                                 LIMIT 1)
                            ) AS own_zone_id
                     FROM utilisateurs user
                     WHERE user.id = :user_id
                     LIMIT 1'
                );
                $accountQuery->execute(['user_id' => $id]);
                $account = $accountQuery->fetch(PDO::FETCH_ASSOC);
                if (!$account) {
                    throw new InvalidArgumentException('Utilisateur introuvable.');
                }
                $clearRole = $this->pdo->prepare(
                    "UPDATE appartenance_zones
                     SET role_dans_zone = 'membre', delegation_exception = FALSE,
                         delegue_classe_zone_id = NULL
                     WHERE utilisateur_id = :user_id AND zone_id = :zone_id
                       AND role_dans_zone IN ('delegue_de_classe', 'delegue_general')"
                );
                $existingRoleQuery = $this->pdo->prepare(
                    'SELECT role_dans_zone, delegation_exception
                     FROM appartenance_zones
                     WHERE utilisateur_id = :user_id AND zone_id = :zone_id
                     LIMIT 1'
                );
                $delegateZoneStatement = $this->pdo->query(
                    "SELECT id FROM zones WHERE type_zone = 'delegation' AND est_active = 1 ORDER BY id LIMIT 1"
                );
                $delegationZoneId = (int)$delegateZoneStatement->fetchColumn();
                $hasClassResponsibility = false;
                $hasGlobalResponsibility = false;

                foreach ($zoneRoles as $zoneId => $role) {
                    $zoneId = filter_var($zoneId, FILTER_VALIDATE_INT);
                    if (!$zoneId || !is_string($role)
                        || !in_array($role, ['membre', 'delegue_de_classe', 'delegue_general'], true)) {
                        throw new InvalidArgumentException('Une responsabilité de zone est invalide.');
                    }

                    $zoneQuery->execute(['zone_id' => $zoneId]);
                    $zoneType = $zoneQuery->fetchColumn();
                    if (!is_string($zoneType)
                        || ($role === 'delegue_de_classe' && $zoneType !== 'filiere')
                        || ($role === 'delegue_general' && $zoneType !== 'delegation')) {
                        throw new InvalidArgumentException('Cette responsabilité ne correspond pas au type de zone.');
                    }

                    if ($role !== 'membre' && $account['role'] !== 'etudiant') {
                        throw new InvalidArgumentException('Seul un compte étudiant peut recevoir une responsabilité de délégation.');
                    }
                    $isClassException = (string)($zoneExceptions[$zoneId] ?? $zoneExceptions[(string)$zoneId] ?? '') === '1';
                    $existingRoleQuery->execute(['user_id' => $id, 'zone_id' => $zoneId]);
                    $existingRole = $existingRoleQuery->fetch(PDO::FETCH_ASSOC);
                    $delegationException = false;
                    if ($role === 'delegue_de_classe'
                        && (int)($account['own_zone_id'] ?? 0) !== $zoneId) {
                        if ($isClassException) {
                            $delegationException = true;
                        } elseif (($existingRole['role_dans_zone'] ?? null) === 'delegue_de_classe') {
                            $clearRole->execute(['user_id' => $id, 'zone_id' => $zoneId]);
                            continue;
                        } else {
                            throw new InvalidArgumentException('Une attribution hors filière exige l’exception explicite de l’administrateur.');
                        }
                    }

                    if ($role === 'membre') {
                        $clearRole->execute(['user_id' => $id, 'zone_id' => $zoneId]);
                    } else {
                        if ($role === 'delegue_general') {
                            $this->pdo->prepare(
                                "UPDATE appartenance_zones
                                SET role_dans_zone = 'membre', delegue_classe_zone_id = NULL,
                                    delegation_exception = FALSE
                                 WHERE role_dans_zone = 'delegue_general'
                                   AND utilisateur_id <> :user_id"
                            )->execute(['user_id' => $id]);
                            $hasGlobalResponsibility = true;
                        } elseif ($role === 'delegue_de_classe') {
                            $this->pdo->prepare(
                                "UPDATE appartenance_zones
                                SET role_dans_zone = 'membre', delegue_classe_zone_id = NULL,
                                    delegation_exception = FALSE
                                 WHERE zone_id = :zone_id
                                   AND role_dans_zone = 'delegue_de_classe'
                                   AND utilisateur_id <> :user_id"
                            )->execute(['zone_id' => $zoneId, 'user_id' => $id]);
                            $hasClassResponsibility = true;
                        }
                        $this->pdo->prepare(
                            'INSERT INTO appartenance_zones
                                (utilisateur_id, zone_id, role_dans_zone, delegation_exception, delegue_classe_zone_id)
                             VALUES (:user_id, :zone_id, :role, :delegation_exception, :class_zone_id)
                             ON DUPLICATE KEY UPDATE
                                 role_dans_zone = VALUES(role_dans_zone),
                                 delegation_exception = VALUES(delegation_exception),
                                 delegue_classe_zone_id = VALUES(delegue_classe_zone_id)'
                        )->execute([
                            'user_id' => $id,
                            'zone_id' => $zoneId,
                            'role' => $role,
                            'delegation_exception' => $delegationException ? 1 : 0,
                            'class_zone_id' => $role === 'delegue_de_classe' ? $zoneId : null,
                        ]);
                    }
                }

                if ($hasClassResponsibility) {
                    if ($delegationZoneId <= 0) {
                        throw new InvalidArgumentException('Aucune zone active de Délégation n’est configurée.');
                    }
                    $this->pdo->prepare(
                        "INSERT INTO appartenance_zones (utilisateur_id, zone_id, role_dans_zone)
                         VALUES (:user_id, :zone_id, 'membre')
                         ON DUPLICATE KEY UPDATE role_dans_zone = IF(role_dans_zone = 'delegue_general', role_dans_zone, 'membre')"
                    )->execute(['user_id' => $id, 'zone_id' => $delegationZoneId]);
                } elseif (!$hasGlobalResponsibility && $delegationZoneId > 0) {
                    $this->pdo->prepare(
                        "DELETE FROM appartenance_zones
                         WHERE utilisateur_id = :user_id AND zone_id = :zone_id
                           AND role_dans_zone = 'membre'"
                    )->execute(['user_id' => $id, 'zone_id' => $delegationZoneId]);
                }
            }

            if ($ownsTransaction) {
                $this->pdo->commit();
            }
            return true;
        } catch (Throwable $exception) {
            if ($ownsTransaction && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            if ($exception instanceof PDOException
                && (int)($exception->errorInfo[1] ?? 0) === 1062) {
                throw new InvalidArgumentException(
                    'Cette responsabilité est déjà attribuée. Actualisez la page puis réessayez.',
                    0,
                    $exception
                );
            }
            throw $exception;
        }
    }

    public function getResponsibilityZones(): array {
        if (!$this->pdo) {
            return [];
        }

        $statement = $this->pdo->query(
            'SELECT id, nom, type_zone FROM zones WHERE est_active = 1 ORDER BY nom'
        );
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserZoneResponsibilities(int $userId): array {
        if (!$this->pdo || $userId <= 0) {
            return [];
        }

        $statement = $this->pdo->prepare(
            "SELECT membership.zone_id, membership.role_dans_zone
             FROM appartenance_zones membership
             JOIN zones zone ON zone.id = membership.zone_id
             WHERE membership.utilisateur_id = :user_id
               AND zone.est_active = 1
               AND membership.role_dans_zone IN ('delegue_de_classe', 'delegue_general')"
        );
        $statement->execute(['user_id' => $userId]);

        $roles = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $roles[(int)$row['zone_id']] = $row['role_dans_zone'];
        }
        return $roles;
    }

    public function getUserZoneExceptions(int $userId): array {
        if (!$this->pdo || $userId <= 0) {
            return [];
        }

        $statement = $this->pdo->prepare(
            "SELECT membership.zone_id
             FROM appartenance_zones membership
             JOIN zones zone ON zone.id = membership.zone_id
             WHERE membership.utilisateur_id = :user_id
               AND zone.est_active = 1
               AND membership.role_dans_zone = 'delegue_de_classe'
               AND membership.delegation_exception = 1"
        );
        $statement->execute(['user_id' => $userId]);

        return array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));
    }

    public function getUserResponsibilities(int $userId): array {
        if (!$this->pdo || $userId <= 0) {
            return [];
        }

        $statement = $this->pdo->prepare(
            "SELECT zone.nom, membership.role_dans_zone
             FROM appartenance_zones membership
             JOIN zones zone ON zone.id = membership.zone_id
             WHERE membership.utilisateur_id = :user_id
               AND zone.est_active = 1
               AND membership.role_dans_zone IN ('delegue_de_classe', 'delegue_general')
             ORDER BY zone.nom"
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Supprime un utilisateur (avec ses cascades SQL)
     *
     * @param int $id
     * @return bool
     */
    public function deleteUser(int $id): bool {
        if (!$this->pdo || $id <= 0) {
            return false;
        }

        $userStatement = $this->pdo->prepare('SELECT avatar_url FROM utilisateurs WHERE id = :id LIMIT 1');
        $userStatement->execute(['id' => $id]);
        $avatarPath = $userStatement->fetchColumn();
        if ($avatarPath === false) {
            return false;
        }

        $attachmentRows = [];
        $publications = $this->pdo->prepare('SELECT id FROM publications WHERE utilisateur_id = :user_id');
        $publications->execute(['user_id' => $id]);
        foreach ($publications->fetchAll(PDO::FETCH_COLUMN) as $publicationId) {
            $attachmentRows = array_merge(
                $attachmentRows,
                Attachment::findForTarget($this->pdo, 'publication', (int)$publicationId)
            );
        }

        $comments = $this->pdo->prepare('SELECT id FROM commentaires WHERE utilisateur_id = :user_id');
        $comments->execute(['user_id' => $id]);
        foreach ($comments->fetchAll(PDO::FETCH_COLUMN) as $commentId) {
            $attachmentRows = array_merge(
                $attachmentRows,
                Attachment::findForTarget($this->pdo, 'commentaire', (int)$commentId)
            );
        }
        $attachmentRows = array_values(array_reduce(
            $attachmentRows,
            static function (array $unique, array $attachment): array {
                $unique[(int)$attachment['id']] = $attachment;
                return $unique;
            },
            []
        ));

        $stmt = $this->pdo->prepare("DELETE FROM utilisateurs WHERE id = :id");
        $deleted = $stmt->execute(['id' => $id]);
        if ($deleted) {
            Attachment::removeStoredFiles($this->pdo, $attachmentRows);
            Avatar::removeStoredFile(is_string($avatarPath) ? $avatarPath : null);
        }

        return $deleted;
    }

    /**
     * Récupère la liste des signalements avec les détails de l'auteur et de la cible
     *
     * @param string|null $statut 'en_attente', 'traite', 'rejete' ou null
     * @return array
     */
    public function getReports(?string $statut = null, ?array $zoneIds = null, ?int $limit = null): array {
        if (!$this->pdo) {
            return [];
        }

        $zonePlaceholders = [];
        $params = [];
        if ($zoneIds !== null) {
            $zoneIds = array_values(array_unique(array_filter(
                array_map('intval', $zoneIds),
                static fn (int $zoneId): bool => $zoneId > 0
            )));
            if (!$zoneIds) {
                return [];
            }

            foreach ($zoneIds as $index => $zoneId) {
                $placeholder = 'zone_' . $index;
                $zonePlaceholders[] = ':' . $placeholder;
                $params[$placeholder] = $zoneId;
            }
        }

        $sql = "
            SELECT 
                s.id, s.type_cible, s.cible_id, s.motif, s.statut, s.date_signalement,
                u.nom as auteur_nom, u.prenom as auteur_prenom, u.email as auteur_email, u.role as auteur_role,
                target_zone.id AS zone_id, target_zone.nom AS zone_name,
                CASE 
                    WHEN s.type_cible = 'publication' THEN target_publication.titre
                    ELSE 'Commentaire'
                END as cible_titre,
                CASE 
                    WHEN s.type_cible = 'publication' THEN target_publication.contenu
                    WHEN s.type_cible = 'commentaire' THEN target_comment.contenu
                    ELSE ''
                END as cible_contenu
            FROM signalements s
            JOIN utilisateurs u ON s.auteur_id = u.id
            LEFT JOIN publications target_publication
              ON s.type_cible = 'publication' AND target_publication.id = s.cible_id
            LEFT JOIN commentaires target_comment
              ON s.type_cible = 'commentaire' AND target_comment.id = s.cible_id
            LEFT JOIN publications comment_publication
              ON comment_publication.id = target_comment.publication_id
            LEFT JOIN categories target_category
              ON target_category.id = COALESCE(target_publication.categorie_id, comment_publication.categorie_id)
            LEFT JOIN zones target_zone ON target_zone.id = target_category.zone_id
        ";

        $conditions = [];
        if ($statut !== null) {
            $conditions[] = 's.statut = :statut';
            $params['statut'] = $statut;
        }
        if ($zoneIds !== null) {
            $conditions[] = 'target_zone.id IN (' . implode(', ', $zonePlaceholders) . ')';
        }
        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY s.date_signalement DESC";
        if ($limit !== null) {
            $limit = max(1, min($limit, 200));
            $sql .= " LIMIT $limit";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Traite un signalement (classer ou supprimer le contenu)
     *
     * @param int $reportId
     * @param string $action 'traiter' (classer), 'rejeter' ou 'supprimer_contenu'
     * @return bool
     */
    public function handleReport(int $reportId, string $action): bool {
        if (!$this->pdo) {
            return false;
        }

        // Récupérer les informations du signalement
        $stmt = $this->pdo->prepare("SELECT * FROM signalements WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $reportId]);
        $report = $stmt->fetch();

        if (!$report) {
            return false;
        }

        if ($action === 'supprimer_contenu') {
            $attachments = Attachment::findForTarget($this->pdo, $report['type_cible'], (int)$report['cible_id']);
            if ($report['type_cible'] === 'publication') {
                $del = $this->pdo->prepare("DELETE FROM publications WHERE id = :id");
                $del->execute(['id' => $report['cible_id']]);
            } elseif ($report['type_cible'] === 'commentaire') {
                $del = $this->pdo->prepare("DELETE FROM commentaires WHERE id = :id");
                $del->execute(['id' => $report['cible_id']]);
            }
            Attachment::removeStoredFiles($this->pdo, $attachments);
            $update = $this->pdo->prepare("UPDATE signalements SET statut = 'traite' WHERE id = :id");
            return $update->execute(['id' => $reportId]);
        } elseif ($action === 'rejeter') {
            $update = $this->pdo->prepare("UPDATE signalements SET statut = 'rejete' WHERE id = :id");
            return $update->execute(['id' => $reportId]);
        } else {
            // Classé / traité
            $update = $this->pdo->prepare("UPDATE signalements SET statut = 'traite' WHERE id = :id");
            return $update->execute(['id' => $reportId]);
        }
    }
}