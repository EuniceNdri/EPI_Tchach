<?php
/**
 * =============================================================
 * controllers/AuthController.php – Contrôleur d'authentification
 * =============================================================
 * Gère toutes les actions liées à l'authentification :
 *   - Connexion (login)
 *   - Inscription (register)
 *   - Déconnexion (logout)
 *   - Mot de passe oublié (forgotPassword)
 *   - Réinitialisation du mot de passe (resetPassword)
 * =============================================================
 */

if (defined('ROOT_PATH')) {
    require_once ROOT_PATH . '/models/User.php';
} else {
    require_once dirname(__DIR__) . '/models/User.php';
}

class AuthController {
    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    /**
     * Page et traitement de la connexion
     */
    public function login(): void {
        global $baseUrl, $base, $assetBase, $asset;
        $error = '';
        $success = $_SESSION['flash_success'] ?? '';
        unset($_SESSION['flash_success']);

        // Si l'utilisateur est déjà connecté, rediriger selon son rôle
        if (!empty($_SESSION['user_id'])) {
            $currentRole = strtolower(trim($_SESSION['user_role'] ?? 'etudiant'));
            $destination = ($currentRole === 'administrateur' || $currentRole === 'admin') ? url('admin') : url('profile');
            if (headers_sent()) {
                echo "<script>window.location.href=" . json_encode($destination) . ";</script>";
                exit;
            }
            header('Location: ' . $destination);
            exit;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $mot_de_passe = $_POST['mot_de_passe'] ?? '';

            if (empty($email) || empty($mot_de_passe)) {
                $error = "Veuillez renseigner votre email et votre mot de passe.";
            } else {
                $user = $this->userModel->login($email, $mot_de_passe);

                if ($user) {
                    $rawRole = strtolower(trim($user['role'] ?? 'etudiant'));
                    $normalizedRole = ($rawRole === 'admin' || $rawRole === 'administrateur') ? 'administrateur' : $rawRole;

                    // Initialisation des variables de session
                    $_SESSION['user_id'] = (int) $user['id'];
                    $_SESSION['user_nom'] = $user['nom'] ?? '';
                    $_SESSION['user_prenom'] = $user['prenom'] ?? '';
                    $_SESSION['user_email'] = $user['email'] ?? $email;
                    $_SESSION['user_role'] = $normalizedRole;
                    $_SESSION['user_avatar'] = $user['avatar_url'] ?? null;
                    $_SESSION['user_bio'] = $user['biographie'] ?? null;
                    $_SESSION['user_filiere'] = $user['filiere'] ?? null;
                    $_SESSION['user_modules'] = $user['modules_enseignes'] ?? null;

                    // Redirection automatique selon le rôle :
                    // Administrateur -> /admin (Dashboard)
                    // Étudiant, Professeur, Délégué -> /profile
                    $targetUrl = ($normalizedRole === 'administrateur') ? url('admin') : url('profile');

                    if (headers_sent()) {
                        echo "<script>window.location.href=" . json_encode($targetUrl) . ";</script>";
                        echo "<noscript><meta http-equiv='refresh' content='0;url=" . htmlspecialchars($targetUrl) . "'></noscript>";
                        exit;
                    }

                    header('Location: ' . $targetUrl);
                    exit;
                } else {
                    $error = "Adresse email ou mot de passe incorrect.";
                }
            }
        }

        require_once ROOT_PATH . '/views/desktop/auth/login.php';
    }

