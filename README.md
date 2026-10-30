# 📘 Documentation du Projet – Forum de l'École

> **Architecture MVC + Organisation Git/GitHub**

---

## 📑 Table des matières

1. [Présentation de l'Architecture MVC](#1--présentation-de-larchitecture-mvc)
2. [Structure des dossiers](#2--structure-des-dossiers)
3. [Organisation Git & GitHub](#3--organisation-git--github)
4. [Règles d'équipe](#4--règles-déquipe)
5. [Résumé rapide des commandes](#5--résumé-rapide-des-commandes)

---

## 1. 🏗️ Présentation de l'Architecture MVC

Ce projet suit le pattern **MVC** (Modèle – Vue – Contrôleur).
L'objectif est de séparer clairement les responsabilités pour rendre le code plus propre, maintenable et évolutif.

### Schéma du fonctionnement

```
Navigateur
    ↓
public/index.php          ← Point d'entrée unique (Front Controller)
    ↓
Controller                ← Traite la demande de l'utilisateur
    ↓
Model                     ← Communique avec la base de données
    ↓
View                      ← Génère le HTML à afficher
    ↓
Navigateur (réponse)
```

---

## 2. 📁 Structure des dossiers

```
EPI_Tchach/
│
├── config/                       → Configuration de l'application
│   └── database.php              → Connexion à la base de données (PDO)
│
├── controllers/                  → Logique métier + traitement des requêtes
│   ├── AuthController.php
│   ├── PublicationController.php
│   ├── ZoneController.php
│   ├── CommentaireController.php
│   └── ...
│
├── models/                       → Accès aux données (requêtes SQL)
│   ├── User.php
│   ├── Publication.php
│   ├── Commentaire.php
│   ├── Zone.php
│   └── ...
│
├── views/                        → Affichage (fichiers HTML/PHP)
│   ├── auth/                    → Pages d'authentification
│   │   ├── login.php            → Formulaire de connexion
│   │   ├── register.php         → Formulaire d'inscription
│   │   ├── forgot-password.php  → Mot de passe oublié
│   │   └── reset-password.php   → Réinitialisation du mot de passe
│   ├── desktop/                 → Vues pour ordinateur
│   └── smartphone/              → Vues pour mobile
│
├── public/                       → Seul dossier accessible depuis le navigateur
│   ├── index.php                → Point d'entrée de toute l'application
│   ├── .htaccess                → Réécriture d'URL (tout passe par index.php)
│   └── assets/
│       ├── css/
│       │   ├── desktop/
│       │   └── smartphone/
│       ├── js/
│       │   ├── desktop/
│       │   └── smartphone/
│       └── images/
│
├── .htaccess                     → Redirige vers public/
├── .gitignore
└── README.md
```

### Rôle précis de chaque dossier

| Dossier | Rôle principal | Qui y touche le plus ? |
|---------|---------------|----------------------|
| `config/` | Paramètres de l'application (BDD, constantes...) | Tout le monde (avec précaution) |
| `controllers/` | Reçoit la demande, appelle le Model, envoie à la View | Développeurs backend |
| `models/` | Contient toutes les requêtes SQL | Développeurs backend |
| `views/` | Contient le HTML + petits bouts de PHP d'affichage | Développeurs frontend |
| `views/auth/` | Pages d'authentification (connexion, inscription...) | Développeurs frontend/backend |
| `public/` | Fichiers publics (CSS, JS, images, `index.php`) | Tout le monde |
| `public/index.php` | Routeur principal de l'application | Développeurs backend |

---

## 3. 🔀 Organisation Git & GitHub

### 3.1 Stratégie de branches

Nous utilisons une stratégie simple et claire adaptée à un projet scolaire en équipe.

| Branche | Utilité | Qui y pousse ? |
|---------|---------|---------------|
| `main` | Version stable (celle qui fonctionne) | Seulement après validation |
| `develop` | Branche de développement principale | Tout le monde (via Merge Request) |
| `prenom/...` | Branches de travail individuelles | Chaque membre |

### 3.2 Convention de nommage des branches (⚠️ OBLIGATOIRE)

**Format strict :**

```
prenom/type/nom-de-la-fonctionnalite
```

**Types autorisés :**

| Type | Description |
|------|-------------|
| `feature/` | Nouvelle fonctionnalité |
| `fix/` | Correction de bug |
| `refactor/` | Amélioration du code sans changer le comportement |
| `style/` | CSS / design uniquement |
| `docs/` | Documentation |
| `chore/` | Configuration, fichiers divers |

**Exemples concrets :**

| Membre | Exemple de branche |
|--------|-------------------|
| Momo | `momo/feature/authentification` |
| Momo | `momo/feature/systeme-reactions` |
| Sarah | `sarah/feature/zones-categories` |
| Jean | `jean/fix/upload-fichier` |
| Sarah | `sarah/style/page-connexion` |
| Shadow | `shadow/feature/authentification` |
| Momo | `momo/docs/mise-a-jour-readme` |

> **⚠️ Important :** Toujours mettre son prénom en minuscule au début de la branche.

### 3.3 Workflow de travail recommandé

```bash
# 1. Se mettre à jour avant de commencer
git checkout develop
git pull origin develop

# 2. Créer sa branche de travail
git checkout -b prenom/feature/nom-fonctionnalite

# 3. Travailler sur les fichiers...

# 4. Voir ce qui a été modifié
git status

# 5. Ajouter les fichiers
git add .

# 6. Faire un commit clair
git commit -m "feat: ajout de l'authentification avec email institutionnel"

# 7. Envoyer sa branche sur GitHub
git push -u origin prenom/feature/nom-fonctionnalite
```

Ensuite, on crée une **Pull Request** (ou Merge Request) vers la branche `develop`.

### 3.4 Convention des messages de commit

Nous utilisons le format **Conventional Commits** :

| Préfixe | Signification | Exemple |
|---------|--------------|---------|
| `feat:` | Nouvelle fonctionnalité | `feat: ajout du système de réactions` |
| `fix:` | Correction de bug | `fix: correction de la validation email` |
| `docs:` | Documentation | `docs: mise à jour du README` |
| `style:` | CSS / mise en page | `style: amélioration du header mobile` |
| `refactor:` | Amélioration du code | `refactor: simplification du modèle User` |
| `chore:` | Tâches diverses | `chore: ajout du fichier .gitignore` |

> **💡 Bonne pratique :** Tu peux aussi indiquer ton prénom dans le commit :
> ```bash
> git commit -m "feat(shadow): ajout de l'authentification"
> ```

### 3.5 Fichier `.gitignore` recommandé

Créer un fichier `.gitignore` à la racine avec au minimum :

```gitignore
# Fichiers système
.DS_Store
Thumbs.db

# IDE
.vscode/
.idea/
*.sublime-project
*.sublime-workspace

# Dépendances
/vendor/
node_modules/

# Fichiers d'environnement
.env
.env.local

# Logs
*.log

# Fichiers uploadés (si stockés localement)
/public/uploads/
```

---

## 4. ✅ Règles d'équipe (à respecter)

- ❌ **Ne jamais** travailler directement sur `main` ou `develop`.
- ✅ **Toujours** créer une branche avec son prénom.
- ✅ Faire des **commits réguliers** et clairs.
- 🔄 Avant de commencer une nouvelle tâche, toujours faire un `git pull` sur `develop`.
- 📬 Une fois la fonctionnalité terminée → créer une **Pull Request** vers `develop`.
- 👀 Ne pas merger soi-même sa Pull Request (demander une **relecture** si possible).

---

## 5. ⌨️ Résumé rapide des commandes

```bash
git status
git checkout develop
git pull origin develop
git checkout -b prenom/feature/nom-fonctionnalite
git add .
git commit -m "feat: description claire"
git push -u origin prenom/feature/nom-fonctionnalite
```

---

## 6. 📎 Pièces jointes

Les publications et commentaires peuvent recevoir jusqu'à cinq fichiers par envoi,
avec une taille maximale de 10 Mo par fichier. Les formats autorisés sont les PDF,
documents Office, fichiers texte, archives ZIP et images courantes.

Chaque fichier est contrôlé côté serveur (erreur d'upload, extension, type MIME réel
et taille), renommé avec un identifiant aléatoire, puis enregistré dans
`public/uploads/`. La table `pieces_jointes` conserve le nom original, le type,
la taille et le lien vers la publication ou le commentaire concerné.

Les fichiers ne sont pas servis directement : le téléchargement passe par la route
contrôlée `attachment/download/{id}` et le dossier d'upload interdit l'exécution de
scripts.

## 7. 🔎 Recherche

Depuis le Salon Général, la recherche permet de retrouver les publications par
titre, contenu, auteur, catégorie, tag ou nom de pièce jointe. Les résultats sont
chargés côté serveur avec des requêtes préparées et sont limités à dix résultats
par page.

Les fichiers associés apparaissent dans les résultats avec leur nom et un lien de
téléchargement contrôlé. Une recherche vide réaffiche le flux habituel et un
message explicite est affiché lorsqu'aucun résultat ne correspond à la recherche.

> 📅 *Document mis à jour – Octobre 2026*
> 📚 *Projet Forum de Discussion et d'Entraide – École*