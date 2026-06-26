# TODO detaillee - EduSphere

Objectif du document : transformer le cahier des charges EduSphere V1.0 + V1.1 en plan de travail concret, suivable et cochable.

Legende :

- [ ] A faire
- [~] En cours
- [x] Termine
- Priorite P0 : indispensable pour lancer le projet
- Priorite P1 : coeur fonctionnel
- Priorite P2 : amelioration importante
- Priorite P3 : finition / confort

---

## Phase 0 - Cadrage du projet

### 0.1 Decisions de base

- [x] P0 Confirmer le nom du projet : EduSphere.
- [x] P0 Confirmer la cible principale : enfants de 7 a 10 ans.
- [x] P0 Confirmer le mode principal d'utilisation : tablette first.
- [x] P0 Confirmer les deux espaces : professeur/admin et eleve.
- [x] P0 Confirmer les technologies imposees : Laravel 12, Blade, Tailwind CSS, Alpine.js, JavaScript ES6+, MySQL.
- [x] P0 Confirmer l'environnement local : Docker obligatoire.
- [x] P0 Confirmer l'hebergement du code : GitHub.
- [x] P0 Confirmer qu'aucune IA ne sera integree.
- [x] P0 Confirmer que les PDF ne sont pas la base principale des activites.
- [x] P0 Definir ce qui appartient a la V1 minimale et ce qui ira en V2.

### 0.2 Livrables de cadrage

- [x] P0 Creer un README de projet.
- [x] P0 Rediger une description courte du produit.
- [x] P0 Rediger les objectifs principaux professeur.
- [x] P0 Rediger les objectifs principaux eleve.
- [x] P0 Rediger les regles UX tablette first.
- [x] P0 Rediger les contraintes de securite.
- [x] P0 Rediger les contraintes de performance.
- [x] P1 Creer une liste des ecrans attendus.
- [x] P1 Creer une liste des routes Laravel attendues.
- [x] P1 Creer une premiere carte de la base de donnees.
- [ ] P2 Preparer un cahier des charges V2 plus detaille avec maquettes, routes, tables et composants.

---

## Phase 1 - Initialisation technique

### 1.1 Repository GitHub

- [ ] P0 Creer le repository GitHub (gh auth login requis — voir docs/GITHUB.md).
- [x] P0 Initialiser Git dans le projet.
- [x] P0 Creer une branche principale propre.
- [x] P0 Ajouter un `.gitignore` adapte a Laravel, Node, Docker et fichiers locaux.
- [x] P0 Ajouter un README initial.
- [x] P0 Verifier que les fichiers sensibles ne sont pas versionnes.
- [x] P1 Definir une convention de branches.
- [x] P1 Definir une convention de commits.

### 1.2 Installation Laravel

- [x] P0 Installer Laravel 12.
- [x] P0 Configurer le fichier `.env`.
- [x] P0 Configurer la connexion MySQL.
- [x] P0 Verifier que l'application demarre.
- [x] P0 Verifier que les migrations fonctionnent.
- [x] P0 Installer les dependances Composer.
- [x] P0 Installer les dependances Node.
- [x] P0 Configurer Vite.
- [x] P0 Configurer Tailwind CSS.
- [x] P0 Configurer Alpine.js.
- [x] P1 Ajouter une page d'accueil temporaire.
- [x] P1 Ajouter une structure de layouts Blade.

### 1.3 Structure du projet

- [x] P0 Verifier la presence des dossiers Laravel standards : `app`, `bootstrap`, `config`, `database`, `public`, `resources`, `routes`, `storage`, `tests`.
- [x] P0 Creer le dossier `docker`.
- [x] P0 Creer le fichier `docker-compose.yml`.
- [x] P0 Creer le fichier `Dockerfile`.
- [x] P1 Creer une structure pour les composants Blade.
- [x] P1 Creer une structure pour les vues admin.
- [x] P1 Creer une structure pour les vues eleve.
- [x] P1 Creer une structure pour les assets PWA.
- [x] P1 Creer une structure pour les imports de documents et medias.

---

## Phase 2 - Docker et infrastructure

### 2.1 Conteneurs obligatoires

- [x] P0 Creer le conteneur `app` pour Laravel/PHP.
- [x] P0 Creer le conteneur `nginx`.
- [x] P0 Creer le conteneur `mysql`.
- [x] P0 Creer le conteneur `phpmyadmin`.
- [x] P0 Creer le conteneur `node`.
- [x] P0 Creer le conteneur `scheduler`.
- [x] P0 Verifier que `docker compose up -d` lance toute l'application.
- [x] P0 Verifier que Laravel est accessible depuis le navigateur.
- [x] P0 Verifier que MySQL est accessible par Laravel.
- [x] P0 Verifier que phpMyAdmin est accessible.
- [x] P0 Verifier que Vite compile les assets.
- [x] P1 Documenter les commandes Docker dans le README.

### 2.2 Configuration serveur

- [x] P0 Configurer Nginx pour pointer vers `public/index.php`.
- [x] P0 Configurer PHP avec les extensions Laravel necessaires.
- [x] P0 Configurer les volumes Docker.
- [x] P0 Configurer les variables d'environnement Docker.
- [x] P1 Configurer les permissions de `storage` et `bootstrap/cache`.
- [x] P1 Configurer les logs applicatifs.
- [x] P1 Configurer le scheduler Laravel.
- [ ] P2 Configurer une strategie de backup MySQL.

---

## Phase 3 - Base de donnees

### 3.1 Tables utilisateurs et profils

- [x] P0 Creer la table `users`.
- [x] P0 Ajouter les champs : nom, email, mot de passe, role, statut.
- [x] P0 Creer la table `students`.
- [x] P0 Lier un eleve a un utilisateur si necessaire.
- [x] P0 Ajouter prenom, nom, date de naissance, avatar, niveau scolaire.
- [x] P0 Creer la table `school_levels`.
- [x] P1 Ajouter les champs de profil eleve.
- [x] P1 Ajouter les champs de preferences d'interface.

### 3.2 Tables pedagogiques

- [x] P0 Creer la table `subjects`.
- [x] P0 Ajouter nom, couleur, icone, ordre d'affichage.
- [x] P0 Creer la table `skills`.
- [x] P0 Lier chaque competence a une matiere.
- [x] P0 Ajouter un pourcentage de ponderation.
- [x] P0 Ajouter une contrainte logique : total des competences d'une matiere = 100%.
- [x] P0 Creer la table `lessons`.
- [x] P0 Lier chaque lecon a une matiere et une competence.
- [x] P0 Creer la table `activities`.
- [x] P0 Lier chaque activite a une matiere et une competence.
- [x] P0 Creer la table `activity_pages`.
- [x] P0 Creer la table `questions`.
- [x] P0 Creer la table `answers`.
- [x] P1 Ajouter un champ `status` aux lecons, activites et examens.
- [x] P1 Ajouter les champs de publication.
- [x] P1 Ajouter les champs d'ordre d'affichage.

### 3.3 Tables examens et resultats

