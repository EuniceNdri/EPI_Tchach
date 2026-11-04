-- Migration non destructive pour les bases existantes.
-- Les valeurs historiques des étudiants sont conservées et reliées à filieres.

CREATE TABLE IF NOT EXISTS filieres (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(150) NOT NULL UNIQUE,
    code            VARCHAR(20) DEFAULT NULL,
    description     TEXT DEFAULT NULL,
    zone_id         INT UNSIGNED DEFAULT NULL,
    est_active      BOOLEAN NOT NULL DEFAULT TRUE,
    date_creation   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_filieres_zone
        FOREIGN KEY (zone_id) REFERENCES zones(id) ON DELETE SET NULL
) ENGINE=InnoDB;

SET @utilisateurs_filiere_id_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'utilisateurs'
      AND COLUMN_NAME = 'filiere_id'
);
SET @add_utilisateurs_filiere_id_sql = IF(
    @utilisateurs_filiere_id_exists = 0,
    'ALTER TABLE utilisateurs ADD COLUMN filiere_id INT UNSIGNED DEFAULT NULL',
    'DO 0'
);
PREPARE add_utilisateurs_filiere_id_stmt FROM @add_utilisateurs_filiere_id_sql;
EXECUTE add_utilisateurs_filiere_id_stmt;
DEALLOCATE PREPARE add_utilisateurs_filiere_id_stmt;

INSERT IGNORE INTO filieres (nom, code, description) VALUES
    ('BTS IDA', 'IDA', 'BTS Informatique de Développement d''Applications'),
    ('Licence Informatique', 'LIC-INFO', 'Licence professionnelle Informatique'),
    ('Génie Mécanique', 'GM', 'Filière Génie Mécanique'),
    ('Génie Civil', 'GC', 'BTS / Licence Génie Civil & BTP'),
    ('Finance Comptabilité', 'FCGE', 'Finance, Comptabilité et Gestion des Entreprises');

SET @legacy_filiere_column_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'utilisateurs'
      AND COLUMN_NAME = 'filiere'
);
SET @legacy_filiere_import_sql = IF(
    @legacy_filiere_column_exists > 0,
    'INSERT IGNORE INTO filieres (nom) SELECT DISTINCT TRIM(filiere) FROM utilisateurs WHERE role = ''etudiant'' AND filiere IS NOT NULL AND TRIM(filiere) <> ''''',
    'DO 0'
);
PREPARE legacy_filiere_import_stmt FROM @legacy_filiere_import_sql;
EXECUTE legacy_filiere_import_stmt;
DEALLOCATE PREPARE legacy_filiere_import_stmt;

SET @legacy_filiere_link_sql = IF(
    @legacy_filiere_column_exists > 0,
    'UPDATE utilisateurs AS utilisateur INNER JOIN filieres AS filiere ON filiere.nom = TRIM(utilisateur.filiere) SET utilisateur.filiere_id = filiere.id WHERE utilisateur.role = ''etudiant'' AND utilisateur.filiere IS NOT NULL AND TRIM(utilisateur.filiere) <> '''' AND utilisateur.filiere_id IS NULL',
    'DO 0'
);
PREPARE legacy_filiere_link_stmt FROM @legacy_filiere_link_sql;
EXECUTE legacy_filiere_link_stmt;
DEALLOCATE PREPARE legacy_filiere_link_stmt;

SET @filieres_zone_fk_exists = (
    SELECT COUNT(*)
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'filieres'
      AND CONSTRAINT_NAME = 'fk_filieres_zone'
);
SET @filieres_zone_fk_sql = IF(
    @filieres_zone_fk_exists = 0,
    'ALTER TABLE filieres ADD CONSTRAINT fk_filieres_zone FOREIGN KEY (zone_id) REFERENCES zones(id) ON DELETE SET NULL',
    'DO 0'
);
PREPARE filieres_zone_fk_stmt FROM @filieres_zone_fk_sql;
EXECUTE filieres_zone_fk_stmt;
DEALLOCATE PREPARE filieres_zone_fk_stmt;

SET @utilisateurs_filiere_fk_exists = (
    SELECT COUNT(*)
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'utilisateurs'
      AND CONSTRAINT_NAME = 'fk_utilisateurs_filiere'
);
SET @utilisateurs_filiere_fk_sql = IF(
    @utilisateurs_filiere_fk_exists = 0,
    'ALTER TABLE utilisateurs ADD CONSTRAINT fk_utilisateurs_filiere FOREIGN KEY (filiere_id) REFERENCES filieres(id) ON DELETE SET NULL',
    'DO 0'
);
PREPARE utilisateurs_filiere_fk_stmt FROM @utilisateurs_filiere_fk_sql;
EXECUTE utilisateurs_filiere_fk_stmt;
DEALLOCATE PREPARE utilisateurs_filiere_fk_stmt;
