CREATE TABLE IF NOT EXISTS stories (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id  INT UNSIGNED NOT NULL,
    type_contenu    ENUM('texte', 'image', 'texte_image') NOT NULL,
    texte           VARCHAR(300) DEFAULT NULL,
    image_url       VARCHAR(500) DEFAULT NULL,
    visibilite      ENUM('public', 'amis') NOT NULL DEFAULT 'amis',
    date_creation   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_expiration DATETIME NOT NULL,
    est_active      BOOLEAN NOT NULL DEFAULT TRUE,
    INDEX idx_stories_expiration (est_active, date_expiration),
    INDEX idx_stories_utilisateur (utilisateur_id, date_expiration),
    CONSTRAINT fk_stories_utilisateur
        FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS story_vues (
    story_id        INT UNSIGNED NOT NULL,
    utilisateur_id  INT UNSIGNED NOT NULL,
    date_vue        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (story_id, utilisateur_id),
    INDEX idx_story_vues_utilisateur (utilisateur_id),
    CONSTRAINT fk_story_vues_story
        FOREIGN KEY (story_id) REFERENCES stories(id) ON DELETE CASCADE,
    CONSTRAINT fk_story_vues_utilisateur
        FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;