- [x] P0 Creer la table `exams`.
- [x] P0 Creer la table `exam_attempts`.
- [x] P0 Creer la table `grades`.
- [x] P0 Creer la table `progressions`.
- [x] P0 Stocker heure de debut, heure de fin, duree totale, progression et note finale.
- [x] P1 Stocker le nombre de pages visitees.
- [x] P1 Stocker le nombre de reponses.
- [x] P1 Stocker les tentatives restantes.

### 3.4 Tables correction et annotations

- [x] P0 Creer la table `corrections`.
- [x] P0 Creer la table `annotations`.
- [x] P0 Stocker les commentaires professeur.
- [x] P0 Stocker les annotations manuscrites ou dessinees.
- [x] P0 Stocker l'historique des corrections.
- [x] P1 Ajouter un statut : soumis, a corriger, corrige, renvoye, valide.
- [x] P1 Ajouter la date de chaque etape.

### 3.5 Tables points et comportement

- [x] P0 Creer la table `points`.
- [x] P0 Creer la table `point_actions`.
- [x] P0 Ajouter actions positives : participation, excellent travail, respect, entraide, perseverance.
- [x] P0 Ajouter actions negatives : distraction, retard, manque d'effort.
- [x] P0 Rendre la valeur de chaque action configurable.
- [x] P1 Ajouter historique complet des points.
- [x] P1 Ajouter la personne qui a attribue les points.

### 3.6 Tables administration scolaire

- [x] P0 Creer la table `schedules`.
- [x] P0 Creer la table `events`.
- [x] P0 Creer la table `announcements`.
- [x] P0 Creer la table `notifications`.
- [x] P0 Creer la table `reports`.
- [x] P0 Creer la table `media_files`.
- [x] P1 Ajouter les relations entre horaires, matieres et periodes.
- [x] P1 Ajouter les relations entre annonces et destinataires.
- [x] P1 Ajouter les relations entre bulletins et eleves.

---

## Phase 4 - Authentification, roles et securite

### 4.1 Authentification

- [x] P0 Installer ou configurer le systeme d'authentification Laravel.
- [x] P0 Creer la page de connexion.
- [x] P0 Creer la deconnexion.
- [x] P0 Ajouter le hashage obligatoire des mots de passe.
- [x] P0 Ajouter la validation backend des formulaires.
- [x] P0 Ajouter la protection CSRF.
- [x] P1 Ajouter la gestion du profil.
- [x] P1 Ajouter le changement de mot de passe.
- [x] P1 Ajouter la deconnexion automatique apres inactivite.

### 4.2 Roles

- [x] P0 Definir les roles : admin/professeur, eleve.
- [x] P0 Creer un middleware pour proteger l'espace admin.
- [x] P0 Creer un middleware pour proteger l'espace eleve.
- [x] P0 Bloquer l'acces admin aux eleves.
- [x] P0 Bloquer l'acces aux donnees des autres eleves.
- [x] P0 Bloquer la modification des notes par les eleves.
- [x] P0 Bloquer la modification des points par les eleves.
- [x] P1 Ajouter des policies Laravel pour les entites sensibles.
- [x] P1 Ajouter des tests de permissions.

### 4.3 Securite des donnees

- [x] P0 Valider toutes les donnees cote serveur.
- [x] P0 Verifier les uploads de fichiers.
- [x] P0 Limiter les formats autorises.
- [x] P0 Limiter la taille des photos de profil a 5 Mo.
- [x] P0 Stocker les fichiers dans un emplacement non public si necessaire.
- [x] P1 Ajouter des noms de fichiers securises.
- [x] P1 Ajouter une protection contre l'acces direct aux fichiers prives.
- [ ] P2 Ajouter des logs d'activite admin.

---

## Phase 5 - Design system UI/UX

### 5.1 Direction visuelle

- [x] P0 Definir une interface tablette first.
- [x] P0 Definir une grille responsive pour tablette, desktop et mobile.
- [x] P0 Definir les couleurs globales : fond blanc casse, fond gris ultra clair.
- [x] P0 Definir les couleurs par matiere.
- [x] P0 Definir les icones par matiere.
- [x] P0 Definir les styles de cartes modernes.
- [x] P0 Definir des coins arrondis de 24 px pour les cartes principales.
- [x] P0 Definir des ombres douces.
- [x] P0 Definir des animations fluides.
- [x] P0 Eviter les tableaux surcharges.
- [x] P0 Eviter les menus complexes.
- [x] P0 Eviter les textes inutiles.
- [x] P0 Eviter les petites polices.

### 5.2 Composants communs

- [x] P0 Creer un layout principal admin.
- [x] P0 Creer un layout principal eleve.
- [x] P0 Creer un composant carte.
- [x] P0 Creer un composant bouton.
- [x] P0 Creer un composant modal.
- [x] P0 Creer un composant alerte.
- [x] P0 Creer un composant badge de statut.
- [x] P0 Creer un composant barre de progression.
- [x] P0 Creer un composant avatar.
- [x] P0 Creer un composant navigation inferieure tablette.
- [x] P0 Creer un composant formulaire.
- [x] P1 Creer un composant onglets.
- [x] P1 Creer un composant calendrier.
- [x] P1 Creer un composant graphique simple.

### 5.3 Navigation

- [x] P0 Ajouter la navigation admin.
- [x] P0 Ajouter la navigation eleve.
- [x] P0 Ajouter la barre inferieure tablette eleve : Accueil, Matieres, Lecons, Activites, Examens, Horaire, Points.
- [x] P0 S'assurer que les fonctions importantes sont accessibles en 3 clics maximum.
- [x] P1 Ajouter des transitions entre pages.
- [x] P1 Ajouter des etats actifs clairs.

---

## Phase 6 - Donnees initiales

### 6.1 Matieres officielles

- [x] P0 Creer le seeder des matieres.
- [x] P0 Ajouter Francais.
- [x] P0 Ajouter Mathematiques.
- [x] P0 Ajouter Sciences.
- [x] P0 Ajouter Histoire.
- [x] P0 Ajouter Geographie.
- [x] P0 Ajouter Islam.
- [x] P0 Ajouter Natation.
- [x] P0 Ajouter Education physique.
- [x] P0 Associer une couleur a chaque matiere.
- [x] P0 Associer une icone a chaque matiere.

### 6.2 Competences officielles

- [x] P0 Creer le seeder des competences de Francais.
- [x] P0 Creer le seeder des competences de Mathematiques.
- [x] P0 Creer le seeder des competences de Sciences.
- [x] P0 Creer le seeder des competences de Histoire.
- [x] P0 Creer le seeder des competences de Geographie.
- [x] P0 Creer le seeder des competences de Islam.
- [x] P0 Creer le seeder des competences de Natation.
- [x] P0 Creer le seeder des competences de Education physique.
- [x] P0 Verifier que chaque matiere totalise 100%.
- [ ] P1 Ajouter une page admin pour ajuster les ponderations.

### 6.3 Comptes de test

- [x] P0 Creer un compte professeur de test.
- [x] P0 Creer plusieurs comptes eleves de test.
- [x] P0 Creer un niveau scolaire de test.
- [x] P1 Creer des donnees de demonstration : lecons, activites, examens, points.

---

## Phase 7 - Espace admin/professeur

### 7.1 Dashboard admin

