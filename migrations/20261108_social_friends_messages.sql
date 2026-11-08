CREATE TABLE IF NOT EXISTS friendships (
    user_one_id INT UNSIGNED NOT NULL,
    user_two_id INT UNSIGNED NOT NULL,
    requested_by INT UNSIGNED NOT NULL,
    status ENUM('pending', 'accepted', 'declined') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_one_id, user_two_id),
    INDEX idx_friendships_requested_by_status (requested_by, status),
    CONSTRAINT chk_friendships_user_order CHECK (user_one_id < user_two_id),
    CONSTRAINT chk_friendships_requester_in_pair CHECK (requested_by IN (user_one_id, user_two_id)),
    CONSTRAINT fk_friendships_user_one FOREIGN KEY (user_one_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    CONSTRAINT fk_friendships_user_two FOREIGN KEY (user_two_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    CONSTRAINT fk_friendships_requested_by FOREIGN KEY (requested_by) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS private_messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sender_id INT UNSIGNED NOT NULL,
    recipient_id INT UNSIGNED NOT NULL,
    contenu TEXT NOT NULL,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    read_at DATETIME DEFAULT NULL,
    INDEX idx_private_messages_thread (sender_id, recipient_id, date_creation, id),
    INDEX idx_private_messages_unread (recipient_id, sender_id, read_at),
    CONSTRAINT fk_private_messages_sender FOREIGN KEY (sender_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    CONSTRAINT fk_private_messages_recipient FOREIGN KEY (recipient_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    CONSTRAINT chk_private_messages_different_users CHECK (sender_id <> recipient_id)
) ENGINE=InnoDB;
