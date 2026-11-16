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

### 2.1 Rôles et responsabilités

Les rôles de compte sont `etudiant`, `professeur` et `administrateur`.
Les responsabilités scolaires sont enregistrées séparément dans
`appartenance_zones.role_dans_zone` :

- **Administrateur** : gestion de toute la plateforme, des utilisateurs, des zones,
  des tags et des signalements; peut publier des annonces dans toutes les zones.
- **Délégué général** : responsabilité dans une zone de type `delegation`; consulte
  les indicateurs et signalements de toute l’école, en lecture seule, et peut
  publier une annonce dans la zone générale.
- **Délégué de classe** : responsabilité `delegue_de_classe` dans sa zone de type
  `filiere`; seul un étudiant de cette filière peut être nommé. Il consulte les
  membres, traite les signalements de sa filière, peut y publier des annonces et
  devient automatiquement membre de la zone Délégation.
- **Professeur** : participe aux salons accessibles et peut signaler du contenu;
  il ne dispose pas de pouvoir de modération par défaut.
- **Étudiant** : participe aux salons accessibles, publie, commente, réagit,
  signale du contenu et gère son profil.

Il n’existe pas de compte séparé « Modérateur ». Le rôle « Chef de classe » est
désormais nommé « Délégué de classe » dans l’interface et correspond à la même
responsabilité de zone.

Seul l’administrateur peut attribuer ou retirer ces responsabilités. Il ne peut
y avoir qu’un Délégué Général et un Délégué de classe par filière. La base
garantit ces limites avec des index uniques; la responsabilité de Délégué Général
peut être cumulée avec celle de Délégué de classe.

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

Toute adresse au format email standard est acceptée, quel que soit son domaine.

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

La sécurité de l’accès est effectuée dans les contrôleurs via des garde-fous
(`checkAdmin()`, `checkAccess()`, etc.). Lorsque la base est disponible, les
accès administrateur et modération revalident le rôle enregistré en base ; les
dashboards délégué et chef de classe vérifient leurs appartenances de zone.
Les écrans d'administration, de modération et les dashboards de responsables
renvoient une erreur de service indisponible si la base ne peut pas être vérifiée.

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

### 6.2 Dashboard délégué général

L'accès est accordé à partir de la responsabilité `delegue_general` dans une zone
de type `delegation`. Le délégué général voit les indicateurs, l’activité par
zone et les derniers signalements à l’échelle de l’école. Cette vue est en
lecture seule : elle ne contient aucune action de modération.

Il affiche les statistiques globales des comptes, publications et signalements,
l’activité des zones de l’établissement et les derniers signalements à transmettre
à l’administration.

### 6.3 Dashboard chef de classe

L'accès est accordé à partir de la responsabilité `delegue_de_classe` dans une zone
de type `filiere`. L’interface l’appelle « Délégué de classe ». Les membres et
signalements affichés ainsi que les actions de modération sont limités à cette
zone.

Le schéma actuel ne définit pas de table de classes ou de groupes : le tableau
de bord ne peut donc pas encore distinguer plusieurs classes au sein d'une
même filière.

### 6.4 Dashboard utilisateur standard

Un utilisateur standard voit ses informations personnelles. Les indicateurs
globaux ne sont pas affichés sur ce tableau de bord.

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

### 8.4 Contenu de la page d’accueil

Depuis `admin/settings`, les administrateurs peuvent modifier les visuels, slogans et descriptions des filières actives, les photos et légendes du carrousel principal, ainsi que les quatre statistiques affichées sur ordinateur et téléphone. Les images téléversées acceptent JPG, PNG ou WebP, jusqu’à 5 Mo chacune.

Les réglages du carrousel et des statistiques sont conservés dans `landing_settings`. Les champs `image_url` et `slogan` sont ajoutés à `filieres` par la migration `20261107_add_landing_management.sql`.

### Tags et mentions dans le salon

Les publications peuvent être associées à des tags actifs et mentionner jusqu'à dix personnes ayant accès au salon. Les résultats de recherche sont classés par rôle (professeurs, délégation, administration) et une mention envoie une notification à la personne sélectionnée. Les membres peuvent proposer de nouveaux tags ; ils restent invisibles dans le fil jusqu'à leur validation par un administrateur depuis « Filières & Salons ». Les administrateurs peuvent également créer, renommer, désactiver, réactiver ou supprimer des tags. Les tags actifs peuvent servir à filtrer le fil.

Pour une base existante, exécuter `database/migrations/20261108_add_publication_tags_mentions.sql` après les migrations précédentes.

---

## 9. Gestion des signalements et modération

### 9.1 Principe

Un utilisateur peut signaler une publication ou un commentaire. Les administrateurs et délégués de classe décident ensuite des actions dans leur périmètre :

- avertir l’auteur,
- bloquer la publication,
- supprimer le contenu,
- rejeter le signalement.

Seuls les administrateurs peuvent agir sur toute la plateforme. Un délégué de
classe peut traiter les signalements de ses filières attribuées uniquement. Le
délégué général consulte les signalements globalement, sans pouvoir les traiter.
Les professeurs et étudiants ne disposent pas de droits de modération.

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

Les publications de type `discussion` ouvrent un panneau latéral contenant le sujet et ses commentaires. Les membres peuvent commenter et aimer les commentaires; le commentaire ayant le plus de likes est automatiquement marqué comme solution. Une `annonce` n'autorise que les likes. Une publication de type `question` est un sondage à choix multiples : les membres votent et peuvent modifier leur vote, sans commentaires ni réactions.

Le formulaire adapte ses champs au type choisi. Un sondage requiert de 2 à 10 choix et chaque membre ne peut avoir qu'un vote par publication.

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

### 10.4 Profil et bibliothèque personnelle

Le profil de son propre compte regroupe les publications, les salons rejoints, les fichiers partagés et les favoris. Les publications personnelles signalées restent visibles par leur auteur avec leur état de modération; elles restent masquées aux autres visiteurs.

Les fichiers sont joints à une publication ou à un commentaire, et non téléversés dans un espace indépendant. Depuis le profil, leur auteur peut les filtrer par rubrique, les prévisualiser lorsqu’il s’agit d’images, les télécharger et les ajouter ou retirer des favoris. L’accès aux fichiers vérifie également les droits sur la publication et le salon.

Les nouvelles pièces jointes sont stockées hors du dossier public puis servies via les routes protégées du projet. Les métadonnées `type_media` et `ordre_affichage` sont ajoutées par `database/migrations/20261108_add_attachment_media_metadata.sql` et sont également présentes dans le schéma neuf de `database.sql`. Les pièces jointes sont supprimées lors de la suppression modérée de leur publication/commentaire; les avatars sont redimensionnés à 400 × 400 pixels et supprimés lors de leur remplacement ou de la suppression du compte.

Le salon propose également la rubrique « Documents partagés », filtrée par salon ou catégorie. Elle regroupe les fichiers des publications et commentaires accessibles; les PDF peuvent y être prévisualisés et tous les fichiers téléchargés via les routes protégées du projet.

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
- `pieces_jointes_sauvegardees`
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
- Extension PHP `mbstring`
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

L'extension `mbstring` est requise par les vues, les notifications et la
modération. La configuration arrête explicitement l'application si elle est
absente ; activez-la dans la configuration PHP de l'environnement.

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
