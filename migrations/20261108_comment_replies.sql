ALTER TABLE commentaires
    ADD COLUMN IF NOT EXISTS parent_comment_id INT UNSIGNED DEFAULT NULL;

CREATE INDEX IF NOT EXISTS idx_commentaires_parent ON commentaires(parent_comment_id);

ALTER TABLE commentaires
    ADD CONSTRAINT fk_commentaires_parent
    FOREIGN KEY (parent_comment_id) REFERENCES commentaires(id) ON DELETE CASCADE;
