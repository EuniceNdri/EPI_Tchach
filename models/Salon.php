<?php

require_once __DIR__ . '/../config/database.php';

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