-- Migration non destructive des evolutions du schema forum.
-- A executer apres 20261102_add_filieres.sql sur une base existante.

SET @zones_est_active_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'zones' AND COLUMN_NAME = 'est_active'
);
SET @migration_sql = IF(
    @zones_est_active_exists = 0,
    'ALTER TABLE zones ADD COLUMN est_active BOOLEAN NOT NULL DEFAULT TRUE AFTER createur_id',
    'DO 0'
);
PREPARE migration_stmt FROM @migration_sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @categories_est_active_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'categories' AND COLUMN_NAME = 'est_active'
);
SET @migration_sql = IF(
    @categories_est_active_exists = 0,
    'ALTER TABLE categories ADD COLUMN est_active BOOLEAN NOT NULL DEFAULT TRUE AFTER ordre',
    'DO 0'
);
PREPARE migration_stmt FROM @migration_sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @categories_unique_slug_exists = (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'categories' AND INDEX_NAME = 'unique_slug_zone'
);
SET @migration_sql = IF(
    @categories_unique_slug_exists = 0,
    'ALTER TABLE categories ADD UNIQUE KEY unique_slug_zone (zone_id, slug)',
    'DO 0'
);
PREPARE migration_stmt FROM @migration_sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @publications_est_signale_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'publications' AND COLUMN_NAME = 'est_signale'
);
SET @migration_sql = IF(
    @publications_est_signale_exists = 0,
    'ALTER TABLE publications ADD COLUMN est_signale BOOLEAN NOT NULL DEFAULT FALSE AFTER commentaires_fermes',
    'DO 0'
);
PREPARE migration_stmt FROM @migration_sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @commentaires_est_signale_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'commentaires' AND COLUMN_NAME = 'est_signale'
);
SET @migration_sql = IF(
    @commentaires_est_signale_exists = 0,
    'ALTER TABLE commentaires ADD COLUMN est_signale BOOLEAN NOT NULL DEFAULT FALSE AFTER est_epingle',
    'DO 0'
);
PREPARE migration_stmt FROM @migration_sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @commentaires_nb_likes_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'commentaires' AND COLUMN_NAME = 'nb_likes'
);
SET @migration_sql = IF(
    @commentaires_nb_likes_exists = 0,
    'ALTER TABLE commentaires ADD COLUMN nb_likes INT UNSIGNED NOT NULL DEFAULT 0 AFTER est_signale',
    'DO 0'
);
PREPARE migration_stmt FROM @migration_sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @signalements_traite_par_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'signalements' AND COLUMN_NAME = 'traite_par'
);
SET @migration_sql = IF(
    @signalements_traite_par_exists = 0,
    'ALTER TABLE signalements ADD COLUMN traite_par INT UNSIGNED DEFAULT NULL AFTER statut',
    'DO 0'
);
PREPARE migration_stmt FROM @migration_sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @signalements_traite_par_fk_exists = (
    SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'signalements'
      AND CONSTRAINT_NAME = 'fk_signalements_traite_par'
);
SET @migration_sql = IF(
    @signalements_traite_par_fk_exists = 0,
    'ALTER TABLE signalements ADD CONSTRAINT fk_signalements_traite_par FOREIGN KEY (traite_par) REFERENCES utilisateurs(id) ON DELETE SET NULL',
    'DO 0'
);
PREPARE migration_stmt FROM @migration_sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

CREATE TABLE IF NOT EXISTS sondage_options (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    publication_id  INT UNSIGNED NOT NULL,
    texte           VARCHAR(255) NOT NULL,
    ordre           INT NOT NULL DEFAULT 0,
    nb_votes        INT UNSIGNED NOT NULL DEFAULT 0,
    CONSTRAINT fk_sondage_options_publication
        FOREIGN KEY (publication_id) REFERENCES publications(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS sondage_votes (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    option_id       INT UNSIGNED NOT NULL,
    utilisateur_id  INT UNSIGNED NOT NULL,
    date_vote       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_vote_user_publication (option_id, utilisateur_id),
    CONSTRAINT fk_sondage_votes_option
        FOREIGN KEY (option_id) REFERENCES sondage_options(id) ON DELETE CASCADE,
    CONSTRAINT fk_sondage_votes_utilisateur
        FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS mots_interdits (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mot         VARCHAR(100) NOT NULL,
    type_mot    ENUM('interdit','suspect','insulte','spam') NOT NULL DEFAULT 'interdit',
    action      ENUM('bloquer','signaler','remplacer') NOT NULL DEFAULT 'signaler',
    actif       BOOLEAN NOT NULL DEFAULT TRUE,
    date_ajout  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

SET @index_exists = (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'publications'
      AND SEQ_IN_INDEX = 1 AND COLUMN_NAME = 'type_publication'
);
SET @migration_sql = IF(@index_exists = 0, 'CREATE INDEX idx_pub_type ON publications(type_publication)', 'DO 0');
PREPARE migration_stmt FROM @migration_sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @index_exists = (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'publications'
      AND SEQ_IN_INDEX = 1 AND COLUMN_NAME = 'statut'
);
SET @migration_sql = IF(@index_exists = 0, 'CREATE INDEX idx_pub_statut ON publications(statut)', 'DO 0');
PREPARE migration_stmt FROM @migration_sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @index_exists = (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'commentaires'
      AND SEQ_IN_INDEX = 1 AND COLUMN_NAME = 'nb_likes'
);
SET @migration_sql = IF(@index_exists = 0, 'CREATE INDEX idx_com_nb_likes ON commentaires(nb_likes)', 'DO 0');
PREPARE migration_stmt FROM @migration_sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @index_exists = (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'signalements'
      AND SEQ_IN_INDEX = 1 AND COLUMN_NAME = 'statut'
);
SET @migration_sql = IF(@index_exists = 0, 'CREATE INDEX idx_signalements_statut ON signalements(statut)', 'DO 0');
PREPARE migration_stmt FROM @migration_sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @index_exists = (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'notifications'
      AND SEQ_IN_INDEX = 1 AND COLUMN_NAME = 'utilisateur_id'
      AND INDEX_NAME IN (
          SELECT INDEX_NAME FROM information_schema.STATISTICS
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'notifications'
          GROUP BY INDEX_NAME
          HAVING COUNT(*) = 2
             AND SUM(SEQ_IN_INDEX = 1 AND COLUMN_NAME = 'utilisateur_id') = 1
             AND SUM(SEQ_IN_INDEX = 2 AND COLUMN_NAME = 'lu') = 1
      )
);
SET @migration_sql = IF(@index_exists = 0, 'CREATE INDEX idx_notif_user_lu ON notifications(utilisateur_id, lu)', 'DO 0');
PREPARE migration_stmt FROM @migration_sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @index_exists = (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'utilisateurs'
      AND SEQ_IN_INDEX = 1 AND COLUMN_NAME = 'filiere_id'
);
SET @migration_sql = IF(@index_exists = 0, 'CREATE INDEX idx_utilisateurs_filiere ON utilisateurs(filiere_id)', 'DO 0');
PREPARE migration_stmt FROM @migration_sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @index_exists = (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sondage_options'
      AND SEQ_IN_INDEX = 1 AND COLUMN_NAME = 'publication_id'
);
SET @migration_sql = IF(@index_exists = 0, 'CREATE INDEX idx_sondage_options_pub ON sondage_options(publication_id)', 'DO 0');
PREPARE migration_stmt FROM @migration_sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;
