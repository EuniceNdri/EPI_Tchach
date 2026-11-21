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
    if (!function_exists('url')) {
        @require_once ROOT_PATH . '/app/Bootstrap.php';
    }
    require_once ROOT_PATH . '/app/Models/Notifications/Notification.php';
} else {
    require_once dirname(__DIR__, 3) . '/config/database.php';
    if (!function_exists('url')) {
        @require_once dirname(__DIR__, 2) . '/Bootstrap.php';
    }
    require_once __DIR__ . '/../Notifications/Notification.php';
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
     * Enregistre un signalement par un utilisateur avec règles anti-abus strictes
     *
     * @param int $auteurId ID de l'utilisateur qui signale
     * @param string $typeCible 'publication', 'commentaire' ou 'utilisateur'
     * @param int $cibleId ID de la cible
     * @param string $motif Motif du signalement
     * @param string|null $detail Détails complémentaires optionnels
     * @param int|null $zoneId ID de la zone/salon concerné (optionnel)
     * @return array Résultat de l'opération
     */
    public function signaler(
        int $auteurId,
        string $typeCible,
        int $cibleId,
        string $motif,
        ?string $detail = null,
        ?int $zoneId = null
    ): array {
        if (!$this->pdo || $auteurId <= 0 || $cibleId <= 0) {
            return ['success' => false, 'message' => "Paramètres de signalement invalides."];
        }

        $typeCible = strtolower(trim($typeCible));
        if (!in_array($typeCible, ['publication', 'commentaire', 'utilisateur'], true)) {
            $typeCible = 'publication';
        }

        $motif = trim($motif);
        if (empty($motif)) {
            return ['success' => false, 'message' => "Veuillez préciser le motif du signalement."];
        }

        $detail = !empty($detail) ? trim($detail) : null;
        $zoneId = ($zoneId !== null && $zoneId > 0) ? (int)$zoneId : null;

        // --- RÈGLE ANTI-ABUS 1 : Quota max 5 signalements par jour et par utilisateur ---
        $quotaStmt = $this->pdo->prepare("
            SELECT COUNT(*) 
            FROM signalements 
            WHERE auteur_id = :user_id 
              AND date_signalement >= CURDATE()
        ");
        $quotaStmt->execute(['user_id' => $auteurId]);
        if ((int)$quotaStmt->fetchColumn() >= 5) {
            return [
                'success' => false,
                'message' => "Limite quotidienne atteinte : vous ne pouvez pas envoyer plus de 5 signalements par jour."
            ];
        }

        // --- VÉRIFICATION DE LA CIBLE ET RÈGLE ANTI-AUTO-SIGNALEMENT ---
        $targetAuthorId = null;
        $resolvedZoneId = $zoneId;
        $isPrivateZone = false;
        $targetTitle = '';

        if ($typeCible === 'publication') {
            $stmtPub = $this->pdo->prepare("
                SELECT p.id, p.utilisateur_id, p.titre, c.zone_id, z.visibilite
                FROM publications p
                LEFT JOIN categories c ON c.id = p.categorie_id
                LEFT JOIN zones z ON z.id = c.zone_id
                WHERE p.id = :id
                LIMIT 1
            ");
            $stmtPub->execute(['id' => $cibleId]);
            $pub = $stmtPub->fetch(PDO::FETCH_ASSOC);

            if (!$pub) {
                return ['success' => false, 'message' => "La publication ciblée est introuvable."];
            }

            $targetAuthorId = (int)$pub['utilisateur_id'];
            $targetTitle = (string)($pub['titre'] ?? 'Publication');
            if (!empty($pub['zone_id'])) {
                $resolvedZoneId = (int)$pub['zone_id'];
            }
            $isPrivateZone = (($pub['visibilite'] ?? 'public') === 'prive');

            if ($targetAuthorId === $auteurId) {
                return ['success' => false, 'message' => "Vous ne pouvez pas signaler votre propre publication."];
            }
        } elseif ($typeCible === 'commentaire') {
            $stmtCom = $this->pdo->prepare("
                SELECT c.id, c.utilisateur_id, c.publication_id, cat.zone_id, z.visibilite
                FROM commentaires c
                LEFT JOIN publications p ON p.id = c.publication_id
                LEFT JOIN categories cat ON cat.id = p.categorie_id
                LEFT JOIN zones z ON z.id = cat.zone_id
                WHERE c.id = :id
                LIMIT 1
            ");
            $stmtCom->execute(['id' => $cibleId]);
            $com = $stmtCom->fetch(PDO::FETCH_ASSOC);

            if (!$com) {
                return ['success' => false, 'message' => "Le commentaire ciblé est introuvable."];
            }

            $targetAuthorId = (int)$com['utilisateur_id'];
            $targetTitle = 'Commentaire';
            if (!empty($com['zone_id'])) {
                $resolvedZoneId = (int)$com['zone_id'];
            }
            $isPrivateZone = (($com['visibilite'] ?? 'public') === 'prive');

            if ($targetAuthorId === $auteurId) {
                return ['success' => false, 'message' => "Vous ne pouvez pas signaler votre propre commentaire."];
            }
        } elseif ($typeCible === 'utilisateur') {
            $stmtUser = $this->pdo->prepare("
                SELECT id, nom, prenom, role 
                FROM utilisateurs 
                WHERE id = :id AND est_actif = 1 
                LIMIT 1
            ");
            $stmtUser->execute(['id' => $cibleId]);
            $usr = $stmtUser->fetch(PDO::FETCH_ASSOC);

            if (!$usr) {
                return ['success' => false, 'message' => "L'utilisateur ciblé est introuvable ou inactif."];
            }

            $targetAuthorId = (int)$usr['id'];
            $targetTitle = trim(($usr['prenom'] ?? '') . ' ' . ($usr['nom'] ?? 'Membre'));

            if ($targetAuthorId === $auteurId) {
                return ['success' => false, 'message' => "Vous ne pouvez pas signaler votre propre compte."];
            }

            if ($resolvedZoneId !== null) {
                $zStmt = $this->pdo->prepare("SELECT visibilite FROM zones WHERE id = :zid LIMIT 1");
                $zStmt->execute(['zid' => $resolvedZoneId]);
                $vis = $zStmt->fetchColumn();
                $isPrivateZone = ($vis === 'prive');
            }
        }

        // --- RÈGLE D'ACCÈS AUX SALONS PRIVÉS ---
        // Dans un salon privé, seuls les membres du salon peuvent signaler
        if ($resolvedZoneId !== null && $isPrivateZone) {
            $memStmt = $this->pdo->prepare("
                SELECT COUNT(*) 
                FROM appartenance_zones 
                WHERE zone_id = :zid AND utilisateur_id = :uid
            ");
            $memStmt->execute(['zid' => $resolvedZoneId, 'uid' => $auteurId]);
            if ((int)$memStmt->fetchColumn() === 0) {
                return [
                    'success' => false,
                    'message' => "Vous devez être membre de ce salon privé pour pouvoir y effectuer un signalement."
                ];
            }
        }

        // --- RÈGLE ANTI-DOUBLON : Vérifier si l'utilisateur a déjà un signalement en attente ---
        if ($this->hasUserReported($auteurId, $typeCible, $cibleId)) {
            return [
                'success' => false, 
                'message' => "Vous avez déjà un signalement en attente pour cet élément. Il est en cours d'examen."
            ];
        }

        // --- ENREGISTREMENT DU SIGNALEMENT ---
        $stmt = $this->pdo->prepare("
            INSERT INTO signalements (auteur_id, type_cible, cible_id, motif, detail, zone_id, statut, date_signalement)
            VALUES (:auteur_id, :type_cible, :cible_id, :motif, :detail, :zone_id, 'en_attente', NOW())
        ");
        $success = $stmt->execute([
            'auteur_id'  => $auteurId,
            'type_cible' => $typeCible,
            'cible_id'   => $cibleId,
            'motif'      => $motif,
            'detail'     => $detail,
            'zone_id'    => $resolvedZoneId
        ]);

        if (!$success) {
            return ['success' => false, 'message' => "Erreur lors de l'enregistrement du signalement."];
        }

        $signalementId = (int)$this->pdo->lastInsertId();

        // Marquer la cible comme signalée (est_signale = 1)
        if ($typeCible === 'publication') {
            $upPub = $this->pdo->prepare("UPDATE publications SET est_signale = 1 WHERE id = :id");
            $upPub->execute(['id' => $cibleId]);
        } elseif ($typeCible === 'commentaire') {
            $upCom = $this->pdo->prepare("UPDATE commentaires SET est_signale = 1 WHERE id = :id");
            $upCom->execute(['id' => $cibleId]);
        }

        // Compter le nombre de signalements en attente pour cette cible
        $totalReports = $this->countReportsForTarget($typeCible, $cibleId);
        $autoBlocked = false;

        // Seuil automatique : Bloquer la publication si seuil atteint
        if ($typeCible === 'publication' && $totalReports >= self::SEUIL_BLOCAGE_AUTO) {
            $autoBlocked = $this->bloquerPublicationAutomatiquement($cibleId, $totalReports);
        }

        // Notifier les administrateurs et modérateurs
        $notifMsg = $autoBlocked
            ? "🚨 Publication #{$cibleId} AUTOMATIQUEMENT BLOQUÉE ({$totalReports} signalements reçus)."
            : "⚠️ Nouveau signalement reçu sur {$typeCible} #{$cibleId} : \"{$motif}\" (Total: {$totalReports}).";

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
     *
     * @param string|null $statut Filtre statut ('en_attente', 'traite', 'rejete' ou null)
     * @param array|null $zoneIds Périmètre autorisé de zones
     * @param int|null $filterZoneId Filtre spécifique sur une zone
     * @return array
     */
    public function getAllReports(?string $statut = null, ?array $zoneIds = null, ?int $filterZoneId = null): array {
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
                s.id, s.auteur_id, s.type_cible, s.cible_id, s.motif, s.detail, s.zone_id, s.statut, 
                s.action_prise, s.traite_par, s.date_signalement, s.date_traitement,
                s.motif AS raison,
                s.cible_id AS message_id,
                s.date_signalement AS created_at,
                -- Info sur l'utilisateur qui a signalé
                u_rep.nom AS signaleur_nom, u_rep.prenom AS signaleur_prenom, u_rep.email AS signaleur_email,
                -- Modérateur ayant traité
                u_mod.nom AS moderateur_nom, u_mod.prenom AS moderateur_prenom,
                -- Auteur du contenu ciblé ou profil ciblé
                CASE 
                    WHEN s.type_cible = 'publication' THEN (SELECT p.utilisateur_id FROM publications p WHERE p.id = s.cible_id)
                    WHEN s.type_cible = 'commentaire' THEN (SELECT c.utilisateur_id FROM commentaires c WHERE c.id = s.cible_id)
                    WHEN s.type_cible = 'utilisateur' THEN s.cible_id
                    ELSE NULL
                END AS cible_auteur_id,
                CASE 
                    WHEN s.type_cible = 'publication' THEN (SELECT CONCAT(u.prenom, ' ', u.nom) FROM publications p JOIN utilisateurs u ON p.utilisateur_id = u.id WHERE p.id = s.cible_id)
                    WHEN s.type_cible = 'commentaire' THEN (SELECT CONCAT(u.prenom, ' ', u.nom) FROM commentaires c JOIN utilisateurs u ON c.utilisateur_id = u.id WHERE c.id = s.cible_id)
                    WHEN s.type_cible = 'utilisateur' THEN (SELECT CONCAT(u.prenom, ' ', u.nom) FROM utilisateurs u WHERE u.id = s.cible_id)
                    ELSE 'Inconnu'
                END AS cible_auteur_nom,
                -- Titre et contenu
                CASE 
                    WHEN s.type_cible = 'publication' THEN (SELECT p.titre FROM publications p WHERE p.id = s.cible_id)
                    WHEN s.type_cible = 'commentaire' THEN 'Commentaire'
                    WHEN s.type_cible = 'utilisateur' THEN 'Profil utilisateur'
                    ELSE 'Contenu'
                END AS cible_titre,
                CASE 
                    WHEN s.type_cible = 'publication' THEN (SELECT p.contenu FROM publications p WHERE p.id = s.cible_id)
                    WHEN s.type_cible = 'commentaire' THEN (SELECT c.contenu FROM commentaires c WHERE c.id = s.cible_id)
                    WHEN s.type_cible = 'utilisateur' THEN (SELECT CONCAT('Email : ', u.email, ' · Rôle : ', u.role) FROM utilisateurs u WHERE u.id = s.cible_id)
                    ELSE ''
                END AS cible_contenu,
                -- Statut de la publication
                CASE 
                    WHEN s.type_cible = 'publication' THEN (SELECT p.statut FROM publications p WHERE p.id = s.cible_id)
                    ELSE NULL
                END AS publication_statut,
                -- Résolution de la zone
                COALESCE(s.zone_id, target_category.zone_id) AS resolved_zone_id,
                COALESCE(explicit_zone.nom, target_zone.nom) AS zone_nom,
                -- Nombre total de signalements sur cette même cible
                (SELECT COUNT(*) FROM signalements sub WHERE sub.type_cible = s.type_cible AND sub.cible_id = s.cible_id AND sub.statut = 'en_attente') AS total_signalements_cible
            FROM signalements s
            JOIN utilisateurs u_rep ON s.auteur_id = u_rep.id
            LEFT JOIN utilisateurs u_mod ON s.traite_par = u_mod.id
            LEFT JOIN publications target_publication
              ON s.type_cible = 'publication' AND target_publication.id = s.cible_id
            LEFT JOIN commentaires target_comment
              ON s.type_cible = 'commentaire' AND target_comment.id = s.cible_id
            LEFT JOIN publications comment_publication
              ON comment_publication.id = target_comment.publication_id
            LEFT JOIN categories target_category
              ON target_category.id = COALESCE(target_publication.categorie_id, comment_publication.categorie_id)
            LEFT JOIN zones target_zone ON target_zone.id = target_category.zone_id
            LEFT JOIN zones explicit_zone ON explicit_zone.id = s.zone_id
        ";

        $conditions = [];
        $parameters = [];
        if ($statut !== null && $statut !== '' && $statut !== 'all') {
            $conditions[] = 's.statut = :statut';
            $parameters['statut'] = $statut;
        }
        if ($filterZoneId !== null && $filterZoneId > 0) {
            $conditions[] = 'COALESCE(s.zone_id, target_category.zone_id) = :filter_zone_id';
            $parameters['filter_zone_id'] = $filterZoneId;
        }
        if ($zoneIds !== null) {
            $placeholders = [];
            foreach ($zoneIds as $index => $zoneId) {
                $placeholder = 'zone_' . $index;
                $placeholders[] = ':' . $placeholder;
                $parameters[$placeholder] = $zoneId;
            }
            $conditions[] = 'COALESCE(s.zone_id, target_category.zone_id) IN (' . implode(', ', $placeholders) . ')';
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
