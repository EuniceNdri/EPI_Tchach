<?php
/**
 * =============================================================
 * models/Signalement.php – Modèle de Gestion des Signalements
 * =============================================================
 * Permet à tout utilisateur de signaler une publication ou un
 * commentaire, et gère le blocage automatique au-delà d'un seuil.
 * =============================================================
 */

if (defined('ROOT_PATH')) {
    require_once ROOT_PATH . '/config/database.php';
    require_once ROOT_PATH . '/models/Notification.php';
} else {
    require_once dirname(__DIR__) . '/config/database.php';
    require_once dirname(__DIR__) . '/models/Notification.php';
}

if (!function_exists('url')) {
    function url(string $path = ''): string {
        global $baseUrl;
        $base = $baseUrl ?? '';
        $trimmed = ltrim($path, '/');
        return $base ? $base . '/' . $trimmed : '/' . $trimmed;
    }
}

class Signalement {
    private ?PDO $pdo;
    private Notification $notificationModel;

    // Seuil de signalements à partir duquel la publication est bloquée automatiquement
    public const SEUIL_BLOCAGE_AUTO = 3;

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
     * Enregistre un signalement par un utilisateur
     *
     * @param int $auteurId ID de l'utilisateur qui signale
     * @param string $typeCible 'publication' ou 'commentaire'
     * @param int $cibleId ID de la cible
     * @param string $motif Motif du signalement
     * @return array Résultat de l'opération
     */
    public function signaler(int $auteurId, string $typeCible, int $cibleId, string $motif): array {
        if (!$this->pdo || $auteurId <= 0 || $cibleId <= 0) {
            return ['success' => false, 'message' => "Paramètres de signalement invalides."];
        }

        $typeCible = ($typeCible === 'commentaire') ? 'commentaire' : 'publication';
        $motif = trim($motif);

        if (empty($motif)) {
            return ['success' => false, 'message' => "Veuillez préciser la raison du signalement."];
        }

        // 1. Vérifier si l'utilisateur a déjà un signalement en attente pour cette cible
        if ($this->hasUserReported($auteurId, $typeCible, $cibleId)) {
            return [
                'success' => false, 
                'message' => "Vous avez déjà signalé cet élément. Les responsables l'examinent actuellement."
            ];
        }

        // 2. Insérer le nouveau signalement
        $stmt = $this->pdo->prepare("
            INSERT INTO signalements (auteur_id, type_cible, cible_id, motif, statut, date_signalement)
            VALUES (:auteur_id, :type_cible, :cible_id, :motif, 'en_attente', NOW())
        ");
        $success = $stmt->execute([
            'auteur_id'  => $auteurId,
            'type_cible' => $typeCible,
            'cible_id'   => $cibleId,
            'motif'      => $motif
        ]);

        if (!$success) {
            return ['success' => false, 'message' => "Erreur lors de l'enregistrement du signalement."];
        }

        $signalementId = (int)$this->pdo->lastInsertId();

        // 3. Marquer la cible comme signalée (est_signale = 1)
        if ($typeCible === 'publication') {
            $upPub = $this->pdo->prepare("UPDATE publications SET est_signale = 1 WHERE id = :id");
            $upPub->execute(['id' => $cibleId]);
        } elseif ($typeCible === 'commentaire') {
            $upCom = $this->pdo->prepare("UPDATE commentaires SET est_signale = 1 WHERE id = :id");
            $upCom->execute(['id' => $cibleId]);
        }

        // 4. Compter le nombre de signalements en attente pour cette cible
        $totalReports = $this->countReportsForTarget($typeCible, $cibleId);
        $autoBlocked = false;

        // 5. Règle de modération automatique : Bloquer si beaucoup de signalements
        if ($typeCible === 'publication' && $totalReports >= self::SEUIL_BLOCAGE_AUTO) {
            $autoBlocked = $this->bloquerPublicationAutomatiquement($cibleId, $totalReports);
        }

        // 6. Notifier les administrateurs et responsables de zone
        $notifMsg = $autoBlocked
            ? "🚨 Publication #{$cibleId} AUTOMATIQUEMENT BLOQUÉE ({$totalReports} signalements reçus)."
            : "⚠️ Nouveau signalement reçu sur {$typeCible} #{$cibleId} (Total: {$totalReports}).";

        $this->notificationModel->notifyAdmins(
            'moderation',
            $notifMsg,
            url('admin/moderation')
        );
        $this->notificationModel->notifyClassDelegatesForReport(
            $signalementId,
            $notifMsg,
            url('moderation')
        );
        $this->notificationModel->notifyGlobalDelegates(
            $signalementId,
            $notifMsg,
            url('dashboard')
        );

        return [
            'success'       => true,
            'report_id'     => $signalementId,
            'total_reports' => $totalReports,
            'auto_blocked'  => $autoBlocked,
            'message'       => $autoBlocked 
                ? "Contenu signalé. En raison du nombre élevé de signalements ({$totalReports}), la publication a été automatiquement bloquée."
                : "Merci. Votre signalement a été transmis à l'équipe de modération."
        ];
    }

    /**
     * Bloque automatiquement une publication ayant dépassé le seuil de signalements
     */
    private function bloquerPublicationAutomatiquement(int $publicationId, int $totalReports): bool {
        if (!$this->pdo) {
            return false;
        }

        // Fermer la publication et bloquer les commentaires
        $stmt = $this->pdo->prepare("
            UPDATE publications 
            SET statut = 'ferme', commentaires_fermes = 1, est_signale = 1 
            WHERE id = :id
        ");
        $stmt->execute(['id' => $publicationId]);

        // Récupérer l'auteur et le titre pour lui envoyer une notification
        $pStmt = $this->pdo->prepare("SELECT utilisateur_id, titre FROM publications WHERE id = :id LIMIT 1");
        $pStmt->execute(['id' => $publicationId]);
        $pub = $pStmt->fetch(PDO::FETCH_ASSOC);

        if ($pub && !empty($pub['utilisateur_id'])) {
            $this->notificationModel->notifyBlocageAutomatique(
                (int)$pub['utilisateur_id'],
                $pub['titre'] ?? 'Votre publication',
                $totalReports
            );
        }

        return true;
    }

    /**
     * Vérifie si un utilisateur a déjà signalé cette cible (en attente)
     */
    public function hasUserReported(int $userId, string $typeCible, int $cibleId): bool {
        if (!$this->pdo) {
            return false;
        }

        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) 
            FROM signalements 
            WHERE auteur_id = :user_id 
              AND type_cible = :type_cible 
              AND cible_id = :cible_id 
              AND statut = 'en_attente'
        ");
        $stmt->execute([
            'user_id'    => $userId,
            'type_cible' => $typeCible,
            'cible_id'   => $cibleId
        ]);

        return ((int)$stmt->fetchColumn()) > 0;
    }

    /**
     * Compte les signalements actifs pour une cible
     */
    public function countReportsForTarget(string $typeCible, int $cibleId): int {
        if (!$this->pdo) {
            return 0;
        }

        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) 
            FROM signalements 
            WHERE type_cible = :type_cible 
              AND cible_id = :cible_id 
              AND statut = 'en_attente'
        ");
        $stmt->execute([
            'type_cible' => $typeCible,
            'cible_id'   => $cibleId
        ]);

        return (int)$stmt->fetchColumn();
    }

