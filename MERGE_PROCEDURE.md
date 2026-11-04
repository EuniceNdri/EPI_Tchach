# Procédure de merge de la branche feature vers main

Cette procédure est dédiée uniquement à la branche suivante :

- paul/feature/reactions-notifications-signalements-moderation

Elle permet de fusionner cette branche dans la branche principale sans toucher à la structure du projet ni aux dossiers déjà validés.

## 1) Vérifier l’état du dépôt

```bash
git status
git branch -a
```

## 2) Valider les changements de la branche

```bash
git checkout paul/feature/reactions-notifications-signalements-moderation
git add .
git commit -m "refactor: finalisation des modules reactions notifications signalements"
```

## 3) Mettre à jour la branche principale

```bash
git checkout main
git pull --ff-only origin main
```

## 4) Fusionner uniquement la branche feature

```bash
git merge --no-ff paul/feature/reactions-notifications-signalements-moderation -m "Merge branch 'paul/feature/reactions-notifications-signalements-moderation'"
```

## 5) Publier sur le dépôt distant

```bash
git push origin main
```

## 6) En cas de conflit

Si Git signale des conflits :

```bash
git status
```

- ouvrir les fichiers conflictuels,
- corriger uniquement les points nécessaires,
- puis :

```bash
git add <fichiers_resolus>
git commit -m "fix: resolution des conflits merge main"
```

## 7) Vérification finale

```bash
git status
git log --oneline --decorate -n 5
```

## Important

Cette procédure ne doit être utilisée que pour la branche feature concernée. Elle n’inclut pas d’autres branches et ne modifie pas la structure du projet en dehors du contenu validé de cette fonctionnalité.
