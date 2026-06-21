# GitHub — EduSphere

Git local initialisé sur `main`. Pour créer le dépôt distant :

## 1. Connexion GitHub (une seule fois)

```powershell
gh auth login
```

## 2. Créer le dépôt et pousser

```powershell
cd c:\Coding\EdusSphere
gh repo create EdusSphere --public --source=. --remote=origin --push
```

Variante privée : remplacer `--public` par `--private`.

## 3. Vérification

```powershell
git remote -v
git status
```

Le fichier `.env` est exclu par `.gitignore` — jamais le committer.