    /**
     * Page et traitement de l'inscription
     */
    public function register(): void {
        global $baseUrl, $base, $assetBase, $asset;
        $error = '';
        $success = '';

        if (!empty($_SESSION['user_id'])) {
            header('Location: ' . url('profile'));
            exit;
        }

        // Récupérer les zones de filières existantes pour l'interface
        $availableFilieres = $this->userModel->getAvailableFiliereZones();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $nom = trim($_POST['nom'] ?? '');
            $prenom = trim($_POST['prenom'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $role = $_POST['role'] ?? 'etudiant';
            $mot_de_passe = $_POST['mot_de_passe'] ?? '';
            $confirmer_mot_de_passe = $_POST['confirmer_mot_de_passe'] ?? '';
            $cgu = !empty($_POST['cgu']);

            // Données spécifiques aux rôles
            $filiereInput = trim($_POST['filiere'] ?? '');
            $autreFiliere = trim($_POST['autre_filiere'] ?? '');
            $profFilieres = isset($_POST['prof_filieres']) && is_array($_POST['prof_filieres']) ? $_POST['prof_filieres'] : [];
            $modulesEnseignes = trim($_POST['modules_enseignes'] ?? '');

            // Normalisation de la filière étudiante
            $filiereFinale = null;
            if ($role === 'etudiant') {
                if ($filiereInput === 'autre' || empty($filiereInput)) {
                    $filiereFinale = $autreFiliere;
                } else {
                    $filiereFinale = $filiereInput;
                }
            } elseif ($role === 'professeur') {
                if (!empty($profFilieres)) {
                    $filiereFinale = implode(', ', array_filter(array_map('trim', $profFilieres)));
                }
            }

            if (empty($nom) || empty($prenom) || empty($email) || empty($mot_de_passe)) {
                $error = "Tous les champs obligatoires doivent être renseignés.";
            } elseif (!$cgu) {
                $error = "Vous devez accepter la charte d'utilisation et le règlement de l'école.";
            } elseif ($mot_de_passe !== $confirmer_mot_de_passe) {
                $error = "Les deux mots de passe ne correspondent pas.";
            } elseif (strlen($mot_de_passe) < 8) {
                $error = "Le mot de passe doit comporter au moins 8 caractères.";
            } elseif ($role === 'etudiant' && empty($filiereFinale)) {
                $error = "Veuillez choisir ou renseigner votre filière d'études.";
            } elseif ($role === 'professeur' && empty($modulesEnseignes) && empty($profFilieres)) {
                $error = "En tant que professeur, veuillez indiquer vos modules d'enseignement ou vos filières rattachées.";
            } elseif ($this->userModel->emailExists($email)) {
                $error = "Cette adresse email institutionnelle est déjà associée à un compte.";
            } else {
                $userId = $this->userModel->register(
                    $email,
                    $mot_de_passe,
                    $nom,
                    $prenom,
                    $role,
                    $filiereFinale,
                    $modulesEnseignes,
                    $profFilieres
                );

                if ($userId) {
                    // Auto-connexion immédiate de l'utilisateur inscrit
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['user_nom'] = $nom;
                    $_SESSION['user_prenom'] = $prenom;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_role'] = $role;
                    $_SESSION['user_filiere'] = $filiereFinale;
                    $_SESSION['user_modules'] = $modulesEnseignes;
                    $_SESSION['user_avatar'] = null;
                    $_SESSION['user_bio'] = null;

                    // Message d'accueil informatif
                    if ($role === 'etudiant') {
                        $zoneTrouvee = $this->userModel->findZoneByFiliereName($filiereFinale);
                        if ($zoneTrouvee) {
                            $_SESSION['flash_success'] = "Bienvenue sur EPI Tchach ! Vous avez été automatiquement rattaché au salon {$zoneTrouvee['nom']}.";
                        } else {
                            $_SESSION['flash_success'] = "Bienvenue sur EPI Tchach ! Votre compte est créé. La zone de la filière \"{$filiereFinale}\" n'existant pas encore, une notification a été envoyée aux administrateurs pour son ouverture immédiate.";
                        }
                    } else {
                        $_SESSION['flash_success'] = "Bienvenue sur EPI Tchach ! Votre compte enseignant a été configuré avec succès.";
                    }

                    // Redirection directe :
                    // Si admin -> /admin, sinon (étudiant, professeur, délégué) -> /profile
                    if ($role === 'administrateur') {
                        header('Location: ' . url('admin'));
                    } else {
                        header('Location: ' . url('profile'));
                    }
                    exit;
                } else {
                    $error = "Une erreur est survenue lors de l'enregistrement. Veuillez réessayer.";
                }
            }
        }

        require_once ROOT_PATH . '/views/desktop/auth/register.php';
    }

    /**
     * Déconnexion de l'utilisateur
     */
    public function logout(): void {
        $_SESSION = [];
        if (session_id() !== '' || headers_sent() === false) {
            session_destroy();
        }
        header('Location: ' . url('login'));
        exit;
    }

    /**
     * Demande de réinitialisation de mot de passe
     */
    public function forgotPassword(): void {
        global $baseUrl, $base, $assetBase, $asset;
        $error = '';
        $success = '';

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $email = trim($_POST['email'] ?? '');

            if (empty($email)) {
                $error = "Veuillez saisir votre adresse email institutionnelle.";
            } else {
                $user = $this->userModel->findByEmail($email);
                // Message sécurisé (ne divulgue pas si l'email existe ou non)
                $success = "Si un compte est associé à cette adresse, un lien de réinitialisation vous a été envoyé.";
            }
        }

        require_once ROOT_PATH . '/views/desktop/auth/forgot-password.php';
    }

    /**
     * Page de réinitialisation du mot de passe
     */
    public function resetPassword(): void {
        global $baseUrl, $base, $assetBase, $asset;
        $error = '';
        $success = '';

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $email = trim($_POST['email'] ?? ($_SESSION['reset_email'] ?? ''));
            $nouveau_mot_de_passe = $_POST['nouveau_mot_de_passe'] ?? '';
            $confirmer_mot_de_passe = $_POST['confirmer_mot_de_passe'] ?? '';

            if (empty($nouveau_mot_de_passe) || strlen($nouveau_mot_de_passe) < 8) {
                $error = "Le mot de passe doit comporter au moins 8 caractères.";
            } elseif ($nouveau_mot_de_passe !== $confirmer_mot_de_passe) {
                $error = "Les deux mots de passe saisis ne sont pas identiques.";
            } else {
                if (!empty($email)) {
                    $this->userModel->resetPassword($email, $nouveau_mot_de_passe);
                }
                $_SESSION['flash_success'] = "Votre mot de passe a été mis à jour avec succès. Vous pouvez vous connecter.";
                header('Location: ' . url('login'));
                exit;
            }
        }

        require_once ROOT_PATH . '/views/desktop/auth/reset-password.php';
    }
}
