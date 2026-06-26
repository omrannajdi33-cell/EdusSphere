# EduSphere

> **GitHub n’est pas l’application.** L’URL `github.com/omrannajdi33-cell/EdusSphere` affiche ce fichier README (documentation).  
> En production, ouvre l’URL de ton **serveur déployé** (ex. `https://edusphere.example.com`) — la page d’accueil redirige vers `/login`.

Plateforme web éducative interactive (PWA) pour enfants de **7 à 10 ans**, conçue en **tablette first**. Elle centralise leçons, activités, examens, corrections, points et administration scolaire dans une seule application tactile.

**Stack :** HTML (Blade) · PHP (Laravel 12) · MySQL · Tailwind CSS · Alpine.js · Docker · GitHub

---

## Démarrage rapide (Docker)

```bash
docker compose up -d --build
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
npm run build
```

**Performance (Windows / Docker)** — après mise à jour, rebuild une fois :

```bash
docker compose down
docker compose up -d --build
docker compose exec app php artisan config:clear
```

Dans ton `.env`, utilise Redis (déjà dans `.env.example`) :

```
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis
LOG_LEVEL=warning
```

Les assets CSS/JS doivent être **compilés** (`npm run build`), pas le serveur Vite en dev, sauf si tu lances `docker compose --profile dev up node`.


| Service | URL |
|---------|-----|
| Application | http://localhost:8080 |
| Connexion | http://localhost:8080/login |

**Comptes démo** (après `db:seed`) : `prof@edusphere.fr` / `eleve1@edusphere.fr` — mot de passe `password`
| Vite (assets dev) | http://localhost:5173 |
| phpMyAdmin | http://localhost:8081 |

Conteneurs par défaut : `app`, `nginx`, `mysql`, `redis`.

Optionnels : `docker compose --profile dev up node` (Vite) · `docker compose --profile tools up phpmyadmin`

Voir [docs/GIT.md](docs/GIT.md) pour les conventions Git · [docs/GITHUB.md](docs/GITHUB.md) pour GitHub · [docs/RAILWAY.md](docs/RAILWAY.md) pour déployer sur Railway (iPad / production).

---

## Description du produit

EduSphere remplace les cahiers papier et les feuilles d'exercices par une expérience numérique immersive, proche d'une application native iPad. Le professeur crée tout le contenu pédagogique ; les élèves consultent leurs matières, réalisent des activités interactives (écrire, dessiner, répondre), passent des examens et suivent leur progression.

Le cœur du produit est un **moteur de pages interactives** — les PDF ne servent pas de base principale des activités.

Aucune intelligence artificielle n'est intégrée.

---

## Objectifs principaux

### Professeur / Admin

- Gérer les élèves (CRUD, avatar, niveau scolaire)
- Gérer matières et compétences (pondération 100 % par matière)
- Créer et publier des leçons (import médias : PPT, PDF, images, vidéo, audio)
- Créer des activités interactives multi-pages avec questions variées
- Créer et planifier des examens (minuterie, tentatives, soumission auto)
- Corriger, annoter et renvoyer les travaux
- Attribuer des points (ClassDojo-like)
- Planifier les horaires et publier des annonces
- Générer des bulletins PDF

### Élève

- Consulter ses matières, leçons, activités et examens
- Écrire, dessiner, surligner sur les pages interactives
- Bénéficier de la sauvegarde automatique (20 s, changement de page, etc.)
- Soumettre ses activités et consulter les corrections
- Passer ses examens dans les créneaux autorisés
- Suivre ses notes, points, progression et horaire
- Gérer son profil (photo, avatar par défaut)

---

## Règles UX — Tablette first

| Règle | Détail |
|-------|--------|
| Appareil principal | Tablette (iPad-like) ; desktop et mobile en support secondaire |
| Simplicité | Interface minimaliste, aérée, adaptée aux enfants |
| Navigation | Barre inférieure élève : Accueil, Matières, Leçons, Activités, Examens, Horaire, Points |
| Clics max | 3 clics pour atteindre une fonctionnalité importante |
| Visuel | Cartes arrondies (24 px), ombres douces, couleurs par matière, grandes polices |
| Interdit | Tableaux surchargés, menus complexes, textes inutiles, petites polices |
| Inspirations | Apple, Goodnotes, Notion, Linear, ClassDojo |

---

## Contraintes de sécurité

- Authentification Laravel avec mots de passe hashés
- Middleware et policies par rôle (admin/professeur vs élève)
- Protection CSRF sur tous les formulaires
- Validation backend systématique
- Un élève ne voit que ses propres données
- Uploads : types MIME et tailles contrôlés (photo profil ≤ 5 Mo)
- Fichiers privés hors accès public direct
- Déconnexion automatique après inactivité (P1)
- Aucune donnée élève accessible publiquement

---

## Contraintes de performance

| Métrique | Objectif |
|----------|----------|
| Chargement initial | < 2 secondes |
| Actions courantes | < 300 ms |
| Animations | 60 FPS |
| Sauvegarde auto | < 1 seconde |

---

## Documentation de cadrage (Phase 0)

| Document | Contenu |
|----------|---------|
| [Décisions de base](docs/cadrage/01-decisions.md) | Choix validés (nom, cible, stack, hébergement) |
| [Périmètre V1 / V2](docs/cadrage/02-v1-v2-scope.md) | Ce qui entre en V1 minimale vs report V2 |
| [Écrans attendus](docs/cadrage/03-ecrans.md) | Liste complète des écrans admin et élève |
| [Routes Laravel](docs/cadrage/04-routes-laravel.md) | Arborescence des routes |
| [Carte BDD](docs/cadrage/05-base-de-donnees.md) | Tables, relations, champs clés |

---

## Matières officielles (seeders)

Français · Mathématiques · Sciences · Histoire · Géographie · Islam · Natation · Éducation physique

Chaque matière : couleur, icône, compétences (total = 100 %).

---

## Hiérarchie pédagogique

```
Matière → Compétence → Leçon → Activité → Pages → Questions → Correction → Résultat
                              ↘ Examen ↗
```

---

## Plan de travail

- **Todo interactive :** ouvrir [`todo-list.html`](todo-list.html) dans le navigateur
- **Cahier des charges complet :** [`cahier-de-charge.md`](cahier-de-charge.md)
- **Phase actuelle :** Phase 7 terminée (P0) → Phase 8 (leçons)

---

## Structure cible du dépôt

```
EdusSphere/
├── app/
├── bootstrap/
├── config/
├── database/
├── docker/
├── docs/cadrage/
├── public/
├── resources/
├── routes/
├── storage/
├── tests/
├── docker-compose.yml
├── Dockerfile
└── README.md
```

---

## Licence & client

Projet : **EduSphere V1** — École d'été personnalisée.
