CREATE TABLE IF NOT EXISTS filiere_message_groups (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    filiere_id INT UNSIGNED NOT NULL UNIQUE,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_filiere_message_groups_filiere
        FOREIGN KEY (filiere_id) REFERENCES filieres(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS filiere_group_professors (
    group_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    assigned_by INT UNSIGNED DEFAULT NULL,
    assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (group_id, user_id),
    INDEX idx_filiere_group_professors_user (user_id),
    CONSTRAINT fk_filiere_group_professors_group
        FOREIGN KEY (group_id) REFERENCES filiere_message_groups(id) ON DELETE CASCADE,
    CONSTRAINT fk_filiere_group_professors_user
        FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    CONSTRAINT fk_filiere_group_professors_admin
        FOREIGN KEY (assigned_by) REFERENCES utilisateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS filiere_group_messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_id INT UNSIGNED NOT NULL,
    sender_id INT UNSIGNED NOT NULL,
    contenu TEXT NOT NULL,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_filiere_group_messages_history (group_id, date_creation, id),
    CONSTRAINT fk_filiere_group_messages_group
        FOREIGN KEY (group_id) REFERENCES filiere_message_groups(id) ON DELETE CASCADE,
    CONSTRAINT fk_filiere_group_messages_sender
        FOREIGN KEY (sender_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS filiere_group_reads (
    group_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    last_read_at DATETIME NOT NULL,
    PRIMARY KEY (group_id, user_id),
    CONSTRAINT fk_filiere_group_reads_group
        FOREIGN KEY (group_id) REFERENCES filiere_message_groups(id) ON DELETE CASCADE,
    CONSTRAINT fk_filiere_group_reads_user
        FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO filiere_message_groups (filiere_id)
SELECT id FROM filieres WHERE est_active = 1
ON DUPLICATE KEY UPDATE filiere_id = VALUES(filiere_id);
