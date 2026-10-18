<?php
/**
 * =============================================================
 * controllers/ProfileController.php – Contrôleur du Profil Membre
 * =============================================================
 * Gère l'affichage du profil et la mise à jour asynchrone (AJAX)
 * sans rechargement de page pour une fluidité totale.
 * =============================================================
 */

if (defined('ROOT_PATH')) {
    require_once ROOT_PATH . '/models/User.php';
} else {
    require_once dirname(__DIR__) . '/models/User.php';
}

class ProfileController {
    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    /**
     * Affiche la page de profil
     */
    public function index(): void {
        global $baseUrl, $base, $assetBase, $asset;

        $userId = $_SESSION['user_id'] ?? null;
        $user = null;

        if ($userId) {
            $user = $this->userModel->findById((int) $userId);
            if ($user) {
                $_SESSION['user_nom'] = $user['nom'];
                $_SESSION['user_prenom'] = $user['prenom'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_bio'] = $user['biographie'];
                $_SESSION['user_filiere'] = $user['filiere'];
                $_SESSION['user_modules'] = $user['modules_enseignes'];
            }
        }

        require_once ROOT_PATH . '/views/desktop/profile/index.php';
    }

    /**
     * Traitement de mise à jour du profil (AJAX Popup / POST)
     */
    public function update(): void {
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
               || isset($_POST['ajax']);

        $userId = $_SESSION['user_id'] ?? null;

        $nom = trim($_POST['nom'] ?? 'Kouassi');
        $prenom = trim($_POST['prenom'] ?? 'Momo');
        $biographie = trim($_POST['biographie'] ?? '');
        $nouveauMotDePasse = $_POST['nouveau_mot_de_passe'] ?? '';

        if (empty($nom) || empty($prenom)) {
            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8', true, 422);
                echo json_encode(['success' => false, 'message' => 'Le nom et le prénom sont obligatoires.']);
                exit;
            }
            header('Location: ' . url('profile'));
            exit;
        }

        // Si connecté en base, mise à jour en BDD
        if ($userId) {
            $this->userModel->updateProfile((int) $userId, $nom, $prenom, $biographie);
            if (!empty($nouveauMotDePasse) && strlen($nouveauMotDePasse) >= 8) {
                $this->userModel->updatePassword((int) $userId, $nouveauMotDePasse);
            }
        }

        // Mise à jour systématique de la session
        $_SESSION['user_nom'] = $nom;
        $_SESSION['user_prenom'] = $prenom;
        $_SESSION['user_bio'] = $biographie;

        $initials = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));

        if ($isAjax) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'success' => true,
                'message' => 'Profil mis à jour avec succès !',
                'user' => [
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'fullname' => $prenom . ' ' . $nom,
                    'biographie' => $biographie,
                    'initials' => $initials
                ]
            ]);
            exit;
        }

        header('Location: ' . url('profile'));
        exit;
    }
}

