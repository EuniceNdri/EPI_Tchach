 ============================================================
--  FORUM ÉCOLE - Script SQL FINAL COMPLET
--  Version stable avec :
--  - Table filieres
--  - Sondages (type question)
--  - Discussion libre + likes → solution
--  - Annonces sans commentaires
--  - Modération intelligente
--  MySQL / MariaDB
-- ============================================================

DROP DATABASE IF EXISTS forum_ecole;
CREATE DATABASE forum_ecole CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE forum_ecole;

-- ============================================================
-- 1. UTILISATEURS
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
    modules_enseignes   TEXT DEFAULT NULL,
    role                ENUM('etudiant','professeur','moderateur','administrateur') NOT NULL DEFAULT 'etudiant',
    est_actif           BOOLEAN NOT NULL DEFAULT TRUE,
    date_inscription    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    derniere_connexion  DATETIME DEFAULT NULL
) ENGINE=InnoDB;

-- ============================================================
-- 2. FILIERES
-- ============================================================
CREATE TABLE filieres (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(150) NOT NULL UNIQUE,
    code            VARCHAR(20) DEFAULT NULL,
    description     TEXT DEFAULT NULL,
    zone_id         INT UNSIGNED DEFAULT NULL,
    est_active      BOOLEAN NOT NULL DEFAULT TRUE,
    date_creation   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 3. ZONES
-- ============================================================
CREATE TABLE zones (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(150) NOT NULL,
    type_zone       ENUM('generale','filiere','professeurs','delegation') NOT NULL,
    description     TEXT DEFAULT NULL,
    createur_id     INT UNSIGNED DEFAULT NULL,
    est_active      BOOLEAN NOT NULL DEFAULT TRUE,
    date_creation   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (createur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB;

ALTER TABLE filieres
    ADD CONSTRAINT fk_filieres_zone
    FOREIGN KEY (zone_id) REFERENCES zones(id) ON DELETE SET NULL;

ALTER TABLE utilisateurs
    ADD CONSTRAINT fk_utilisateurs_filiere
    FOREIGN KEY (filiere_id) REFERENCES filieres(id) ON DELETE SET NULL;

-- ============================================================
-- 4. APPARTENANCE_ZONES
-- ============================================================
CREATE TABLE appartenance_zones (
    utilisateur_id  INT UNSIGNED NOT NULL,
    zone_id         INT UNSIGNED NOT NULL,
    role_dans_zone  ENUM('membre','chef_de_classe','delegue_general','sous_delegue','moderateur_zone') NOT NULL DEFAULT 'membre',
    date_ajout      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (utilisateur_id, zone_id),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (zone_id) REFERENCES zones(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 5. CATEGORIES
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
    UNIQUE KEY unique_slug_zone (zone_id, slug)
) ENGINE=InnoDB;

-- ============================================================
-- 6. PUBLICATIONS
-- ============================================================
-- type_publication :
--   discussion = discussion libre (commentaires + likes → solution)
--   question   = sondage (options + votes)
--   annonce    = info officielle (commentaires fermés par défaut)
CREATE TABLE publications (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id      INT UNSIGNED NOT NULL,
    categorie_id        INT UNSIGNED NOT NULL,
    titre               VARCHAR(255) NOT NULL,
    contenu             TEXT NOT NULL,
    type_publication    ENUM('discussion','question','annonce') NOT NULL DEFAULT 'discussion',
    statut              ENUM('ouvert','resolu','ferme','epingle') NOT NULL DEFAULT 'ouvert',
    commentaires_fermes BOOLEAN NOT NULL DEFAULT FALSE,
    est_signale         BOOLEAN NOT NULL DEFAULT FALSE,
    date_creation       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_modification   DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 7. COMMENTAIRES (pour type discussion)
-- ============================================================
CREATE TABLE commentaires (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    publication_id      INT UNSIGNED NOT NULL,
    utilisateur_id      INT UNSIGNED NOT NULL,
    contenu             TEXT NOT NULL,
    est_solution        BOOLEAN NOT NULL DEFAULT FALSE,
    est_epingle         BOOLEAN NOT NULL DEFAULT FALSE,
    est_signale         BOOLEAN NOT NULL DEFAULT FALSE,
    nb_likes            INT UNSIGNED NOT NULL DEFAULT 0,
    date_creation       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_modification   DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (publication_id) REFERENCES publications(id) ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 8. SONDAGE_OPTIONS (pour type question = sondage)
-- ============================================================
CREATE TABLE sondage_options (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    publication_id  INT UNSIGNED NOT NULL,
    texte           VARCHAR(255) NOT NULL,
    ordre           INT NOT NULL DEFAULT 0,
    nb_votes        INT UNSIGNED NOT NULL DEFAULT 0,
    FOREIGN KEY (publication_id) REFERENCES publications(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 9. SONDAGE_VOTES
-- ============================================================
CREATE TABLE sondage_votes (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    option_id       INT UNSIGNED NOT NULL,
    utilisateur_id  INT UNSIGNED NOT NULL,
    date_vote       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_vote_user_publication (option_id, utilisateur_id),
    FOREIGN KEY (option_id) REFERENCES sondage_options(id) ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 10. REACTIONS (likes sur publications et commentaires)
-- ============================================================
CREATE TABLE reactions (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id  INT UNSIGNED NOT NULL,
    type_cible      ENUM('publication','commentaire') NOT NULL,
    cible_id        INT UNSIGNED NOT NULL,
    type_reaction   ENUM('like','utile','jadore') NOT NULL DEFAULT 'like',
    date_reaction   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_reaction (utilisateur_id, type_cible, cible_id),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 11. PIECES_JOINTES
-- ============================================================
CREATE TABLE pieces_jointes (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type_cible      ENUM('publication','commentaire') NOT NULL,
    cible_id        INT UNSIGNED NOT NULL,
    nom_fichier     VARCHAR(255) NOT NULL,
    chemin_fichier  VARCHAR(500) NOT NULL,
    type_mime       VARCHAR(100) NOT NULL,
    taille          INT UNSIGNED NOT NULL DEFAULT 0,
    date_upload     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 12. TAGS
-- ============================================================
CREATE TABLE tags (
    id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom     VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ============================================================
-- 13. PUBLICATION_TAGS
-- ============================================================
CREATE TABLE publication_tags (
    publication_id  INT UNSIGNED NOT NULL,
    tag_id          INT UNSIGNED NOT NULL,
    PRIMARY KEY (publication_id, tag_id),
    FOREIGN KEY (publication_id) REFERENCES publications(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 14. SIGNALEMENTS
-- ============================================================
CREATE TABLE signalements (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    auteur_id           INT UNSIGNED NOT NULL,
    type_cible          ENUM('publication','commentaire') NOT NULL,
    cible_id            INT UNSIGNED NOT NULL,
    motif               TEXT NOT NULL,
    statut              ENUM('en_attente','traite','rejete') NOT NULL DEFAULT 'en_attente',
    traite_par          INT UNSIGNED DEFAULT NULL,
    date_signalement    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_traitement     DATETIME DEFAULT NULL,
    FOREIGN KEY (auteur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (traite_par) REFERENCES utilisateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- 15. NOTIFICATIONS
-- ============================================================
CREATE TABLE notifications (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id      INT UNSIGNED NOT NULL,
    type_notification   VARCHAR(50) NOT NULL,
    contenu             VARCHAR(500) NOT NULL,
    lien                VARCHAR(255) DEFAULT NULL,
    lu                  BOOLEAN NOT NULL DEFAULT FALSE,
    date_creation       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 16. MOTS_INTERDITS
-- ============================================================
CREATE TABLE mots_interdits (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mot         VARCHAR(100) NOT NULL,
    type_mot    ENUM('interdit','suspect','insulte','spam') NOT NULL DEFAULT 'interdit',
    action      ENUM('bloquer','signaler','remplacer') NOT NULL DEFAULT 'signaler',
    actif       BOOLEAN NOT NULL DEFAULT TRUE,
    date_ajout  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- INDEX
-- ============================================================
CREATE INDEX idx_pub_categorie        ON publications(categorie_id);
CREATE INDEX idx_pub_utilisateur      ON publications(utilisateur_id);
CREATE INDEX idx_pub_type             ON publications(type_publication);
CREATE INDEX idx_pub_statut           ON publications(statut);
CREATE INDEX idx_com_publication      ON commentaires(publication_id);
CREATE INDEX idx_com_nb_likes         ON commentaires(nb_likes);
CREATE INDEX idx_reactions_cible      ON reactions(type_cible, cible_id);
CREATE INDEX idx_pieces_cible         ON pieces_jointes(type_cible, cible_id);
CREATE INDEX idx_appartenance_role    ON appartenance_zones(role_dans_zone);
CREATE INDEX idx_signalements_statut  ON signalements(statut);
CREATE INDEX idx_notif_user_lu        ON notifications(utilisateur_id, lu);
CREATE INDEX idx_utilisateurs_filiere ON utilisateurs(filiere_id);
CREATE INDEX idx_sondage_options_pub  ON sondage_options(publication_id);


-- ############################################################
--                    DONNÉES DE TEST
-- ############################################################

-- Mot de passe pour tous = password123

INSERT INTO filieres (nom, code, description) VALUES
('BTS IDA', 'IDA', 'BTS Informatique de Développement d''Applications'),
('Licence Informatique', 'LIC-INFO', 'Licence professionnelle Informatique'),
('Génie Mécanique', 'GM', 'Filière Génie Mécanique (zone pas encore créée)'),
('Génie Civil', 'GC', 'BTS / Licence Génie Civil & BTP'),
('Finance Comptabilité', 'FCGE', 'Finance, Comptabilité et Gestion des Entreprises');

INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, filiere_id, modules_enseignes, biographie) VALUES
('Kouassi', 'Momo', 'momo.kouassi@epi-yakro.ci', '$2y$12$8F/UiuqNU1NEs611GD4lBOxmZHqqD/4bwZhn3kNpevRAluU0S6ILG', 'etudiant', 1, NULL, 'Étudiant en BTS IDA, passionné de développement web.'),
('Traoré', 'Sarah', 'sarah.traore@epi-yakro.ci', '$2y$12$8F/UiuqNU1NEs611GD4lBOxmZHqqD/4bwZhn3kNpevRAluU0S6ILG', 'etudiant', 1, NULL, 'Étudiante en BTS IDA.'),
('Diallo', 'Jean', 'jean.diallo@epi-yakro.ci', '$2y$12$8F/UiuqNU1NEs611GD4lBOxmZHqqD/4bwZhn3kNpevRAluU0S6ILG', 'etudiant', 2, NULL, 'Étudiant en Licence Informatique.'),
('N''Guessan', 'Awa', 'awa.nguessan@epi-yakro.ci', '$2y$12$8F/UiuqNU1NEs611GD4lBOxmZHqqD/4bwZhn3kNpevRAluU0S6ILG', 'etudiant', 1, NULL, 'Chef de classe BTS IDA + membre du Bureau de la Délégation.'),
('Yao', 'Kofi', 'kofi.yao@epi-yakro.ci', '$2y$12$8F/UiuqNU1NEs611GD4lBOxmZHqqD/4bwZhn3kNpevRAluU0S6ILG', 'etudiant', 2, NULL, 'Délégué Général des étudiants.'),
('Koné', 'Dr. Ibrahim', 'ibrahim.kone@epi-yakro.ci', '$2y$12$8F/UiuqNU1NEs611GD4lBOxmZHqqD/4bwZhn3kNpevRAluU0S6ILG', 'professeur', NULL, 'Développement Web, Base de données, Algorithmique', 'Professeur de Développement Web et Base de données.'),
('Bamba', 'Fatou', 'fatou.bamba@epi-yakro.ci', '$2y$12$8F/UiuqNU1NEs611GD4lBOxmZHqqD/4bwZhn3kNpevRAluU0S6ILG', 'professeur', NULL, 'Réseaux, Systèmes d''Exploitation, Sécurité', 'Professeure de Réseaux et Systèmes.'),
('Admin', 'Système', 'admin@epi-yakro.ci', '$2y$12$8F/UiuqNU1NEs611GD4lBOxmZHqqD/4bwZhn3kNpevRAluU0S6ILG', 'administrateur', NULL, NULL, 'Compte administrateur principal.'),
('Modo', 'Paul', 'paul.modo@epi-yakro.ci', '$2y$12$8F/UiuqNU1NEs611GD4lBOxmZHqqD/4bwZhn3kNpevRAluU0S6ILG', 'moderateur', NULL, NULL, 'Modérateur global de la plateforme.'),
('Bakayoko', 'Amadou', 'amadou.bakayoko@epi-yakro.ci', '$2y$12$8F/UiuqNU1NEs611GD4lBOxmZHqqD/4bwZhn3kNpevRAluU0S6ILG', 'etudiant', 3, NULL, 'Étudiant en Génie Mécanique (zone pas encore créée).');

INSERT INTO zones (nom, type_zone, description, createur_id) VALUES
('Zone Générale - Vie de l''École', 'generale', 'Espace public pour les annonces officielles et la vie étudiante.', 8),
('Filière BTS IDA', 'filiere', 'Espace dédié aux étudiants et professeurs de BTS IDA.', 4),
('Filière Licence Informatique', 'filiere', 'Espace dédié aux étudiants de Licence Informatique.', 8),
('Espace Professeurs', 'professeurs', 'Zone restreinte réservée aux enseignants.', 8),
('Bureau de la Délégation', 'delegation', 'Espace de travail de la Délégation étudiante.', 5);

UPDATE filieres SET zone_id = 2 WHERE id = 1;
UPDATE filieres SET zone_id = 3 WHERE id = 2;

INSERT INTO appartenance_zones (utilisateur_id, zone_id, role_dans_zone) VALUES
(1,1,'membre'),(2,1,'membre'),(3,1,'membre'),(4,1,'membre'),
(5,1,'membre'),(6,1,'membre'),(7,1,'membre'),(8,1,'moderateur_zone'),
(9,1,'moderateur_zone'),(10,1,'membre'),
(1,2,'membre'),(2,2,'membre'),(4,2,'chef_de_classe'),(6,2,'membre'),(5,2,'membre'),
(3,3,'membre'),(7,3,'membre'),
(6,4,'membre'),(7,4,'membre'),(8,4,'moderateur_zone'),
(5,5,'delegue_general'),(4,5,'chef_de_classe'),(1,5,'membre'),(9,5,'moderateur_zone');

INSERT INTO categories (zone_id, nom, slug, description, ordre) VALUES
(1, 'Annonces Officielles', 'annonces-officielles', 'Communications de la direction.', 1),
(1, 'Vie Étudiante & Événements', 'vie-etudiante', 'Discussions générales et activités.', 2),
(1, 'Boîte à idées', 'boite-a-idees', 'Suggestions des étudiants.', 3),
(2, 'Cours & TP', 'cours-tp-ida', 'Questions et entraide sur les cours.', 1),
(2, 'Projets de Classe', 'projets-ida', 'Suivi des projets tuteurés.', 2),
(2, 'Examens & Révisions', 'examens-ida', 'Annales et révisions.', 3),
(3, 'Cours & TP', 'cours-tp-licence', 'Questions sur les modules de Licence.', 1),
(3, 'Projets', 'projets-licence', 'Projets de fin d''année.', 2),
(4, 'Pédagogie & Syllabus', 'pedagogie', 'Échanges entre enseignants.', 1),
(4, 'Organisation', 'organisation-profs', 'Coordination entre professeurs.', 2),
(5, 'Réunions & Décisions', 'reunions-delegation', 'Comptes-rendus de la Délégation.', 1),
(5, 'Gestion des Filières', 'gestion-filieres', 'Suivi des Chefs de classe.', 2),
(5, 'Communications Officielles', 'comms-delegation', 'Messages officiels de la Délégation.', 3);

INSERT INTO tags (nom) VALUES
('laravel'),('php'),('mysql'),('javascript'),('html-css'),
('examen'),('tp'),('projet'),('stage'),('reseaux'),
('python'),('fastapi'),('git'),('api'),('debug');

-- PUBLICATIONS
-- 1-2 : annonces (commentaires fermés)
-- 3-5 : discussions
-- 6   : discussion épinglée
-- 7   : discussion
-- 8   : discussion profs
-- 9   : annonce délégation
-- 10  : sondage (question)
INSERT INTO publications (utilisateur_id, categorie_id, titre, contenu, type_publication, statut, commentaires_fermes) VALUES
(8, 1, 'Rentrée 2026 - Informations importantes', 'Chers étudiants, la rentrée officielle est fixée au 5 octobre 2026.', 'annonce', 'epingle', TRUE),
(6, 1, 'Remise des notes du premier contrôle', 'Les notes du contrôle de Développement Web seront disponibles demain.', 'annonce', 'ouvert', TRUE),
(1, 4, 'Erreur SQL dans mon TP Laravel', 'Bonjour, j''ai une erreur "Column not found" sur une jointure. Quelqu''un peut m''aider ?', 'discussion', 'ouvert', FALSE),
(2, 4, 'Comment faire une API REST avec Laravel ?', 'Quelles sont les bonnes pratiques pour les routes et les controllers ?', 'discussion', 'resolu', FALSE),
(1, 5, 'Recherche de binôme pour le projet tuteuré', 'Je cherche un binôme motivé pour le projet de fin d''année.', 'discussion', 'ouvert', FALSE),
(4, 6, 'Annales de l''examen de Base de données 2025', 'Voici les sujets de l''année dernière. Bonne révision !', 'discussion', 'epingle', FALSE),
(3, 7, 'Problème avec les threads en Python', 'J''ai un souci de concurrence dans mon programme multi-thread.', 'discussion', 'ouvert', FALSE),
(6, 9, 'Proposition de modification du syllabus BTS IDA', 'Je propose d''ajouter un module sur les API modernes.', 'discussion', 'ouvert', FALSE),
(5, 11, 'Réunion du Bureau de la Délégation - 20 octobre', 'Ordre du jour : point sur les filières et organisation de la journée portes ouvertes.', 'annonce', 'epingle', TRUE),
(4, 2, 'Quel jour pour le TP de rattrapage ?', 'Choisissez le créneau qui vous arrange le plus.', 'question', 'ouvert', TRUE);

-- OPTIONS DU SONDAGE (publication id = 10)
INSERT INTO sondage_options (publication_id, texte, ordre, nb_votes) VALUES
(10, 'Lundi 14h-16h', 1, 3),
(10, 'Mercredi 10h-12h', 2, 5),
(10, 'Vendredi 14h-16h', 3, 2);

-- VOTES DU SONDAGE
INSERT INTO sondage_votes (option_id, utilisateur_id) VALUES
(1, 1), (1, 2), (1, 3),
(2, 4), (2, 5), (2, 6), (2, 7), (2, 9),
(3, 8), (3, 10);

-- COMMENTAIRES (sur les discussions)
INSERT INTO commentaires (publication_id, utilisateur_id, contenu, est_solution, est_epingle, nb_likes) VALUES
(3, 6, 'Vérifie le nom de ta colonne dans la migration. Souvent c''est un oubli de clé étrangère.', TRUE, TRUE, 4),
(3, 2, 'Merci Prof ! Ça marche maintenant.', FALSE, FALSE, 1),
(3, 1, 'Super, merci beaucoup !', FALSE, FALSE, 0),
(4, 6, 'Utilise les API Resources + Sanctum. Voici la doc officielle.', TRUE, FALSE, 3),
(4, 1, 'Très utile, merci Professeur !', FALSE, FALSE, 1),
(5, 2, 'Je suis intéressée ! On peut en discuter.', FALSE, FALSE, 2),
(6, 1, 'Merci Awa, trop cool !', FALSE, FALSE, 1),
(6, 2, 'Parfait pour réviser, merci la chef de classe.', FALSE, FALSE, 2);

-- REACTIONS
INSERT INTO reactions (utilisateur_id, type_cible, cible_id, type_reaction) VALUES
(2,'publication',3,'utile'),(4,'publication',3,'like'),(6,'publication',3,'utile'),
(1,'commentaire',1,'like'),(2,'commentaire',1,'like'),(4,'commentaire',1,'like'),(5,'commentaire',1,'like'),
(1,'commentaire',2,'like'),
(3,'commentaire',4,'like'),(1,'commentaire',4,'like'),(2,'commentaire',4,'like'),
(1,'publication',4,'like'),(3,'publication',6,'like'),
(1,'publication',6,'utile'),(2,'publication',6,'like'),
(1,'commentaire',6,'like'),(4,'commentaire',6,'like');

-- PIECES JOINTES
INSERT INTO pieces_jointes (type_cible, cible_id, nom_fichier, chemin_fichier, type_mime, taille) VALUES
('publication', 6, 'annales_bdd_2025.pdf', '/uploads/annales_bdd_2025.pdf', 'application/pdf', 245760),
('commentaire', 1, 'exemple_migration.php', '/uploads/exemple_migration.php', 'application/x-php', 2048),
('publication', 3, 'screenshot_erreur.png', '/uploads/screenshot_erreur.png', 'image/png', 153600);

-- PUBLICATION_TAGS
INSERT INTO publication_tags (publication_id, tag_id) VALUES
(3,1),(3,2),(3,3),(3,15),
(4,1),(4,14),
(5,8),
(6,3),(6,6),
(7,11);

-- SIGNALEMENTS
INSERT INTO signalements (auteur_id, type_cible, cible_id, motif, statut) VALUES
(2, 'commentaire', 3, 'Message un peu agressif, pourrait être reformulé.', 'en_attente');

-- NOTIFICATIONS
INSERT INTO notifications (utilisateur_id, type_notification, contenu, lien, lu) VALUES
(1, 'reponse', 'Dr. Ibrahim Koné a répondu à votre discussion "Erreur SQL dans mon TP Laravel"', '/publication/3', FALSE),
(1, 'solution', 'Un commentaire a été marqué comme solution sous votre publication', '/publication/3', FALSE),
(1, 'reaction', 'Sarah Traoré a trouvé votre publication utile', '/publication/3', TRUE),
(5, 'mention', 'Vous avez été mentionné dans une discussion de la Délégation', '/publication/9', FALSE),
(2, 'reponse', 'Momo Kouassi a commenté la publication "Recherche de binôme"', '/publication/5', FALSE),
(8, 'demande_creation_zone', 'Nouvelle filière demandée : l''étudiant Amadou Bakayoko s''est inscrit en "Génie Mécanique" qui n''a pas encore de salon. Veuillez créer la zone.', '/admin/zones', FALSE);

-- MOTS INTERDITS
INSERT INTO mots_interdits (mot, type_mot, action) VALUES
('insulte', 'insulte', 'bloquer'),
('spam', 'spam', 'signaler'),
('pub', 'spam', 'signaler'),
('arnaque', 'suspect', 'signaler');


-- ============================================================
-- RÉSUMÉ DES TYPES DE PUBLICATION
-- ============================================================
-- discussion : commentaires ouverts + likes → le plus liké = solution
-- question   : sondage (options + votes) — pas de commentaires libres
-- annonce    : commentaires fermés par défaut
-- ============================================================
-- COMPTES DE TEST (mot de passe = password123)
-- ============================================================
-- Étudiant          : momo.kouassi@epi-yakro.ci
-- Chef de classe    : awa.nguessan@epi-yakro.ci
-- Délégué Général   : kofi.yao@epi-yakro.ci
-- Professeur        : ibrahim.kone@epi-yakro.ci
-- Administrateur    : admin@epi-yakro.ci
-- Modérateur        : paul.modo@epi-yakro.ci
-- Étudiant (sans zone) : amadou.bakayoko@epi-yakro.ci
-- ============================================================
