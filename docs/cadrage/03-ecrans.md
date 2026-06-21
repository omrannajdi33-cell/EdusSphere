# Écrans attendus — Phase 0.2

Liste des écrans à implémenter en Blade (HTML). Convention de nommage des vues : `resources/views/{espace}/{module}/{action}.blade.php`.

---

## Écrans publics

| # | Écran | Vue Blade cible | Description |
|---|-------|-----------------|-------------|
| P1 | Accueil / redirection | `welcome.blade.php` | Redirige vers login ou dashboard selon rôle |
| P2 | Connexion | `auth/login.blade.php` | Email + mot de passe, CSRF |
| P3 | Mot de passe oublié | `auth/forgot-password.blade.php` | P1 — optionnel V1 |
| P4 | Page offline PWA | `offline.blade.php` | Affichée sans réseau |

---

## Espace Admin / Professeur

Layout : `layouts/admin.blade.php` (sidebar ou top nav desktop, responsive tablette).

| # | Écran | Vue | Route |
|---|-------|-----|-------|
| A1 | Dashboard admin | `admin/dashboard.blade.php` | `/admin` |
| A2 | Liste élèves | `admin/students/index.blade.php` | `/admin/students` |
| A3 | Créer élève | `admin/students/create.blade.php` | `/admin/students/create` |
| A4 | Modifier élève | `admin/students/edit.blade.php` | `/admin/students/{id}/edit` |
| A5 | Liste matières | `admin/subjects/index.blade.php` | `/admin/subjects` |
| A6 | CRUD matière | `admin/subjects/form.blade.php` | create/edit |
| A7 | Compétences par matière | `admin/skills/index.blade.php` | `/admin/subjects/{id}/skills` |
| A8 | CRUD compétence | `admin/skills/form.blade.php` | create/edit |
| A9 | Liste leçons | `admin/lessons/index.blade.php` | `/admin/lessons` |
| A10 | Créer / éditer leçon | `admin/lessons/form.blade.php` | create/edit |
| A11 | Prévisualisation leçon | `admin/lessons/preview.blade.php` | `/admin/lessons/{id}/preview` |
| A12 | Liste activités | `admin/activities/index.blade.php` | `/admin/activities` |
| A13 | Éditeur activité | `admin/activities/editor.blade.php` | create/edit (multi-pages) |
| A14 | Prévisualisation activité | `admin/activities/preview.blade.php` | preview |
| A15 | Liste examens | `admin/exams/index.blade.php` | `/admin/exams` |
| A16 | Créer / éditer examen | `admin/exams/form.blade.php` | create/edit |
| A17 | File corrections | `admin/corrections/index.blade.php` | `/admin/corrections` |
| A18 | Interface correction | `admin/corrections/show.blade.php` | annoter, noter, renvoyer |
| A19 | Grille points | `admin/points/index.blade.php` | `/admin/points` |
| A20 | Config actions points | `admin/points/settings.blade.php` | `/admin/points/settings` |
| A21 | Gestion horaires | `admin/schedules/index.blade.php` | `/admin/schedules` |
| A22 | Éditeur horaire | `admin/schedules/form.blade.php` | jour/semaine/mois |
| A23 | Liste annonces | `admin/announcements/index.blade.php` | `/admin/announcements` |
| A24 | CRUD annonce | `admin/announcements/form.blade.php` | create/edit |
| A25 | Bulletins | `admin/reports/index.blade.php` | `/admin/reports` |
| A26 | Générer bulletin | `admin/reports/generate.blade.php` | sélection élève/période |
| A27 | Paramètres / profil | `admin/settings/profile.blade.php` | `/admin/settings` |

---

## Espace Élève

Layout : `layouts/student.blade.php` + **barre de navigation inférieure** (tablette).

| # | Écran | Vue | Route |
|---|-------|-----|-------|
| E1 | Dashboard élève | `student/dashboard.blade.php` | `/student` |
| E2 | Mes matières | `student/subjects/index.blade.php` | `/student/subjects` |
| E3 | Détail matière | `student/subjects/show.blade.php` | `/student/subjects/{id}` |
| E4 | Mes leçons | `student/lessons/index.blade.php` | `/student/lessons` |
| E5 | Lecture leçon | `student/lessons/reader.blade.php` | pages, nav prev/next |
| E6 | Mes activités | `student/activities/index.blade.php` | à faire / en cours / terminées |
| E7 | Activité interactive | `student/activities/player.blade.php` | moteur pages + outils |
| E8 | Correction reçue | `student/activities/correction.blade.php` | vue simplifiée |
| E9 | Mes examens | `student/exams/index.blade.php` | à venir / actifs / terminés |
| E10 | Passage examen | `student/exams/take.blade.php` | minuterie + soumission |
| E11 | Résultat examen | `student/exams/result.blade.php` | note + commentaires |
| E12 | Mes points | `student/points/index.blade.php` | total + historique |
| E13 | Ma progression | `student/progress/index.blade.php` | graphiques |
| E14 | Mon horaire | `student/schedule/index.blade.php` | jour / semaine / mois |
| E15 | Mes annonces | `student/announcements/index.blade.php` | liste messages |
| E16 | Mon profil | `student/profile/edit.blade.php` | photo, infos |
| E17 | Centre notifications | `student/notifications/index.blade.php` | P1 |

---

## Composants Blade réutilisables

| Composant | Fichier | Usage |
|-----------|---------|-------|
| Carte | `components/card.blade.php` | Dashboard, listes |
| Bouton | `components/button.blade.php` | Actions primaires/secondaires |
| Modal | `components/modal.blade.php` | Confirmations, popup points |
| Badge statut | `components/status-badge.blade.php` | brouillon, publié, soumis… |
| Barre progression | `components/progress-bar.blade.php` | leçons, activités |
| Avatar | `components/avatar.blade.php` | profil, grille points |
| Nav inférieure | `components/student-bottom-nav.blade.php` | layout élève tablette |
| Indicateur sauvegarde | `components/save-indicator.blade.php` | activité / examen |
| Alerte | `components/alert.blade.php` | flash messages |

---

## Total

| Espace | Écrans |
|--------|--------|
| Public | 4 |
| Admin | 27 |
| Élève | 17 |
| Composants | 9 |
| **Total écrans** | **48** |