- [x] P0 Creer la route du dashboard admin.
- [x] P0 Afficher le nombre d'eleves.
- [x] P0 Afficher les activites en attente.
- [x] P0 Afficher les examens actifs.
- [x] P0 Afficher les corrections a effectuer.
- [x] P0 Afficher les annonces publiees.
- [ ] P1 Afficher la moyenne generale.
- [ ] P1 Afficher la moyenne par matiere.
- [ ] P1 Afficher les dernieres activites creees.
- [ ] P1 Afficher les alertes : activites non corrigees, examens expires.

### 7.2 Gestion des eleves

- [x] P0 Creer la liste des eleves.
- [x] P0 Creer le formulaire d'ajout.
- [x] P0 Creer le formulaire de modification.
- [x] P0 Ajouter la suppression.
- [x] P0 Ajouter le niveau scolaire.
- [x] P0 Ajouter l'avatar/photo.
- [ ] P1 Ajouter les statistiques par eleve.
- [ ] P1 Ajouter la recherche.
- [ ] P1 Ajouter les filtres.
- [ ] P2 Ajouter une vue detaillee eleve.

### 7.3 Gestion des matieres

- [x] P0 Creer la liste des matieres.
- [x] P0 Creer l'ajout de matiere.
- [x] P0 Creer la modification de matiere.
- [x] P0 Creer la suppression de matiere.
- [x] P0 Gerer les couleurs.
- [x] P0 Gerer les icones.
- [ ] P1 Afficher les statistiques par matiere.

### 7.4 Gestion des competences

- [x] P0 Creer la liste des competences par matiere.
- [x] P0 Creer l'ajout de competence.
- [x] P0 Creer la modification de competence.
- [x] P0 Creer la suppression de competence.
- [x] P0 Gerer le pourcentage de chaque competence.
- [x] P0 Bloquer l'enregistrement si le total n'est pas egal a 100%.
- [ ] P1 Afficher l'impact de la ponderation sur les moyennes.

---

## Phase 8 - Systeme de lecons

### 8.1 Gestion des lecons

- [ ] P0 Creer la table et le modele `Lesson`.
- [ ] P0 Creer la liste admin des lecons.
- [ ] P0 Creer le formulaire de creation.
- [ ] P0 Creer le formulaire de modification.
- [ ] P0 Ajouter la suppression.
- [ ] P0 Ajouter titre, description, matiere, competence, image de couverture, date, niveau scolaire, duree estimee.
- [ ] P0 Ajouter le statut brouillon/publie.
- [ ] P1 Ajouter une previsualisation professeur.

### 8.2 Importation de documents

- [ ] P0 Autoriser les imports PowerPoint : `.ppt`, `.pptx`.
- [ ] P0 Autoriser les imports documents : `.pdf`, `.doc`, `.docx`.
- [ ] P0 Autoriser les imports images : `.png`, `.jpg`, `.jpeg`, `.webp`.
- [ ] P0 Autoriser les imports video : `.mp4`.
- [ ] P0 Autoriser les imports audio : `.mp3`.
- [ ] P0 Valider la taille et le type MIME.
- [ ] P1 Stocker les fichiers dans `media_files`.
- [ ] P1 Associer les fichiers a une lecon.
- [ ] P2 Convertir automatiquement les documents en pages consultables.
- [ ] P2 Generer les miniatures des pages.

### 8.3 Lecture des lecons cote eleve

- [ ] P0 Creer la page "Mes lecons".
- [ ] P0 Afficher les lecons par matiere.
- [ ] P0 Afficher les lecons par competence.
- [ ] P0 Creer l'ecran de lecture.
- [ ] P0 Ajouter navigation page suivante/precedente.
- [ ] P1 Ajouter zoom.
- [ ] P1 Ajouter defilement fluide.
- [ ] P1 Ajouter surlignage.
- [ ] P1 Ajouter prise de notes.
- [ ] P1 Ajouter plein ecran.
- [ ] P1 Sauvegarder la derniere page lue.
- [ ] P1 Sauvegarder le temps passe.
- [ ] P1 Sauvegarder la progression.

---

## Phase 9 - Moteur d'activites interactives

### 9.1 Creation des activites

- [x] P0 Creer la liste admin des activites.
- [x] P0 Creer une activite.
- [x] P0 Modifier une activite.
- [x] P0 Supprimer une activite.
- [x] P0 Publier une activite.
- [x] P0 Depublier une activite.
- [x] P0 Lier l'activite a une matiere.
- [x] P0 Lier l'activite a une competence.
- [x] P0 Ajouter plusieurs pages.
- [x] P0 Ajouter des questions.
- [x] P0 Ajouter des medias.
- [x] P1 Ajouter une previsualisation professeur.

### 9.2 Pages interactives

- [x] P0 Creer le modele de page interactive.
- [x] P0 Ajouter ordre, titre, contenu et configuration.
- [x] P0 Permettre le changement de page.
- [x] P0 Permettre la sauvegarde de l'etat de chaque page.
- [ ] P1 Ajouter historique des modifications.
- [ ] P1 Ajouter reprise plus tard.
- [ ] P2 Ajouter duplication de page.

### 9.3 Outils eleve

- [x] P0 Permettre d'ecrire.
- [x] P0 Permettre de dessiner.
- [x] P0 Permettre de surligner.
- [x] P0 Permettre d'effacer.
- [x] P0 Permettre d'ajouter des reponses.
- [x] P0 Permettre de sauvegarder.
- [ ] P1 Ajouter support tactile fluide.
- [ ] P1 Ajouter annulation/retablissement.
- [x] P1 Ajouter indicateur de page.

### 9.4 Types de questions

- [x] P0 QCM.
- [x] P0 Vrai/Faux.
- [x] P0 Reponse courte.
- [x] P0 Reponse longue.
- [ ] P1 Texte a trous.
- [ ] P1 Associer des elements.
- [ ] P1 Relier des elements.
- [ ] P1 Glisser-deposer.
- [ ] P1 Remettre dans l'ordre.
- [ ] P1 Tableau a completer.
- [ ] P1 Dessin libre.
- [ ] P2 Geometrie.
- [ ] P2 Calculs.
- [ ] P2 Questions audio.
- [ ] P2 Questions video.
- [ ] P2 Images interactives.
- [ ] P2 Texte annotable.

---

## Phase 10 - Sauvegarde automatique

### 10.1 Evenements de sauvegarde

- [ ] P0 Sauvegarder toutes les 20 secondes.
- [ ] P0 Sauvegarder lors du changement de page.
- [ ] P0 Sauvegarder lors d'un changement de reponse.
- [ ] P0 Sauvegarder lors d'une annotation.
- [ ] P0 Sauvegarder avant fermeture de l'application si possible.
- [ ] P1 Ajouter une file d'attente locale en cas d'erreur reseau.
- [ ] P1 Rejouer les sauvegardes non envoyees.

### 10.2 Etats visibles

- [ ] P0 Afficher "Sauvegarde en cours...".
- [ ] P0 Afficher "Sauvegarde".
- [ ] P0 Afficher "Erreur de synchronisation".
- [ ] P1 Ajouter un bouton "Reessayer".
- [ ] P1 Ajouter une date de derniere sauvegarde.

