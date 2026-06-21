# Décisions de base — Phase 0.1

Document de validation des choix fondateurs du projet EduSphere.  
**Statut :** confirmé le 21 juin 2026.

---

## Décisions P0 (validées)

| # | Décision | Valeur retenue |
|---|----------|----------------|
| 1 | Nom du projet | **EduSphere** |
| 2 | Cible principale | Enfants de **7 à 10 ans** |
| 3 | Mode d'utilisation | **Tablette first** (desktop/mobile secondaires) |
| 4 | Espaces applicatifs | **Professeur/Admin** + **Élève** (deux espaces distincts) |
| 5 | Backend | **PHP — Laravel 12** |
| 6 | Frontend HTML | **Blade** (templates Laravel) |
| 7 | Styles | **Tailwind CSS** |
| 8 | Interactivité | **Alpine.js** + **JavaScript ES6+** |
| 9 | Base de données | **MySQL** |
| 10 | Environnement local | **Docker obligatoire** (`docker compose up -d`) |
| 11 | Hébergement code | **GitHub** |
| 12 | Intelligence artificielle | **Aucune** — contenu 100 % créé par le professeur |
| 13 | Base des activités | **Moteur de pages interactives** — les PDF ne sont pas la base principale |

---

## Conteneurs Docker retenus

| Service | Rôle |
|---------|------|
| `app` | Laravel / PHP-FPM |
| `nginx` | Serveur web → `public/index.php` |
| `mysql` | Base de données |
| `phpmyadmin` | Administration BDD (dev) |
| `node` | Compilation Vite (assets) |
| `scheduler` | Tâches planifiées Laravel |

---

## Rôles utilisateurs

| Rôle | Code | Accès |
|------|------|-------|
| Administrateur / Professeur | `teacher` | Complet |
| Élève | `student` | Restreint à son propre contenu |

---

## Clarifications par rapport au cahier V1.0

Le cahier V1.0 mentionnait Laragon et GitLab. Les ajouts **V1.1** imposent **Docker** et **GitHub** — ces choix prévalent pour le développement actuel.

---

## Références

- [Périmètre V1 / V2](02-v1-v2-scope.md)
- [Cahier des charges](../../cahier-de-charge.md)
