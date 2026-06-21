# Périmètre V1 minimale vs V2 — Phase 0.1

Ce document fixe ce qui doit être livré en **V1** (lancement école d'été) et ce qui est reporté en **V2**.

---

## V1 — Indispensable (Definition of Done)

La V1 est terminée lorsque tous les critères suivants sont remplis :

### Infrastructure

- [ ] `docker compose up -d` démarre toute l'application
- [ ] Laravel 12 + MySQL + Vite + Tailwind + Alpine opérationnels

### Auth & sécurité

- [ ] Connexion professeur et élève
- [ ] Routes protégées par rôle (middleware)
- [ ] Données élèves non publiques

### Gestion de base

- [ ] CRUD élèves, matières, compétences
- [ ] Pondération compétences = 100 % par matière (bloquant si invalide)
- [ ] 8 matières officielles en seeders

### Pédagogie

- [ ] Leçons : création admin + consultation élève + import médias basique
- [ ] Activités : pages interactives + types de questions **P0** (QCM, V/F, courte, longue)
- [ ] Sauvegarde automatique (20 s, changement page/réponse)
- [ ] Soumission + workflow correction (annoter, commenter, renvoyer, valider)
- [ ] Examens : création, minuterie, tentatives, soumission auto à expiration
- [ ] Calcul notes et moyennes (compétence → matière → générale)

### Administration

- [ ] Points (grille ClassDojo-like, actions configurables)
- [ ] Horaire (jour / semaine / mois côté élève ; planification prof)
- [ ] Annonces (création + affichage élève)
- [ ] Bulletins PDF (moyennes + commentaires + export)

### Expérience

- [ ] PWA installable (manifest, icônes, standalone, cache offline partiel)
- [ ] Interface fluide sur tablette
- [ ] Tests essentiels backend + sécurité passent

---

## V1 — Inclus mais priorité P1 (polish avant livraison si possible)

- Changement mot de passe, déconnexion auto inactivité
- Filtres/recherche élèves, stats dashboard enrichies
- Types de questions P1 : texte à trous, associer, glisser-déposer, ordre, tableau
- Zoom, surlignage, notes, plein écran sur leçons
- Centre de notifications, marquer lu/non lu
- Graphiques progression élève
- Duplication d'examen
- Tests frontend mobile/desktop

---

## V2 — Reporté explicitement

| Domaine | Fonctionnalité V2 |
|---------|-------------------|
| Leçons | Conversion auto documents → pages + miniatures |
| Questions | Géométrie, calculs, audio, vidéo, images interactives, texte annotable |
| Activités | Duplication de page, historique modifications avancé |
| Points | Animations de récompense |
| Admin | Logs d'activité admin, monitoring, backup MySQL automatisé |
| Élèves | Vue détail élève enrichie (portfolio) |
| UX | Transitions avancées, polish design « premium » final |
| Docs | Cahier des charges V2 (80–120 p.) avec maquettes Figma |
| Horaire | Planification 6 mois / 1 an (V1 = jour, semaine, mois) |
| Bulletins | Signature numérique avancée, templates multiples |

---

## Matrice de priorité (rappel)

| Priorité | Signification | Version cible |
|----------|---------------|---------------|
| **P0** | Bloquant pour lancer | V1 obligatoire |
| **P1** | Cœur fonctionnel complet | V1 souhaité |
| **P2** | Amélioration importante | V1.1 ou V2 |
| **P3** | Confort / finition | V2 |

---

## Ordre de développement recommandé (23 étapes)

Voir Phase 23 du [plan de travail](../../todo-list.html) ou section correspondante du cahier des charges.