### 10.3 Performance de sauvegarde

- [ ] P0 Objectif : sauvegarde en moins de 1 seconde.
- [ ] P0 Eviter l'envoi de donnees inutiles.
- [ ] P1 Ajouter debounce/throttle cote JavaScript.
- [ ] P1 Ajouter tests de charge simples.

---

## Phase 11 - Workflow de correction

### 11.1 Soumission eleve

- [ ] P0 Ajouter bouton de soumission d'activite.
- [ ] P0 Demander confirmation avant soumission.
- [ ] P0 Passer l'activite au statut `soumise`.
- [ ] P0 Bloquer la modification si l'activite est soumise, sauf si renvoyee.
- [ ] P0 Notifier le professeur.

### 11.2 Correction professeur

- [ ] P0 Creer la liste des corrections a faire.
- [ ] P0 Ouvrir une soumission eleve.
- [ ] P0 Corriger les reponses.
- [ ] P0 Ajouter une note.
- [ ] P0 Ajouter des commentaires.
- [ ] P0 Annoter a l'encre.
- [ ] P0 Dessiner sur la copie.
- [ ] P1 Recorriger.
- [ ] P1 Renvoyer une activite a l'eleve.
- [ ] P1 Valider definitivement.

### 11.3 Historique complet

- [ ] P0 Stocker chaque etape du workflow.
- [ ] P0 Stocker qui a fait l'action.
- [ ] P0 Stocker la date de l'action.
- [ ] P0 Stocker les commentaires.
- [ ] P1 Afficher l'historique au professeur.
- [ ] P1 Afficher une version simplifiee a l'eleve.

---

## Phase 12 - Examens

### 12.1 Creation d'examens

- [ ] P0 Creer la liste admin des examens.
- [ ] P0 Creer un examen.
- [ ] P0 Modifier un examen.
- [ ] P0 Supprimer un examen.
- [ ] P0 Ajouter titre, description, matiere, competence, date, heure, duree.
- [ ] P0 Ajouter date d'ouverture.
- [ ] P0 Ajouter date de fermeture.
- [ ] P0 Ajouter nombre de tentatives.
- [ ] P0 Ajouter minuterie.
- [ ] P0 Ajouter soumission automatique.
- [ ] P1 Ajouter duplication d'examen.

### 12.2 Passage d'examen cote eleve

- [ ] P0 Afficher les examens a venir.
- [ ] P0 Afficher les examens actifs.
- [ ] P0 Afficher les examens termines.
- [ ] P0 Permettre de demarrer un examen ouvert.
- [ ] P0 Enregistrer heure de debut.
- [ ] P0 Enregistrer heure de fin.
- [ ] P0 Enregistrer temps total.
- [ ] P0 Enregistrer progression.
- [ ] P0 Enregistrer nombre de pages visitees.
- [ ] P0 Enregistrer nombre de reponses.
- [ ] P0 Soumettre automatiquement si le temps expire.
- [ ] P1 Bloquer nouvelle tentative si limite atteinte.

### 12.3 Correction d'examens

- [ ] P0 Corriger les reponses.
- [ ] P0 Annoter.
- [ ] P0 Dessiner.
- [ ] P0 Ajouter commentaires.
- [ ] P0 Calculer note finale.
- [ ] P1 Permettre recorriger.
- [ ] P1 Notifier l'eleve du resultat.

---

## Phase 13 - Calcul des resultats

### 13.1 Notes

- [ ] P0 Calculer la note d'une activite.
- [ ] P0 Calculer la note d'un examen.
- [ ] P0 Calculer le pourcentage obtenu.
- [ ] P0 Associer chaque note a une competence.
- [ ] P0 Associer chaque note a une matiere.
- [ ] P1 Gerer les activites non notees.
- [ ] P1 Gerer les examens absents ou non termines.

### 13.2 Moyennes

- [ ] P0 Calculer la moyenne par competence.
- [ ] P0 Calculer la moyenne par matiere avec ponderation.
- [ ] P0 Calculer la moyenne generale.
- [ ] P0 Verifier que la ponderation des competences influence correctement la moyenne.
- [ ] P1 Ajouter tests unitaires sur les calculs.
- [ ] P1 Afficher le detail des calculs au professeur.
- [ ] P2 Afficher une version simplifiee a l'eleve.

---

## Phase 14 - Espace eleve

### 14.1 Dashboard eleve

- [ ] P0 Creer la route du dashboard eleve.
- [ ] P0 Afficher les matieres sous forme de cartes colorees.
- [ ] P0 Afficher la progression.
- [ ] P0 Afficher les activites a faire.
- [ ] P0 Afficher les activites en cours.
- [ ] P0 Afficher les activites terminees.
- [ ] P0 Afficher les examens a venir.
- [ ] P0 Afficher les examens actifs.
- [ ] P0 Afficher les examens termines.
- [ ] P0 Afficher le total de points.
- [ ] P0 Afficher les annonces.
- [ ] P1 Afficher des graphiques de progression.
- [ ] P1 Afficher des rappels.

### 14.2 Mes matieres

- [ ] P0 Afficher toutes les matieres accessibles.
- [ ] P0 Afficher couleur, icone et progression.
- [ ] P0 Ouvrir le detail d'une matiere.
- [ ] P1 Afficher les competences de la matiere.
- [ ] P1 Afficher lecons, activites et examens lies.

### 14.3 Mes activites

- [ ] P0 Lister les activites a faire.
- [ ] P0 Lister les activites en cours.
- [ ] P0 Lister les activites terminees.
- [ ] P0 Ouvrir une activite.
- [ ] P0 Reprendre une activite.
- [ ] P0 Soumettre une activite.
- [ ] P1 Voir les corrections recues.

### 14.4 Mon profil

- [ ] P0 Afficher les informations de l'eleve.
- [ ] P0 Permettre de televerser une photo.
- [ ] P0 Permettre de changer la photo.
- [ ] P0 Permettre de supprimer la photo.
- [ ] P0 Generer un avatar par defaut si aucune photo.
- [ ] P0 Accepter JPG, PNG, WEBP.
- [ ] P0 Limiter la photo a 5 Mo.

---

## Phase 15 - Systeme de points

### 15.1 Admin/professeur

- [ ] P0 Creer la grille des eleves.
- [ ] P0 Afficher avatar, nom et total de points.
- [ ] P0 Ouvrir une popup de points.
- [ ] P0 Ajouter des points positifs.
- [ ] P0 Retirer des points avec actions negatives.
- [ ] P0 Modifier une action de points.
- [ ] P0 Configurer la valeur d'une action.
- [ ] P1 Afficher historique par eleve.
- [ ] P1 Afficher classement.

### 15.2 Eleve

- [ ] P0 Afficher le total de points.
- [ ] P0 Afficher l'historique.
- [ ] P1 Afficher les categories de points.
- [ ] P2 Ajouter animations de recompense.

---

## Phase 16 - Horaire et calendrier scolaire

### 16.1 Configuration professeur

