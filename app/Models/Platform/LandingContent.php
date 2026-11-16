<?php

class LandingContent {
    private ?PDO $pdo;

    public function __construct(?PDO $pdo) {
        $this->pdo = $pdo;
    }

    public static function defaultHeroSlides(): array {
        return [
            ['image' => 'images/landing-students.jpg', 'title' => 'On avance ensemble.', 'caption' => 'Une communauté, toutes les filières.'],
            ['image' => 'images/filiere-entraide.jpg', 'title' => "L'entraide au quotidien.", 'caption' => 'Les bonnes idées se partagent.'],
            ['image' => 'images/filiere-industrielle.jpg', 'title' => 'Apprendre en construisant.', 'caption' => 'La pratique nous rassemble.'],
            ['image' => 'images/filiere-ressources.jpg', 'title' => 'Chacun apporte sa pierre.', 'caption' => 'Une école, mille talents.'],
            ['image' => 'images/filiere-tertiaire.jpg', 'title' => 'Grandir ensemble.', 'caption' => 'Chaque parcours compte.'],
        ];
    }

    public static function defaultStats(): array {
        return [
            ['value' => '1250', 'prefix' => '+', 'suffix' => '', 'label' => 'Membres actifs', 'description' => 'Étudiants, professeurs & délégués'],
            ['value' => '12', 'prefix' => '', 'suffix' => '', 'label' => 'Filières officielles', 'description' => 'Pôles industriels & tertiaires'],
            ['value' => '850', 'prefix' => '+', 'suffix' => '', 'label' => 'Ressources & annales', 'description' => 'Sujets corrigés, TP et fiches'],
            ['value' => '98', 'prefix' => '', 'suffix' => '%', 'label' => "Taux d'entraide", 'description' => 'Questions avec solution validée'],
        ];
    }

    public function getSettings(): array {
        $defaults = [
            'hero_slides' => self::defaultHeroSlides(),
            'stats' => self::defaultStats(),
        ];
        if (!$this->pdo) {
            return $defaults;
        }

        try {
            $statement = $this->pdo->query(
                "SELECT config_key, config_value FROM landing_settings
                 WHERE config_key IN ('hero_slides', 'stats')"
            );
            foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $decoded = json_decode((string)$row['config_value'], true);
                if (!is_array($decoded)) {
                    error_log('Configuration de la landing invalide pour la clé : ' . $row['config_key']);
                    continue;
                }
                if ($row['config_key'] === 'hero_slides' && $this->isValidHeroSlides($decoded)) {
                    $defaults['hero_slides'] = $decoded;
                } elseif ($row['config_key'] === 'hero_slides') {
                    error_log('Configuration de diapositives invalide, utilisation des valeurs par défaut.');
                } elseif ($row['config_key'] === 'stats' && $this->isValidStats($decoded)) {
                    $defaults['stats'] = $decoded;
                } elseif ($row['config_key'] === 'stats') {
                    error_log('Configuration des statistiques invalide, utilisation des valeurs par défaut.');
                }
            }
        } catch (PDOException $exception) {
            error_log('Chargement des réglages de la landing impossible : ' . $exception->getMessage());
        }

        return $defaults;
    }

    private function isValidHeroSlides(array $slides): bool {
        if (count($slides) < 1 || count($slides) > 5) {
            return false;
        }
        foreach ($slides as $slide) {
            if (!is_array($slide)
                || !isset($slide['image'], $slide['title'], $slide['caption'])
                || !is_string($slide['image'])
                || !is_string($slide['title'])
                || !is_string($slide['caption'])
                || !preg_match('#^(?:images/[A-Za-z0-9._-]+|uploads/landing/[a-f0-9]{32}\.(?:jpg|png|webp))$#', $slide['image'])) {
                return false;
            }
        }
        return true;
    }

    private function isValidStats(array $stats): bool {
        if (count($stats) !== 4) {
            return false;
        }
        foreach ($stats as $stat) {
            if (!is_array($stat)
                || !isset($stat['value'], $stat['prefix'], $stat['suffix'], $stat['label'], $stat['description'])
                || !preg_match('/^\d{1,9}$/', (string)$stat['value'])
                || !is_string($stat['prefix'])
                || !is_string($stat['suffix'])
                || !is_string($stat['label'])
                || !is_string($stat['description'])) {
                return false;
            }
        }
        return true;
    }

    public function saveSettings(array $heroSlides, array $stats): void {
        if (!$this->pdo) {
            throw new RuntimeException('La base de données est indisponible.');
        }

        $statement = $this->pdo->prepare(
            'INSERT INTO landing_settings (config_key, config_value)
             VALUES (:config_key, :config_value)
             ON DUPLICATE KEY UPDATE config_value = VALUES(config_value)'
        );
        foreach (['hero_slides' => $heroSlides, 'stats' => $stats] as $key => $value) {
            $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($json === false) {
                throw new RuntimeException('Impossible d’encoder les réglages de la landing.');
            }
            $statement->execute([
                'config_key' => $key,
                'config_value' => $json,
            ]);
        }
    }
}
