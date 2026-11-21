<?php

class Binomage
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Récupère le rôle de binômage d'un utilisateur
     */
    public function getUserBinomageRole(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT u.id, u.nom, u.prenom, u.role, u.annee, u.filiere_id, f.nom AS filiere_nom
             FROM utilisateurs u
             LEFT JOIN filieres f ON f.id = u.filiere_id
             WHERE u.id = ? LIMIT 1"
        );
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            return ['type' => 'none', 'annee' => 0, 'filiere_id' => 0, 'filiere_nom' => ''];
        }

        $type = 'none';
        if ($user['role'] === 'etudiant') {
            if ((int)$user['annee'] === 1 && !empty($user['filiere_id'])) {
                $type = 'mentore';
            } elseif ((int)$user['annee'] >= 2 && !empty($user['filiere_id'])) {
                $type = 'mentor';
            }
        }

        return [
            'type' => $type,
            'annee' => (int)($user['annee'] ?? 0),
            'filiere_id' => (int)($user['filiere_id'] ?? 0),
            'filiere_nom' => (string)($user['filiere_nom'] ?? ''),
        ];
    }

    /**
     * Vérifie la relation active ou en attente entre deux utilisateurs
     */
    public function getRelationBetween(int $userId1, int $userId2): ?array
    {
        $statement = $this->pdo->prepare(
            "SELECT b.*,
                    mentor.nom AS mentor_nom, mentor.prenom AS mentor_prenom, mentor.avatar_url AS mentor_avatar,
                    mentore.nom AS mentore_nom, mentore.prenom AS mentore_prenom, mentore.avatar_url AS mentore_avatar,
                    filiere.nom AS filiere
             FROM binomages b
             JOIN utilisateurs mentor ON mentor.id = b.mentor_id
             JOIN utilisateurs mentore ON mentore.id = b.mentore_id
             LEFT JOIN filieres filiere ON filiere.id = mentor.filiere_id
             WHERE (b.mentor_id = :u1 AND b.mentore_id = :u2)
                OR (b.mentor_id = :u2 AND b.mentore_id = :u1)
             ORDER BY CASE b.statut
                 WHEN 'accepte' THEN 1
                 WHEN 'en_attente' THEN 2
                 ELSE 3
             END, b.date_demande DESC
             LIMIT 1"
        );
        $statement->execute(['u1' => $userId1, 'u2' => $userId2]);
        $relation = $statement->fetch(PDO::FETCH_ASSOC);
        return $relation ?: null;
    }

    /**
     * Vérifie si un étudiant (année 1) peut demander un mentor (année >= 2, même filière)
     */
    public function checkEligibility(int $mentoreId, int $mentorId): array
    {
        if ($mentoreId === $mentorId) {
            return ['eligible' => false, 'reason' => 'Impossible de se choisir soi-même.'];
        }

        $stmt = $this->pdo->prepare(
            "SELECT u.id, u.role, u.annee, u.filiere_id, u.annee_scolaire_id, f.nom AS filiere_nom
             FROM utilisateurs u
             LEFT JOIN filieres f ON f.id = u.filiere_id
             WHERE u.id IN (?, ?) AND u.est_actif = 1"
        );
        $stmt->execute([$mentoreId, $mentorId]);
        $users = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $users[(int)$row['id']] = $row;
        }

        $mentore = $users[$mentoreId] ?? null;
        $mentor = $users[$mentorId] ?? null;

        if (!$mentore || !$mentor) {
            return ['eligible' => false, 'reason' => 'Utilisateur introuvable.'];
        }

        if ($mentore['role'] !== 'etudiant' || $mentor['role'] !== 'etudiant') {
            return ['eligible' => false, 'reason' => 'Le binômage est réservé aux étudiants.'];
        }

        if ((int)$mentore['annee'] !== 1) {
            return ['eligible' => false, 'reason' => 'Seuls les étudiants de 1ère année peuvent demander un mentor.'];
        }

        if ((int)$mentor['annee'] < 2) {
            return ['eligible' => false, 'reason' => 'Un mentor doit être en 2ème année ou plus.'];
        }

        if (empty($mentore['filiere_id']) || empty($mentor['filiere_id']) || (int)$mentore['filiere_id'] !== (int)$mentor['filiere_id']) {
            return ['eligible' => false, 'reason' => 'Le mentor et le mentoré doivent impérativement être de la même filière.'];
        }

        if (!empty($mentore['annee_scolaire_id']) && !empty($mentor['annee_scolaire_id']) && (int)$mentore['annee_scolaire_id'] !== (int)$mentor['annee_scolaire_id']) {
            return ['eligible' => false, 'reason' => 'Le mentor et le mentoré doivent appartenir à la même année scolaire / promotion.'];
        }

        // Vérifier si le mentoré a déjà un mentor actif
        $existingMentorStmt = $this->pdo->prepare(
            "SELECT id, mentor_id FROM binomages WHERE mentore_id = ? AND statut = 'accepte' LIMIT 1"
        );
        $existingMentorStmt->execute([$mentoreId]);
        $activeMentor = $existingMentorStmt->fetch(PDO::FETCH_ASSOC);
        if ($activeMentor) {
            return ['eligible' => false, 'reason' => 'Vous avez déjà un mentor actif. Vous devez rompre ce binômage pour en choisir un nouveau.'];
        }

        // Vérifier si une relation en attente existe déjà avec ce mentor
        $withThis = $this->getRelationBetween($mentoreId, $mentorId);
        if ($withThis && in_array($withThis['statut'], ['accepte', 'en_attente'], true)) {
            return ['eligible' => false, 'reason' => 'Une relation existe déjà avec ce mentor.', 'relation' => $withThis];
        }

        // Vérifier la capacité du mentor (max 3 mentorés acceptés)
        $capacityStmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM binomages WHERE mentor_id = ? AND statut = 'accepte'"
        );
        $capacityStmt->execute([$mentorId]);
        if ((int)$capacityStmt->fetchColumn() >= 3) {
            return ['eligible' => false, 'reason' => 'Ce mentor accompagne déjà le maximum de 3 mentorés.'];
        }

        return ['eligible' => true, 'reason' => 'Éligible'];
    }

    /**
     * Liste des mentors éligibles disponibles pour un mentoré de 1ère année
     */
    public function getAvailableMentors(int $mentoreId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT mentor.id, mentor.nom, mentor.prenom, mentor.avatar_url,
                    mentor.annee, filiere.nom AS filiere,
                    (SELECT COUNT(*) FROM binomages b
                     WHERE b.mentor_id = mentor.id AND b.statut = 'accepte') AS mentor_count,
                    (SELECT statut FROM binomages b
                     WHERE b.mentor_id = mentor.id AND b.mentore_id = :mentore_id
                     ORDER BY b.date_demande DESC LIMIT 1) AS relation_status
             FROM utilisateurs mentor
             JOIN utilisateurs mentore ON mentore.id = :mentore_id
             LEFT JOIN filieres filiere ON filiere.id = mentor.filiere_id
             WHERE mentor.role = 'etudiant'
               AND mentor.est_actif = 1
               AND mentor.id <> mentore.id
               AND mentore.role = 'etudiant'
               AND mentore.annee = 1
               AND mentor.annee >= 2
               AND mentore.filiere_id IS NOT NULL
               AND mentor.filiere_id = mentore.filiere_id
               AND (mentore.annee_scolaire_id IS NULL OR mentor.annee_scolaire_id IS NULL OR mentor.annee_scolaire_id = mentore.annee_scolaire_id)
               AND mentor.id NOT IN (
                    SELECT mentor_id FROM binomages
                    WHERE mentore_id = :mentore_id AND statut IN ('en_attente','accepte')
               )
             HAVING mentor_count < 3
             ORDER BY mentor.prenom, mentor.nom"
        );
        $statement->execute(['mentore_id' => $mentoreId]);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Envoie une demande de binômage (Mentoré 1ère année -> Mentor 2ème année+, même filière)
     */
    public function sendRequest(int $mentoreId, int $mentorId, string $message): string
    {
        $eligibility = $this->checkEligibility($mentoreId, $mentorId);
        if (!$eligibility['eligible']) {
            throw new InvalidArgumentException($eligibility['reason']);
        }

        $existing = $this->pdo->prepare(
            "SELECT id, statut FROM binomages
             WHERE mentor_id = :mentor_id AND mentore_id = :mentore_id
             LIMIT 1"
        );
        $existing->execute(['mentor_id' => $mentorId, 'mentore_id' => $mentoreId]);
        $relation = $existing->fetch(PDO::FETCH_ASSOC);

        if ($relation && in_array($relation['statut'], ['en_attente', 'accepte'], true)) {
            return 'already_exists';
        }

        if ($relation) {
            $statement = $this->pdo->prepare(
                "UPDATE binomages SET statut = 'en_attente', message_demande = :message,
                        date_demande = NOW(), date_reponse = NULL
                 WHERE id = :id"
            );
            $statement->execute([
                'message' => mb_substr(trim($message), 0, 2000) ?: null,
                'id' => (int)$relation['id'],
            ]);
        } else {
            $statement = $this->pdo->prepare(
                "INSERT INTO binomages (mentor_id, mentore_id, statut, message_demande)
                 VALUES (:mentor_id, :mentore_id, 'en_attente', :message)"
            );
            $statement->execute([
                'mentor_id' => $mentorId,
                'mentore_id' => $mentoreId,
                'message' => mb_substr(trim($message), 0, 2000) ?: null,
            ]);
        }
        return 'sent';
    }

    /**
     * Demandes reçues par un mentor
     */
    public function getIncomingRequests(int $mentorId): array
    {
        return $this->fetchRelations(
            "WHERE b.mentor_id = :user_id AND b.statut = 'en_attente'",
            $mentorId
        );
    }

    /**
     * Demandes envoyées par un mentoré
     */
    public function getOutgoingRequests(int $mentoreId): array
    {
        return $this->fetchRelations(
            "WHERE b.mentore_id = :user_id AND b.statut = 'en_attente'",
            $mentoreId
        );
    }

    /**
     * Historique des demandes d'un mentoré (toutes y compris refusées ou terminées)
     */
    public function getAllOutgoingRequests(int $mentoreId): array
    {
        return $this->fetchRelations(
            "WHERE b.mentore_id = :user_id",
            $mentoreId
        );
    }

    /**
     * Liste des mentorés actifs d'un mentor
     */
    public function getMentees(int $mentorId): array
    {
        return $this->fetchRelations(
            "WHERE b.mentor_id = :user_id AND b.statut = 'accepte'",
            $mentorId
        );
    }

    /**
     * Mentor actif d'un mentoré
     */
    public function getMentor(int $mentoreId): ?array
    {
        $relations = $this->fetchRelations(
            "WHERE b.mentore_id = :user_id AND b.statut = 'accepte'",
            $mentoreId
        );
        return $relations[0] ?? null;
    }

    private function fetchRelations(string $where, int $userId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT b.*, mentor.id AS mentor_id, mentor.nom AS mentor_nom,
                    mentor.prenom AS mentor_prenom, mentor.avatar_url AS mentor_avatar,
                    mentor.annee AS mentor_annee,
                    mentore.id AS mentore_id, mentore.nom AS mentore_nom,
                    mentore.prenom AS mentore_prenom, mentore.avatar_url AS mentore_avatar,
                    mentore.annee AS mentore_annee,
                    filiere.nom AS filiere
             FROM binomages b
             JOIN utilisateurs mentor ON mentor.id = b.mentor_id
             JOIN utilisateurs mentore ON mentore.id = b.mentore_id
             LEFT JOIN filieres filiere ON filiere.id = mentor.filiere_id
             $where
             ORDER BY b.date_demande DESC"
        );
        $statement->execute(['user_id' => $userId]);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Le mentor accepte ou refuse une demande
     */
    public function respond(int $relationId, int $mentorId, string $decision): bool
    {
        if (!in_array($decision, ['accepte', 'refuse'], true)) {
            throw new InvalidArgumentException('Décision de binômage invalide.');
        }

        $relation = $this->getRelation($relationId);
        if (!$relation || (int)$relation['mentor_id'] !== $mentorId || $relation['statut'] !== 'en_attente') {
            return false;
        }
        if ($decision === 'accepte') {
            $count = $this->pdo->prepare(
                "SELECT COUNT(*) FROM binomages WHERE mentor_id = ? AND statut = 'accepte'"
            );
            $count->execute([$mentorId]);
            if ((int)$count->fetchColumn() >= 3) {
                throw new InvalidArgumentException('Vous accompagnez déjà le nombre maximum de 3 mentorés.');
            }
        }

        $statement = $this->pdo->prepare(
            "UPDATE binomages SET statut = :statut, date_reponse = NOW()
             WHERE id = :id AND mentor_id = :mentor_id AND statut = 'en_attente'"
        );
        return $statement->execute([
            'statut' => $decision,
            'id' => $relationId,
            'mentor_id' => $mentorId,
        ]);
    }

    /**
     * Annule une demande en attente (par le mentoré) ou termine un binômage actif (par l'un ou l'autre)
     */
    public function cancelOrTerminate(int $relationId, int $userId): bool
    {
        $relation = $this->getRelation($relationId);
        if (!$relation
            || ((int)$relation['mentor_id'] !== $userId && (int)$relation['mentore_id'] !== $userId)) {
            return false;
        }

        if ($relation['statut'] === 'en_attente') {
            if ((int)$relation['mentore_id'] !== $userId) {
                return false;
            }
            $statement = $this->pdo->prepare("DELETE FROM binomages WHERE id = ? AND mentore_id = ? AND statut = 'en_attente'");
            $statement->execute([$relationId, $userId]);
            return $statement->rowCount() > 0;
        }

        if ($relation['statut'] !== 'accepte') {
            return false;
        }

        $statement = $this->pdo->prepare(
            "UPDATE binomages SET statut = 'termine', date_reponse = NOW()
             WHERE id = ? AND statut = 'accepte'"
        );
        $statement->execute([$relationId]);
        return $statement->rowCount() > 0;
    }

    public function getRelation(int $relationId): ?array
    {
        $statement = $this->pdo->prepare("SELECT * FROM binomages WHERE id = ? LIMIT 1");
        $statement->execute([$relationId]);
        $relation = $statement->fetch(PDO::FETCH_ASSOC);
        return $relation ?: null;
    }
}
