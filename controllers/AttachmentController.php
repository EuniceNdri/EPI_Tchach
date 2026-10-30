<?php

require_once ROOT_PATH . '/models/Attachment.php';

class AttachmentController
{
    public function download(int $attachmentId): void
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

        $statement = $pdo->prepare(
            'SELECT nom_fichier, chemin_fichier, type_mime, taille
             FROM pieces_jointes WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $attachmentId]);
        $attachment = $statement->fetch();
        if (!$attachment) {
            http_response_code(404);
            exit('Pièce jointe introuvable.');
        }

        $uploadDirectory = realpath(ROOT_PATH . '/public/uploads');
        $filePath = realpath(ROOT_PATH . '/public/' . ltrim($attachment['chemin_fichier'], '/'));
        if (!$uploadDirectory || !$filePath || !str_starts_with($filePath, $uploadDirectory . DIRECTORY_SEPARATOR) || !is_file($filePath)) {
            http_response_code(404);
            exit('Fichier indisponible.');
        }

        header('Content-Type: ' . $attachment['type_mime']);
        header('Content-Length: ' . (int)$attachment['taille']);
        header('Content-Disposition: attachment; filename="' . addcslashes($attachment['nom_fichier'], '"\\') . '"');
        header('X-Content-Type-Options: nosniff');
        readfile($filePath);
        exit;
    }
}
