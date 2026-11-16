<?php

require_once ROOT_PATH . '/app/Models/Publications/Attachment.php';
require_once ROOT_PATH . '/app/Models/Moderation/RoleAccess.php';

class AttachmentController
{
    public function download(int $attachmentId): void
    {
        $this->send($attachmentId, false);
    }

    public function view(int $attachmentId): void
    {
        $this->send($attachmentId, true);
    }

    private function send(int $attachmentId, bool $inline): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        if (empty($_SESSION['user_id'])) {
            http_response_code(401);
            exit('Connexion requise.');
        }

        global $pdo;
        if (!$pdo) {
            http_response_code(503);
            exit('La base de données est indisponible.');
        }

        $userId = (int)$_SESSION['user_id'];
        $roleStatement = $pdo->prepare('SELECT role FROM utilisateurs WHERE id = :user_id LIMIT 1');
        $roleStatement->execute(['user_id' => $userId]);
        $role = $roleStatement->fetchColumn();
        if (!is_string($role) || $role === '') {
            http_response_code(401);
            exit('Compte utilisateur introuvable.');
        }
        $canViewAll = RoleAccess::canViewAllZones($pdo, $userId);

        $statement = $pdo->prepare(
            "SELECT attachment.type_cible, attachment.cible_id, attachment.nom_fichier, attachment.chemin_fichier,
                    attachment.type_mime, attachment.taille,
                    publication.est_signale AS publication_reported,
                    comment.est_signale AS comment_reported,
                    category.est_active AS category_active,
                    COALESCE(publication.zone_id, zm.zone_id) AS zone_id,
                    COALESCE(zone.est_active, zm_zone.est_active) AS zone_active,
                    mp.expediteur_id, c.user1_id, c.user2_id
             FROM pieces_jointes attachment
             LEFT JOIN publications direct_publication
               ON attachment.type_cible = 'publication'
              AND direct_publication.id = attachment.cible_id
             LEFT JOIN commentaires comment
               ON attachment.type_cible = 'commentaire'
              AND comment.id = attachment.cible_id
             LEFT JOIN publications publication
               ON publication.id = COALESCE(direct_publication.id, comment.publication_id)
             LEFT JOIN zones zone ON zone.id = publication.zone_id
             LEFT JOIN categories category ON category.id = publication.categorie_id
             LEFT JOIN zone_messages zm ON attachment.type_cible = 'message_zone' AND zm.id = attachment.cible_id
             LEFT JOIN zones zm_zone ON zm_zone.id = zm.zone_id
             LEFT JOIN messages_prives mp ON attachment.type_cible = 'message_prive' AND mp.id = attachment.cible_id
             LEFT JOIN conversations c ON c.id = mp.conversation_id
             WHERE attachment.id = :id
             LIMIT 1"
        );
        $statement->execute(['id' => $attachmentId]);
        $attachment = $statement->fetch();
        if (!$attachment) {
            http_response_code(404);
            exit('Pièce jointe introuvable.');
        }

        $hasZoneAccess = false;
        if ($attachment['type_cible'] === 'message_prive') {
            if ($userId === (int)($attachment['user1_id'] ?? 0) || $userId === (int)($attachment['user2_id'] ?? 0)) {
                $hasZoneAccess = true;
            }
        } elseif ($attachment['type_cible'] === 'message_zone') {
            $effectiveZoneId = (int)$attachment['zone_id'];
            if ($canViewAll && (int)$attachment['zone_active'] === 1) {
                $hasZoneAccess = true;
            } elseif ((int)$attachment['zone_active'] === 1) {
                $membership = $pdo->prepare('SELECT 1 FROM appartenance_zones WHERE utilisateur_id = :user_id AND zone_id = :zone_id LIMIT 1');
                $membership->execute(['user_id' => $userId, 'zone_id' => $effectiveZoneId]);
                $hasZoneAccess = (bool)$membership->fetchColumn();
            }
        } else {
            $catActive = $attachment['category_active'] === null || (int)$attachment['category_active'] === 1;
            if ($canViewAll && (int)$attachment['zone_active'] === 1 && $catActive) {
                $hasZoneAccess = true;
            } elseif ((int)$attachment['zone_active'] === 1 && $catActive
                && !(bool)$attachment['publication_reported'] && !(bool)($attachment['comment_reported'] ?? false)) {
                $membership = $pdo->prepare(
                    'SELECT 1 FROM appartenance_zones WHERE utilisateur_id = :user_id AND zone_id = :zone_id LIMIT 1'
                );
                $membership->execute(['user_id' => $userId, 'zone_id' => (int)$attachment['zone_id']]);
                $hasZoneAccess = (bool)$membership->fetchColumn();
            }
        }
        if (!$hasZoneAccess) {
            http_response_code(403);
            exit('Vous n’avez pas accès à ce fichier.');
        }

        if ($inline && !in_array($attachment['type_mime'], ['image/png', 'image/jpeg', 'image/gif', 'image/webp', 'application/pdf'], true)) {
            http_response_code(415);
            exit('Aperçu indisponible pour ce format de fichier.');
        }

        $filePath = Attachment::resolveStoredPath((string)$attachment['chemin_fichier']);
        if ($filePath === null) {
            http_response_code(404);
            exit('Fichier indisponible.');
        }

        header('Content-Type: ' . $attachment['type_mime']);
        header('Content-Length: ' . (int)$attachment['taille']);
        $safeFilename = preg_replace('/[\r\n"\\\\]/', '_', (string)$attachment['nom_fichier']);
        header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename="' . $safeFilename . '"; filename*=UTF-8\'\'' . rawurlencode((string)$attachment['nom_fichier']));
        header('Cache-Control: private, no-store');
        header('X-Content-Type-Options: nosniff');
        readfile($filePath);
        exit;
    }
}