- [ ] P0 Creer la gestion des horaires.
- [ ] P0 Configurer 4 periodes par jour.
- [ ] P0 Configurer les heures de debut et de fin.
- [ ] P0 Choisir la matiere.
- [ ] P0 Choisir le titre.
- [ ] P0 Choisir la couleur.
- [ ] P0 Planifier 1 jour.
- [ ] P0 Planifier 1 semaine.
- [ ] P1 Planifier 1 mois.
- [ ] P1 Planifier 6 mois.
- [ ] P1 Planifier 1 annee complete.

### 16.2 Vues eleve

- [ ] P0 Afficher vue du jour.
- [ ] P0 Afficher vue semaine.
- [ ] P0 Afficher vue calendrier mensuel.
- [ ] P0 Afficher examens.
- [ ] P0 Afficher devoirs.
- [ ] P0 Afficher rappels.
- [ ] P1 Ajouter navigation entre semaines/mois.
- [ ] P1 Ajouter couleurs par matiere.

---

## Phase 17 - Annonces et notifications

### 17.1 Annonces

- [ ] P0 Creer la liste admin des annonces.
- [ ] P0 Creer une annonce.
- [ ] P0 Modifier une annonce.
- [ ] P0 Supprimer une annonce.
- [ ] P0 Publier une annonce.
- [ ] P0 Afficher les annonces cote eleve.
- [ ] P1 Ajouter destinataires : tous, niveau, eleve specifique.
- [ ] P1 Ajouter date de publication.

### 17.2 Notifications

- [ ] P0 Notifier professeur lors d'une soumission.
- [ ] P0 Notifier eleve lors d'une correction.
- [ ] P0 Notifier eleve lors d'une activite renvoyee.
- [ ] P0 Notifier eleve lors d'un resultat publie.
- [ ] P1 Ajouter centre de notifications.
- [ ] P1 Marquer comme lu/non lu.

---

## Phase 18 - Bulletins

### 18.1 Generation

- [ ] P0 Generer moyenne generale.
- [ ] P0 Generer moyenne par matiere.
- [ ] P0 Inclure commentaires.
- [ ] P0 Inclure resultats des examens.
- [ ] P0 Inclure progression globale.
- [ ] P1 Ajouter periode du bulletin.
- [ ] P1 Ajouter signature ou information professeur.

### 18.2 Export PDF

- [ ] P0 Creer un template PDF.
- [ ] P0 Exporter le bulletin en PDF.
- [ ] P0 Verifier la lisibilite du PDF.
- [ ] P1 Ajouter telechargement cote professeur.
- [ ] P1 Ajouter consultation cote eleve.

---

## Phase 19 - PWA

### 19.1 Installation

- [ ] P0 Creer `manifest.json`.
- [ ] P0 Ajouter nom, short_name, theme_color, background_color.
- [ ] P0 Ajouter icone 192x192.
- [ ] P0 Ajouter icone 512x512.
- [ ] P0 Activer le mode standalone.
- [ ] P0 Preparer splash screen.
- [ ] P0 Ajouter meta tags PWA.
- [ ] P1 Tester installation sur tablette.
- [ ] P1 Tester plein ecran.

### 19.2 Offline partiel

- [ ] P0 Ajouter un service worker.
- [ ] P0 Mettre en cache les assets principaux.
- [ ] P0 Prevoir page offline simple.
- [ ] P1 Mettre en cache certaines donnees non sensibles.
- [ ] P1 Gerer les erreurs de synchronisation.

---

## Phase 20 - Performance

### 20.1 Objectifs

- [ ] P0 Temps de chargement inferieur a 2 secondes.
- [ ] P0 Temps de reponse inferieur a 300 ms pour les actions courantes.
- [ ] P0 Animations a 60 FPS.
- [ ] P0 Sauvegarde inferieure a 1 seconde.

### 20.2 Optimisations

- [ ] P0 Optimiser les requetes principales.
- [ ] P0 Ajouter index en base de donnees.
- [ ] P0 Eviter les chargements inutiles.
- [ ] P0 Optimiser les images.
- [ ] P1 Ajouter pagination ou chargement progressif.
- [ ] P1 Ajouter cache serveur si necessaire.
- [ ] P1 Mesurer avec les outils navigateur.
- [ ] P2 Ajouter monitoring basique.

---

## Phase 21 - Tests

### 21.1 Tests backend

- [ ] P0 Tester authentification.
- [ ] P0 Tester roles et permissions.
- [ ] P0 Tester CRUD eleves.
- [ ] P0 Tester CRUD matieres.
- [ ] P0 Tester CRUD competences.
- [ ] P0 Tester CRUD lecons.
- [ ] P0 Tester CRUD activites.
- [ ] P0 Tester CRUD examens.
- [ ] P0 Tester calcul des moyennes.
- [ ] P0 Tester ponderation des competences.
- [ ] P0 Tester points.
- [ ] P0 Tester bulletins.

### 21.2 Tests frontend

- [ ] P0 Tester dashboard admin sur tablette.
- [ ] P0 Tester dashboard eleve sur tablette.
- [ ] P0 Tester navigation inferieure.
- [ ] P0 Tester formulaires.
- [ ] P0 Tester moteur d'activites.
- [ ] P0 Tester sauvegarde automatique.
- [ ] P0 Tester correction.
- [ ] P1 Tester sur mobile.
- [ ] P1 Tester sur desktop.

### 21.3 Tests securite

- [ ] P0 Verifier qu'un eleve ne peut pas acceder aux routes admin.
- [ ] P0 Verifier qu'un eleve ne peut pas voir les donnees d'un autre eleve.
- [ ] P0 Verifier qu'un eleve ne peut pas modifier notes ou points.
- [ ] P0 Verifier validation des uploads.
- [ ] P0 Verifier protection CSRF.
- [ ] P1 Verifier expiration de session.

---

## Phase 22 - Documentation

### 22.1 Documentation technique

- [ ] P0 Documenter installation Docker.
- [ ] P0 Documenter commandes utiles.
- [ ] P0 Documenter migrations et seeders.
- [ ] P0 Documenter structure du projet.
- [ ] P1 Documenter architecture des modules.
- [ ] P1 Documenter systeme de sauvegarde.
- [ ] P1 Documenter systeme de calcul des notes.

### 22.2 Documentation utilisateur

- [ ] P1 Rediger guide professeur.
- [ ] P1 Rediger guide eleve simplifie.
- [ ] P1 Ajouter captures d'ecran.
- [ ] P2 Ajouter FAQ.

---

## Phase 23 - Ordre recommande de developpement

1. [ ] P0 Initialiser Laravel, GitHub et Docker.
2. [ ] P0 Configurer MySQL, migrations de base et seeders.
3. [ ] P0 Mettre en place authentification, roles et middleware.
4. [ ] P0 Creer le design system minimal : layouts, cartes, boutons, navigation.
5. [ ] P0 Creer dashboard admin et dashboard eleve vides mais navigables.
6. [ ] P0 Implementer eleves, matieres et competences.
7. [ ] P0 Implementer ponderation des competences.
8. [ ] P0 Implementer lecons simples avec import de medias.
9. [ ] P0 Implementer activites simples avec pages et questions de base.
10. [ ] P0 Implementer sauvegarde automatique.
11. [ ] P0 Implementer soumission et correction.
12. [ ] P0 Implementer examens.
13. [ ] P0 Implementer calcul des resultats.
14. [ ] P0 Implementer points.
15. [ ] P0 Implementer horaire/calendrier.
16. [ ] P0 Implementer annonces et notifications.
17. [ ] P0 Implementer bulletins PDF.
18. [ ] P0 Ajouter PWA.
19. [ ] P0 Tester securite, performance et UX tablette.
20. [ ] P1 Polir le design pour atteindre le rendu premium.

