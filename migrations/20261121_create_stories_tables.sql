-- Migration pour créer les tables stories et story_vues
CREATE TABLE IF NOT EXISTS stories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT UNSIGNED NOT NULL,
    type_contenu ENUM('texte', 'image', 'texte_image') NOT NULL DEFAULT 'texte',
    texte TEXT NULL,
    image_url VARCHAR(255) NULL,
    visibilite ENUM('public', 'amis') NOT NULL DEFAULT 'public',
    est_active TINYINT(1) NOT NULL DEFAULT 1,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_expiration DATETIME NOT NULL,
    INDEX idx_stories_user_active (utilisateur_id, est_active, date_expiration),
    CONSTRAINT fk_stories_user FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS story_vues (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    story_id INT UNSIGNED NOT NULL,
    utilisateur_id INT UNSIGNED NOT NULL,
    date_vue DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_story_user (story_id, utilisateur_id),
    INDEX idx_story_vues_story (story_id),
    INDEX idx_story_vues_user (utilisateur_id),
    CONSTRAINT fk_story_vues_story FOREIGN KEY (story_id) REFERENCES stories(id) ON DELETE CASCADE,
    CONSTRAINT fk_story_vues_user FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
