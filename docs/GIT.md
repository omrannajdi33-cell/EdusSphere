# Conventions Git — EduSphere

## Branches

| Préfixe | Usage | Exemple |
|---------|-------|---------|
| `main` | Production stable | — |
| `develop` | Intégration continue | — |
| `feature/` | Nouvelle fonctionnalité | `feature/auth-login` |
| `fix/` | Correction de bug | `fix/docker-mysql` |
| `docs/` | Documentation seule | `docs/cadrage-v2` |

## Commits (Conventional Commits)

```
type(scope): description courte en français ou anglais

feat(admin): ajouter CRUD élèves
fix(docker): corriger connexion MySQL
docs(readme): documenter docker compose
chore(deps): mettre à jour Laravel
test(auth): tester middleware rôle élève
```

Types : `feat`, `fix`, `docs`, `style`, `refactor`, `test`, `chore`, `build`, `ci`.

## GitHub

1. Créer un dépôt vide `EdusSphere` sur GitHub
2. Lier le remote :

```bash
git remote add origin https://github.com/VOTRE_COMPTE/EdusSphere.git
git push -u origin main
```

## Fichiers jamais versionnés

- `.env`
- `vendor/`, `node_modules/`
- `storage/*.key`, logs
- `database/database.sqlite`
