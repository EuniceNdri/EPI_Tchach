-- ============================================================
-- Migration : Années scolaires et promotion des utilisateurs
-- Date : 2026-11-21
-- ============================================================

CREATE TABLE IF NOT EXISTS annees_scolaires (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    libelle       VARCHAR(20) NOT NULL UNIQUE,
    date_debut    DATE NOT NULL,
    date_fin      DATE NOT NULL,
    est_active    BOOLEAN NOT NULL DEFAULT FALSE,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion des années initiales avec 2025-2026 active
INSERT INTO annees_scolaires (libelle, date_debut, date_fin, est_active) VALUES
('2024-2025', '2024-09-01', '2025-08-31', FALSE),
('2025-2026', '2025-09-01', '2026-08-31', TRUE),
('2026-2027', '2026-09-01', '2027-08-31', FALSE)
ON DUPLICATE KEY UPDATE est_active = VALUES(est_active);

-- Ajout de annee_scolaire_id sur utilisateurs si non présent
SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'utilisateurs' AND COLUMN_NAME = 'annee_scolaire_id'
);
SET @sql = IF(@col_exists = 0, 'ALTER TABLE utilisateurs ADD COLUMN annee_scolaire_id INT UNSIGNED DEFAULT NULL AFTER annee', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Clé étrangère vers annees_scolaires si elle n'existe pas
SET @fk_exists = (
    SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'utilisateurs' AND CONSTRAINT_NAME = 'fk_user_annee_scolaire'
);
SET @sql_fk = IF(@fk_exists = 0, 'ALTER TABLE utilisateurs ADD CONSTRAINT fk_user_annee_scolaire FOREIGN KEY (annee_scolaire_id) REFERENCES annees_scolaires(id) ON DELETE SET NULL', 'SELECT 1');
PREPARE stmt_fk FROM @sql_fk;
EXECUTE stmt_fk;
DEALLOCATE PREPARE stmt_fk;

-- Associer l'année active par défaut aux utilisateurs existants
UPDATE utilisateurs
SET annee_scolaire_id = (SELECT id FROM annees_scolaires WHERE est_active = 1 LIMIT 1)
WHERE annee_scolaire_id IS NULL;
