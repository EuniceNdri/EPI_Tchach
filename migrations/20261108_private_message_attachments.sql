CREATE TABLE IF NOT EXISTS private_message_attachments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    message_id BIGINT UNSIGNED NOT NULL,
    nom_fichier VARCHAR(255) NOT NULL,
    chemin_fichier VARCHAR(255) NOT NULL,
    type_mime VARCHAR(127) NOT NULL,
    taille BIGINT UNSIGNED NOT NULL,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_private_message_attachments_message (message_id),
    CONSTRAINT fk_private_message_attachments_message
        FOREIGN KEY (message_id) REFERENCES private_messages(id) ON DELETE CASCADE
) ENGINE=InnoDB;