---

## Definition de fini pour la V1

- [ ] L'application demarre avec `docker compose up -d`.
- [ ] Un professeur peut se connecter.
- [ ] Un eleve peut se connecter.
- [ ] Les routes sont protegees selon les roles.
- [ ] Le professeur peut gerer eleves, matieres et competences.
- [ ] Les competences ont une ponderation valide de 100% par matiere.
- [ ] Le professeur peut creer une lecon.
- [ ] L'eleve peut consulter une lecon.
- [ ] Le professeur peut creer une activite.
- [ ] L'eleve peut faire et soumettre une activite.
- [ ] La sauvegarde automatique fonctionne.
- [ ] Le professeur peut corriger et annoter.
- [ ] L'eleve peut consulter sa correction.
- [ ] Le professeur peut creer un examen.
- [ ] L'eleve peut passer un examen.
- [ ] Les resultats sont calcules automatiquement.
- [ ] Les points fonctionnent.
- [ ] L'horaire fonctionne.
- [ ] Les annonces fonctionnent.
- [ ] Les bulletins PDF fonctionnent.
- [ ] L'application est installable comme PWA.
- [ ] L'interface est fluide sur tablette.
- [ ] Les donnees des eleves ne sont pas accessibles publiquement.
- [ ] Les tests essentiels passent.


CAHIER DES CHARGES PROFESSIONNEL COMPLET — EDUSPHERE (VERSION ENTREPRISE V1.0)
Projet : EduSphere

Nom du projet : EduSphere

Version : 1.0

Type de projet : Plateforme web éducative interactive (PWA)

Public cible : Enfants de 7 à 10 ans

Client : École d'été personnalisée

Technologies imposées :

Backend : Laravel
Frontend : Blade
CSS : Tailwind CSS
Interactivité : Alpine.js + JavaScript moderne
Base de données : MySQL
Environnement local : Laragon
Versionnement : GitLab
1. CONTEXTE DU PROJET

EduSphere est une plateforme scolaire numérique haut de gamme conçue pour remplacer les cahiers papier, les feuilles d'exercices et les systèmes fragmentés actuellement utilisés dans les écoles.

L'objectif est de centraliser l'ensemble des activités pédagogiques dans une seule application moderne, tactile et immersive.

L'application doit reproduire l'expérience d'une véritable application native iPad tout en restant une application web.

Le contenu pédagogique sera entièrement créé par l'administrateur.

Aucun système d'intelligence artificielle ne sera intégré.

2. OBJECTIFS PRINCIPAUX

La plateforme doit permettre :

Pour le professeur :
gérer les élèves
créer des activités
créer des examens
corriger les travaux
annoter les réponses
gérer les points
suivre la progression
créer les horaires
publier des annonces
générer des bulletins
Pour les élèves :
consulter leurs matières
réaliser leurs activités
passer leurs examens
écrire et dessiner
consulter leurs notes
suivre leur progression
consulter leurs points
3. OBJECTIFS UX (EXPÉRIENCE UTILISATEUR)

L'application doit être pensée selon le principe :

TABLETTE FIRST

La tablette est l'appareil principal.

L'ordinateur n'est qu'un support secondaire.

L'interface doit être :

extrêmement simple
très intuitive
tactile
rapide
fluide
immersive
adaptée aux enfants
moderne
sécuritaire

Chaque action importante doit nécessiter un minimum de clics.

Objectif :

Jamais plus de 3 clics pour atteindre une fonctionnalité importante.

4. ARCHITECTURE GLOBALE

Le système se compose de deux espaces.

Espace Administrateur / Professeur

Accès complet.

Espace Élève

Accès restreint.

5. ARBORESCENCE COMPLÈTE DE L'APPLICATION
Accueil

├── Connexion

├── Dashboard Admin
│
├── Gestion des élèves
│
├── Gestion des matières
│
├── Gestion des compétences
│
├── Gestion des activités
│
├── Gestion des examens
│
├── Gestion des corrections
│
├── Gestion des points
│
├── Gestion des horaires
│
├── Gestion des bulletins
│
├── Gestion des annonces
│
└── Paramètres

Dashboard Élève

├── Mes matières
├── Mes activités
├── Mes examens
├── Mes points
├── Ma progression
├── Mon horaire
├── Mes annonces
└── Mon profil
6. RÔLES ET PERMISSIONS
ADMINISTRATEUR / PROFESSEUR

Accès complet.

Peut :

Gestion pédagogique
créer des matières
modifier des matières
supprimer des matières
Gestion des compétences
créer des compétences
modifier des compétences
supprimer des compétences
Gestion des activités
créer
modifier
supprimer
publier
dépublier
Gestion des examens
créer
lancer
fermer
corriger
Gestion des corrections
annoter
dessiner
commenter
recorriger
renvoyer
Gestion des élèves
ajouter
modifier
supprimer
Gestion des points
ajouter
retirer
modifier
Gestion administrative
créer horaires
publier annonces
générer bulletins
ÉLÈVE

Peut uniquement :

consulter son contenu
compléter ses activités
écrire
dessiner
écouter des audios
regarder des vidéos
consulter ses résultats
consulter ses points

Restrictions :

impossible d'accéder à l'espace admin
impossible de modifier les notes
impossible de modifier les points
impossible de consulter les autres élèves
7. IDENTITÉ VISUELLE
Style graphique

Inspirations :

Apple
Goodnotes
Notion
Linear
ClassDojo
Principes graphiques

Interface :

minimaliste
aérée
moderne
premium

Éléments :

cartes flottantes
ombres douces
coins très arrondis
animations fluides
grandes marges
8. PALETTE DE COULEURS
Élément	Couleur
Fond principal	Blanc cassé
Fond secondaire	Gris ultra clair
Français	Bleu
Mathématiques	Violet
Sciences	Vert
Histoire	Orange
Géographie	Cyan
Islam	Emerald
Natation	Aqua
Éducation physique	Rouge doux
9. MATIÈRES OFFICIELLES

Le système doit intégrer :

Français
Mathématiques
Sciences
Histoire
Géographie
Islam
Natation
Éducation physique

Chaque matière possède :

une couleur
une icône
des compétences
des activités
des examens
des statistiques
10. STRUCTURE PÉDAGOGIQUE

Hiérarchie obligatoire :

Matière

↓

Compétence

↓

Activité

↓

Pages interactives

↓

Questions

↓

Correction

↓

Résultat
11. MOTEUR DE PAGES INTERACTIVES

C'est le cœur du projet.

Le système ne doit PAS utiliser des PDF comme base principale.

Le moteur doit permettre :

