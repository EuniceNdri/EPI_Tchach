<?php

/**
 * Service de modération automatique
 * Gère la détection et le traitement des mots interdits dans les contenus
 */
class ModerationService {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Analyse un contenu et retourne le résultat de l'analyse
     * Retourne : ['status' => 'ok'|'bloquer'|'signaler', 'content' => string]
     */
    public function analyser(string $contenu): array {
        $motsInterdits = $this->getMotsInterdits();

        $resultat = $contenu;
        $actionFinale = 'ok';

        foreach ($motsInterdits as $item) {
            $mot = $item['mot'];
            if (mb_stripos($resultat, $mot) !== false) {
                switch ($item['action']) {
                    case 'bloquer':
                        return ['status' => 'bloquer', 'content' => $resultat];
                    case 'signaler':
                        $actionFinale = 'signaler';
                        break;
                    case 'remplacer':
                        $resultat = str_ireplace($mot, str_repeat('*', mb_strlen($mot)), $resultat);
                        break;
                }
            }
        }

        return ['status' => $actionFinale, 'content' => $resultat];
    }

    /**
     * Récupère la liste des mots interdits actifs
     */
    private function getMotsInterdits(): array {
        $stmt = $this->pdo->query("SELECT mot, action FROM mots_interdits WHERE actif = 1");
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * Crée un signalement automatique
     */
    public function creerSignalementAutomatique(string $typeCible, int $cibleId): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO signalements (auteur_id, type_cible, cible_id, motif, statut)
            VALUES (0, :type_cible, :cible_id, 'Détection automatique de contenu suspect', 'en_attente')
        ");
        return $stmt->execute([
            'type_cible' => $typeCible,
            'cible_id'   => $cibleId
        ]);
    }
}
