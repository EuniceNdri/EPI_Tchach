-- ============================================================
--  FORUM ÉCOLE - Script SQL complet
--  Création des tables + Données de test
--  MySQL / MariaDB
-- ============================================================

DROP DATABASE IF EXISTS forum_ecole;
CREATE DATABASE forum_ecole CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE forum_ecole;

-- ============================================================
-- 1. TABLE utilisateurs
-- ============================================================
CREATE TABLE utilisateurs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    avatar_url VARCHAR(255) DEFAULT NULL,
    biographie TEXT DEFAULT NULL,
    role ENUM('etudiant', 'professeur', 'delegue', 'moderateur', 'administrateur') NOT NULL DEFAULT 'etudiant',
    date_inscription DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 2. TABLE zones
-- ============================================================
CREATE TABLE zones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    type_zone ENUM('generale', 'filiere', 'professeurs') NOT NULL,
    description TEXT DEFAULT NULL,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 3. TABLE appartenance_zones
-- ============================================================
CREATE TABLE appartenance_zones (
    utilisateur_id INT UNSIGNED NOT NULL,
    zone_id INT UNSIGNED NOT NULL,
    role_dans_zone ENUM('membre', 'moderateur_zone') NOT NULL DEFAULT 'membre',
    date_ajout DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (utilisateur_id, zone_id),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (zone_id) REFERENCES zones(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 4. TABLE categories
-- ============================================================
CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    zone_id INT UNSIGNED NOT NULL,
    nom VARCHAR(150) NOT NULL,
    slug VARCHAR(150) NOT NULL,
    description TEXT DEFAULT NULL,
    ordre INT NOT NULL DEFAULT 0,
    FOREIGN KEY (zone_id) REFERENCES zones(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 5. TABLE publications
-- ============================================================
CREATE TABLE publications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT UNSIGNED NOT NULL,
    categorie_id INT UNSIGNED NOT NULL,
    titre VARCHAR(255) NOT NULL,
    contenu TEXT NOT NULL,
    type_publication ENUM('discussion', 'question', 'annonce') NOT NULL DEFAULT 'discussion',
    statut ENUM('ouvert', 'resolu', 'ferme', 'epingle') NOT NULL DEFAULT 'ouvert',
    commentaires_fermes BOOLEAN NOT NULL DEFAULT FALSE,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 6. TABLE commentaires
-- ============================================================
CREATE TABLE commentaires (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    publication_id INT UNSIGNED NOT NULL,
    utilisateur_id INT UNSIGNED NOT NULL,
    contenu TEXT NOT NULL,
    est_solution BOOLEAN NOT NULL DEFAULT FALSE,
    est_epingle BOOLEAN NOT NULL DEFAULT FALSE,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (publication_id) REFERENCES publications(id) ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 7. TABLE reactions
-- ============================================================
CREATE TABLE reactions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT UNSIGNED NOT NULL,
    type_cible ENUM('publication', 'commentaire') NOT NULL,
    cible_id INT UNSIGNED NOT NULL,
    type_reaction ENUM('like', 'utile', 'jadore') NOT NULL DEFAULT 'like',
    date_reaction DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_reaction (utilisateur_id, type_cible, cible_id),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 8. TABLE pieces_jointes
-- ============================================================
CREATE TABLE pieces_jointes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type_cible ENUM('publication', 'commentaire') NOT NULL,
    cible_id INT UNSIGNED NOT NULL,
    nom_fichier VARCHAR(255) NOT NULL,
    chemin_fichier VARCHAR(500) NOT NULL,
    type_mime VARCHAR(100) NOT NULL,
    taille INT UNSIGNED NOT NULL DEFAULT 0,
    date_upload DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 9. TABLE tags
-- ============================================================
CREATE TABLE tags (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ============================================================
-- 10. TABLE publication_tags
-- ============================================================
CREATE TABLE publication_tags (
    publication_id INT UNSIGNED NOT NULL,
    tag_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (publication_id, tag_id),
    FOREIGN KEY (publication_id) REFERENCES publications(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 11. TABLE signalements
-- ============================================================
CREATE TABLE signalements (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    auteur_id INT UNSIGNED NOT NULL,
    type_cible ENUM('publication', 'commentaire') NOT NULL,
    cible_id INT UNSIGNED NOT NULL,
    motif TEXT NOT NULL,
    statut ENUM('en_attente', 'traite', 'rejete') NOT NULL DEFAULT 'en_attente',
    date_signalement DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (auteur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 12. TABLE notifications
-- ============================================================
CREATE TABLE notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT UNSIGNED NOT NULL,
    type_notification VARCHAR(50) NOT NULL,
    contenu VARCHAR(500) NOT NULL,
    lien VARCHAR(255) DEFAULT NULL,
    lu BOOLEAN NOT NULL DEFAULT FALSE,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- INDEX pour améliorer les performances
-- ============================================================
CREATE INDEX idx_publications_categorie ON publications(categorie_id);
CREATE INDEX idx_publications_utilisateur ON publications(utilisateur_id);
CREATE INDEX idx_commentaires_publication ON commentaires(publication_id);
CREATE INDEX idx_reactions_cible ON reactions(type_cible, cible_id);
CREATE INDEX idx_pieces_cible ON pieces_jointes(type_cible, cible_id);


-- ############################################################
--                    DONNÉES DE TEST
-- ############################################################

-- ============================================================
-- UTILISATEURS (mot de passe = "password123" hashé en bcrypt)
-- ============================================================
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, biographie) VALUES
('Kouassi', 'Momo', 'momo.kouassi@epi-yakro.ci', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'etudiant', 'Étudiant en BTS IDA, passionné de développement web.'),
('Traoré', 'Sarah', 'sarah.traore@epi-yakro.ci', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'etudiant', 'Étudiante en BTS IDA.'),
('Diallo', 'Jean', 'jean.diallo@epi-yakro.ci', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'etudiant', 'Étudiant en Licence Informatique.'),
('N''Guessan', 'Awa', 'awa.nguessan@epi-yakro.ci', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'delegue', 'Déléguée de la classe BTS IDA.'),
('Koné', 'Dr. Ibrahim', 'ibrahim.kone@epi-yakro.ci', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'professeur', 'Professeur de Développement Web et Base de données.'),
('Bamba', 'Fatou', 'fatou.bamba@epi-yakro.ci', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'professeur', 'Professeure de Réseaux et Systèmes.'),
('Admin', 'Système', 'admin@epi-yakro.ci', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrateur', 'Compte administrateur principal.'),
('Modo', 'Paul', 'paul.modo@epi-yakro.ci', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'moderateur', 'Modérateur de la plateforme.');

-- ============================================================
-- ZONES
-- ============================================================
INSERT INTO zones (nom, type_zone, description) VALUES
('Zone Générale - Vie de l''École', 'generale', 'Espace public pour les annonces officielles, la vie étudiante et les événements de l''école.'),
('Filière BTS IDA', 'filiere', 'Espace dédié aux étudiants et professeurs de la filière BTS Informatique de Développement d''Applications.'),
('Filière Licence Informatique', 'filiere', 'Espace dédié aux étudiants de Licence Informatique.'),
('Espace Professeurs', 'professeurs', 'Zone restreinte réservée aux enseignants pour les échanges pédagogiques.');

-- ============================================================
-- APPARTENANCE AUX ZONES
-- ============================================================
INSERT INTO appartenance_zones (utilisateur_id, zone_id, role_dans_zone) VALUES
-- Tout le monde dans Zone Générale
(1, 1, 'membre'), (2, 1, 'membre'), (3, 1, 'membre'), (4, 1, 'membre'),
(5, 1, 'membre'), (6, 1, 'membre'), (7, 1, 'moderateur_zone'), (8, 1, 'moderateur_zone'),
-- BTS IDA
(1, 2, 'membre'), (2, 2, 'membre'), (4, 2, 'moderateur_zone'), (5, 2, 'moderateur_zone'),
-- Licence Info
(3, 3, 'membre'), (6, 3, 'moderateur_zone'),
-- Espace Professeurs
(5, 4, 'membre'), (6, 4, 'membre'), (7, 4, 'moderateur_zone');

-- ============================================================
-- CATEGORIES
-- ============================================================
INSERT INTO categories (zone_id, nom, slug, description, ordre) VALUES
-- Zone Générale
(1, 'Annonces Officielles', 'annonces-officielles', 'Communications de la direction et de l''administration.', 1),
(1, 'Vie Étudiante & Événements', 'vie-etudiante', 'Discussions générales, clubs, activités extrascolaires.', 2),
(1, 'Boîte à idées', 'boite-a-idees', 'Suggestions pour améliorer la vie au sein de l''école.', 3),
-- Filière BTS IDA
(2, 'Cours & TP', 'cours-tp-ida', 'Questions et entraide sur les modules de cours et travaux pratiques.', 1),
(2, 'Projets de Classe', 'projets-ida', 'Partage d''idées et suivi des projets tuteurés.', 2),
(2, 'Examens & Révisions', 'examens-ida', 'Annales, fiches de révision et entraide avant les partiels.', 3),
-- Licence Informatique
(3, 'Cours & TP', 'cours-tp-licence', 'Questions sur les modules de Licence.', 1),
(3, 'Projets', 'projets-licence', 'Projets de fin d''année et travaux de groupe.', 2),
-- Espace Professeurs
(4, 'Pédagogie & Syllabus', 'pedagogie', 'Échanges entre enseignants sur les programmes et méthodes.', 1),
(4, 'Organisation', 'organisation-profs', 'Coordination et planning entre professeurs.', 2);

-- ============================================================
-- TAGS
-- ============================================================
INSERT INTO tags (nom) VALUES
('laravel'), ('php'), ('mysql'), ('javascript'), ('html-css'),
('examen'), ('tp'), ('projet'), ('stage'), ('reseaux'),
('python'), ('fastapi'), ('git'), ('api'), ('debug');

-- ============================================================
-- PUBLICATIONS
-- ============================================================
INSERT INTO publications (utilisateur_id, categorie_id, titre, contenu, type_publication, statut) VALUES
-- Annonces
(7, 1, 'Rentrée 2026 - Informations importantes', 'Chers étudiants, la rentrée officielle est fixée au 5 octobre 2026. Merci de consulter le planning sur l''intranet.', 'annonce', 'epingle'),
(5, 1, 'Remise des notes du premier contrôle', 'Les notes du contrôle de Développement Web seront disponibles dès demain sur la plateforme.', 'annonce', 'ouvert'),

-- Questions BTS IDA
(1, 4, 'Erreur SQL dans mon TP Laravel', 'Bonjour, j''ai une erreur "SQLSTATE[42S22]: Column not found" quand j''essaie de faire une jointure. Voici mon code... Quelqu''un peut m''aider ?', 'question', 'ouvert'),
(2, 4, 'Comment faire une API REST avec Laravel ?', 'Je dois créer une API pour mon projet. Quelles sont les bonnes pratiques pour les routes et les controllers ?', 'question', 'resolu'),
(1, 5, 'Recherche de binôme pour le projet tuteuré', 'Salut, je cherche un binôme motivé pour le projet de fin d''année (sujet : plateforme de gestion). Contactez-moi !', 'discussion', 'ouvert'),
(4, 6, 'Annales de l''examen de Base de données 2025', 'Voici les sujets de l''année dernière pour vous entraîner. Bonne révision à tous !', 'discussion', 'epingle'),

-- Licence
(3, 7, 'Problème avec les threads en Python', 'J''ai un souci de concurrence dans mon programme multi-thread. Est-ce que quelqu''un a déjà rencontré ce problème ?', 'question', 'ouvert'),

-- Professeurs
(5, 9, 'Proposition de modification du syllabus BTS IDA', 'Bonjour collègues, je propose d''ajouter un module sur les API modernes (FastAPI / Laravel Sanctum). Qu''en pensez-vous ?', 'discussion', 'ouvert');

-- ============================================================
-- COMMENTAIRES
-- ============================================================
INSERT INTO commentaires (publication_id, utilisateur_id, contenu, est_solution, est_epingle) VALUES
-- Sous la question Laravel SQL
(3, 5, 'Vérifie bien le nom de ta colonne dans la migration. Souvent c''est un problème de majuscule/minuscule ou un oubli de $table->foreign().', TRUE, TRUE),
(3, 2, 'Merci Prof ! Effectivement j''avais oublié de spécifier la clé étrangère. Ça marche maintenant.', FALSE, FALSE),
(3, 1, 'Super, merci beaucoup pour l''aide !', FALSE, FALSE),

-- Sous la question API Laravel
(4, 5, 'Utilise les API Resources de Laravel + Sanctum pour l''authentification. Je te mets un lien vers la doc officielle.', TRUE, FALSE),
(4, 1, 'Très utile, merci Professeur !', FALSE, FALSE),

-- Sous recherche de binôme
(5, 2, 'Je suis intéressée ! On peut en discuter en privé.', FALSE, FALSE),

-- Sous annales
(6, 1, 'Merci Awa, trop cool !', FALSE, FALSE),
(6, 2, 'Parfait pour réviser, merci la déléguée.', FALSE, FALSE);

-- ============================================================
-- REACTIONS
-- ============================================================
INSERT INTO reactions (utilisateur_id, type_cible, cible_id, type_reaction) VALUES
(2, 'publication', 3, 'utile'),
(4, 'publication', 3, 'like'),
(5, 'publication', 3, 'utile'),
(1, 'commentaire', 1, 'jadore'),
(2, 'commentaire', 1, 'utile'),
(1, 'publication', 4, 'like'),
(3, 'publication', 6, 'like'),
(1, 'publication', 6, 'utile'),
(2, 'publication', 6, 'like');

-- ============================================================
-- PIECES JOINTES
-- ============================================================
INSERT INTO pieces_jointes (type_cible, cible_id, nom_fichier, chemin_fichier, type_mime, taille) VALUES
('publication', 6, 'annales_bdd_2025.pdf', '/uploads/annales_bdd_2025.pdf', 'application/pdf', 245760),
('commentaire', 1, 'exemple_migration.php', '/uploads/exemple_migration.php', 'application/x-php', 2048),
('publication', 3, 'screenshot_erreur.png', '/uploads/screenshot_erreur.png', 'image/png', 153600);

-- ============================================================
-- PUBLICATION_TAGS
-- ============================================================
INSERT INTO publication_tags (publication_id, tag_id) VALUES
(3, 1), (3, 2), (3, 3), (3, 15),  -- laravel, php, mysql, debug
(4, 1), (4, 14),                    -- laravel, api
(5, 8),                             -- projet
(6, 3), (6, 6),                     -- mysql, examen
(7, 11);                            -- python

-- ============================================================
-- SIGNALEMENTS
-- ============================================================
INSERT INTO signalements (auteur_id, type_cible, cible_id, motif, statut) VALUES
(2, 'commentaire', 3, 'Message un peu agressif, pourrait être reformulé.', 'en_attente');

-- ============================================================
-- NOTIFICATIONS
-- ============================================================
INSERT INTO notifications (utilisateur_id, type_notification, contenu, lien, lu) VALUES
(1, 'reponse', 'Dr. Ibrahim Koné a répondu à votre question "Erreur SQL dans mon TP Laravel"', '/publication/3', FALSE),
(1, 'solution', 'Votre question a été marquée comme résolue', '/publication/3', FALSE),
(1, 'reaction', 'Sarah Traoré a trouvé votre publication utile', '/publication/3', TRUE),
(5, 'mention', 'Vous avez été mentionné dans une discussion', '/publication/9', FALSE),
(2, 'reponse', 'Momo Kouassi a commenté la publication "Recherche de binôme"', '/publication/5', FALSE);

-- ============================================================
-- FIN DU SCRIPT
-- ============================================================
-- Compte de test :
-- Email : momo.kouassi@epi-yakro.ci
-- Mot de passe : password123
-- ============================================================
