# GitHub — EduSphere

Dépôt distant : [github.com/omrannajdi33-cell/EdusSphere](https://github.com/omrannajdi33-cell/EdusSphere)

## 1. Lier le projet (une seule fois)

```powershell
cd c:\Coding\EdusSphere
git remote add origin https://github.com/omrannajdi33-cell/EdusSphere.git
git branch -M main
```

Si `origin` existe déjà :

```powershell
git remote set-url origin https://github.com/omrannajdi33-cell/EdusSphere.git
```

## 2. Pousser le code

```powershell
git add .
git status
git commit -m "V1 EduSphere : activités, examens, leçons, bulletins PDF, PWA"
git push -u origin main
```

GitHub demandera une connexion (navigateur ou token personnel).

## 3. iPad — installer l’app (PWA)

GitHub **héberge le code**, pas l’application Laravel. Pour utiliser EduSphere sur iPad :

1. **Déployer** sur un serveur avec URL **HTTPS** (Railway, Render, VPS, etc.) ou utiliser ton PC en réseau local avec Docker (`http://IP-DU-PC:8080` — PWA limitée sans HTTPS).
2. Ouvrir l’URL dans **Safari** sur l’iPad.
3. Se connecter (`eleve1@edusphere.fr` / `password` pour tester).
4. Bouton **Partager** → **Sur l’écran d’accueil** → l’icône EduSphere s’installe comme une app.

Le manifest PWA est dans `public/pwa/manifest.json`.

## 4. Fichiers à ne jamais committer

- `.env` (secrets, mots de passe DB)
- `vendor/`, `node_modules/`
- Données élèves en production

Copier `.env.example` → `.env` sur le serveur après déploiement.
