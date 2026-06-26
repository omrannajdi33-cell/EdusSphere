# Design system — Phase 5

Référence visuelle et composants Blade réutilisables pour EduSphere (tablette first, 7–10 ans).

---

## Principes UX

| Règle | Application |
|-------|-------------|
| Tablette first | Touch targets ≥ 48px, nav inférieure élève jusqu'à `lg` |
| Pas de tableaux lourds | Cartes, grilles, graphiques barres |
| Pas de menus complexes | Sidebar admin linéaire, 7 onglets élève |
| Texte lisible | `text-base` minimum, titres `text-2xl+` |
| 3 clics max | Dashboard → section via nav inférieure en 1 clic |

---

## Palette globale

| Token CSS | Valeur | Usage |
|-----------|--------|-------|
| `--color-es-bg` | `#f6f5f2` | Fond principal (blanc cassé) |
| `--color-es-bg-soft` | `#eeede8` | Fond secondaire (gris ultra clair) |
| `--color-es-accent` | `#6366f1` | Actions, liens actifs |

Config matières : `config/subjects.php` — helper `App\Support\SubjectTheme`.

| Matière | Couleur |
|---------|---------|
| Français | Bleu `#3b82f6` |
| Mathématiques | Violet `#8b5cf6` |
| Sciences | Vert `#22c55e` |
| Histoire | Orange `#f97316` |
| Géographie | Cyan `#06b6d4` |
| Islam | Emerald `#10b981` |
| Natation | Aqua `#2dd4bf` |
| Éducation physique | Rouge doux `#f87171` |

---

## Grille responsive

| Classe | Breakpoints |
|--------|-------------|
| `.es-container` | max-width 5xl, padding 4/6 |
| `.es-page-grid` | 1 → 2 → 3 → 4 colonnes |
| `.es-stat-grid` | 2 colonnes → 4 sur desktop |

---

## Composants Blade

| Composant | Fichier | Props clés |
|-----------|---------|------------|
| Carte | `card.blade.php` | `title`, `padding` |
| Bouton | `button.blade.php` | `variant`, `href` |
| Formulaire | `form.blade.php` | `action`, `method` |
| Input | `input.blade.php` | `label`, `name`, `error` |
| Modal | `modal.blade.php` | `title`, Alpine `open` |
| Alerte | `alert.blade.php` | `type`, `title` |
| Badge statut | `status-badge.blade.php` | `status`, `label` |
| Progression | `progress-bar.blade.php` | `value`, `max`, `color` |
| Avatar | `avatar.blade.php` | `name`, `src`, `size` |
| Onglets | `tabs.blade.php` + `tab-panel.blade.php` | `tabs[]`, `default` |
| Calendrier | `calendar.blade.php` | `month`, `year`, `events[]` |
| Graphique | `chart.blade.php` | `items[]` label/value/color |
| Icône matière | `subject-icon.blade.php` | `icon`, `color` |
| Carte matière | `subject-card.blade.php` | `subject`, `progress`, `href` |
| Nav admin | `admin-nav.blade.php` | `active` |
| Nav élève | `student-bottom-nav.blade.php` | `active` (7 onglets) |

---

## Classes utilitaires (`resources/css/app.css`)

- `.es-card` — carte 24px radius, ombre douce
- `.es-card-interactive` — hover lift
- `.es-btn-primary` / `.es-btn-ghost` — boutons
- `.es-input`, `.es-label`, `.es-select`, `.es-textarea`
- `.es-page-enter` — animation entrée page
- `.es-bottom-nav` — barre inférieure tablette

---

## Layouts

| Layout | Usage |
|--------|-------|
| `layouts/app.blade.php` | Base HTML, mesh, flash |
| `layouts/admin.blade.php` | Sidebar + contenu |
| `layouts/student.blade.php` | Header + nav inférieure |
| `layouts/guest.blade.php` | Login / pages publiques |

---

## Navigation élève (7 onglets)

Accueil · Matières · Leçons · Activités · Examens · Horaire · Points

Routes placeholder : `/student/subjects`, `/lessons`, `/activities`, `/exams`, `/schedule`, `/points`.
