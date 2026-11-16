-- Migration : Ajout des 16 filières officielles du BTS en Côte d'Ivoire
-- et initialisation de leurs groupes de messagerie respectifs.

INSERT INTO filieres (nom, code, description, slogan, image_url, est_active) VALUES
-- 1. Filières Industrielles et Techniques
('Agriculture Tropicale option Production Animale', 'ATPA', 'Élevage, santé animale, aviculture et gestion de fermes agricoles.', 'Élevage, santé animale et agrobusiness moderne', 'images/filiere-industrielle.jpg', 1),
('Agriculture Tropicale option Production Végétale', 'ATPV', 'Agronomie, cultures vivrières et d\'exportation, gestion d\'exploitations agricoles.', 'Agronomie, cultures vivrières et développement agricole', 'images/filiere-industrielle.jpg', 1),
('Gestion de l\'Environnement et des Ressources Naturelles', 'GERNA', 'Écologie, traitement des déchets, préservation des écosystèmes et développement durable.', 'Écologie, préservation des écosystèmes et développement durable', 'images/filiere-industrielle.jpg', 1),
('Génie Civil option Bâtiment', 'GBAT', 'Conception de plans, calcul de structures, suivi et réalisation de chantiers de construction.', 'Conception de plans, calcul de structures et chantiers durables', 'images/filiere-industrielle.jpg', 1),
('Génie Civil option Travaux Publics', 'GTP', 'Construction et entretien des routes, ponts, barrages et réseaux d\'assainissement.', 'Grandes infrastructures, voiries, ponts et assainissement', 'images/filiere-industrielle.jpg', 1),
('Génie Civil option Géomètre Topographe', 'GGT', 'Relevés de terrains, cartographie, bornage et implantation d\'ouvrages.', 'Topographie de pointe, cartographie et bornage territorial', 'images/filiere-industrielle.jpg', 1),
('Informatique Développeur d\'Applications', 'IDA', 'Conception de logiciels, développement web et mobile, bases de données.', 'Développement logiciel, web, mobile et bases de données', 'images/filiere-industrielle.jpg', 1),
('Réseaux Informatiques et Télécommunications', 'RIT', 'Installation, administration et sécurité des réseaux informatiques et systèmes télécoms.', 'Administration réseaux, cybersécurité et télécommunications', 'images/filiere-industrielle.jpg', 1),
('Électrotechnique', 'ELT', 'Installation, distribution et maintenance des équipements électriques industriels et domestiques.', 'Énergie électrique, automatismes et maintenance industrielle', 'images/filiere-industrielle.jpg', 1),
('Maintenance des Systèmes de Production', 'MSP', 'Entretien, diagnostic et réparation des machines et lignes de production en usine.', 'Diagnostic machine, maintenance avancée et productivité industrielle', 'images/filiere-industrielle.jpg', 1),

-- 2. Filières Tertiaires et de Gestion
('Ressources Humaines et Communication', 'RHCOM', 'Recrutement, gestion du personnel, paie et communication interne ou externe.', 'Capital humain, relations professionnelles et communication stratégique', 'images/filiere-tertiaire.jpg', 1),
('Assistanat de Direction', 'AD', 'Organisation administrative, secrétariat de haut niveau, gestion d\'agenda et communication professionnelle.', 'Excellence administrative, secrétariat de haut niveau et coordination', 'images/filiere-tertiaire.jpg', 1),
('Tourisme - Hôtellerie', 'TH', 'Gestion hôtelière, restauration, organisation de voyages et accueil touristique.', 'Accueil haut de gamme, hébergement et management touristique', 'images/filiere-tertiaire.jpg', 1),
('Logistique', 'LOG', 'Gestion des stocks, approvisionnement, transport de marchandises et chaîne logistique.', 'Supply chain, optimisation des flux et gestion des transports', 'images/filiere-tertiaire.jpg', 1),
('Finances Comptabilité et Gestion des Entreprises', 'FCGE', 'Tenue comptable, fiscalité, audit et analyse financière.', 'Gestion financière, audit, fiscalité et pilotage d\'entreprise', 'images/filiere-tertiaire.jpg', 1),
('Gestion Commerciale', 'GEC', 'Marketing, techniques de vente, négociation commerciale et relation client.', 'Marketing opérationnel, négociation commerciale et relation client', 'images/filiere-tertiaire.jpg', 1)
ON DUPLICATE KEY UPDATE
    code = VALUES(code),
    description = VALUES(description),
    slogan = VALUES(slogan),
    image_url = VALUES(image_url),
    est_active = VALUES(est_active);

-- Création / synchronisation automatique des groupes de messagerie
INSERT INTO filiere_message_groups (filiere_id)
SELECT id FROM filieres WHERE est_active = 1
ON DUPLICATE KEY UPDATE filiere_id = VALUES(filiere_id);

-- Rapprochement des étudiants existants avec leur filière
UPDATE utilisateurs u
INNER JOIN filieres f ON (
    f.nom = TRIM(u.filiere)
    OR f.code = TRIM(u.filiere)
    OR CONCAT('BTS ', f.code) = TRIM(u.filiere)
    OR TRIM(u.filiere) LIKE CONCAT('%', f.code, '%')
)
SET u.filiere_id = f.id
WHERE u.role = 'etudiant' AND (u.filiere_id IS NULL OR u.filiere_id = 0);

