-- Normalise les responsabilites scolaires et enforce leur attribution unique.
-- En cas de plusieurs delegues generaux existants, preserve le compte ayant
-- l'identifiant utilisateur le plus eleve, puis une seule zone de delegation.

ALTER TABLE appartenance_zones
    MODIFY role_dans_zone ENUM('membre','chef_de_classe','delegue_de_classe','delegue_general') NOT NULL DEFAULT 'membre',
    ADD COLUMN delegation_exception BOOLEAN NOT NULL DEFAULT FALSE;

UPDATE appartenance_zones
SET role_dans_zone = 'delegue_de_classe'
WHERE role_dans_zone = 'chef_de_classe';

UPDATE appartenance_zones
SET delegation_exception = FALSE
WHERE role_dans_zone <> 'delegue_de_classe';

UPDATE appartenance_zones membership
JOIN zones zone ON zone.id = membership.zone_id
LEFT JOIN utilisateurs u ON u.id = membership.utilisateur_id
LEFT JOIN filieres own_filiere ON own_filiere.id = u.filiere_id
LEFT JOIN zones own_zone
  ON own_zone.nom = CONCAT('Filière ', own_filiere.nom)
 AND own_zone.type_zone = 'filiere'
LEFT JOIN filieres named_filiere
  ON LOWER(TRIM(named_filiere.nom)) = LOWER(TRIM(u.filiere))
LEFT JOIN zones named_zone
  ON named_zone.nom = CONCAT('Filière ', named_filiere.nom)
 AND named_zone.type_zone = 'filiere'
SET membership.role_dans_zone = 'membre'
WHERE membership.role_dans_zone = 'delegue_de_classe'
  AND (
      u.role <> 'etudiant'
      OR zone.type_zone <> 'filiere'
      OR COALESCE(own_filiere.zone_id, own_zone.id, named_filiere.zone_id, named_zone.id) IS NULL
      OR COALESCE(own_filiere.zone_id, own_zone.id, named_filiere.zone_id, named_zone.id) <> zone.id
  );

UPDATE appartenance_zones membership
JOIN (
    SELECT class_role.zone_id, MAX(class_role.utilisateur_id) AS utilisateur_id
    FROM appartenance_zones class_role
    WHERE class_role.role_dans_zone = 'delegue_de_classe'
    GROUP BY class_role.zone_id
) chosen ON chosen.zone_id = membership.zone_id
SET membership.role_dans_zone = 'membre'
WHERE membership.role_dans_zone = 'delegue_de_classe'
  AND membership.utilisateur_id <> chosen.utilisateur_id;

UPDATE appartenance_zones membership
JOIN zones zone ON zone.id = membership.zone_id
LEFT JOIN utilisateurs u ON u.id = membership.utilisateur_id
SET membership.role_dans_zone = 'membre'
WHERE membership.role_dans_zone = 'delegue_general'
  AND (u.role <> 'etudiant' OR zone.type_zone <> 'delegation' OR zone.est_active = 0);

UPDATE appartenance_zones membership
JOIN (
    SELECT selected.utilisateur_id, MIN(selected.zone_id) AS zone_id
    FROM appartenance_zones selected
    JOIN zones selected_zone ON selected_zone.id = selected.zone_id
    WHERE selected.role_dans_zone = 'delegue_general'
      AND selected_zone.type_zone = 'delegation'
      AND selected_zone.est_active = 1
      AND selected.utilisateur_id = (
          SELECT MAX(candidate.utilisateur_id)
          FROM appartenance_zones candidate
          JOIN zones candidate_zone ON candidate_zone.id = candidate.zone_id
          WHERE candidate.role_dans_zone = 'delegue_general'
            AND candidate_zone.type_zone = 'delegation'
            AND candidate_zone.est_active = 1
      )
    GROUP BY selected.utilisateur_id
) chosen ON chosen.utilisateur_id = membership.utilisateur_id
SET membership.role_dans_zone = 'membre'
WHERE membership.role_dans_zone = 'delegue_general'
  AND membership.zone_id <> chosen.zone_id;

UPDATE appartenance_zones membership
JOIN (
    SELECT MAX(candidate.utilisateur_id) AS utilisateur_id
    FROM appartenance_zones candidate
    JOIN zones candidate_zone ON candidate_zone.id = candidate.zone_id
    WHERE candidate.role_dans_zone = 'delegue_general'
      AND candidate_zone.type_zone = 'delegation'
      AND candidate_zone.est_active = 1
) chosen ON chosen.utilisateur_id IS NOT NULL
SET membership.role_dans_zone = 'membre'
WHERE membership.role_dans_zone = 'delegue_general'
  AND membership.utilisateur_id <> chosen.utilisateur_id;

INSERT IGNORE INTO appartenance_zones (utilisateur_id, zone_id, role_dans_zone)
SELECT DISTINCT class_membership.utilisateur_id, delegation_zone.id, 'membre'
FROM appartenance_zones class_membership
JOIN zones class_zone
  ON class_zone.id = class_membership.zone_id
 AND class_zone.type_zone = 'filiere'
 AND class_zone.est_active = 1
JOIN zones delegation_zone
  ON delegation_zone.id = (
      SELECT MIN(id) FROM zones
      WHERE type_zone = 'delegation' AND est_active = 1
  )
JOIN utilisateurs u
  ON u.id = class_membership.utilisateur_id
 AND u.role = 'etudiant'
WHERE class_membership.role_dans_zone = 'delegue_de_classe'
;

ALTER TABLE appartenance_zones
    MODIFY role_dans_zone ENUM('membre','delegue_de_classe','delegue_general') NOT NULL DEFAULT 'membre',
    ADD COLUMN delegue_general_slot TINYINT DEFAULT NULL,
    ADD COLUMN delegue_classe_zone_id INT UNSIGNED DEFAULT NULL,
    ADD UNIQUE KEY uq_appartenance_global_delegate (delegue_general_slot);

UPDATE appartenance_zones
SET delegue_classe_zone_id = CASE
    WHEN role_dans_zone = 'delegue_de_classe' THEN zone_id
    ELSE NULL
END,
    delegue_general_slot = CASE
        WHEN role_dans_zone = 'delegue_general' THEN 1
        ELSE NULL
    END
WHERE role_dans_zone = 'delegue_de_classe';

UPDATE appartenance_zones
SET delegue_general_slot = 1
WHERE role_dans_zone = 'delegue_general';

ALTER TABLE appartenance_zones
    ADD UNIQUE KEY uq_appartenance_class_delegate_zone (delegue_classe_zone_id);