    /**
     * Récupère la liste complète des signalements avec informations enrichies
     */
    public function getAllReports(?string $statut = null, ?array $zoneIds = null): array {
        if (!$this->pdo) {
            return [];
        }

        $zoneIds = $zoneIds === null ? null : array_values(array_unique(array_filter(
            array_map('intval', $zoneIds),
            static fn (int $zoneId): bool => $zoneId > 0
        )));
        if ($zoneIds !== null && $zoneIds === []) {
            return [];
        }

        $sql = "
            SELECT 
                s.id, s.auteur_id, s.type_cible, s.cible_id, s.motif, s.statut, s.date_signalement, s.date_traitement,
                s.motif AS raison,
                s.cible_id AS message_id,
                s.date_signalement AS created_at,
                -- Info sur l'utilisateur qui a signalé
                u_rep.nom AS signaleur_nom, u_rep.prenom AS signaleur_prenom, u_rep.email AS signaleur_email,
                -- Auteur du contenu ciblé
                CASE 
                    WHEN s.type_cible = 'publication' THEN (SELECT p.utilisateur_id FROM publications p WHERE p.id = s.cible_id)
                    WHEN s.type_cible = 'commentaire' THEN (SELECT c.utilisateur_id FROM commentaires c WHERE c.id = s.cible_id)
                    ELSE NULL
                END AS cible_auteur_id,
                CASE 
                    WHEN s.type_cible = 'publication' THEN (SELECT CONCAT(u.prenom, ' ', u.nom) FROM publications p JOIN utilisateurs u ON p.utilisateur_id = u.id WHERE p.id = s.cible_id)
                    WHEN s.type_cible = 'commentaire' THEN (SELECT CONCAT(u.prenom, ' ', u.nom) FROM commentaires c JOIN utilisateurs u ON c.utilisateur_id = u.id WHERE c.id = s.cible_id)
                    ELSE 'Inconnu'
                END AS cible_auteur_nom,
                -- Titre et contenu
                CASE 
                    WHEN s.type_cible = 'publication' THEN (SELECT p.titre FROM publications p WHERE p.id = s.cible_id)
                    ELSE 'Commentaire'
                END AS cible_titre,
                CASE 
                    WHEN s.type_cible = 'publication' THEN (SELECT p.contenu FROM publications p WHERE p.id = s.cible_id)
                    WHEN s.type_cible = 'commentaire' THEN (SELECT c.contenu FROM commentaires c WHERE c.id = s.cible_id)
                    ELSE ''
                END AS cible_contenu,
                -- Statut de la publication
                CASE 
                    WHEN s.type_cible = 'publication' THEN (SELECT p.statut FROM publications p WHERE p.id = s.cible_id)
                    ELSE NULL
                END AS publication_statut,
                -- Nombre total de signalements sur cette même cible
                (SELECT COUNT(*) FROM signalements sub WHERE sub.type_cible = s.type_cible AND sub.cible_id = s.cible_id AND sub.statut = 'en_attente') AS total_signalements_cible
            FROM signalements s
            JOIN utilisateurs u_rep ON s.auteur_id = u_rep.id
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
        $parameters = [];
        if ($statut !== null) {
            $conditions[] = 's.statut = :statut';
            $parameters['statut'] = $statut;
        }
        if ($zoneIds !== null) {
            $placeholders = [];
            foreach ($zoneIds as $index => $zoneId) {
                $placeholder = 'zone_' . $index;
                $placeholders[] = ':' . $placeholder;
                $parameters[$placeholder] = $zoneId;
            }
            $conditions[] = 'target_zone.id IN (' . implode(', ', $placeholders) . ')';
        }
        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY s.date_signalement DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parameters);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un signalement par son ID
     */
    public function getReportById(int $reportId): ?array {
        if (!$this->pdo || $reportId <= 0) {
            return null;
        }

        $stmt = $this->pdo->prepare("SELECT * FROM signalements WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $reportId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }
}
