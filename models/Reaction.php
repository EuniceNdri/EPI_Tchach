<?php
/**
 * =============================================================
 * models/Reaction.php – Modèle de Gestion des Réactions
 * =============================================================
 * Gère les interactions sociales (Like, J'adore, Utile) sur les
 * publications et commentaires, avec bascule (toggle) et notification.
 * =============================================================
 */

if (defined('ROOT_PATH')) {
    require_once ROOT_PATH . '/config/database.php';
    require_once ROOT_PATH . '/models/Notification.php';
} else {
    require_once dirname(__DIR__) . '/config/database.php';
    require_once dirname(__DIR__) . '/models/Notification.php';
}

class Reaction {
    private ?PDO $pdo;
    private Notification $notificationModel;

    // Types de réactions autorisés dans la base (alignés avec ENUM SQL)
    public const VALID_TYPES = ['like', 'jadore', 'utile'];

    public function __construct(?PDO $pdo = null) {
        if ($pdo !== null) {
            $this->pdo = $pdo;
        } else {
            global $pdo;
            $this->pdo = $pdo;
        }
        $this->notificationModel = new Notification($this->pdo);
    }

    /**
     * Ajoute, modifie ou supprime (toggle) une réaction d'un utilisateur
     *
     * @param int $userId ID de l'utilisateur
     * @param string $typeCible 'publication' ou 'commentaire'
     * @param int $cibleId ID de la cible
     * @param string $typeReaction 'like', 'jadore', 'utile'
     * @return array Résultat avec statut, réaction actuelle et totaux actualisés
     */
    public function toggle(int $userId, string $typeCible, int $cibleId, string $typeReaction): array {
        if (!$this->pdo || $userId <= 0 || $cibleId <= 0) {
            return ['success' => false, 'message' => "Données invalides."];
        }

        $typeCible = ($typeCible === 'commentaire') ? 'commentaire' : 'publication';
        $typeReaction = strtolower(trim($typeReaction));

        if (!in_array($typeReaction, self::VALID_TYPES, true)) {
            $typeReaction = 'like';
        }

        // Vérifier si une réaction existe déjà
        $checkStmt = $this->pdo->prepare("
            SELECT id, type_reaction 
            FROM reactions 
            WHERE utilisateur_id = :user_id 
              AND type_cible = :type_cible 
              AND cible_id = :cible_id 
            LIMIT 1
        ");
        $checkStmt->execute([
            'user_id'    => $userId,
            'type_cible' => $typeCible,
            'cible_id'   => $cibleId
        ]);
        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

        $action = '';
        $currentReaction = null;

        if ($existing) {
            if ($existing['type_reaction'] === $typeReaction) {
                // Même réaction cliquée -> Retrait (toggle off)
                $delStmt = $this->pdo->prepare("DELETE FROM reactions WHERE id = :id");
                $delStmt->execute(['id' => $existing['id']]);
                $action = 'removed';
                $currentReaction = null;
            } else {
                // Réaction différente -> Mise à jour
                $upStmt = $this->pdo->prepare("
                    UPDATE reactions 
                    SET type_reaction = :type_reaction, date_reaction = NOW() 
                    WHERE id = :id
                ");
                $upStmt->execute([
                    'type_reaction' => $typeReaction,
                    'id'            => $existing['id']
                ]);
                $action = 'updated';
                $currentReaction = $typeReaction;
            }
        } else {
            // Nouvelle réaction -> Insertion
            $insStmt = $this->pdo->prepare("
                INSERT INTO reactions (utilisateur_id, type_cible, cible_id, type_reaction, date_reaction)
                VALUES (:user_id, :type_cible, :cible_id, :type_reaction, NOW())
            ");
            $insStmt->execute([
                'user_id'       => $userId,
                'type_cible'    => $typeCible,
                'cible_id'      => $cibleId,
                'type_reaction' => $typeReaction
            ]);
            $action = 'added';
            $currentReaction = $typeReaction;

            // Déclencher la notification à l'auteur du contenu
            $this->dispatchReactionNotification($userId, $typeCible, $cibleId, $typeReaction);
        }

        // Récupérer les compteurs actualisés
        $counts = $this->getCounts($typeCible, $cibleId);

        return [
            'success'         => true,
            'action'          => $action,
            'user_reaction'   => $currentReaction,
            'counts'          => $counts
        ];
    }

    /**
     * Envoie la notification à l'auteur de la cible lors d'un like/jadore
     */
    private function dispatchReactionNotification(int $reactorId, string $typeCible, int $cibleId, string $reactionType): void {
        if (!$this->pdo) {
            return;
        }

        if ($typeCible === 'publication') {
            $stmt = $this->pdo->prepare("SELECT utilisateur_id, titre FROM publications WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $cibleId]);
            $pub = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($pub && (int)$pub['utilisateur_id'] !== $reactorId) {
                $this->notificationModel->notifyReaction(
                    (int)$pub['utilisateur_id'],
                    $reactorId,
                    $reactionType,
                    $pub['titre'] ?? 'Publication',
                    $cibleId
                );
            }
        } elseif ($typeCible === 'commentaire') {
            $stmt = $this->pdo->prepare("
                SELECT c.utilisateur_id, c.publication_id, p.titre 
                FROM commentaires c
                JOIN publications p ON c.publication_id = p.id
                WHERE c.id = :id 
                LIMIT 1
            ");
            $stmt->execute(['id' => $cibleId]);
            $com = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($com && (int)$com['utilisateur_id'] !== $reactorId) {
                $this->notificationModel->notifyReaction(
                    (int)$com['utilisateur_id'],
                    $reactorId,
                    $reactionType,
                    "un commentaire sur : " . ($com['titre'] ?? 'Publication'),
                    (int)$com['publication_id']
                );
            }
        }
    }

    /**
     * Récupère le décompte de chaque réaction pour une cible donnée
     */
    public function getCounts(string $typeCible, int $cibleId): array {
        $result = [
            'like'   => 0,
            'jadore' => 0,
            'utile'  => 0,
            'total'  => 0
        ];

        if (!$this->pdo || $cibleId <= 0) {
            return $result;
        }

        $stmt = $this->pdo->prepare("
            SELECT type_reaction, COUNT(*) as cnt 
            FROM reactions 
            WHERE type_cible = :type_cible AND cible_id = :cible_id 
            GROUP BY type_reaction
        ");
        $stmt->execute([
            'type_cible' => $typeCible,
            'cible_id'   => $cibleId
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total = 0;
        foreach ($rows as $row) {
            $t = $row['type_reaction'];
            $c = (int)$row['cnt'];
            if (isset($result[$t])) {
                $result[$t] = $c;
            }
            $total += $c;
        }
        $result['total'] = $total;

        return $result;
    }

    /**
     * Récupère la réaction d'un utilisateur spécifique sur une cible
     */
    public function getUserReaction(int $userId, string $typeCible, int $cibleId): ?string {
        if (!$this->pdo || $userId <= 0 || $cibleId <= 0) {
            return null;
        }

        $stmt = $this->pdo->prepare("
            SELECT type_reaction 
            FROM reactions 
            WHERE utilisateur_id = :user_id 
              AND type_cible = :type_cible 
              AND cible_id = :cible_id 
            LIMIT 1
        ");
        $stmt->execute([
            'user_id'    => $userId,
            'type_cible' => $typeCible,
            'cible_id'   => $cibleId
        ]);

        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ? $res['type_reaction'] : null;
    }

    /**
     * Récupère les réactions résumées pour un lot de cibles (optimisation pour listes)
     */
    public function getBatchReactions(string $typeCible, array $cibleIds, ?int $currentUserId = null): array {
        if (!$this->pdo || empty($cibleIds)) {
            return [];
        }

        $cleanIds = array_map('intval', $cibleIds);
        $inQuery = implode(',', $cleanIds);

        // 1. Compteurs par type
        $stmt = $this->pdo->query("
            SELECT cible_id, type_reaction, COUNT(*) as cnt 
            FROM reactions 
            WHERE type_cible = '{$typeCible}' AND cible_id IN ({$inQuery}) 
            GROUP BY cible_id, type_reaction
        ");
        $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

        $summary = [];
        foreach ($cleanIds as $id) {
            $summary[$id] = [
                'like'   => 0,
                'jadore' => 0,
                'utile'  => 0,
                'total'  => 0,
                'user_reaction' => null
            ];
        }

        foreach ($rows as $r) {
            $cid = (int)$r['cible_id'];
            $t = $r['type_reaction'];
            $cnt = (int)$r['cnt'];
            if (isset($summary[$cid][$t])) {
                $summary[$cid][$t] = $cnt;
                $summary[$cid]['total'] += $cnt;
            }
        }

        // 2. Réaction de l'utilisateur courant
        if ($currentUserId && $currentUserId > 0) {
            $uStmt = $this->pdo->prepare("
                SELECT cible_id, type_reaction 
                FROM reactions 
                WHERE type_cible = :type_cible 
                  AND utilisateur_id = :user_id 
                  AND cible_id IN ({$inQuery})
            ");
            $uStmt->execute([
                'type_cible' => $typeCible,
                'user_id'    => $currentUserId
            ]);
            $uRows = $uStmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($uRows as $ur) {
                $cid = (int)$ur['cible_id'];
                if (isset($summary[$cid])) {
                    $summary[$cid]['user_reaction'] = $ur['type_reaction'];
                }
            }
        }

        return $summary;
    }
}
