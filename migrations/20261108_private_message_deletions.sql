SET @deleted_for_everyone_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'private_messages'
      AND COLUMN_NAME = 'deleted_for_everyone_at'
);

SET @migration_sql = IF(
    @deleted_for_everyone_exists = 0,
    'ALTER TABLE private_messages ADD COLUMN deleted_for_everyone_at DATETIME DEFAULT NULL',
    'DO 0'
);
PREPARE migration_stmt FROM @migration_sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

CREATE TABLE IF NOT EXISTS private_message_deletions (
    message_id BIGINT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    deleted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (message_id, user_id),
    INDEX idx_private_message_deletions_user (user_id, message_id),
    CONSTRAINT fk_private_message_deletions_message
        FOREIGN KEY (message_id) REFERENCES private_messages(id) ON DELETE CASCADE,
    CONSTRAINT fk_private_message_deletions_user
        FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;
