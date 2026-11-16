-- Gestion des visuels et contenus de la landing page depuis l'administration.

SET @filiere_image_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'filieres'
      AND COLUMN_NAME = 'image_url'
);
SET @migration_sql = IF(
    @filiere_image_exists = 0,
    'ALTER TABLE filieres ADD COLUMN image_url VARCHAR(255) DEFAULT NULL AFTER description',
    'DO 0'
);
PREPARE migration_stmt FROM @migration_sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @filiere_slogan_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'filieres'
      AND COLUMN_NAME = 'slogan'
);
SET @migration_sql = IF(
    @filiere_slogan_exists = 0,
    'ALTER TABLE filieres ADD COLUMN slogan VARCHAR(180) DEFAULT NULL AFTER image_url',
    'DO 0'
);
PREPARE migration_stmt FROM @migration_sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

CREATE TABLE IF NOT EXISTS landing_settings (
    config_key   VARCHAR(64) PRIMARY KEY,
    config_value LONGTEXT NOT NULL,
    updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
