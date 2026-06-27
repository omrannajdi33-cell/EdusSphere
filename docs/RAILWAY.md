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
| `SESSION_DRIVER` | `file` |
| `CACHE_STORE` | `file` |
| `QUEUE_CONNECTION` | `sync` |
| `RUN_SEED` | `false` (ne pas re-seeder à chaque deploy) |
| `RUN_FRESH_SEED` | `true` **une seule fois** pour effacer la démo et créer l’admin prod |
| `PORT` | `8080` (fixe le port public Railway — **obligatoire** si 502) |
| `ADMIN_EMAIL` | `admin@ontech.com` |
| `ADMIN_PASSWORD` | `123` |
| `ADMIN_NAME` | `Administrateur` |

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

Ouvre `https://TON-DOMAINE.up.railway.app/login` → connexion **admin** :

- Email : `admin@ontech.com`
- Mot de passe : `123`

### Réinitialiser la prod (effacer la démo)

Variables temporaires :

```
RUN_FRESH_SEED=true
RUN_SEED=false
APP_ENV=production
```

Redeploy une fois, puis remets `RUN_FRESH_SEED=false`.

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
| **Application failed to respond (502)** | Serveur arrêté ou mauvaise config | Voir ci-dessous |

### 502 — « Application failed to respond »

Cause la plus fréquente : **le port public Railway ≠ port de l’app**.

1. **Variables** → ajoute `PORT=8080` → Save → Redeploy
2. **Settings → Networking → Public Networking** → port cible = **8080** (pas 3000)
3. **Settings → Deploy → Custom Start Command** : **vide** (railway.toml gère `/start.sh`)
4. **Deploy Logs** : cherche `Starting HTTP server on 0.0.0.0:8080`
5. **Console** : `curl -s http://127.0.0.1:8080/railway-health.txt` → `ok`

Réf. : [Railway — Application failed to respond](https://docs.railway.com/networking/troubleshooting/application-failed-to-respond)
