<?php
/**
 * =============================================================
 * models/ModerationService.php – Service Complet de Modération
 * =============================================================
 * Gère :
 * 1. La détection et le traitement automatique des mots interdits
 * 2. Les décisions prises par les administrateurs et délégués de classe :
 *    - Avertir l'auteur d'un contenu signalé (avec notification)
 *    - Supprimer la publication / commentaire (avec notification)
 *    - Bloquer / verrouiller une publication
 *    - Rejeter un signalement sans suite
 * =============================================================
 */

if (defined('ROOT_PATH')) {
    require_once ROOT_PATH . '/config/database.php';
    if (!function_exists('url')) {
        @require_once ROOT_PATH . '/app/Bootstrap.php';
    }
    require_once ROOT_PATH . '/app/Models/Publications/Attachment.php';
    require_once ROOT_PATH . '/app/Models/Notifications/Notification.php';
    require_once ROOT_PATH . '/app/Models/Moderation/RoleAccess.php';
} else {
    require_once dirname(__DIR__, 2) . '/config/database.php';
    if (!function_exists('url')) {
        @require_once dirname(__DIR__) . '/Bootstrap.php';
    }
    require_once __DIR__ . '/../Models/Publications/Attachment.php';
    require_once __DIR__ . '/../Models/Notifications/Notification.php';
    require_once __DIR__ . '/../Models/Moderation/RoleAccess.php';
}

class ModerationService {
    private ?PDO $pdo;
    private Notification $notificationModel;

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
     * Analyse un contenu et retourne le résultat de l'analyse
     * Retourne : ['status' => 'ok'|'bloquer'|'signaler', 'content' => string]
     */
    public function analyser(string $contenu): array {
        $motsInterdits = $this->getMotsInterdits();

        $resultat = $contenu;
        $actionFinale = 'ok';

        foreach ($motsInterdits as $item) {
            $mot = $item['mot'];
            if (mb_stripos($resultat, $mot) !== false) {
                switch ($item['action']) {
                    case 'bloquer':
                        return ['status' => 'bloquer', 'content' => $resultat];
                    case 'signaler':
                        $actionFinale = 'signaler';
                        break;
                    case 'remplacer':
                        $resultat = str_ireplace($mot, str_repeat('*', mb_strlen($mot)), $resultat);
                        break;
                }
            }
        }

        return ['status' => $actionFinale, 'content' => $resultat];
    }

