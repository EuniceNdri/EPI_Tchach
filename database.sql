-- ============================================================
--  FORUM ÉCOLE - BASE COMPLÈTE FINALE (OPTIMISÉE)
--  Zones public/privé · Amis · Invitations
--  Publications (discussion / annonce / sondage)
--  Chat de groupe par zone (auto) · Chat privé entre amis
--  MySQL 8+ / MariaDB
-- ============================================================

DROP DATABASE IF EXISTS forum_ecole;
CREATE DATABASE forum_ecole CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE forum_ecole;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. ANNÉES SCOLAIRES
-- ============================================================
CREATE TABLE annees_scolaires (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    libelle         VARCHAR(20) NOT NULL UNIQUE,
    date_debut      DATE NOT NULL,
    date_fin        DATE NOT NULL,
    est_active      BOOLEAN NOT NULL DEFAULT FALSE,
    date_creation   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO annees_scolaires (libelle, date_debut, date_fin, est_active) VALUES
('2024-2025', '2024-09-01', '2025-08-31', FALSE),
('2025-2026', '2025-09-01', '2026-08-31', TRUE),
('2026-2027', '2026-09-01', '2027-08-31', FALSE);

-- ============================================================
-- 2. FILIERES
-- ============================================================
CREATE TABLE filieres (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(150) NOT NULL UNIQUE,
    code            VARCHAR(20) DEFAULT NULL,
    description     TEXT DEFAULT NULL,
    image_url       VARCHAR(255) DEFAULT NULL,
    slogan          VARCHAR(180) DEFAULT NULL,
    est_active      BOOLEAN NOT NULL DEFAULT TRUE,
    date_creation   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 3. UTILISATEURS
-- ============================================================
CREATE TABLE utilisateurs (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom                 VARCHAR(100) NOT NULL,
    prenom              VARCHAR(100) NOT NULL,
    email               VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe        VARCHAR(255) NOT NULL,
    avatar_url          VARCHAR(255) DEFAULT NULL,
    biographie          TEXT DEFAULT NULL,
    filiere_id          INT UNSIGNED DEFAULT NULL,
    annee               TINYINT UNSIGNED DEFAULT NULL,
    annee_scolaire_id   INT UNSIGNED DEFAULT NULL,
    modules_enseignes   TEXT DEFAULT NULL,
    role                ENUM('etudiant','professeur','administrateur') NOT NULL DEFAULT 'etudiant',
    est_actif           BOOLEAN NOT NULL DEFAULT TRUE,
    date_inscription    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    derniere_connexion  DATETIME DEFAULT NULL,
    derniere_activite   DATETIME DEFAULT NULL,
    FOREIGN KEY (filiere_id) REFERENCES filieres(id) ON DELETE SET NULL,
    FOREIGN KEY (annee_scolaire_id) REFERENCES annees_scolaires(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- 3. BINOMAGES (MENTORAT)
-- ============================================================
CREATE TABLE binomages (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mentor_id       INT UNSIGNED NOT NULL,
    mentore_id      INT UNSIGNED NOT NULL,
    statut          ENUM('en_attente','accepte','refuse','termine') NOT NULL DEFAULT 'en_attente',
    message_demande TEXT DEFAULT NULL,
    date_demande    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_reponse    DATETIME DEFAULT NULL,
    UNIQUE KEY uq_binomage_mentor_mentore (mentor_id, mentore_id),
    INDEX idx_binomages_mentore_statut (mentore_id, statut),
    INDEX idx_binomages_mentor_statut (mentor_id, statut),
    CONSTRAINT chk_binomages_utilisateurs_differents CHECK (mentor_id <> mentore_id),
    FOREIGN KEY (mentor_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (mentore_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 4. ZONES
-- ============================================================
CREATE TABLE zones (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(150) NOT NULL,
    type_zone       ENUM('generale','filiere','professeurs','delegation','libre') NOT NULL DEFAULT 'libre',
    visibilite      ENUM('public','prive') NOT NULL DEFAULT 'public',
    est_generale    BOOLEAN NOT NULL DEFAULT FALSE,
    description     TEXT DEFAULT NULL,
    createur_id     INT UNSIGNED DEFAULT NULL,
    est_active      BOOLEAN NOT NULL DEFAULT TRUE,
    date_creation   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (createur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- 4. APPARTENANCE_ZONES
-- ============================================================
CREATE TABLE appartenance_zones (
    utilisateur_id  INT UNSIGNED NOT NULL,
    zone_id         INT UNSIGNED NOT NULL,
    role_dans_zone  ENUM('membre','admin_zone','delegue_de_classe','delegue_general') NOT NULL DEFAULT 'membre',
    date_ajout      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (utilisateur_id, zone_id),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (zone_id) REFERENCES zones(id) ON DELETE CASCADE,
    INDEX idx_role_zone (role_dans_zone)
) ENGINE=InnoDB;

-- ============================================================
-- 5. AMIS
-- ============================================================
CREATE TABLE amis (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id  INT UNSIGNED NOT NULL,
    ami_id          INT UNSIGNED NOT NULL,
    statut          ENUM('en_attente','accepte','refuse','bloque') NOT NULL DEFAULT 'en_attente',
    date_demande    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_reponse    DATETIME DEFAULT NULL,
    UNIQUE KEY uq_relation (utilisateur_id, ami_id),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (ami_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 5. STORIES ÉPHÉMÈRES
-- ============================================================
CREATE TABLE stories (
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
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE story_vues (
    story_id        INT UNSIGNED NOT NULL,
    utilisateur_id  INT UNSIGNED NOT NULL,
    date_vue        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (story_id, utilisateur_id),
    INDEX idx_story_vues_utilisateur (utilisateur_id),
    FOREIGN KEY (story_id) REFERENCES stories(id) ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 6. INVITATIONS ZONES PRIVÉES
-- ============================================================
CREATE TABLE zone_invitations (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    zone_id         INT UNSIGNED NOT NULL,
    invite_par      INT UNSIGNED NOT NULL,
    invite_id       INT UNSIGNED NOT NULL,
    statut          ENUM('en_attente','acceptee','refusee') NOT NULL DEFAULT 'en_attente',
    date_invitation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_reponse    DATETIME DEFAULT NULL,
    UNIQUE KEY uq_inv (zone_id, invite_id),
    FOREIGN KEY (zone_id) REFERENCES zones(id) ON DELETE CASCADE,
    FOREIGN KEY (invite_par) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (invite_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 7. CATEGORIES (optionnelles)
-- ============================================================
CREATE TABLE categories (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    zone_id     INT UNSIGNED NOT NULL,
    nom         VARCHAR(150) NOT NULL,
    slug        VARCHAR(150) NOT NULL,
    description TEXT DEFAULT NULL,
    ordre       INT NOT NULL DEFAULT 0,
    est_active  BOOLEAN NOT NULL DEFAULT TRUE,
    FOREIGN KEY (zone_id) REFERENCES zones(id) ON DELETE CASCADE,
    UNIQUE KEY uq_slug_zone (zone_id, slug)
) ENGINE=InnoDB;

-- ============================================================
-- 8. PUBLICATIONS
-- ============================================================
CREATE TABLE publications (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id      INT UNSIGNED NOT NULL,
    zone_id             INT UNSIGNED NOT NULL,
    categorie_id        INT UNSIGNED DEFAULT NULL,
    titre               VARCHAR(255) NOT NULL,
    contenu             TEXT NOT NULL,
    type_publication    ENUM('discussion','annonce','sondage') NOT NULL DEFAULT 'discussion',
    statut              ENUM('ouvert','resolu','ferme','epingle') NOT NULL DEFAULT 'ouvert',
    commentaires_fermes BOOLEAN NOT NULL DEFAULT FALSE,
    est_signale         BOOLEAN NOT NULL DEFAULT FALSE,
    date_creation       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_modification   DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (zone_id) REFERENCES zones(id) ON DELETE CASCADE,
    FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_pub_zone (zone_id),
    INDEX idx_pub_type (type_publication)
) ENGINE=InnoDB;

-- ============================================================
-- 9. COMMENTAIRES (discussion uniquement)
-- ============================================================
CREATE TABLE commentaires (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    publication_id      INT UNSIGNED NOT NULL,
    utilisateur_id      INT UNSIGNED NOT NULL,
    parent_comment_id   INT UNSIGNED DEFAULT NULL,
    contenu             TEXT NOT NULL,
    est_solution        BOOLEAN NOT NULL DEFAULT FALSE,
    est_epingle         BOOLEAN NOT NULL DEFAULT FALSE,
    est_signale         BOOLEAN NOT NULL DEFAULT FALSE,
    nb_likes            INT UNSIGNED NOT NULL DEFAULT 0,
    date_creation       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_modification   DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (publication_id) REFERENCES publications(id) ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_comment_id) REFERENCES commentaires(id) ON DELETE CASCADE,
    INDEX idx_com_pub (publication_id),
    INDEX idx_com_likes (nb_likes)
) ENGINE=InnoDB;

-- ============================================================
-- 10. SONDAGES
-- ============================================================
CREATE TABLE sondage_options (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    publication_id  INT UNSIGNED NOT NULL,
    texte           VARCHAR(255) NOT NULL,
    ordre           INT NOT NULL DEFAULT 0,
    nb_votes        INT UNSIGNED NOT NULL DEFAULT 0,
    FOREIGN KEY (publication_id) REFERENCES publications(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE sondage_votes (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    option_id       INT UNSIGNED NOT NULL,
    utilisateur_id  INT UNSIGNED NOT NULL,
    date_vote       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_vote (option_id, utilisateur_id),
    FOREIGN KEY (option_id) REFERENCES sondage_options(id) ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 11. REACTIONS (Style Facebook 6 emojis)
-- ============================================================
CREATE TABLE reactions (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id  INT UNSIGNED NOT NULL,
    type_cible      ENUM('publication','commentaire') NOT NULL,
    cible_id        INT UNSIGNED NOT NULL,
    type_reaction   ENUM('like','jadore','utile','haha','wouah','triste') NOT NULL DEFAULT 'like',
    date_reaction   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_react (utilisateur_id, type_cible, cible_id),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_cible (type_cible, cible_id)
) ENGINE=InnoDB;

-- ============================================================
-- 12. PIECES JOINTES
-- ============================================================
CREATE TABLE pieces_jointes (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type_cible      ENUM('publication','commentaire','message_prive','message_zone') NOT NULL,
    cible_id        INT UNSIGNED NOT NULL,
    nom_fichier     VARCHAR(255) NOT NULL,
    chemin_fichier  VARCHAR(500) NOT NULL,
    type_mime       VARCHAR(100) NOT NULL,
    taille          INT UNSIGNED NOT NULL DEFAULT 0,
    date_upload     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 13. TAGS
-- ============================================================
CREATE TABLE tags (
    id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom  VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE publication_tags (
    publication_id INT UNSIGNED NOT NULL,
    tag_id         INT UNSIGNED NOT NULL,
    PRIMARY KEY (publication_id, tag_id),
    FOREIGN KEY (publication_id) REFERENCES publications(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 14. SIGNALEMENTS
-- ============================================================
CREATE TABLE signalements (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    auteur_id        INT UNSIGNED NOT NULL,
    type_cible       ENUM('publication','commentaire','utilisateur') NOT NULL,
    cible_id         INT UNSIGNED NOT NULL,
    zone_id          INT UNSIGNED DEFAULT NULL,
    motif            VARCHAR(100) NOT NULL,
    detail           TEXT DEFAULT NULL,
    statut           ENUM('en_attente','traite','rejete') NOT NULL DEFAULT 'en_attente',
    action_prise     ENUM('aucune','avertissement','suppression_contenu','expulsion_salon','suspension_compte') DEFAULT NULL,
    traite_par       INT UNSIGNED DEFAULT NULL,
    date_signalement DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_traitement  DATETIME DEFAULT NULL,
    FOREIGN KEY (auteur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (traite_par) REFERENCES utilisateurs(id) ON DELETE SET NULL,
    FOREIGN KEY (zone_id) REFERENCES zones(id) ON DELETE SET NULL,
    INDEX idx_signalements_statut (statut),
    INDEX idx_signalements_zone (zone_id),
    INDEX idx_signalements_cible (type_cible, cible_id),
    INDEX idx_signalements_auteur_cible (auteur_id, type_cible, cible_id, statut)
) ENGINE=InnoDB;

-- ============================================================
-- 15. NOTIFICATIONS
-- ============================================================
CREATE TABLE notifications (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id    INT UNSIGNED NOT NULL,
    type_notification VARCHAR(50) NOT NULL,
    contenu           VARCHAR(500) NOT NULL,
    lien              VARCHAR(255) DEFAULT NULL,
    lu                BOOLEAN NOT NULL DEFAULT FALSE,
    date_creation     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_notif (utilisateur_id, lu)
) ENGINE=InnoDB;

-- ============================================================
-- 16. MOTS INTERDITS
-- ============================================================
CREATE TABLE mots_interdits (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mot        VARCHAR(100) NOT NULL,
    type_mot   ENUM('interdit','suspect','insulte','spam') NOT NULL DEFAULT 'interdit',
    action     ENUM('bloquer','signaler','remplacer') NOT NULL DEFAULT 'signaler',
    actif      BOOLEAN NOT NULL DEFAULT TRUE,
    date_ajout DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 17. CHAT DE GROUPE PAR ZONE (1 zone = 1 conversation)
-- ============================================================
CREATE TABLE zone_messages (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    zone_id         INT UNSIGNED NOT NULL,
    expediteur_id   INT UNSIGNED NOT NULL,
    contenu         TEXT NOT NULL,
    date_creation   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (zone_id) REFERENCES zones(id) ON DELETE CASCADE,
    FOREIGN KEY (expediteur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_zone_msg (zone_id, date_creation)
) ENGINE=InnoDB;

CREATE TABLE zone_message_reads (
    zone_id      INT UNSIGNED NOT NULL,
    user_id      INT UNSIGNED NOT NULL,
    last_read_at DATETIME NOT NULL,
    PRIMARY KEY (zone_id, user_id),
    FOREIGN KEY (zone_id) REFERENCES zones(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 18. CHAT PRIVÉ ENTRE AMIS (1-1)
-- ============================================================
CREATE TABLE conversations (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user1_id        INT UNSIGNED NOT NULL,
    user2_id        INT UNSIGNED NOT NULL,
    date_creation   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    derniere_activite DATETIME DEFAULT NULL,
    UNIQUE KEY uq_conv (user1_id, user2_id),
    FOREIGN KEY (user1_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (user2_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE messages_prives (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT UNSIGNED NOT NULL,
    expediteur_id   INT UNSIGNED NOT NULL,
    contenu         TEXT NOT NULL,
    lu              BOOLEAN NOT NULL DEFAULT FALSE,
    date_creation   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (expediteur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_conv_msg (conversation_id, date_creation)
) ENGINE=InnoDB;

-- ============================================================
-- 19. TABLES COMPATIBILITÉ ET EXTENSIONS
-- ============================================================
CREATE TABLE publications_sauvegardees (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id  INT UNSIGNED NOT NULL,
    publication_id  INT UNSIGNED NOT NULL,
    date_sauvegarde DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_pub (utilisateur_id, publication_id),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (publication_id) REFERENCES publications(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE pieces_jointes_sauvegardees (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id  INT UNSIGNED NOT NULL,
    piece_jointe_id INT UNSIGNED NOT NULL,
    date_sauvegarde DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_piece (utilisateur_id, piece_jointe_id),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (piece_jointe_id) REFERENCES pieces_jointes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE publications_mentions (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    publication_id  INT UNSIGNED NOT NULL,
    utilisateur_id  INT UNSIGNED NOT NULL,
    FOREIGN KEY (publication_id) REFERENCES publications(id) ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE tags_publication (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(100) NOT NULL UNIQUE,
    statut          ENUM('actif','en_attente','inactif') NOT NULL DEFAULT 'actif',
    cree_par        INT UNSIGNED DEFAULT NULL,
    valide_par      INT UNSIGNED DEFAULT NULL,
    date_creation   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cree_par) REFERENCES utilisateurs(id) ON DELETE SET NULL,
    FOREIGN KEY (valide_par) REFERENCES utilisateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE publications_tags (
    publication_id  INT UNSIGNED NOT NULL,
    tag_id          INT UNSIGNED NOT NULL,
    PRIMARY KEY (publication_id, tag_id),
    FOREIGN KEY (publication_id) REFERENCES publications(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags_publication(id) ON DELETE CASCADE
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;


-- ############################################################
--                      DONNÉES DE TEST
-- ############################################################
-- Mot de passe TOUS comptes = password123

INSERT INTO filieres (id, nom, code, description) VALUES
(1, 'BTS IDA', 'IDA', 'BTS Informatique Développement Applications'),
(2, 'Licence Informatique', 'LIC-INFO', 'Licence pro Informatique'),
(3, 'Génie Mécanique', 'GM', 'Génie Mécanique'),
(4, 'Génie Civil', 'GC', 'Génie Civil & BTP'),
(5, 'Finance Comptabilité', 'FCGE', 'Finance & Comptabilité');

INSERT INTO utilisateurs (id, nom, prenom, email, mot_de_passe, role, filiere_id, modules_enseignes, biographie) VALUES
(1, 'Kouassi', 'Momo', 'momo.kouassi@epi-yakro.ci', '$2y$12$8F/UiuqNU1NEs611GD4lBOxmZHqqD/4bwZhn3kNpevRAluU0S6ILG', 'etudiant', 1, NULL, 'Étudiant BTS IDA'),
(2, 'Traoré', 'Sarah', 'sarah.traore@epi-yakro.ci', '$2y$12$8F/UiuqNU1NEs611GD4lBOxmZHqqD/4bwZhn3kNpevRAluU0S6ILG', 'etudiant', 1, NULL, 'Étudiante BTS IDA'),
(3, 'Diallo', 'Jean', 'jean.diallo@epi-yakro.ci', '$2y$12$8F/UiuqNU1NEs611GD4lBOxmZHqqD/4bwZhn3kNpevRAluU0S6ILG', 'etudiant', 2, NULL, 'Licence Info'),
(4, 'N''Guessan', 'Awa', 'awa.nguessan@epi-yakro.ci', '$2y$12$8F/UiuqNU1NEs611GD4lBOxmZHqqD/4bwZhn3kNpevRAluU0S6ILG', 'etudiant', 1, NULL, 'Déléguée de classe BTS IDA'),
(5, 'Yao', 'Kofi', 'kofi.yao@epi-yakro.ci', '$2y$12$8F/UiuqNU1NEs611GD4lBOxmZHqqD/4bwZhn3kNpevRAluU0S6ILG', 'etudiant', 2, NULL, 'Délégué Général'),
(6, 'Koné', 'Dr. Ibrahim', 'ibrahim.kone@epi-yakro.ci', '$2y$12$8F/UiuqNU1NEs611GD4lBOxmZHqqD/4bwZhn3kNpevRAluU0S6ILG', 'professeur', NULL, 'Dev Web, BDD', 'Prof Dev Web'),
(7, 'Bamba', 'Fatou', 'fatou.bamba@epi-yakro.ci', '$2y$12$8F/UiuqNU1NEs611GD4lBOxmZHqqD/4bwZhn3kNpevRAluU0S6ILG', 'professeur', NULL, 'Réseaux', 'Prof Réseaux'),
(8, 'Admin', 'Système', 'admin@epi-yakro.ci', '$2y$12$8F/UiuqNU1NEs611GD4lBOxmZHqqD/4bwZhn3kNpevRAluU0S6ILG', 'administrateur', NULL, NULL, 'Admin principal'),
(9, 'Bakayoko', 'Amadou', 'amadou.bakayoko@epi-yakro.ci', '$2y$12$8F/UiuqNU1NEs611GD4lBOxmZHqqD/4bwZhn3kNpevRAluU0S6ILG', 'etudiant', 3, NULL, 'Génie Mécanique'),
(10, 'Coulibaly', 'Issa', 'issa.coulibaly@epi-yakro.ci', '$2y$12$8F/UiuqNU1NEs611GD4lBOxmZHqqD/4bwZhn3kNpevRAluU0S6ILG', 'etudiant', 1, NULL, 'BTS IDA');

INSERT INTO zones (id, nom, type_zone, visibilite, est_generale, description, createur_id) VALUES
(1, 'Zone Générale', 'generale', 'public', TRUE, 'Espace de toute l''école', 8),
(2, 'Filière BTS IDA', 'filiere', 'public', FALSE, 'Salon BTS IDA', 4),
(3, 'Filière Licence Informatique', 'filiere', 'public', FALSE, 'Salon Licence Info', 8),
(4, 'Espace Professeurs', 'professeurs', 'prive', FALSE, 'Réservé aux enseignants', 8),
(5, 'Bureau de la Délégation', 'delegation', 'prive', FALSE, 'Espace Délégation', 5),
(6, 'Projet Tutoré 2026', 'libre', 'public', FALSE, 'Groupe projet (créé étudiant)', 1),
(7, 'Révisions Examens', 'libre', 'prive', FALSE, 'Groupe privé de révisions', 2);

INSERT INTO appartenance_zones (utilisateur_id, zone_id, role_dans_zone) VALUES
(1,1,'membre'),(2,1,'membre'),(3,1,'membre'),(4,1,'membre'),(5,1,'membre'),
(6,1,'membre'),(7,1,'membre'),(8,1,'admin_zone'),(9,1,'membre'),(10,1,'membre'),
(1,2,'membre'),(2,2,'membre'),(4,2,'delegue_de_classe'),(6,2,'membre'),(10,2,'membre'),
(3,3,'membre'),(5,3,'membre'),(7,3,'membre'),
(6,4,'membre'),(7,4,'membre'),(8,4,'admin_zone'),
(5,5,'delegue_general'),(4,5,'membre'),(8,5,'admin_zone'),
(1,6,'admin_zone'),(2,6,'membre'),(10,6,'membre'),
(2,7,'admin_zone'),(1,7,'membre');

INSERT INTO amis (utilisateur_id, ami_id, statut, date_reponse) VALUES
(1,8,'accepte',NOW()),(2,8,'accepte',NOW()),(3,8,'accepte',NOW()),(4,8,'accepte',NOW()),
(5,8,'accepte',NOW()),(6,8,'accepte',NOW()),(7,8,'accepte',NOW()),(9,8,'accepte',NOW()),(10,8,'accepte',NOW()),
(1,2,'accepte',NOW()),(1,10,'accepte',NOW()),(2,4,'accepte',NOW()),(3,5,'accepte',NOW()),
(1,4,'en_attente',NULL);

INSERT INTO zone_invitations (zone_id, invite_par, invite_id, statut, date_reponse) VALUES
(7, 2, 1, 'acceptee', NOW()),
(7, 2, 10, 'en_attente', NULL);

INSERT INTO categories (id, zone_id, nom, slug, description, ordre) VALUES
(1,1,'Annonces Officielles','annonces-officielles','Communications direction',1),
(2,1,'Vie Étudiante','vie-etudiante','Discussions générales',2),
(3,2,'Cours & TP','cours-tp-ida','Entraide BTS IDA',1),
(4,2,'Projets','projets-ida','Projets de classe',2),
(5,3,'Cours & TP','cours-tp-licence','Entraide Licence',1),
(6,5,'Réunions','reunions-delegation','Comptes-rendus',1);
-- Zones 6 et 7 : aucune catégorie

INSERT INTO tags (nom) VALUES ('laravel'),('php'),('mysql'),('javascript'),('examen'),('tp'),('projet'),('stage');

INSERT INTO tags_publication (id, nom, statut)
SELECT id, nom, 'actif' FROM tags;

INSERT INTO publication_tags (publication_id, tag_id) VALUES
(3, 1), (3, 2), (3, 3), (4, 1), (4, 4), (5, 7);

INSERT INTO publications_tags (publication_id, tag_id)
SELECT publication_id, tag_id FROM publication_tags;

INSERT INTO publications (id, utilisateur_id, zone_id, categorie_id, titre, contenu, type_publication, statut, commentaires_fermes) VALUES
(1, 8, 1, 1, 'Rentrée 2026', 'Rentrée fixée au 5 octobre 2026.', 'annonce', 'epingle', TRUE),
(2, 6, 1, 1, 'Notes contrôle Dev Web', 'Notes disponibles demain.', 'annonce', 'ouvert', TRUE),
(3, 1, 2, 3, 'Erreur SQL TP Laravel', 'Column not found sur jointure, aide SVP.', 'discussion', 'ouvert', FALSE),
(4, 2, 2, 3, 'API REST Laravel', 'Bonnes pratiques routes ?', 'discussion', 'resolu', FALSE),
(5, 1, 6, NULL, 'Organisation projet tutoré', 'Répartition des tâches.', 'discussion', 'ouvert', FALSE),
(6, 2, 7, NULL, 'Planning révisions', 'Qui est dispo ce week-end ?', 'discussion', 'ouvert', FALSE),
(7, 4, 2, 4, 'Jour TP rattrapage ?', 'Choisissez un créneau.', 'sondage', 'ouvert', TRUE),
(8, 5, 1, 2, 'Portes ouvertes - bénévoles', 'Qui participe ?', 'sondage', 'ouvert', TRUE);

INSERT INTO sondage_options (publication_id, texte, ordre, nb_votes) VALUES
(7, 'Lundi 14h-16h', 1, 2),
(7, 'Mercredi 10h-12h', 2, 3),
(7, 'Vendredi 14h-16h', 3, 1),
(8, 'Oui, volontaire', 1, 4),
(8, 'Non disponible', 2, 1);

INSERT INTO sondage_votes (option_id, utilisateur_id) VALUES
(1,1),(1,10),(2,2),(2,4),(2,6),(3,3),(4,1),(4,2),(4,4),(4,5),(5,9);

INSERT INTO commentaires (id, publication_id, utilisateur_id, parent_comment_id, contenu, est_solution, est_epingle, nb_likes) VALUES
(1, 3, 6, NULL, 'Vérifie le nom de colonne dans la migration (FK oubliée souvent).', TRUE, TRUE, 4),
(2, 3, 2, NULL, 'Merci Prof, ça marche !', FALSE, FALSE, 1),
(3, 3, 1, 1, 'Super, merci pour la précision.', FALSE, FALSE, 0),
(4, 4, 6, NULL, 'Utilise API Resources + Sanctum.', TRUE, FALSE, 3),
(5, 5, 2, NULL, 'Je prends le front.', FALSE, FALSE, 2),
(6, 5, 10, NULL, 'Je m''occupe de la BDD.', FALSE, FALSE, 1),
(7, 6, 1, NULL, 'Dispo samedi matin.', FALSE, FALSE, 1);

INSERT INTO reactions (utilisateur_id, type_cible, cible_id, type_reaction) VALUES
(2,'publication',3,'utile'),(4,'publication',3,'like'),(10,'publication',3,'utile'),
(1,'commentaire',1,'like'),(2,'commentaire',1,'like'),(4,'commentaire',1,'like'),(10,'commentaire',1,'like'),
(1,'commentaire',4,'like'),(2,'commentaire',4,'like'),(10,'commentaire',4,'like'),
(1,'commentaire',5,'like'),(10,'commentaire',5,'like');

INSERT INTO pieces_jointes (type_cible, cible_id, nom_fichier, chemin_fichier, type_mime, taille) VALUES
('publication', 3, 'screenshot_erreur.png', '/uploads/screenshot_erreur.png', 'image/png', 153600),
('commentaire', 1, 'exemple_migration.php', '/uploads/exemple_migration.php', 'application/x-php', 2048);

INSERT INTO publication_tags (publication_id, tag_id) VALUES (3,1),(3,2),(3,3),(4,1),(4,2),(7,5),(7,6);

INSERT INTO signalements (auteur_id, type_cible, cible_id, motif, statut) VALUES
(2, 'commentaire', 3, 'Message peu clair.', 'en_attente');

INSERT INTO notifications (utilisateur_id, type_notification, contenu, lien, lu) VALUES
(1, 'reponse', 'Dr. Ibrahim a répondu à votre discussion', '/publication/3', FALSE),
(1, 'solution', 'Un commentaire a été mis en avant', '/publication/3', FALSE),
(1, 'ami', 'Sarah a accepté votre demande d''ami', '/amis', TRUE),
(10, 'invitation_zone', 'Sarah vous invite dans "Révisions Examens"', '/zones/7', FALSE),
(4, 'ami', 'Momo vous a envoyé une demande d''ami', '/amis', FALSE),
(1, 'message', 'Nouveau message de Sarah', '/messages', FALSE);

INSERT INTO mots_interdits (mot, type_mot, action) VALUES
('insulte','insulte','bloquer'),('spam','spam','signaler'),('arnaque','suspect','signaler');

-- CHAT DE GROUPE PAR ZONE
INSERT INTO zone_messages (zone_id, expediteur_id, contenu) VALUES
(2, 4, 'Bienvenue dans le salon BTS IDA !'),
(2, 1, 'Salut tout le monde, prêt pour les TP ?'),
(2, 6, 'N''hésitez pas à poser vos questions ici.'),
(6, 1, 'On démarre le projet tutoré, qui est là ?'),
(6, 2, 'Présente ! Je prends le front.'),
(6, 10, 'Ok pour la BDD.'),
(7, 2, 'Groupe de révisions privé, on s''organise ici.'),
(7, 1, 'Je suis dispo samedi.');

INSERT INTO zone_message_reads (zone_id, user_id, last_read_at) VALUES
(2,1,NOW()),(2,2,NOW()),(2,4,NOW()),(2,6,NOW()),
(6,1,NOW()),(6,2,NOW()),(6,10,NOW()),
(7,1,NOW()),(7,2,NOW());

-- CHAT PRIVÉ ENTRE AMIS
INSERT INTO conversations (id, user1_id, user2_id, derniere_activite) VALUES
(1, 1, 2, NOW()),   -- Momo ↔ Sarah
(2, 1, 10, NOW()),  -- Momo ↔ Issa
(3, 2, 4, NOW());   -- Sarah ↔ Awa

INSERT INTO messages_prives (conversation_id, expediteur_id, contenu, lu) VALUES
(1, 1, 'Salut Sarah, tu as avancé sur le TP Laravel ?', TRUE),
(1, 2, 'Oui, j''ai bloqué sur la jointure. Tu peux m''aider ?', TRUE),
(1, 1, 'Regarde le commentaire du prof sur le forum.', FALSE),
(2, 1, 'Issa, tu prends bien la partie BDD du projet ?', TRUE),
(2, 10, 'Oui, je commence ce soir.', FALSE),
(3, 2, 'Awa, réunion délégués demain ?', TRUE),
(3, 4, 'Oui, 14h au bureau de la délégation.', FALSE);
