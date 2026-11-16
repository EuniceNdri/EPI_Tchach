-- Suivi de presence et favoris personnels du salon.

SET @last_activity_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'utilisateurs'
      AND COLUMN_NAME = 'derniere_activite'
);
SET @migration_sql = IF(
    @last_activity_exists = 0,
    'ALTER TABLE utilisateurs ADD COLUMN derniere_activite DATETIME DEFAULT NULL AFTER derniere_connexion',
    'DO 0'
);
PREPARE migration_stmt FROM @migration_sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @last_activity_index_exists = (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'utilisateurs'
      AND INDEX_NAME = 'idx_utilisateurs_derniere_activite'
);
SET @migration_sql = IF(
    @last_activity_index_exists = 0,
    'CREATE INDEX idx_utilisateurs_derniere_activite ON utilisateurs (est_actif, derniere_activite)',
    'DO 0'
);
PREPARE migration_stmt FROM @migration_sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

CREATE TABLE IF NOT EXISTS publications_sauvegardees (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id  INT UNSIGNED NOT NULL,
    publication_id  INT UNSIGNED NOT NULL,
    date_sauvegarde DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_publication_sauvegardee (utilisateur_id, publication_id),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (publication_id) REFERENCES publications(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pieces_jointes_sauvegardees (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id  INT UNSIGNED NOT NULL,
    piece_jointe_id INT UNSIGNED NOT NULL,
    date_sauvegarde DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_piece_jointe_sauvegardee (utilisateur_id, piece_jointe_id),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (piece_jointe_id) REFERENCES pieces_jointes(id) ON DELETE CASCADE
) ENGINE=InnoDB;