Élève
écrire
dessiner
surligner
effacer
ajouter des réponses
changer de page
sauvegarder
reprendre plus tard
Professeur
corriger à l'encre
dessiner
annoter
commenter
recorriger
renvoyer une activité

Chaque activité possède :

plusieurs pages
une sauvegarde automatique
un historique des modifications
12. SYSTÈME DE SAUVEGARDE AUTOMATIQUE

Une sauvegarde doit être effectuée :

toutes les 20 secondes
lors du changement de page
lors d'un changement de réponse
lors d'une annotation
avant la fermeture de l'application

Affichage :

● Sauvegarde en cours...

✓ Sauvegardé

⚠️ Erreur de synchronisation
13. WORKFLOW DE CORRECTION

Étape 1 :

L'élève soumet son activité.

↓

Étape 2 :

Le professeur reçoit une notification.

↓

Étape 3 :

Le professeur corrige.

↓

Étape 4 :

Le professeur ajoute des annotations.

↓

Étape 5 :

Le professeur renvoie l'activité.

↓

Étape 6 :

L'élève corrige ses erreurs.

↓

Étape 7 :

Le professeur valide.

L'historique complet doit être conservé.

14. TYPES DE QUESTIONS OBLIGATOIRES

Le système doit supporter :

QCM
Vrai/Faux
Réponse courte
Réponse longue
Texte à trous
Associer des éléments
Relier des éléments
Glisser-déposer
Remettre dans l'ordre
Tableau à compléter
Dessin libre
Géométrie
Calculs
Questions audio
Questions vidéo
Images interactives
Texte annotable
15. DASHBOARD ÉLÈVE

Le tableau de bord doit afficher :

Mes matières
cartes colorées
progression
Mes activités
à faire
en cours
terminées
Mes examens
à venir
actifs
terminés
Mes points
total
historique
Mes annonces
rappels
messages
Ma progression
graphiques
statistiques
16. DASHBOARD ADMIN

Doit afficher :

Statistiques générales
nombre d'élèves
activités en attente
examens actifs
corrections à effectuer
annonces publiées
Progression
moyenne générale
moyenne par matière
Activités récentes
dernières activités créées
Alertes
activités non corrigées
examens expirés
17. SYSTÈME DE POINTS

Inspiré de ClassDojo.

Affichage :

Grille d'élèves.

Chaque élève possède :

avatar
nom
total de points

Popup des points.

Points positifs :

participation
excellent travail
respect
entraide
persévérance

Points négatifs :

distraction
retard
manque d'effort

Chaque action possède :

un nom
une description
une valeur configurable
18. BULLETINS

Le système doit générer automatiquement :

moyenne générale
moyenne par matière
commentaires
résultats des examens
progression globale

Export possible :

PDF
19. CALENDRIER SCOLAIRE

Le calendrier doit permettre :

horaires
événements
examens
devoirs
rappels

Affichages :

jour
semaine
mois
20. APPLICATION PWA

Fonctionnalités obligatoires :

Manifest

manifest.json

Icônes
192x192
512x512
Splash Screen

Obligatoire.

Mode Standalone

Obligatoire.

Cache Offline

Partiel.

Plein écran

Obligatoire.

21. PERFORMANCE

Objectifs techniques :

Temps de chargement :

inférieur à 2 secondes

Temps de réponse :

inférieur à 300 ms

Animations :

60 FPS minimum

Sauvegarde :

inférieure à 1 seconde
22. SÉCURITÉ

Obligatoire :

Authentification
connexion sécurisée
déconnexion automatique après inactivité
Protection des routes

Middleware Laravel.

Mots de passe

Hashage obligatoire.

Validation

Validation backend Laravel.

Protection des données

Aucune donnée d'élève accessible publiquement.

23. BASE DE DONNÉES (ENTITÉS PRINCIPALES)

Tables principales :

users

students

school_levels

subjects

skills

activities

activity_pages

questions

answers

exams

exam_attempts

annotations

corrections

points

point_actions

reports

grades

schedules

events

announcements

notifications

progressions

media_files
24. TODO LIST GLOBALE (VERSION DÉVELOPPEMENT)
Authentification

☐ Connexion

☐ Déconnexion

☐ Gestion profil

☐ Changement mot de passe

Gestion des élèves

☐ Ajouter

☐ Modifier

☐ Supprimer

☐ Avatar

☐ Niveau scolaire

☐ Statistiques

Gestion des matières

☐ Créer

☐ Modifier

☐ Supprimer

☐ Couleurs

☐ Icônes

Activités

☐ Créer

☐ Modifier

☐ Supprimer

☐ Publier

☐ Ajouter pages

☐ Ajouter questions

☐ Ajouter médias

Examens

☐ Créer

☐ Minuterie

☐ Lancer

☐ Fermer

☐ Corriger

Corrections

☐ Annoter

☐ Dessiner

☐ Commenter

☐ Recorriger

☐ Valider

Points

☐ Ajouter

☐ Retirer

☐ Historique

☐ Classement

Bulletins

☐ Générer

☐ Exporter PDF

Calendrier

☐ Horaires

☐ Événements

☐ Devoirs

☐ Examens

PWA

☐ Manifest

☐ Splash Screen

☐ Icônes

☐ Cache Offline

☒ Mode Standalone

25. OBJECTIF FINAL

EduSphere doit donner l'impression d'utiliser une véritable application éducative professionnelle haut de gamme, combinant les meilleures idées des plateformes modernes, tout en restant simple, sécuritaire, tactile et parfaitement adaptée aux enfants de 7 à 10 ans.

Ajoute a tout cela, de creer des lecons, importer pour les lecon des power point ou des document etc, en plusieur pages. Je eux utiliser Docker, et je veux heberger le site sur github, et precise les detail des donnes pour les examens, design mega ultra moderne et surtout simple.et precise pour chacune des matieres le compétences, qui seront ditignerue, et je opourais, pour la matere indiquer chaucne de ses competences vaut combien de pourcent pour le resultaat disciplinaire. Les eleves doivent pouvoir emettre photo de profil. Et je eux un ssytme d'horaire aussi. DOnc, le admin(prof) met le  heures des periodes, il y en a 4 par jour, et pour chauqe periode, il met kelle matiere. Il peut planifier autant loin kil veut, et leeleve doit pouvoir voir sa.Et focus moin sur le fait que le eleves ont aps beaucoup de permission, genre sa on senf ou un peu.

Oui, et je modifierais aussi certaines choses pour que le cahier des charges soit beaucoup plus professionnel et plus précis. On est rendu à un niveau où ça ressemble davantage à un projet de développement réel qui pourrait facilement dépasser 300 à 500 heures de travail.

Je rajouterais notamment :

un module de leçons complet
un système de gestion des documents
un système d'horaire avancé
une pondération des compétences
un système de photo de profil
des détails beaucoup plus poussés sur les examens
un design encore plus moderne
l'architecture Docker
l'hébergement via GitHub
un système pédagogique beaucoup plus structuré.
AJOUTS AU CAHIER DES CHARGES — EDUSPHERE V1.1
26. INFRASTRUCTURE ET DÉPLOIEMENT
Technologies obligatoires
Backend
Laravel 12
Frontend
Blade
Tailwind CSS
Alpine.js
JavaScript ES6+
Base de données
MySQL
Conteneurisation

