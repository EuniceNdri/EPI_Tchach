<?php

require_once ROOT_PATH . '/models/Attachment.php';

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
        $canViewAll = in_array(strtolower(trim($role)), ['administrateur', 'admin', 'moderateur'], true);

        $statement = $pdo->prepare(
            "SELECT attachment.type_cible, attachment.nom_fichier, attachment.chemin_fichier,
                    attachment.type_mime, attachment.taille,
                    publication.utilisateur_id AS publication_owner,
                    publication.est_signale AS publication_reported,
                    comment.utilisateur_id AS comment_owner,
                    comment.est_signale AS comment_reported,
                    category.est_active AS category_active,
                    zone.id AS zone_id, zone.est_active AS zone_active
             FROM pieces_jointes attachment
             LEFT JOIN publications direct_publication
               ON attachment.type_cible = 'publication'
              AND direct_publication.id = attachment.cible_id
             LEFT JOIN commentaires comment
               ON attachment.type_cible = 'commentaire'
              AND comment.id = attachment.cible_id
             JOIN publications publication
               ON publication.id = COALESCE(direct_publication.id, comment.publication_id)
             JOIN categories category ON category.id = publication.categorie_id
             JOIN zones zone ON zone.id = category.zone_id
             WHERE attachment.id = :id
             LIMIT 1"
        );
        $statement->execute(['id' => $attachmentId]);
        $attachment = $statement->fetch();
        if (!$attachment) {
            http_response_code(404);
            exit('Pièce jointe introuvable.');
        }

        $isOwner = $attachment['type_cible'] === 'publication'
            ? (int)$attachment['publication_owner'] === $userId
            : (int)($attachment['comment_owner'] ?? 0) === $userId;
        if (!$isOwner) {
            $hasZoneAccess = false;
            if ($canViewAll && (int)$attachment['zone_active'] === 1 && (int)$attachment['category_active'] === 1) {
                $hasZoneAccess = true;
            } elseif ((int)$attachment['zone_active'] === 1 && (int)$attachment['category_active'] === 1
                && !(bool)$attachment['publication_reported'] && !(bool)($attachment['comment_reported'] ?? false)) {
                $membership = $pdo->prepare(
                    'SELECT 1 FROM appartenance_zones WHERE utilisateur_id = :user_id AND zone_id = :zone_id LIMIT 1'
                );
                $membership->execute(['user_id' => $userId, 'zone_id' => (int)$attachment['zone_id']]);
                $hasZoneAccess = (bool)$membership->fetchColumn();
            }
            if (!$hasZoneAccess) {
                http_response_code(403);
                exit('Vous n’avez pas accès à ce fichier.');
            }
        }

        if ($inline && !in_array($attachment['type_mime'], ['image/png', 'image/jpeg', 'image/gif', 'application/pdf'], true)) {
            http_response_code(415);
            exit('Aperçu indisponible pour ce format de fichier.');
        }

        $uploadDirectory = realpath(ROOT_PATH . '/public/uploads');
        $filePath = realpath(ROOT_PATH . '/public/' . ltrim($attachment['chemin_fichier'], '/'));
        if (!$uploadDirectory || !$filePath || !str_starts_with($filePath, $uploadDirectory . DIRECTORY_SEPARATOR) || !is_file($filePath)) {
            http_response_code(404);
            exit('Fichier indisponible.');
        }

        header('Content-Type: ' . $attachment['type_mime']);
        header('Content-Length: ' . (int)$attachment['taille']);
        $safeFilename = preg_replace('/[\r\n"\\\\]/', '_', (string)$attachment['nom_fichier']);
        header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename="' . $safeFilename . '"; filename*=UTF-8\'\'' . rawurlencode((string)$attachment['nom_fichier']));
        header('X-Content-Type-Options: nosniff');
        readfile($filePath);
        exit;
    }
}
