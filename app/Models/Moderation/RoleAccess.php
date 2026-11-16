<?php

class RoleAccess
{
    public static function isAdministrator(PDO $pdo, int $userId): bool
    {
        return in_array(self::accountRole($pdo, $userId), ['administrateur', 'admin'], true);
    }

    public static function isGlobalDelegate(PDO $pdo, int $userId): bool
    {
        if ($userId <= 0) {
            return false;
        }

        $statement = $pdo->prepare(
            "SELECT 1
             FROM appartenance_zones membership
             JOIN zones zone ON zone.id = membership.zone_id
             WHERE membership.utilisateur_id = :user_id
               AND membership.role_dans_zone = 'delegue_general'
               AND zone.type_zone = 'delegation'
               AND zone.est_active = 1
               AND EXISTS (
                   SELECT 1 FROM utilisateurs account
                   WHERE account.id = membership.utilisateur_id AND account.role = 'etudiant'
               )
             LIMIT 1"
        );
        $statement->execute(['user_id' => $userId]);

        return (bool)$statement->fetchColumn();
    }

    public static function isClassDelegate(PDO $pdo, int $userId): bool
    {
        return self::classDelegateZoneIds($pdo, $userId) !== [];
    }

    /**
     * @return int[]
     */
    public static function classDelegateZoneIds(PDO $pdo, int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }

        $statement = $pdo->prepare(
            "SELECT membership.zone_id
             FROM appartenance_zones membership
             JOIN zones zone ON zone.id = membership.zone_id
             WHERE membership.utilisateur_id = :user_id
               AND membership.role_dans_zone = 'delegue_de_classe'
               AND zone.type_zone = 'filiere'
               AND zone.est_active = 1
               AND EXISTS (
                   SELECT 1 FROM utilisateurs account
                   WHERE account.id = membership.utilisateur_id AND account.role = 'etudiant'
               )"
        );
        $statement->execute(['user_id' => $userId]);

        return array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));
    }

    public static function canViewAllZones(PDO $pdo, int $userId): bool
    {
        return self::isAdministrator($pdo, $userId) || self::isGlobalDelegate($pdo, $userId);
    }

    public static function canPublishAnnouncement(PDO $pdo, int $userId, int $categoryId): bool
    {
        if ($userId <= 0 || $categoryId <= 0) {
            return false;
        }
        if (self::isAdministrator($pdo, $userId)) {
            return true;
        }

        $statement = $pdo->prepare(
            "SELECT zone.id, zone.type_zone
             FROM categories category
             JOIN zones zone ON zone.id = category.zone_id
             WHERE category.id = :category_id
               AND category.est_active = 1
               AND zone.est_active = 1
             LIMIT 1"
        );
        $statement->execute(['category_id' => $categoryId]);
        $zone = $statement->fetch(PDO::FETCH_ASSOC);
        if (!$zone) {
            return false;
        }

        if ($zone['type_zone'] === 'generale' && self::isGlobalDelegate($pdo, $userId)) {
            return true;
        }

        return $zone['type_zone'] === 'filiere'
            && in_array((int)$zone['id'], self::classDelegateZoneIds($pdo, $userId), true);
    }

    public static function canModerateReport(PDO $pdo, int $userId, int $reportId): bool
    {
        if ($userId <= 0 || $reportId <= 0) {
            return false;
        }
        if (self::isAdministrator($pdo, $userId)) {
            return true;
        }

        $statement = $pdo->prepare(
            "SELECT zone.id
             FROM signalements report
             LEFT JOIN publications publication
               ON report.type_cible = 'publication' AND publication.id = report.cible_id
             LEFT JOIN commentaires comment
               ON report.type_cible = 'commentaire' AND comment.id = report.cible_id
             LEFT JOIN publications comment_publication
               ON comment_publication.id = comment.publication_id
             JOIN categories category
               ON category.id = COALESCE(publication.categorie_id, comment_publication.categorie_id)
             JOIN zones zone ON zone.id = category.zone_id
             JOIN appartenance_zones membership
               ON membership.zone_id = zone.id
              AND membership.utilisateur_id = :user_id
              AND membership.role_dans_zone = 'delegue_de_classe'
             JOIN utilisateurs account
               ON account.id = membership.utilisateur_id AND account.role = 'etudiant'
             WHERE report.id = :report_id
               AND zone.type_zone = 'filiere'
               AND zone.est_active = 1
             LIMIT 1"
        );
        $statement->execute(['user_id' => $userId, 'report_id' => $reportId]);

        return (bool)$statement->fetchColumn();
    }

    private static function accountRole(PDO $pdo, int $userId): string
    {
        if ($userId <= 0) {
            return '';
        }

        $statement = $pdo->prepare('SELECT role FROM utilisateurs WHERE id = :user_id LIMIT 1');
        $statement->execute(['user_id' => $userId]);
        return strtolower(trim((string)$statement->fetchColumn()));
    }
}
