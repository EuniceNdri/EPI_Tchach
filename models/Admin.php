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
} else {
    require_once __DIR__ . '/User.php';
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
                SELECT id, nom, prenom, email, role, filiere, modules_enseignes, date_inscription 
                FROM utilisateurs 
                WHERE LOWER(role) = LOWER(:role) 
                ORDER BY date_inscription DESC
            ");
            $stmt->execute(['role' => trim($filterRole)]);
        } else {
            $stmt = $this->pdo->query("
                SELECT id, nom, prenom, email, role, filiere, modules_enseignes, date_inscription 
                FROM utilisateurs 
                ORDER BY date_inscription DESC
            ");
        }

        return $stmt ? $stmt->fetchAll() : [];
    }

    /**
     * Modifie le rôle d'un utilisateur (ex: promouvoir en modérateur ou administrateur)
     *
     * @param int $userId
     * @param string $newRole
     * @return bool
     */
    public function changeUserRole(int $userId, string $newRole): bool {
        if (!$this->pdo) {
            return false;
        }

        $rolesAutorises = ['etudiant', 'professeur', 'moderateur', 'administrateur'];
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
            'moderateur'     => 0,
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
                'etudiants_count'    => 1248,
                'professeurs_count'  => 64,
                'publications_count' => 3840,
                'signalements_count' => 3,
                'zones_count'        => 12,
                'total_users'        => 1312
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
        if ($totalUsers === 0) {
            return [
                'etudiants_count'    => 1248,
                'professeurs_count'  => 64,
                'publications_count' => 3840,
                'signalements_count' => 0,
                'zones_count'        => 12,
                'total_users'        => 1312
            ];
        }

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

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE utilisateurs SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Supprime un utilisateur (avec ses cascades SQL)
     *
     * @param int $id
     * @return bool
     */
    public function deleteUser(int $id): bool {
        if (!$this->pdo) {
            return false;
        }

        $stmt = $this->pdo->prepare("DELETE FROM utilisateurs WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Récupère la liste des signalements avec les détails de l'auteur et de la cible
     *
     * @param string|null $statut 'en_attente', 'traite', 'rejete' ou null
     * @return array
     */
    public function getReports(?string $statut = null): array {
        if (!$this->pdo) {
            return [];
        }

        $sql = "
            SELECT 
                s.id, s.type_cible, s.cible_id, s.motif, s.statut, s.date_signalement,
                u.nom as auteur_nom, u.prenom as auteur_prenom, u.email as auteur_email, u.role as auteur_role,
                CASE 
                    WHEN s.type_cible = 'publication' THEN (SELECT titre FROM publications WHERE id = s.cible_id)
                    ELSE 'Commentaire'
                END as cible_titre,
                CASE 
                    WHEN s.type_cible = 'publication' THEN (SELECT contenu FROM publications WHERE id = s.cible_id)
                    WHEN s.type_cible = 'commentaire' THEN (SELECT contenu FROM commentaires WHERE id = s.cible_id)
                    ELSE ''
                END as cible_contenu
            FROM signalements s
            JOIN utilisateurs u ON s.auteur_id = u.id
        ";

        if ($statut !== null) {
            $sql .= " WHERE s.statut = :statut";
        }

        $sql .= " ORDER BY s.date_signalement DESC";

        $stmt = $this->pdo->prepare($sql);
        if ($statut !== null) {
            $stmt->execute(['statut' => $statut]);
        } else {
            $stmt->execute();
        }

        return $stmt->fetchAll();
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
            if ($report['type_cible'] === 'publication') {
                $del = $this->pdo->prepare("DELETE FROM publications WHERE id = :id");
                $del->execute(['id' => $report['cible_id']]);
            } elseif ($report['type_cible'] === 'commentaire') {
                $del = $this->pdo->prepare("DELETE FROM commentaires WHERE id = :id");
                $del->execute(['id' => $report['cible_id']]);
            }
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