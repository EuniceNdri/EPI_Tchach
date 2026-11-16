-- Aligne les roles de compte et les responsabilites par zone sur les roles EPI Tchach.
-- Les anciens moderateurs deviennent des membres ordinaires. Les roles de zone
-- obsoletes sont retires sans accorder de nouveaux droits de moderation.

INSERT INTO appartenance_zones (utilisateur_id, zone_id, role_dans_zone, date_ajout)
SELECT u.id, delegation_zone.id, 'delegue_general', NOW()
FROM utilisateurs u
JOIN zones delegation_zone
  ON delegation_zone.id = (
      SELECT MIN(id) FROM zones WHERE type_zone = 'delegation' AND est_active = 1
  )
WHERE u.role = 'delegue_general'
ON DUPLICATE KEY UPDATE role_dans_zone = 'delegue_general';

UPDATE utilisateurs
SET role = 'etudiant'
WHERE role IN ('moderateur', 'delegue_general');

UPDATE appartenance_zones
SET role_dans_zone = 'membre'
WHERE role_dans_zone IN ('sous_chef', 'sous_delegue', 'moderateur_zone');

UPDATE appartenance_zones membership
JOIN zones zone ON zone.id = membership.zone_id
SET membership.role_dans_zone = 'membre'
WHERE (membership.role_dans_zone = 'chef_de_classe' AND zone.type_zone <> 'filiere')
   OR (membership.role_dans_zone = 'delegue_general' AND zone.type_zone <> 'delegation');

ALTER TABLE utilisateurs
    MODIFY role ENUM('etudiant','professeur','administrateur') NOT NULL DEFAULT 'etudiant';

ALTER TABLE appartenance_zones
    MODIFY role_dans_zone ENUM('membre','chef_de_classe','delegue_general') NOT NULL DEFAULT 'membre';
