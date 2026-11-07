# Documentation complète du projet EPI Tchach

## 1. Objectif du projet

EPI Tchach est une plateforme éducative / communautaire conçue pour une école ou un établissement. Elle permet :

- la gestion des comptes utilisateurs,
- l'authentification des élèves, professeurs et administrateurs,
- la gestion des filières et des salons de discussion,
- les publications et réactions,
- la modération des contenus signalés,
- le suivi d'activité par rôle : administrateur, délégué, chef de classe.

Le projet est construit selon une architecture MVC simple en PHP, avec des vues HTML/PHP, des contrôleurs et des modèles.

---

## 2. Vision fonctionnelle globale

### 2.1 Rôles disponibles

Le système est pensé autour de plusieurs profils :

- Administrateur : accès total, gestion des utilisateurs, des filières, des signalements, du dashboard global.
- Professeur : espace de gestion de cours / modules, participation aux salons et suivi de publications.
- Étudiant : accès au forum, publication, réactions, consultation des salons.
- Délégué : vue centrée sur sa filière.
- Chef de classe : vue centrée sur sa classe / groupe.
- Modérateur : gestion modération et signalements.

### 2.2 Modules principaux

- Authentification / inscription / déconnexion
- Profil utilisateur
- Salon général et filières
- Publications et commentaires
- Réactions (like, utile, j'adore)
- Notifications
- Gestion des signalements
- Administration du système
- Dashboard par rôle

---

## 3. Structure du projet

Voici la structure du dépôt :

- `index.php` : point d'entrée unique, routage global
- `config/database.php` : connexion PDO + configuration
- `controllers/` : logique applicative (auth, admin, dashboard, moderation, etc.)
- `models/` : accès aux données et logique métier
- `views/` : pages HTML/PHP affichées à l'utilisateur
- `public/assets/` : CSS, JS, images
- `database.sql` : script SQL complet de création de base et données de test

### 3.1 Dossiers importants

#### controllers

- `AuthController.php` : login, register, logout
- `AdminController.php` : utilisateurs, filières, signalements, paramètres
- `DashboardController.php` : dashboard selon le rôle
- `ModerationController.php` : traitement des signalements
- `ProfileController.php` : profil utilisateur
- `PublicationController.php` : publications
- `ReactionController.php` : likes, réactions
- `NotificationController.php` : notifications
- `SalonController.php` : salons / filières

#### models

- `User.php` : authentification, inscription, rôles
- `Admin.php` : statistiques, gestion admin, signalements
- `ModerationService.php` : logique de modération
- `Signalement.php` : gestion des signalements
- `Notification.php` : notifications
- `Salon.php` : filières, zones, salons
- `Reaction.php` : réactions

#### views

- `desktop/auth/` : connexion / inscription
- `desktop/profile/` : profil
- `desktop/dashboard/` : dashboards admin, délégué, chef de classe
- `desktop/admin/` : gestion utilisateurs, modération, filières
- `desktop/partials/` : sidebar, éléments globaux

---

## 4. Fonctionnement du projet

### 4.1 Point d'entrée : index.php

Le projet fonctionne avec un routeur unique dans `index.php`.

Cela signifie que toutes les pages passent par la même entrée. La route est analysée et redirigée vers le bon contrôleur.

Exemples de routes :

- `/login` → `AuthController::login()`
- `/admin` → `AdminController::dashboard()`
- `/admin/users` → `AdminController::users()`
- `/dashboard/admin` → `DashboardController::adminDashboard()`
- `/dashboard/delegue` → `DashboardController::delegueDashboard()`
- `/dashboard/chef-classe` → `DashboardController::chefClasseDashboard()`
- `/admin/moderation` → `ModerationController::index()`

Le routeur calcule aussi l’URL de base du projet pour générer les liens HTTP et les assets.

### 4.2 Contrôleurs

Les contrôleurs jouent le rôle de chaîne de commande entre :

- la requête HTTP,
- les modèles,
- et les vues.

Ils sont responsables de :

- la vérification d’accès,
- la récupération des données,
- le traitement du POST,
- le chargement de la bonne vue.

### 4.3 Modèles

Les modèles récupèrent et traitent les données. Ils contiennent :

- les requêtes SQL,
- les validations métier,
- les calculs de statistiques,
- la logique de gestion du rôle / filière / modération.

### 4.4 Vues

Les vues sont les pages HTML affichées à l’utilisateur. Elles utilisent des variables transmises par les contrôleurs et rendent les tableaux, cartes KPI, formulaires et éléments visuels.

---

## 5. Gestion des utilisateurs et rôles

### 5.1 Connexion

Le processus est dans `AuthController::login()` et `User::login()`.

Étapes :

1. L’utilisateur soumet le formulaire de connexion.
2. Le contrôleur récupère email + mot de passe.
3. `User::login()` cherche l’utilisateur dans la table `utilisateurs`.
4. Le mot de passe est vérifié via `password_verify()`.
5. Les informations sont stockées dans la session PHP.
6. L’utilisateur est redirigé vers sa destination :
   - administrateur → `/admin`
   - autre rôle → `/profile` ou `/salon`

### 5.2 Inscription

Le processus est centralisé dans `AuthController::register()` et `User::register()`.

Il prend en charge :

- les rôles,
- la filière,
- les modules d’enseignement,
- la création automatique de la zone / filière dans le système,
- la notification адміністра tive si besoin.

### 5.3 Session

Les données de session démonstratives sont stockées dans `$_SESSION` :

- `user_id`
- `user_nom`
- `user_prenom`
- `user_email`
- `user_role`
- `user_filiere`
- `user_modules`

La sécurité de l’accès est effectuée dans les contrôleurs via des garde-fous (`checkAdmin()`, `checkAccess()`, etc.).

---

## 6. Dashboard et vue par rôle

### 6.1 Dashboard global admin

Le dashboard administrateur est dans :

- `controllers/DashboardController.php`
- `views/desktop/dashboard/admin.php`

Il affiche :

- KPI globaux : utilisateurs, filières, signalements, publications
- liste des utilisateurs
- signalements récents
- vue d’administration transversale

### 6.2 Dashboard délégué

Le dashboard délégué est filtré sur la filière de l’utilisateur.

Il affiche :

- étudiants de la filière,
- signalements liés à cette filière,
- indicateurs spécifiques.

### 6.3 Dashboard chef de classe

Le dashboard chef de classe filtre selon :

- la filière,
- la classe / groupe de l’utilisateur.

### 6.4 Dashboard utilisateur standard

Un utilisateur simple a une vue personnelle avec ses informations et un état global simplifié.

---

## 7. Gestion des utilisateurs

### 7.1 Administration

Le module d'administration est géré par :

- `AdminController::users()`
- `AdminController::getUserJson()`
- `AdminController::editUser()`
- `AdminController::deleteUser()`

Les opérations possibles :

- liste des utilisateurs,
- recherche,
- filtre par rôle,
- modification,
- suppression,
- visualisation du profil.

### 7.2 Fichiers de gestion

- `views/desktop/admin/users.php` : interface de gestion
- `models/Admin.php` : méthodes `listUser()`, `countUsersByRole()`, `updateUser()`, `deleteUser()`

---

## 8. Gestion des filières et salons

Les filières et les salons sont gérés via le modèle `Salon` et la vue `admin/categories.php`.

### 8.1 Tables concernées

- `zones`
- `categories`
- `appartenance_zones`

### 8.2 Rôle du modèle Salon

`Salon.php` permet de :

- créer des zones (filières, générales, professeurs, délégation),
- créer des catégories / salons,
- lister les zones avec leurs salons,
- gérer les appartenances des utilisateurs aux zones.

### 8.3 Exemple de cas d’usage

- une filière est créée,
- les étudiants affiliés sont rattachés à cette zone,
- un salon est ajouté dans cette zone,
- les publications et discussions s’y déroulent.

---

## 9. Gestion des signalements et modération

### 9.1 Principe

Un utilisateur peut signaler une publication ou un commentaire. Les modérateurs / administrateurs décident ensuite des actions :

- avertir l’auteur,
- bloquer la publication,
- supprimer le contenu,
- rejeter le signalement.

### 9.2 Fichiers impliqués

- `controllers/ModerationController.php`
- `models/ModerationService.php`
- `models/Signalement.php`
- `views/desktop/admin/moderation.php`

### 9.3 Actions de modération

Les actions supportées sont :

- `avertir`
- `bloquer`
- `supprimer`
- `rejeter`

Suite à l’action, le système met à jour les signalements et envoie une notification à l’auteur si nécessaire.

---

## 10. Publications, réactions et notifications

### 10.1 Publications

Les publications sont stockées dans la table `publications`.

Elles portent :

- titre,
- contenu,
- catégorie / salon,
- auteur,
- statut,
- date de création.

### 10.2 Réactions

Les réactions sont gérées dans la table `reactions`.

Types pris en charge :

- `like`
- `utile`
- `jadore`

### 10.3 Notifications

`Notification.php` permet de créer des messages automatiques comme :

- avertissement de modération,
- suppression de publication,
- réaction sur une publication,
- signalement reçu.

---

## 11. Base de données

La base de données est définie dans `database.sql`.

### 11.1 Tables principales

- `utilisateurs`
- `zones`
- `appartenance_zones`
- `categories`
- `publications`
- `commentaires`
- `reactions`
- `pieces_jointes`
- `tags`
- `publication_tags`
- `signalements`
- `notifications`
- `mots_interdits`

### 11.2 Données de test

Le script SQL contient déjà des comptes de test et quelques données de démonstration.

Comptes de test disponibles :

- Email : `admin@epi-yakro.ci` / Mot de passe : `password123`
- Email : `momo.kouassi@epi-yakro.ci` / Mot de passe : `password123`

---

## 12. Comment lancer le projet

### 12.1 Prérequis

- XAMPP ou WAMP avec Apache + MySQL
- PHP 8.x
- MySQL / MariaDB
- Un navigateur web

### 12.2 Étapes

1. Ouvrir le projet dans le dossier `EPI_Tchach`.
2. Vérifier que le serveur Apache et MySQL de XAMPP sont démarrés.
3. Créer / vérifier la base `forum_ecole`.
4. Importer le fichier `database.sql`.
5. Vérifier le fichier `.env`.
6. Démarrer le projet localement :

```bash
php -S 127.0.0.1:8000 -t .
```

7. Ouvrir :

```text
http://127.0.0.1:8000/login
```

### 12.3 Exemple d’environnement

Le `.env` du projet contient des informations comme :

```env
DB_HOST=localhost
DB_NAME=forum_ecole
DB_USER=root
DB_PASS=
```

---

## 13. Dépannage et bonnes pratiques

### 13.1 Erreur mbstring non activé

Le projet a connu une erreur liée à des fonctions comme :

- `mb_strimwidth()`
- `mb_substr()`

Cela arrive si l’extension `mbstring` n’est pas activée dans PHP.

La correction consiste à gérer un fallback PHP ou activer `mbstring` dans PHP.

### 13.2 Vérification syntaxique

Commande de validation :

```bash
php -l index.php
php -l controllers/AdminController.php
php -l controllers/DashboardController.php
php -l models/Admin.php
```

### 13.3 Vérification de routes

Le projet a été validé via des appels HTTP sur :

- `/login`
- `/admin`
- `/admin/users`
- `/admin/moderation`
- `/dashboard/admin`

Les routes protégées redirigent correctement vers la page de connexion si l’utilisateur n’est pas authentifié.

---

## 14. Modèle de travail recommandé

### 14.1 Workflow de développement

1. Identifier la fonctionnalité à développer.
2. Vérifier le contrôleur concerné.
3. Vérifier le modèle de données associé.
4. Ajouter ou modifier la vue correspondante.
5. Tester la route.
6. Vérifier le comportement avec un compte de test.
7. Vérifier la cohérence des permissions.

### 14.2 Bonnes pratiques

- Toujours passer par le contrôleur pour les demandes utilisateur.
- Ne pas faire de logique métier dans les vues.
- Utiliser les méthodes du modèle pour les requêtes SQL.
- Protéger les accès par rôle.
- Valider les données avant insertion ou mise à jour.
- Tester chaque module avec au moins un cas normal et un cas vide.

---

## 15. Ce qu’on a construit dans ce projet

Le travail réalisé couvre principalement :

- l’architecture de base MVC,
- les comptes et le routage,
- le dashboard admin,
- les dashboards délégué et chef de classe,
- la gestion utilisateurs,
- la gestion filières / salons,
- la modération et les signalements,
- les notifications,
- la sécurisation et le test des pages.

Le but est d’avoir un système fonctionnel de forum éducatif avec gestion administrative et logique métier en place.

---

## 16. Prochaines améliorations possibles

- ajouter un vrai système de gestion des classes / groupes détaillés,
- gérer les rôles délégué et chef de classe dans la base de manière plus stricte,
- ajouter une interface de paramétrage admin plus avancée,
- gérer les droits d’accès finement par filière,
- ajouter des CSV / export de données,
- créer un système de pagination complet,
- sécuriser davantage les formulaires avec CSRF.

---

## 17. Résumé final

EPI Tchach est un projet de forum scolaire en PHP MVC. Il regroupe :

- l’authentification,
- la gestion d’utilisateurs,
- les structures de filière / salons,
- les publications et réactions,
- la modération,
- le dashboard administratif,
- les vues spécifiques par rôle.

Le projet fonctionne autour d’un routeur unique, de contrôleurs, de modèles et de vues, avec une base MySQL et des données de test intégrées pour faciliter le développement et la validation.

---

## 18. Liens utiles du projet

- `index.php`
- `controllers/`
- `models/`
- `views/`
- `database.sql`
- `config/database.php`

---

## 19. Comptes de test

- Admin : `admin@epi-yakro.ci` / `password123`
- Étudiant : `momo.kouassi@epi-yakro.ci` / `password123`

---

## 20. Vérification de fonctionnement

Les tests de validation ont confirmé que :

- le site démarre correctement,
- les pages PHP sont syntaxiquement valides,
- le compte admin de test se connecte et redirige vers `/admin`.

---

Si besoin, ce document peut ensuite être enrichi avec :

- un schéma fonctionnel complet,
- un diagramme de navigation,
- une description SQL table par table,
- ou un guide de maintenance du projet.