    /**
     * Récupère la liste des mots interdits actifs
     */
    private function getMotsInterdits(): array {
        if (!$this->pdo) {
            return [];
        }
        $stmt = $this->pdo->query("SELECT mot, action FROM mots_interdits WHERE actif = 1");
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * Crée un signalement automatique par le système
     */
    public function creerSignalementAutomatique(string $typeCible, int $cibleId): bool {
        if (!$this->pdo) {
            return false;
        }

        // Récupérer un compte administrateur ou un délégué de classe valide
        $adminStmt = $this->pdo->query("SELECT id FROM utilisateurs WHERE role = 'administrateur' LIMIT 1");
        $adminId = $adminStmt ? (int)$adminStmt->fetchColumn() : 0;

        $stmt = $this->pdo->prepare("
            INSERT INTO signalements (auteur_id, type_cible, cible_id, motif, statut, date_signalement)
            VALUES (:auteur_id, :type_cible, :cible_id, 'Détection automatique de contenu suspect par le filtre système', 'en_attente', NOW())
        ");

        return $stmt->execute([
            'auteur_id'  => $adminId > 0 ? $adminId : 1,
            'type_cible' => $typeCible,
            'cible_id'   => $cibleId
        ]);
    }

    /**
     * DÉCISION MODÉRATEUR : Avertir l'auteur de la publication ou du compte
     *
     * @param int $reportId ID du signalement
     * @param int $adminId ID du modérateur / administrateur
     * @param string $messageAvertissement Motif de l'avertissement
     * @return bool
     */
    public function avertirAuteur(int $reportId, int $adminId, string $messageAvertissement): bool {
        if (!$this->pdo || $reportId <= 0) {
            return false;
        }

        $report = $this->getReportDetails($reportId);
        if (!$report || empty($report['cible_auteur_id']) || !$this->canActOnReport($reportId, $adminId)) {
            return false;
        }

        $authorId = (int)$report['cible_auteur_id'];
        $titre = $report['cible_titre'] ?? 'votre contenu';

        // 1. Envoyer la notification d'avertissement à l'auteur
        $this->notificationModel->notifyAvertissement($authorId, $messageAvertissement, $titre);

        // 2. Mettre à jour le signalement comme traité avec action_prise = 'avertissement'
        $stmt = $this->pdo->prepare("
            UPDATE signalements 
            SET statut = 'traite', action_prise = 'avertissement', traite_par = :admin_id, date_traitement = NOW() 
            WHERE id = :id
        ");
        $success = $stmt->execute([
            'admin_id' => $adminId,
            'id'       => $reportId
        ]);

        // 3. Notifier l'utilisateur ayant signalé
        $reporterId = (int)($report['auteur_id'] ?? 0);
        if ($reporterId > 0 && $reporterId !== $adminId) {
            $this->notificationModel->create(
                $reporterId,
                'moderation',
                "Votre signalement concernant \"{$titre}\" a été traité : un avertissement a été adressé par l'équipe de modération.",
                url('profile')
            );
        }

        return $success;
    }

    /**
     * DÉCISION MODÉRATEUR : Supprimer la publication ou le commentaire
     *
     * @param int $reportId ID du signalement
     * @param int $adminId ID de l'administrateur
     * @param string $motif Motif de la suppression communiqué à l'auteur
     * @return bool
     */
    public function supprimerContenu(int $reportId, int $adminId, string $motif = ''): bool {
        if (!$this->pdo || $reportId <= 0) {
            return false;
        }

        $report = $this->getReportDetails($reportId);
        if (!$report || !$this->canActOnReport($reportId, $adminId)) {
            return false;
        }

        $typeCible = $report['type_cible'];
        if ($typeCible === 'utilisateur') {
            return false; // On ne peut pas supprimer physiquement un profil via cette méthode (voir suspension)
        }

        $cibleId = (int)$report['cible_id'];
        $authorId = (int)($report['cible_auteur_id'] ?? 0);
        $titre = $report['cible_titre'] ?? 'Votre publication';

        // 1. Prévenir l'auteur par notification
        if ($authorId > 0) {
            $this->notificationModel->notifySuppression($authorId, $titre, $motif);
        }

        // 2. Supprimer physiquement la cible
        $attachments = Attachment::findForTarget($this->pdo, $typeCible, $cibleId);
        if ($typeCible === 'publication') {
            $del = $this->pdo->prepare("DELETE FROM publications WHERE id = :id");
            $del->execute(['id' => $cibleId]);
        } elseif ($typeCible === 'commentaire') {
            $del = $this->pdo->prepare("DELETE FROM commentaires WHERE id = :id");
            $del->execute(['id' => $cibleId]);
        }
        Attachment::removeStoredFiles($this->pdo, $attachments);

        // 3. Clôturer tous les signalements associés à cette même cible avec action_prise = 'suppression_contenu'
        $closeAll = $this->pdo->prepare("
            UPDATE signalements 
            SET statut = 'traite', action_prise = 'suppression_contenu', traite_par = :admin_id, date_traitement = NOW() 
            WHERE type_cible = :type_cible AND cible_id = :cible_id
        ");
        $success = $closeAll->execute([
            'admin_id'   => $adminId,
            'type_cible' => $typeCible,
            'cible_id'   => $cibleId
        ]);

        // 4. Notifier l'auteur du signalement
        $reporterId = (int)($report['auteur_id'] ?? 0);
        if ($reporterId > 0 && $reporterId !== $adminId) {
            $this->notificationModel->create(
                $reporterId,
                'moderation',
                "Votre signalement concernant \"{$titre}\" a été traité : le contenu a été supprimé par l'équipe de modération.",
                url('profile')
            );
        }

        return $success;
    }

    /**
     * DÉCISION MODÉRATEUR : Bloquer / verrouiller la publication
     */
    public function bloquerPublication(int $reportId, int $adminId): bool {
        if (!$this->pdo || $reportId <= 0) {
            return false;
        }

        $report = $this->getReportDetails($reportId);
        if (!$report || $report['type_cible'] !== 'publication' || !$this->canActOnReport($reportId, $adminId)) {
            return false;
        }

        $pubId = (int)$report['cible_id'];
        $authorId = (int)($report['cible_auteur_id'] ?? 0);
        $titre = $report['cible_titre'] ?? 'Votre publication';

        // Verrouiller la publication
        $lock = $this->pdo->prepare("
            UPDATE publications 
            SET statut = 'ferme', commentaires_fermes = 1 
            WHERE id = :id
        ");
        $lock->execute(['id' => $pubId]);

        if ($authorId > 0) {
            $this->notificationModel->create(
                $authorId,
                'moderation',
                "🔒 Votre publication \"{$titre}\" a été verrouillée par un modérateur.",
                url('salon')
            );
        }

        // Marquer le signalement comme traité
        $stmt = $this->pdo->prepare("
            UPDATE signalements 
            SET statut = 'traite', action_prise = 'avertissement', traite_par = :admin_id, date_traitement = NOW() 
            WHERE id = :id
        ");
        $success = $stmt->execute([
            'admin_id' => $adminId,
            'id'       => $reportId
        ]);

        // Notifier le signaleur
        $reporterId = (int)($report['auteur_id'] ?? 0);
        if ($reporterId > 0 && $reporterId !== $adminId) {
            $this->notificationModel->create(
                $reporterId,
                'moderation',
                "Votre signalement concernant la publication \"{$titre}\" a été traité : elle a été verrouillée.",
                url('profile')
            );
        }

        return $success;
    }

    /**
     * DÉCISION MODÉRATEUR : Expulser l'utilisateur du salon (zone)
     *
     * @param int $reportId ID du signalement
     * @param int $adminId ID du modérateur
     * @param string $motif Motif de l'expulsion
     * @return bool
     */
    public function expulserDuSalon(int $reportId, int $adminId, string $motif = ''): bool {
        if (!$this->pdo || $reportId <= 0) {
            return false;
        }

        $report = $this->getReportDetails($reportId);
        if (!$report || !$this->canActOnReport($reportId, $adminId)) {
            return false;
        }

        $targetUserId = (int)($report['cible_auteur_id'] ?? 0);
        $zoneId = (int)($report['resolved_zone_id'] ?? 0);
        $zoneNom = !empty($report['zone_nom']) ? $report['zone_nom'] : 'ce salon';

        if ($targetUserId <= 0 || $zoneId <= 0) {
            return false;
        }

        // On ne peut pas expulser un administrateur général de la plateforme
        if (RoleAccess::isAdministrator($this->pdo, $targetUserId)) {
            return false;
        }

        // Supprimer l'appartenance à la zone
        $del = $this->pdo->prepare("
            DELETE FROM appartenance_zones 
            WHERE zone_id = :zone_id AND utilisateur_id = :user_id
        ");
        $del->execute([
            'zone_id' => $zoneId,
            'user_id' => $targetUserId
        ]);

        // Mettre à jour le signalement
        $stmt = $this->pdo->prepare("
            UPDATE signalements 
            SET statut = 'traite', action_prise = 'expulsion_salon', traite_par = :admin_id, date_traitement = NOW() 
            WHERE id = :id
        ");
        $success = $stmt->execute([
            'admin_id' => $adminId,
            'id'       => $reportId
        ]);

        // Notifier l'utilisateur expulsé
        $notifMsg = "Vous avez été retiré du salon \"{$zoneNom}\" par l'équipe de modération.";
        if (!empty($motif)) {
            $notifMsg .= " Motif : " . $motif;
        }
        $this->notificationModel->create(
            $targetUserId,
            'moderation',
            $notifMsg,
            url('salon')
        );

        // Notifier le signaleur
        $reporterId = (int)($report['auteur_id'] ?? 0);
        if ($reporterId > 0 && $reporterId !== $adminId) {
            $this->notificationModel->create(
                $reporterId,
                'moderation',
                "Votre signalement a été traité : l'utilisateur a été retiré du salon \"{$zoneNom}\".",
                url('profile')
            );
        }

        return $success;
    }

    /**
     * DÉCISION MODÉRATEUR : Suspendre le compte de l'utilisateur (RÉSERVÉ AUX ADMINISTRATEURS GÉNÉRAUX)
     *
     * @param int $reportId ID du signalement
     * @param int $adminId ID de l'administrateur exécutant l'action
     * @param string $motif Motif de la suspension
     * @return bool
     */
    public function suspendreCompte(int $reportId, int $adminId, string $motif = ''): bool {
        if (!$this->pdo || $reportId <= 0) {
            return false;
        }

        // RÈGLE STRICTE : Seul un administrateur général peut suspendre un compte
        if (!RoleAccess::isAdministrator($this->pdo, $adminId)) {
            return false;
        }

        $report = $this->getReportDetails($reportId);
        if (!$report) {
            return false;
        }

        $targetUserId = (int)($report['cible_auteur_id'] ?? 0);
        if ($targetUserId <= 0) {
            return false;
        }

        // On ne peut pas suspendre un autre administrateur
        if (RoleAccess::isAdministrator($this->pdo, $targetUserId)) {
            return false;
        }

        // Désactiver le compte
        $suspendStmt = $this->pdo->prepare("UPDATE utilisateurs SET est_actif = 0 WHERE id = :id");
        $suspendStmt->execute(['id' => $targetUserId]);

        // Mettre à jour le signalement
        $stmt = $this->pdo->prepare("
            UPDATE signalements 
            SET statut = 'traite', action_prise = 'suspension_compte', traite_par = :admin_id, date_traitement = NOW() 
            WHERE id = :id
        ");
        $success = $stmt->execute([
            'admin_id' => $adminId,
            'id'       => $reportId
        ]);

        // Notifier le signaleur
        $reporterId = (int)($report['auteur_id'] ?? 0);
        if ($reporterId > 0 && $reporterId !== $adminId) {
            $this->notificationModel->create(
                $reporterId,
                'moderation',
                "Votre signalement a été traité : le compte de l'utilisateur a été suspendu par l'administration générale.",
                url('profile')
            );
        }

        return $success;
    }

    /**
     * DÉCISION MODÉRATEUR : Rejeter le signalement (classer sans suite car contenu conforme)
     */
    public function rejeterSignalement(int $reportId, int $adminId): bool {
        if (!$this->pdo || $reportId <= 0) {
            return false;
        }

        $report = $this->getReportDetails($reportId);
        if (!$report || !$this->canActOnReport($reportId, $adminId)) {
            return false;
        }

        // 1. Mettre le signalement en rejeté avec action_prise = 'aucune'
        $stmt = $this->pdo->prepare("
            UPDATE signalements 
            SET statut = 'rejete', action_prise = 'aucune', traite_par = :admin_id, date_traitement = NOW() 
            WHERE id = :id
        ");
        $success = $stmt->execute([
            'admin_id' => $adminId,
            'id'       => $reportId
        ]);

        // 2. Si plus aucun signalement en attente sur cette cible, lever le drapeau est_signale
        $check = $this->pdo->prepare("
            SELECT COUNT(*) 
            FROM signalements 
            WHERE type_cible = :type_cible 
              AND cible_id = :cible_id 
              AND statut = 'en_attente'
        ");
        $check->execute([
            'type_cible' => $report['type_cible'],
            'cible_id'   => $report['cible_id']
        ]);

        if ((int)$check->fetchColumn() === 0) {
            if ($report['type_cible'] === 'publication') {
                $unflag = $this->pdo->prepare("UPDATE publications SET est_signale = 0 WHERE id = :id");
                $unflag->execute(['id' => $report['cible_id']]);
            } elseif ($report['type_cible'] === 'commentaire') {
                $unflag = $this->pdo->prepare("UPDATE commentaires SET est_signale = 0 WHERE id = :id");
                $unflag->execute(['id' => $report['cible_id']]);
            }
        }

        // 3. Notifier l'auteur du signalement du classement sans suite
        $reporterId = (int)($report['auteur_id'] ?? 0);
        $titre = $report['cible_titre'] ?? 'votre signalement';
        if ($reporterId > 0 && $reporterId !== $adminId) {
            $this->notificationModel->create(
                $reporterId,
                'moderation',
                "Votre signalement concernant \"{$titre}\" a été examiné et classé sans suite par l'équipe de modération.",
                url('profile')
            );
        }

        return $success;
    }

    /**
     * Récupère les détails enrichis d'un signalement
     */
    public function getReportDetails(int $reportId): ?array {
        if (!$this->pdo || $reportId <= 0) {
            return null;
        }

        $stmt = $this->pdo->prepare("
            SELECT 
                s.*,
                CASE 
                    WHEN s.type_cible = 'publication' THEN (SELECT p.utilisateur_id FROM publications p WHERE p.id = s.cible_id)
                    WHEN s.type_cible = 'commentaire' THEN (SELECT c.utilisateur_id FROM commentaires c WHERE c.id = s.cible_id)
                    WHEN s.type_cible = 'utilisateur' THEN s.cible_id
                    ELSE NULL
                END AS cible_auteur_id,
                CASE 
                    WHEN s.type_cible = 'publication' THEN (SELECT p.titre FROM publications p WHERE p.id = s.cible_id)
                    WHEN s.type_cible = 'commentaire' THEN 'Commentaire'
                    WHEN s.type_cible = 'utilisateur' THEN (SELECT CONCAT(u.prenom, ' ', u.nom) FROM utilisateurs u WHERE u.id = s.cible_id)
                    ELSE 'Contenu'
                END AS cible_titre,
                COALESCE(s.zone_id, target_category.zone_id) AS resolved_zone_id,
                COALESCE(explicit_zone.nom, target_zone.nom) AS zone_nom
            FROM signalements s 
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
            WHERE s.id = :id 
            LIMIT 1
        ");
        $stmt->execute(['id' => $reportId]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        return $res ?: null;
    }

    private function canActOnReport(int $reportId, int $actorId): bool
    {
        return $this->pdo instanceof PDO
            && RoleAccess::canModerateReport($this->pdo, $actorId, $reportId);
    }
}
