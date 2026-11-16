-- Migration MySQL 8.4 compatible pour le support des reponses aux commentaires

SET @parent_comment_col_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'commentaires'
      AND COLUMN_NAME = 'parent_comment_id'
);

SET @migration_sql = IF(
    @parent_comment_col_exists = 0,
    'ALTER TABLE commentaires ADD COLUMN parent_comment_id INT UNSIGNED DEFAULT NULL AFTER utilisateur_id',
    'DO 0'
);
PREPARE migration_stmt FROM @migration_sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @parent_comment_index_exists = (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'commentaires'
      AND INDEX_NAME = 'idx_commentaires_parent'
);

SET @migration_sql = IF(
    @parent_comment_index_exists = 0,
    'CREATE INDEX idx_commentaires_parent ON commentaires(parent_comment_id)',
    'DO 0'
);
PREPARE migration_stmt FROM @migration_sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @parent_comment_fk_exists = (
    SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'commentaires'
      AND CONSTRAINT_NAME = 'fk_commentaires_parent'
);

SET @migration_sql = IF(
    @parent_comment_fk_exists = 0,
    'ALTER TABLE commentaires ADD CONSTRAINT fk_commentaires_parent FOREIGN KEY (parent_comment_id) REFERENCES commentaires(id) ON DELETE CASCADE',
    'DO 0'
);
PREPARE migration_stmt FROM @migration_sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

