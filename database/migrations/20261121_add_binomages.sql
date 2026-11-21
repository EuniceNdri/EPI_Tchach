-- Ajoute l'annee d'etude et les relations de mentorat entre etudiants.
-- annee reste nullable pour conserver les comptes etudiants existants.

SET @utilisateurs_annee_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'utilisateurs'
      AND COLUMN_NAME = 'annee'
);
SET @add_utilisateurs_annee_sql = IF(
    @utilisateurs_annee_exists = 0,
    'ALTER TABLE utilisateurs ADD COLUMN annee TINYINT UNSIGNED DEFAULT NULL AFTER filiere_id',
    'DO 0'
);
PREPARE add_utilisateurs_annee_stmt FROM @add_utilisateurs_annee_sql;
EXECUTE add_utilisateurs_annee_stmt;
DEALLOCATE PREPARE add_utilisateurs_annee_stmt;

CREATE TABLE IF NOT EXISTS binomages (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mentor_id       INT UNSIGNED NOT NULL,
    mentore_id      INT UNSIGNED NOT NULL,
    statut          ENUM('en_attente','accepte','refuse','termine') NOT NULL DEFAULT 'en_attente',
    message_demande TEXT DEFAULT NULL,
    date_demande    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_reponse    DATETIME DEFAULT NULL,
    UNIQUE KEY uq_binomage_mentor_mentore (mentor_id, mentore_id),
    INDEX idx_binomages_mentore_statut (mentore_id, statut),
    INDEX idx_binomages_mentor_statut (mentor_id, statut),
    CONSTRAINT chk_binomages_utilisateurs_differents CHECK (mentor_id <> mentore_id),
    CONSTRAINT fk_binomages_mentor FOREIGN KEY (mentor_id)
        REFERENCES utilisateurs(id) ON DELETE CASCADE,
    CONSTRAINT fk_binomages_mentore FOREIGN KEY (mentore_id)
        REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;
