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
    require_once ROOT_PATH . '/models/Attachment.php';
    require_once ROOT_PATH . '/models/Notification.php';
    require_once ROOT_PATH . '/models/RoleAccess.php';
} else {
    require_once dirname(__DIR__) . '/config/database.php';
    require_once dirname(__DIR__) . '/models/Attachment.php';
    require_once dirname(__DIR__) . '/models/Notification.php';
    require_once dirname(__DIR__) . '/models/RoleAccess.php';
}

if (!function_exists('url')) {
    function url(string $path = ''): string {
        global $baseUrl;
        $base = $baseUrl ?? '';
        $trimmed = ltrim($path, '/');
        return $base ? $base . '/' . $trimmed : '/' . $trimmed;
    }
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
     * DÉCISION MODÉRATEUR : Avertir l'auteur de la publication
     *
     * @param int $reportId ID du signalement
     * @param int $adminId ID de l'administrateur
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

        // 2. Mettre à jour le signalement comme traité
        $stmt = $this->pdo->prepare("
            UPDATE signalements 
            SET statut = 'traite', traite_par = :admin_id, date_traitement = NOW() 
            WHERE id = :id
        ");

        return $stmt->execute([
            'admin_id' => $adminId,
            'id'       => $reportId
        ]);
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

        // 3. Clôturer tous les signalements associés à cette même cible
        $closeAll = $this->pdo->prepare("
            UPDATE signalements 
            SET statut = 'traite', traite_par = :admin_id, date_traitement = NOW() 
            WHERE type_cible = :type_cible AND cible_id = :cible_id
        ");

        return $closeAll->execute([
            'admin_id'   => $adminId,
            'type_cible' => $typeCible,
            'cible_id'   => $cibleId
        ]);
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
                "🔒 Votre publication \"{$report['cible_titre']}\" a été verrouillée par un administrateur.",
                url('salon')
            );
        }

        // Marquer le signalement comme traité
        $stmt = $this->pdo->prepare("
            UPDATE signalements 
            SET statut = 'traite', traite_par = :admin_id, date_traitement = NOW() 
            WHERE id = :id
        ");

        return $stmt->execute([
            'admin_id' => $adminId,
            'id'       => $reportId
        ]);
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

        // 1. Mettre le signalement en rejeté
        $stmt = $this->pdo->prepare("
            UPDATE signalements 
            SET statut = 'rejete', traite_par = :admin_id, date_traitement = NOW() 
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
                    ELSE NULL
                END AS cible_auteur_id,
                CASE 
                    WHEN s.type_cible = 'publication' THEN (SELECT p.titre FROM publications p WHERE p.id = s.cible_id)
                    ELSE 'Commentaire'
                END AS cible_titre
            FROM signalements s 
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
