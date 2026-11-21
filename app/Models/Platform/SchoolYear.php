<?php

class SchoolYear
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Récupère l'année scolaire active pour l'école
     */
    public function getActive(): ?array
    {
        $stmt = $this->pdo->query("SELECT * FROM annees_scolaires WHERE est_active = 1 LIMIT 1");
        $year = $stmt->fetch(PDO::FETCH_ASSOC);
        return $year ?: null;
    }

    /**
     * Liste toutes les années scolaires
     */
    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM annees_scolaires ORDER BY date_debut DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Trouve une année scolaire par son identifiant
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM annees_scolaires WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $year = $stmt->fetch(PDO::FETCH_ASSOC);
        return $year ?: null;
    }

    /**
     * Crée une nouvelle année scolaire
     */
    public function create(string $libelle, string $dateDebut, string $dateFin, bool $estActive = false): int
    {
        $libelle = trim($libelle);
        if ($libelle === '' || $dateDebut === '' || $dateFin === '') {
            throw new InvalidArgumentException("Tous les champs de l'année scolaire sont obligatoires.");
        }

        if ($dateDebut >= $dateFin) {
            throw new InvalidArgumentException("La date de début doit être antérieure à la date de fin.");
        }

        $this->pdo->beginTransaction();
        try {
            if ($estActive) {
                $this->pdo->exec("UPDATE annees_scolaires SET est_active = 0");
            }

            $stmt = $this->pdo->prepare("
                INSERT INTO annees_scolaires (libelle, date_debut, date_fin, est_active)
                VALUES (:libelle, :date_debut, :date_fin, :est_active)
            ");
            $stmt->execute([
                'libelle' => $libelle,
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
                'est_active' => $estActive ? 1 : 0,
            ]);
            $id = (int)$this->pdo->lastInsertId();
            $this->pdo->commit();
            return $id;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Active une année scolaire (désactive automatiquement toutes les autres)
     */
    public function activate(int $id): bool
    {
        $year = $this->findById($id);
        if (!$year) {
            return false;
        }

        $this->pdo->beginTransaction();
        try {
            $this->pdo->exec("UPDATE annees_scolaires SET est_active = 0");
            $stmt = $this->pdo->prepare("UPDATE annees_scolaires SET est_active = 1 WHERE id = ?");
            $res = $stmt->execute([$id]);
            $this->pdo->commit();
            return $res;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Supprime une année scolaire (interdit si c'est l'année active)
     */
    public function delete(int $id): bool
    {
        $year = $this->findById($id);
        if (!$year || (bool)$year['est_active']) {
            return false;
        }

        $stmt = $this->pdo->prepare("DELETE FROM annees_scolaires WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
