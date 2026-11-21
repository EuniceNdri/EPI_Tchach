-- ============================================================
-- Migration : Signalements (Repérage + Exécution)
-- Date : 2026-11-21
-- ============================================================

-- 1. Élargir type_cible pour inclure 'utilisateur'
ALTER TABLE signalements
  MODIFY COLUMN type_cible ENUM('publication','commentaire','utilisateur') NOT NULL;

-- 2. Ajouter la colonne zone_id si elle n'existe pas
SET @col_zone = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'signalements' AND COLUMN_NAME = 'zone_id'
);
SET @sql_zone = IF(@col_zone = 0, 'ALTER TABLE signalements ADD COLUMN zone_id INT UNSIGNED DEFAULT NULL AFTER cible_id', 'SELECT 1');
PREPARE stmt_z FROM @sql_zone;
EXECUTE stmt_z;
DEALLOCATE PREPARE stmt_z;

-- 3. Ajouter la colonne detail si elle n'existe pas
SET @col_detail = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'signalements' AND COLUMN_NAME = 'detail'
);
SET @sql_detail = IF(@col_detail = 0, 'ALTER TABLE signalements ADD COLUMN detail TEXT DEFAULT NULL AFTER motif', 'SELECT 1');
PREPARE stmt_d FROM @sql_detail;
EXECUTE stmt_d;
DEALLOCATE PREPARE stmt_d;

-- 4. Ajouter la colonne action_prise si elle n'existe pas
SET @col_action = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'signalements' AND COLUMN_NAME = 'action_prise'
);
SET @sql_action = IF(@col_action = 0, 
    'ALTER TABLE signalements ADD COLUMN action_prise ENUM(\'aucune\',\'avertissement\',\'suppression_contenu\',\'expulsion_salon\',\'suspension_compte\') DEFAULT NULL AFTER statut', 
    'SELECT 1'
);
PREPARE stmt_a FROM @sql_action;
EXECUTE stmt_a;
DEALLOCATE PREPARE stmt_a;

-- 5. Clé étrangère vers zones si non existante
SET @fk_zone = (
    SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'signalements' AND CONSTRAINT_NAME = 'fk_signalement_zone'
);
SET @sql_fkz = IF(@fk_zone = 0, 'ALTER TABLE signalements ADD CONSTRAINT fk_signalement_zone FOREIGN KEY (zone_id) REFERENCES zones(id) ON DELETE SET NULL', 'SELECT 1');
PREPARE stmt_fkz FROM @sql_fkz;
EXECUTE stmt_fkz;
DEALLOCATE PREPARE stmt_fkz;
