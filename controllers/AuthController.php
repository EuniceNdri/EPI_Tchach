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
    require_once ROOT_PATH . '/models/Avatar.php';
} else {
    require_once dirname(__DIR__) . '/models/User.php';
    require_once dirname(__DIR__) . '/models/Avatar.php';
}

class AuthController {
    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    private function renderAuthView(string $view): void {
        global $baseUrl, $base, $assetBase, $asset;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $device = preg_match('/(?:iphone|ipod|windows phone|blackberry|opera mini|mobile|android.*mobile)/i', $userAgent)
            ? 'smartphone'
            : 'desktop';

        header('Vary: User-Agent');
        require_once ROOT_PATH . '/views/' . $device . '/auth/' . $view . '.php';
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
            $email = strtolower(trim((string)($_POST['email'] ?? '')));
            $mot_de_passe = (string)($_POST['mot_de_passe'] ?? '');

            if (empty($email) || empty($mot_de_passe)) {
                $error = "Veuillez renseigner votre email et votre mot de passe.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "L'adresse email saisie n'est pas valide. Vérifiez son format puis réessayez.";
            } elseif (!$this->userModel->getPdo() instanceof PDO) {
                http_response_code(503);
                $error = "La connexion est momentanément indisponible. Réessayez dans quelques instants.";
            } else {
                try {
                    $user = $this->userModel->login($email, $mot_de_passe);
                } catch (PDOException $exception) {
                    error_log('Connexion utilisateur impossible : ' . $exception->getMessage());
                    http_response_code(503);
                    $error = "La connexion est momentanément indisponible. Réessayez dans quelques instants.";
                    $user = false;
                }

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
                    // Étudiant, Professeur, Délégué -> /salon (Salon Général)
                    $targetUrl = ($normalizedRole === 'administrateur') ? url('admin') : url('salon');

                    if (headers_sent()) {
                        echo "<script>window.location.href=" . json_encode($targetUrl) . ";</script>";
                        echo "<noscript><meta http-equiv='refresh' content='0;url=" . htmlspecialchars($targetUrl) . "'></noscript>";
                        exit;
                    }

                    header('Location: ' . $targetUrl);
                    exit;
                } else {
                    if ($error === '') {
                        $error = "Adresse email ou mot de passe incorrect.";
                    }
                }
            }
        }

        $this->renderAuthView('login');
    }

    /**
     * Page et traitement de l'inscription
     */
    public function register(): void {
        global $baseUrl, $base, $assetBase, $asset;
        $error = '';
        $success = '';
        $registerStep = 1;

        if (!empty($_SESSION['user_id'])) {
            header('Location: ' . url('profile'));
            exit;
        }

        if (!$this->userModel->getPdo() instanceof PDO) {
            http_response_code(503);
            $error = "L'inscription est momentanément indisponible. Réessayez dans quelques instants.";
        }

        // Récupérer les zones de filières existantes pour l'interface
        $availableFilieres = $this->userModel->getAvailableFiliereZones();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $this->userModel->getPdo() instanceof PDO) {
            $nom = trim(is_string($_POST['nom'] ?? null) ? $_POST['nom'] : '');
            $prenom = trim(is_string($_POST['prenom'] ?? null) ? $_POST['prenom'] : '');
            $email = strtolower(trim(is_string($_POST['email'] ?? null) ? $_POST['email'] : ''));
            $role = is_string($_POST['role'] ?? null) ? $_POST['role'] : '';
            $mot_de_passe = is_string($_POST['mot_de_passe'] ?? null) ? $_POST['mot_de_passe'] : '';
            $confirmer_mot_de_passe = is_string($_POST['confirmer_mot_de_passe'] ?? null) ? $_POST['confirmer_mot_de_passe'] : '';
            $cgu = !empty($_POST['cgu']);

            // Données spécifiques aux rôles
            $filiereInput = trim(is_string($_POST['filiere'] ?? null) ? $_POST['filiere'] : '');
            $autreFiliere = trim(is_string($_POST['autre_filiere'] ?? null) ? $_POST['autre_filiere'] : '');
            $profFilieres = isset($_POST['prof_filieres']) && is_array($_POST['prof_filieres'])
                ? array_values(array_filter($_POST['prof_filieres'], 'is_string'))
                : [];
            $modulesEnseignes = trim(is_string($_POST['modules_enseignes'] ?? null) ? $_POST['modules_enseignes'] : '');

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
                $registerStep = empty($nom) || empty($prenom) || empty($email) ? 1 : 3;
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Veuillez saisir une adresse email valide.";
                $registerStep = 1;
            } elseif (!in_array($role, ['etudiant', 'professeur'], true)) {
                $error = "Veuillez choisir un rôle valide.";
                $registerStep = 1;
            } elseif (!$cgu) {
                $error = "Vous devez accepter la charte d'utilisation et le règlement de l'école.";
                $registerStep = 3;
            } elseif ($mot_de_passe !== $confirmer_mot_de_passe) {
                $error = "Les deux mots de passe ne correspondent pas.";
                $registerStep = 3;
            } elseif (strlen($mot_de_passe) < 8) {
                $error = "Le mot de passe doit comporter au moins 8 caractères.";
                $registerStep = 3;
            } elseif ($role === 'etudiant' && empty($filiereFinale)) {
                $error = "Veuillez choisir ou renseigner votre filière d'études.";
                $registerStep = 2;
            } elseif ($role === 'professeur' && empty($modulesEnseignes) && empty($profFilieres)) {
                $error = "En tant que professeur, veuillez indiquer vos modules d'enseignement ou vos filières rattachées.";
                $registerStep = 2;
            } else {
                $registerStep = 3;
                $userId = false;
                $avatarUrl = null;
                try {
                    if ($this->userModel->emailExists($email)) {
                        $error = "Cette adresse email est déjà associée à un compte. Connectez-vous ou utilisez une autre adresse.";
                        $registerStep = 1;
                    } else {
                        $avatarUrl = Avatar::storeUploadedFile($_FILES['avatar'] ?? []);
                        $userId = $this->userModel->register(
                            $email,
                            $mot_de_passe,
                            $nom,
                            $prenom,
                            $role,
                            $filiereFinale,
                            $modulesEnseignes,
                            $profFilieres,
                            $avatarUrl
                        );
                        if (!$userId) {
                            Avatar::removeStoredFile($avatarUrl);
                            $avatarUrl = null;
                        }
                    }

                if (!empty($userId)) {
                    // Auto-connexion immédiate de l'utilisateur inscrit
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['user_nom'] = $nom;
                    $_SESSION['user_prenom'] = $prenom;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_role'] = $role;
                    $_SESSION['user_filiere'] = $filiereFinale;
                    $_SESSION['user_modules'] = $modulesEnseignes;
                    $_SESSION['user_avatar'] = $avatarUrl;
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
                    // Si admin -> /admin, sinon (étudiant, professeur, délégué) -> /salon (Salon Général)
                    if ($role === 'administrateur') {
                        header('Location: ' . url('admin'));
                    } else {
                        header('Location: ' . url('salon'));
                    }
                    exit;
                } elseif ($error === '') {
                    $error = "Une erreur est survenue lors de l'enregistrement. Veuillez réessayer.";
                }
                } catch (InvalidArgumentException $exception) {
                    Avatar::removeStoredFile($avatarUrl);
                    $error = $exception->getMessage();
                    $registerStep = 1;
                } catch (PDOException $exception) {
                    Avatar::removeStoredFile($avatarUrl);
                    error_log('Inscription utilisateur impossible : ' . $exception->getMessage());
                    $databaseErrorCode = (int)($exception->errorInfo[1] ?? 0);
                    $error = $databaseErrorCode === 1062
                        ? "Cette adresse email est déjà associée à un compte. Connectez-vous ou utilisez une autre adresse."
                        : "La création du compte est momentanément indisponible. Vérifiez les informations puis réessayez.";
                    if ($databaseErrorCode === 1062) {
                        $registerStep = 1;
                    }
                    if ($databaseErrorCode !== 1062) {
                        http_response_code(503);
                    }
                } catch (RuntimeException $exception) {
                    Avatar::removeStoredFile($avatarUrl);
                    error_log('Stockage de la photo de profil impossible : ' . $exception->getMessage());
                    http_response_code(503);
                    $error = "La photo de profil n'a pas pu être enregistrée. Réessayez dans quelques instants.";
                    $registerStep = 1;
                }
            }
        }

        $this->renderAuthView('register');
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
                $error = "Veuillez saisir votre adresse email.";
            } else {
                $user = $this->userModel->findByEmail($email);
                // Message sécurisé (ne divulgue pas si l'email existe ou non)
                $success = "Si un compte est associé à cette adresse, un lien de réinitialisation vous a été envoyé.";
            }
        }

        $this->renderAuthView('forgot-password');
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

        $this->renderAuthView('reset-password');
    }
}