Docker obligatoire.

L'ensemble du projet doit fonctionner dans des conteneurs Docker.

Conteneurs :

app (Laravel)

nginx

mysql

phpmyadmin

node

scheduler
Docker Compose

Le projet doit pouvoir être lancé avec :

docker compose up -d

L'ensemble du projet doit démarrer automatiquement.

GitHub

Le code source sera hébergé sur GitHub.

L'arborescence doit être propre.

EDUSPHERE

app/

bootstrap/

config/

database/

docker/

public/

resources/

routes/

storage/

tests/

docker-compose.yml

Dockerfile

README.md
27. SYSTÈME DE LEÇONS

Une nouvelle section doit être ajoutée.

Leçons

Une leçon est indépendante des activités.

Une leçon sert à enseigner avant de faire les exercices.

Hiérarchie :

Matière

↓

Compétence

↓

Leçon

↓

Activité

↓

Examen
Structure d'une leçon

Chaque leçon possède :

un titre
une description
une matière
une compétence
une image de couverture
une date
un niveau scolaire
une durée estimée
Importation de documents

Le professeur peut importer :

PowerPoint
.ppt
.pptx
Documents
.pdf
.doc
.docx
Images
.png
.jpg
.jpeg
.webp
Vidéos
.mp4
Audio
.mp3
Affichage des leçons

Les documents sont automatiquement convertis en plusieurs pages.

Exemple :

Leçon : Les fractions

Page 1

Introduction

↓

Page 2

Exemples

↓

Page 3

Explications

↓

Page 4

Résumé
Outils disponibles pendant une leçon

L'élève peut :

zoomer
défiler
surligner
prendre des notes
reprendre sa lecture
consulter en plein écran

Le système doit sauvegarder :

la dernière page lue
le temps passé
la progression
28. DESIGN UI/UX ULTRA MODERNE

Objectif :

Créer une expérience visuelle comparable aux meilleures applications premium.

Inspirations :

Apple
Linear
Arc Browser
Notion
Goodnotes
ClassDojo

Le design doit être :

très épuré
très moderne
très simple
très intuitif
très fluide
très aéré
Principes visuels

Interdictions :

❌ Aucun tableau surchargé

❌ Aucun menu complexe

❌ Aucun texte inutile

❌ Aucune petite police

Cartes modernes

Toutes les informations sont affichées sous forme de cartes.

Chaque carte possède :

ombres douces
coins très arrondis (24 px)
animations fluides
icônes modernes
Navigation

Barre inférieure tablette :

🏠 Accueil

📚 Matières

📖 Leçons

📝 Activités

📋 Examens

📅 Horaire

⭐ Points
29. PHOTOS DE PROFIL

Chaque élève doit pouvoir :

téléverser une photo
changer sa photo
supprimer sa photo

Formats :

JPG
PNG
WEBP

Limite :

5 Mo maximum.

Avatar par défaut

Si aucune photo :

avatar généré automatiquement.
30. SYSTÈME D'HORAIRE

Le professeur doit pouvoir créer des horaires très avancés.

Chaque journée comporte :

4 périodes.

Le professeur configure :

Les heures

Exemple :

Période 1

09:00 - 10:00

Période 2

10:15 - 11:15

Période 3

11:30 - 12:30

Période 4

13:30 - 14:30

Puis il choisit :

la matière
le titre
la couleur
Planification illimitée

Le professeur peut planifier :

1 jour
1 semaine
1 mois
6 mois
1 année complète

Aucune limite.

Affichage élève

L'élève peut consulter :

Vue du jour
09:00 - Français

10:15 - Mathématiques

11:30 - Natation

13:30 - Sciences
Vue semaine
Lundi

Mardi

Mercredi

Jeudi

Vendredi
Vue calendrier
Mois complet
31. COMPÉTENCES ET PONDÉRATION

Chaque matière doit posséder des compétences officielles.

Le professeur doit pouvoir attribuer un pourcentage à chaque compétence.

La somme doit toujours être égale à 100 %.

🇫🇷 FRANÇAIS
Compétence	%
Lecture et compréhension	30 %
Écriture	25 %
Grammaire	15 %
Orthographe	10 %
Vocabulaire	10 %
Communication orale	10 %
➗ MATHÉMATIQUES
Compétence	%
Arithmétique	25 %
Résolution de problèmes	25 %
Géométrie	20 %
Mesures	10 %
Fractions	10 %
Logique	10 %
🔬 SCIENCES
Compétence	%
Observation	20 %
Expérimentation	25 %
Univers vivant	20 %
Univers matériel	20 %
Terre et espace	15 %
🏛️ HISTOIRE
Compétence	%
Temps historique	30 %
Civilisations	30 %
Sociétés	20 %
Analyse historique	20 %
🌍 GÉOGRAPHIE
Compétence	%
Territoires	35 %
Cartes	35 %
Environnement	15 %
Population	15 %
☪️ ISLAM
Compétence	%
Lecture	20 %
Compréhension	25 %
Histoire islamique	25 %
Mémorisation	15 %
Valeurs	15 %
🏊 NATATION
Compétence	%
Flottaison	20 %
Respiration	20 %
Déplacements	20 %
Techniques de nage	25 %
Sécurité aquatique	15 %
🏃 ÉDUCATION PHYSIQUE
Compétence	%
Mouvement	25 %
Coordination	25 %
Santé	25 %
Activité physique	25 %
32. SYSTÈME DE CALCUL DES RÉSULTATS

Chaque examen doit contenir :

Matière

↓

Compétence

↓

Questions

↓

Points

↓

Résultat

↓

Résultat disciplinaire
Exemple

Mathématiques

Résolution de problèmes

Question 1 : 5 points

Question 2 : 10 points

Question 3 : 5 points

Total :

20 points

Résultat :

16/20

80 %

Cette note est ensuite ajoutée à la compétence.

Calcul automatique

Le système doit calculer :

note de l'activité
note de l'examen
moyenne des compétences
moyenne de la matière
moyenne générale
33. DÉTAILS AVANCÉS DES EXAMENS

Chaque examen possède :

Informations générales
titre
description
matière
compétence
date
heure
durée
Paramètres
date d'ouverture
date de fermeture
nombre de tentatives
minuterie
soumission automatique
Données enregistrées

Pour chaque élève :

heure de début
heure de fin
temps total
progression
nombre de pages visitées
nombre de réponses
note finale
Corrections

Le professeur peut :

corriger
annoter
dessiner
ajouter des commentaires
recorriger
34. NOUVEL OBJECTIF FINAL

EduSphere doit donner l'impression d'utiliser une véritable application native premium, située entre Goodnotes, Notion, ClassDojo et Google Classroom, spécialement conçue pour des enfants de 7 à 10 ans, avec une expérience extrêmement moderne, très simple, tactile, rapide et professionnelle, sans jamais devenir compliquée à utiliser.

Si tu développes réellement ce projet, je recommanderais même de faire un cahier des charges V2 encore plus détaillé (80 à 120 pages), avec les maquettes des écrans, la structure de la base de données (50+ tables), les routes Laravel, les composants Blade et toute l'architecture Docker.
