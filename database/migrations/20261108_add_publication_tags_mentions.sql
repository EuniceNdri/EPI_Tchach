CREATE TABLE IF NOT EXISTS tags_publication (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(50) NOT NULL UNIQUE,
    statut          ENUM('en_attente','actif','inactif') NOT NULL DEFAULT 'en_attente',
    cree_par        INT UNSIGNED DEFAULT NULL,
    valide_par      INT UNSIGNED DEFAULT NULL,
    date_creation   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cree_par) REFERENCES utilisateurs(id) ON DELETE SET NULL,
    FOREIGN KEY (valide_par) REFERENCES utilisateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS publications_tags (
    publication_id  INT UNSIGNED NOT NULL,
    tag_id          INT UNSIGNED NOT NULL,
    PRIMARY KEY (publication_id, tag_id),
    INDEX idx_publications_tags_tag (tag_id, publication_id),
    FOREIGN KEY (publication_id) REFERENCES publications(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags_publication(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS publications_mentions (
    publication_id  INT UNSIGNED NOT NULL,
    utilisateur_id  INT UNSIGNED NOT NULL,
    mentionne_par   INT UNSIGNED NOT NULL,
    date_creation   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (publication_id, utilisateur_id),
    INDEX idx_publications_mentions_user (utilisateur_id, publication_id),
    FOREIGN KEY (publication_id) REFERENCES publications(id) ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (mentionne_par) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;
