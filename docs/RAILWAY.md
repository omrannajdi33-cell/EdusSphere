# Déploiement Railway — EduSphere

Railway exécute **`Dockerfile`** (production). Le dev local utilise **`Dockerfile.dev`** + volumes docker-compose.

## 1. Pousser la config sur GitHub

```powershell
cd c:\Coding\EdusSphere
git add Dockerfile Dockerfile.dev railway.toml docker/railway/ .dockerignore docs/RAILWAY.md docker-compose.yml
git commit -m "Ajouter la configuration de déploiement Railway."
git push
```

## 2. Railway — services

Dans ton projet Railway :

1. **Service web** `EdusSphere` → Settings → **Root Directory** : laisser vide (racine du repo)
2. **Settings → Build** : Dockerfile path = `Dockerfile` (défaut après push)
3. **Add service → Database → MySQL** (plugin Railway)
4. **Networking** → **Generate Domain** (sinon « Unexposed service » = pas d’URL publique)

## 3. Variables d’environnement (service web)

Clique sur le service **EdusSphere** → **Variables** → bouton **Add Reference** → choisis le service **MySQL** → ajoute toutes les variables (`MYSQLHOST`, `MYSQLPORT`, etc.).

Puis ajoute manuellement (Raw Editor) :

| Variable | Valeur |
|----------|--------|
| `APP_NAME` | `EduSphere` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://TON-DOMAINE.up.railway.app` (après Generate Domain) |
| `APP_KEY` | `base64:...` (voir ci-dessous) |
| `LOG_LEVEL` | `warning` |
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | `${{MySQL.MYSQLHOST}}` |
| `DB_PORT` | `${{MySQL.MYSQLPORT}}` |
| `DB_DATABASE` | `${{MySQL.MYSQLDATABASE}}` |
| `DB_USERNAME` | `${{MySQL.MYSQLUSER}}` |
| `DB_PASSWORD` | `${{MySQL.MYSQLPASSWORD}}` |
| `SESSION_DRIVER` | `database` |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `database` |
| `RUN_SEED` | `true` (première fois seulement) |

**APP_KEY** (sur ton PC) :

```powershell
docker compose exec app php artisan key:generate --show
```

> Remplace `MySQL` par le **nom exact** du service MySQL dans Railway (casse incluse).

## 4. Redéployer

Après le push GitHub, Railway rebuild automatiquement. Vérifie **Deploy Logs** :

- ✅ `migrate --force` réussi
- ✅ nginx démarre sur le port `$PORT`

## 5. Tester

Ouvre `https://TON-DOMAINE.up.railway.app` → tu dois voir la **page de connexion**.

Comptes démo (si `RUN_SEED=true`) : `eleve1@edusphere.fr` / `password`

## 6. iPad (PWA)

Safari → URL Railway → **Partager** → **Sur l’écran d’accueil**

## Dépannage

| Erreur | Cause | Solution |
|--------|-------|----------|
| `composer.json not found` | Ancien Dockerfile dev sans code copié | Push Git + redeploy (utilise `Dockerfile` production) |
| `APP_KEY is missing` | Variable non définie | Ajouter `APP_KEY` dans Variables |
| Database not reachable | MySQL pas lié | Vérifier `DB_HOST` etc. avec références `${{MySQL...}}` |
| Unexposed service | Pas de domaine | Generate Domain dans Networking |
| Healthcheck failure | Variables manquantes ou nginx pas démarré | Vérifier `APP_KEY` + références MySQL, puis Redeploy |
