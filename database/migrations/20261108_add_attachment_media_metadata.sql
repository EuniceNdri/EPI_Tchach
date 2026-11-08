-- Ajoute une classification et un ordre d'affichage aux pieces jointes existantes.

SET @media_type_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'pieces_jointes'
      AND COLUMN_NAME = 'type_media'
);
SET @migration_sql = IF(
    @media_type_exists = 0,
    'ALTER TABLE pieces_jointes ADD COLUMN type_media ENUM(''image'',''document'') NOT NULL DEFAULT ''document'' AFTER type_mime',
    'DO 0'
);
PREPARE migration_stmt FROM @migration_sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @display_order_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'pieces_jointes'
      AND COLUMN_NAME = 'ordre_affichage'
);
SET @migration_sql = IF(
    @display_order_exists = 0,
    'ALTER TABLE pieces_jointes ADD COLUMN ordre_affichage INT UNSIGNED NOT NULL DEFAULT 0 AFTER taille',
    'DO 0'
);
PREPARE migration_stmt FROM @migration_sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

UPDATE pieces_jointes
SET type_media = CASE WHEN type_mime LIKE 'image/%' THEN 'image' ELSE 'document' END;

SET @media_order_index_exists = (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'pieces_jointes'
      AND INDEX_NAME = 'idx_pieces_cible_ordre'
);
SET @migration_sql = IF(
    @media_order_index_exists = 0,
    'CREATE INDEX idx_pieces_cible_ordre ON pieces_jointes (type_cible, cible_id, ordre_affichage, id)',
    'DO 0'
);
PREPARE migration_stmt FROM @migration_sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;
