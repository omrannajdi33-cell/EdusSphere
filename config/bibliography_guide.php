<?php

/**
 * Aide-mémoire bibliographique (inspiré des guides BANQ / Collecto).
 * Chaque cas propose un modèle par style : APA, DIONNE (traditionnel), Vancouver.
 */
return [
    'default_style' => 'dionne',

    'styles' => [
        'apa' => [
            'label' => 'APA',
            'description' => 'Format : auteur-date',
        ],
        'dionne' => [
            'label' => 'DIONNE (Traditionnel)',
            'description' => 'Format : classique (notes de bas de page)',
        ],
        'vancouver' => [
            'label' => 'VANCOUVER',
            'description' => 'Format : traditionnel (numéroté)',
        ],
    ],

    'fallback' => [
        'apa' => [
            'title' => 'Référence générale (APA)',
            'structure' => 'Auteur, A. A. (Année). Titre. Éditeur ou site. URL',
            'example' => 'Dupont, M. (2024). Mon sujet de recherche. Éditions du Savoir. https://exemple.ca',
            'tips' => [
                'Commence par l\'auteur ou l\'organisation.',
                'Indique l\'année entre parenthèses.',
                'Termine par l\'URL pour les sources en ligne.',
            ],
        ],
        'dionne' => [
            'title' => 'Référence générale (DIONNE)',
            'structure' => 'Prénom Nom, Titre, (lieu : éditeur, année).',
            'example' => 'Marie Dupont, Mon sujet de recherche, (Montréal : Éditions du Savoir, 2024).',
            'tips' => [
                'Inverse l\'ordre : Nom, Prénom.',
                'Le titre du livre s\'écrit en italique.',
                'Indique le lieu de publication et l\'éditeur.',
            ],
        ],
        'vancouver' => [
            'title' => 'Référence générale (Vancouver)',
            'structure' => 'Auteur AA. Titre. Lieu : Éditeur ; année.',
            'example' => 'Dupont M. Mon sujet de recherche. Montréal : Éditions du Savoir ; 2024.',
            'tips' => [
                'Utilise uniquement les initiales du prénom.',
                'Pas de point après le titre principal.',
                'Sépare l\'année par un point-virgule.',
            ],
        ],
    ],

    'documents' => [
        'books' => [
            'label' => 'Livres',
            'cases' => [
                'book_whole' => [
                    'label' => 'Livre complet',
                    'formats' => [
                        'apa' => [
                            'title' => 'Livre complet (APA)',
                            'structure' => 'Auteur, A. A. (Année). Titre du livre. Éditeur.',
                            'example' => 'Tremblay, J. (2023). Les sciences au primaire. Éditions Scolaires.',
                            'tips' => ['Titre en italique.', 'Indique l\'édition si ce n\'est pas la première.'],
                        ],
                        'dionne' => [
                            'title' => 'Livre complet (DIONNE)',
                            'structure' => 'Prénom Nom, Titre du livre, (ville : éditeur, année).',
                            'example' => 'Jean Tremblay, Les sciences au primaire, (Montréal : Éditions Scolaires, 2023).',
                            'tips' => ['Nom de famille en premier.', 'Titre en italique dans ta bibliographie.'],
                        ],
                        'vancouver' => [
                            'title' => 'Livre complet (Vancouver)',
                            'structure' => 'Auteur AA. Titre du livre. Ville : Éditeur ; année.',
                            'example' => 'Tremblay J. Les sciences au primaire. Montréal : Éditions Scolaires ; 2023.',
                            'tips' => ['Initiales du prénom sans point entre elles.', 'Année après un point-virgule.'],
                        ],
                    ],
                ],
                'book_chapter' => [
                    'label' => 'Chapitre de livre',
                    'formats' => [
                        'apa' => [
                            'title' => 'Chapitre (APA)',
                            'structure' => 'Auteur du chapitre, A. A. (Année). Titre du chapitre. In Éditeur (dir.), Titre de l\'ouvrage (pp. xx-xx). Éditeur.',
                            'example' => 'Gagnon, L. (2022). La photosynthèse. In P. Martin (dir.), Biologie vivante (pp. 45-62). Éditions Nord.',
                            'tips' => ['Titre du chapitre sans italique.', 'Titre de l\'ouvrage en italique.'],
                        ],
                        'dionne' => [
                            'title' => 'Chapitre (DIONNE)',
                            'structure' => 'Prénom Nom, « Titre du chapitre », in Prénom Nom (dir.), Titre de l\'ouvrage, (ville : éditeur, année), pages.',
                            'example' => 'Luc Gagnon, « La photosynthèse », in Pierre Martin (dir.), Biologie vivante, (Québec : Éditions Nord, 2022), 45-62.',
                            'tips' => ['Titre du chapitre entre guillemets français « ».', 'Indique les pages consultées.'],
                        ],
                        'vancouver' => [
                            'title' => 'Chapitre (Vancouver)',
                            'structure' => 'Auteur AA. Titre du chapitre. In : Éditeur AA, éditeur. Titre de l\'ouvrage. Ville : Éditeur ; année. p. xx-xx.',
                            'example' => 'Gagnon L. La photosynthèse. In : Martin P, éditeur. Biologie vivante. Québec : Éditions Nord ; 2022. p. 45-62.',
                            'tips' => ['Précise « éditeur » ou « éditrice ».', 'Pages avec « p. » avant les numéros.'],
                        ],
                    ],
                ],
            ],
        ],
        'journals' => [
            'label' => 'Journaux et revues',
            'cases' => [
                'journal_article' => [
                    'label' => 'Article de revue',
                    'formats' => [
                        'apa' => [
                            'title' => 'Article de revue (APA)',
                            'structure' => 'Auteur, A. A. (Année). Titre de l\'article. Nom de la revue, volume(numéro), pages. DOI ou URL',
                            'example' => 'Roy, S. (2024). Le climat change. Science & Vie, 125(3), 12-18. https://doi.org/xxxxx',
                            'tips' => ['Titre de la revue en italique.', 'Volume en italique, numéro entre parenthèses.'],
                        ],
                        'dionne' => [
                            'title' => 'Article de revue (DIONNE)',
                            'structure' => 'Prénom Nom, « Titre de l\'article », Nom de la revue, vol. X, no Y (année), pages.',
                            'example' => 'Sophie Roy, « Le climat change », Science & Vie, vol. 125, no 3 (2024), 12-18.',
                            'tips' => ['Titre de l\'article entre guillemets.', 'Indique volume et numéro.'],
                        ],
                        'vancouver' => [
                            'title' => 'Article de revue (Vancouver)',
                            'structure' => 'Auteur AA. Titre de l\'article. Nom de la revue. Année ;volume(numéro):pages.',
                            'example' => 'Roy S. Le climat change. Science & Vie. 2024 ;125(3):12-18.',
                            'tips' => ['Pas de guillemets autour du titre.', 'Deux-points avant les pages.'],
                        ],
                    ],
                ],
                'newspaper' => [
                    'label' => 'Article de journal',
                    'formats' => [
                        'apa' => [
                            'title' => 'Article de journal (APA)',
                            'structure' => 'Auteur, A. A. (Année, mois jour). Titre de l\'article. Nom du journal. URL',
                            'example' => 'Le Devoir. (2024, 15 mars). Une nouvelle découverte. Le Devoir. https://www.ledevoir.com/...',
                            'tips' => ['Indique la date complète.', 'URL si consulté en ligne.'],
                        ],
                        'dionne' => [
                            'title' => 'Article de journal (DIONNE)',
                            'structure' => 'Prénom Nom, « Titre de l\'article », Nom du journal, date, section ou page.',
                            'example' => 'Marie Lavoie, « Une nouvelle découverte », Le Devoir, 15 mars 2024, p. A4.',
                            'tips' => ['Date en toutes lettres ou abrégée selon ton enseignant.', 'Précise la page si disponible.'],
                        ],
                        'vancouver' => [
                            'title' => 'Article de journal (Vancouver)',
                            'structure' => 'Auteur AA. Titre de l\'article. Nom du journal. Année mois jour ;section:page.',
                            'example' => 'Lavoie M. Une nouvelle découverte. Le Devoir. 2024 mar 15 ;A:A4.',
                            'tips' => ['Mois abrégé en anglais (mar, avr…).', 'Section avant les deux-points.'],
                        ],
                    ],
                ],
            ],
        ],
        'web' => [
            'label' => 'Web et communications',
            'cases' => [
                'webpage' => [
                    'label' => 'Page Web',
                    'formats' => [
                        'apa' => [
                            'title' => 'Page Web (APA)',
                            'structure' => 'Auteur ou organisation. (Année, mois jour). Titre de la page. Nom du site. URL',
                            'example' => 'Bibliothèque et Archives nationales du Québec. (2026). Pour faire une bibliographie. Collecto. https://collecto.banq.qc.ca',
                            'tips' => ['Organisation comme auteur si pas de personne.', 'Date de consultation parfois demandée après l\'URL.'],
                        ],
                        'dionne' => [
                            'title' => 'Page Web (DIONNE)',
                            'structure' => 'Prénom Nom, « Titre de la page », Nom du site, URL, [consulté le jour mois année].',
                            'example' => 'Collecto, « Pour faire une bibliographie », Bibliothèque et Archives nationales du Québec, https://collecto.banq.qc.ca, [consulté le 21 juin 2026].',
                            'tips' => ['Titre entre guillemets « ».', 'Toujours indiquer la date de consultation entre crochets.'],
                        ],
                        'vancouver' => [
                            'title' => 'Page Web (Vancouver)',
                            'structure' => 'Auteur ou organisation. Titre de la page [Internet]. Ville : éditeur ; [consulté année mois jour]. Disponible : URL',
                            'example' => 'Bibliothèque et Archives nationales du Québec. Pour faire une bibliographie [Internet]. Québec ; [consulté 2026 juin 21]. Disponible : https://collecto.banq.qc.ca',
                            'tips' => ['Ajoute [Internet] après le titre.', 'Date de consultation entre crochets.'],
                        ],
                    ],
                ],
                'website' => [
                    'label' => 'Site Web complet',
                    'formats' => [
                        'apa' => [
                            'title' => 'Site Web (APA)',
                            'structure' => 'Organisation. (Année). Titre du site. URL',
                            'example' => 'Radio-Canada. (2026). ICI Radio-Canada. https://ici.radio-canada.ca',
                            'tips' => ['Cite le site dans son ensemble, pas une page précise.', 'Organisation comme auteur principal.'],
                        ],
                        'dionne' => [
                            'title' => 'Site Web (DIONNE)',
                            'structure' => 'Organisation, Titre du site, URL, [consulté le jour mois année].',
                            'example' => 'Radio-Canada, ICI Radio-Canada, https://ici.radio-canada.ca, [consulté le 21 juin 2026].',
                            'tips' => ['Pas de guillemets pour le titre du site.', 'Date de consultation obligatoire.'],
                        ],
                        'vancouver' => [
                            'title' => 'Site Web (Vancouver)',
                            'structure' => 'Organisation. Titre du site [Internet]. [consulté année mois jour]. Disponible : URL',
                            'example' => 'Radio-Canada. ICI Radio-Canada [Internet]. [consulté 2026 juin 21]. Disponible : https://ici.radio-canada.ca',
                            'tips' => ['[Internet] après le titre.', 'Pas de lieu d\'édition si inconnu.'],
                        ],
                    ],
                ],
                'blog' => [
                    'label' => 'Blogue',
                    'formats' => [
                        'apa' => [
                            'title' => 'Blogue (APA)',
                            'structure' => 'Auteur, A. A. (Année, mois jour). Titre de l\'article de blogue. Nom du blogue. URL',
                            'example' => 'Bouchard, A. (2025, 3 avril). Comment bien citer ses sources. Le coin des élèves. https://exemple.ca/blog/citation',
                            'tips' => ['Titre de l\'article en italique.', 'Nom du blogue après le titre.'],
                        ],
                        'dionne' => [
                            'title' => 'Blogue (DIONNE)',
                            'structure' => 'Prénom Nom, « Titre de l\'article », blogue Nom du blogue, date, URL, [consulté le jour mois année].',
                            'example' => 'Alex Bouchard, « Comment bien citer ses sources », blogue Le coin des élèves, 3 avril 2025, https://exemple.ca/blog/citation, [consulté le 21 juin 2026].',
                            'tips' => ['Précise le mot « blogue ».', 'Article entre guillemets.'],
                        ],
                        'vancouver' => [
                            'title' => 'Blogue (Vancouver)',
                            'structure' => 'Auteur AA. Titre de l\'article [Internet]. Nom du blogue ; année mois jour [consulté année mois jour]. Disponible : URL',
                            'example' => 'Bouchard A. Comment bien citer ses sources [Internet]. Le coin des élèves ; 2025 avr 3 [consulté 2026 juin 21]. Disponible : https://exemple.ca/blog/citation',
                            'tips' => ['Date de publication et de consultation.', '[Internet] après le titre.'],
                        ],
                    ],
                ],
                'social' => [
                    'label' => 'Réseaux sociaux',
                    'formats' => [
                        'apa' => [
                            'title' => 'Réseau social (APA)',
                            'structure' => 'Auteur ou @pseudo. (Année, mois jour). Texte ou description [Type de publication]. Nom du réseau. URL',
                            'example' => '@scienceqc. (2024, 10 mai). Découverte fascinante sur les baleines [Vidéo]. TikTok. https://tiktok.com/...',
                            'tips' => ['Indique le type entre crochets : [Publication], [Vidéo], [Fil].', 'Utilise @ si pas de nom réel.'],
                        ],
                        'dionne' => [
                            'title' => 'Réseau social (DIONNE)',
                            'structure' => 'Prénom Nom (@pseudo), « Texte ou titre », réseau social, date, URL, [consulté le jour mois année].',
                            'example' => 'Science Québec (@scienceqc), « Découverte fascinante sur les baleines », TikTok, 10 mai 2024, https://tiktok.com/..., [consulté le 21 juin 2026].',
                            'tips' => ['Nom du réseau en toutes lettres.', 'Contenu entre guillemets si court.'],
                        ],
                        'vancouver' => [
                            'title' => 'Réseau social (Vancouver)',
                            'structure' => 'Auteur AA. Texte ou titre [Internet]. Réseau social ; année mois jour [consulté année mois jour]. Disponible : URL',
                            'example' => 'Science Québec. Découverte fascinante sur les baleines [Internet]. TikTok ; 2024 mai 10 [consulté 2026 juin 21]. Disponible : https://tiktok.com/...',
                            'tips' => ['Sources web éphémères : capture ou URL stable si possible.', 'Date de consultation essentielle.'],
                        ],
                    ],
                ],
                'personal_comm' => [
                    'label' => 'Communication personnelle (courriel, téléphone)',
                    'formats' => [
                        'apa' => [
                            'title' => 'Communication personnelle (APA)',
                            'structure' => 'Prénom Nom, communication personnelle, mois jour, année.',
                            'example' => 'Marie Tremblay, communication personnelle, 15 mars 2024.',
                            'tips' => ['Cite dans le texte ; rarement en bibliographie sauf si demandé.', 'Ne pas inclure dans la bibliographie APA standard.'],
                        ],
                        'dionne' => [
                            'title' => 'Communication personnelle (DIONNE)',
                            'structure' => 'Prénom Nom, entretien / courriel / téléphone, date.',
                            'example' => 'Marie Tremblay, entretien téléphonique, 15 mars 2024.',
                            'tips' => ['Précise le type : entretien, courriel, message vocal.', 'Note de bas de page plutôt que bibliographie.'],
                        ],
                        'vancouver' => [
                            'title' => 'Communication personnelle (Vancouver)',
                            'structure' => 'Prénom Nom. Type de communication. Année mois jour.',
                            'example' => 'Marie Tremblay. Entretien téléphonique. 2024 mar 15.',
                            'tips' => ['Généralement citée dans le texte seulement.', 'Non récupérable par le lecteur.'],
                        ],
                    ],
                ],
                'ai_chat' => [
                    'label' => 'Échange avec un robot (ChatGPT, Copilot…)',
                    'formats' => [
                        'apa' => [
                            'title' => 'IA conversationnelle (APA)',
                            'structure' => 'Organisation. (Année). Nom du modèle (version) [Grand modèle de langage]. URL du service',
                            'example' => 'OpenAI. (2024). ChatGPT (4o) [Grand modèle de langage]. https://chat.openai.com',
                            'tips' => ['Décris l\'échange dans le texte, pas comme auteur.', 'Cite le modèle et la date de l\'échange.'],
                        ],
                        'dionne' => [
                            'title' => 'IA conversationnelle (DIONNE)',
                            'structure' => '« Prompt ou question posée », réponse générée par Nom du modèle, date de l\'échange, [outil d\'IA].',
                            'example' => '« Quelles sont les étapes d\'une recherche documentaire? », réponse générée par ChatGPT-4o, 10 mai 2024, [outil d\'IA].',
                            'tips' => ['Indique clairement que c\'est une réponse générée.', 'Ton enseignant peut limiter l\'usage de l\'IA.'],
                        ],
                        'vancouver' => [
                            'title' => 'IA conversationnelle (Vancouver)',
                            'structure' => 'Nom du modèle [Internet]. Échange du année mois jour. Disponible : URL',
                            'example' => 'ChatGPT-4o [Internet]. Échange du 2024 mai 10. Disponible : https://chat.openai.com',
                            'tips' => ['Précise la date de l\'échange.', 'Conserve une capture si possible.'],
                        ],
                    ],
                ],
                'software' => [
                    'label' => 'Logiciel et application mobile',
                    'formats' => [
                        'apa' => [
                            'title' => 'Logiciel (APA)',
                            'structure' => 'Auteur ou organisation. (Année). Nom du logiciel (version) [Logiciel]. URL ou magasin d\'applications',
                            'example' => 'Microsoft Corporation. (2024). Microsoft Word (Version 16) [Logiciel]. https://www.microsoft.com',
                            'tips' => ['Version entre parenthèses.', '[Logiciel] ou [Application mobile] entre crochets.'],
                        ],
                        'dionne' => [
                            'title' => 'Logiciel (DIONNE)',
                            'structure' => 'Organisation, Nom du logiciel, version, [logiciel], URL, [consulté le jour mois année].',
                            'example' => 'Microsoft Corporation, Microsoft Word, version 16, [logiciel], https://www.microsoft.com, [consulté le 21 juin 2026].',
                            'tips' => ['Organisation comme auteur.', 'Indique la version utilisée.'],
                        ],
                        'vancouver' => [
                            'title' => 'Logiciel (Vancouver)',
                            'structure' => 'Organisation. Nom du logiciel [Internet]. Version ; année [consulté année mois jour]. Disponible : URL',
                            'example' => 'Microsoft Corporation. Microsoft Word [Internet]. Version 16 ; 2024 [consulté 2026 juin 21]. Disponible : https://www.microsoft.com',
                            'tips' => ['[Internet] si téléchargé en ligne.', 'Version et année séparées par un point-virgule.'],
                        ],
                    ],
                ],
                'webinar' => [
                    'label' => 'Webinaire',
                    'formats' => [
                        'apa' => [
                            'title' => 'Webinaire (APA)',
                            'structure' => 'Auteur, A. A. (Année, mois jour). Titre du webinaire [Webinaire]. Organisation. URL',
                            'example' => 'Gouvernement du Québec. (2024, 20 septembre). Agir pour le climat [Webinaire]. Ministère de l\'Environnement. https://...',
                            'tips' => ['[Webinaire] entre crochets après le titre.', 'Organisation hôte après le titre.'],
                        ],
                        'dionne' => [
                            'title' => 'Webinaire (DIONNE)',
                            'structure' => 'Prénom Nom / Organisation, « Titre du webinaire », webinaire, date, URL, [consulté le jour mois année].',
                            'example' => 'Ministère de l\'Environnement, « Agir pour le climat », webinaire, 20 septembre 2024, https://..., [consulté le 21 juin 2026].',
                            'tips' => ['Précise le mot « webinaire ».', 'Date de la session en direct.'],
                        ],
                        'vancouver' => [
                            'title' => 'Webinaire (Vancouver)',
                            'structure' => 'Auteur AA. Titre du webinaire [Internet]. Organisation ; année mois jour [consulté année mois jour]. Disponible : URL',
                            'example' => 'Gouvernement du Québec. Agir pour le climat [Internet]. Ministère de l\'Environnement ; 2024 sep 20 [consulté 2026 juin 21]. Disponible : https://...',
                            'tips' => ['Date de diffusion et de consultation.', '[Internet] après le titre.'],
                        ],
                    ],
                ],
                'brochure' => [
                    'label' => 'Brochure, dépliant, programme',
                    'formats' => [
                        'apa' => [
                            'title' => 'Brochure (APA)',
                            'structure' => 'Organisation. (Année). Titre de la brochure [Brochure]. Éditeur. URL',
                            'example' => 'Musée de la civilisation. (2023). Programme scolaire 2023-2024 [Brochure]. Musée de la civilisation.',
                            'tips' => ['[Brochure], [Dépliant] ou [Programme] entre crochets.', 'Organisation comme auteur.'],
                        ],
                        'dionne' => [
                            'title' => 'Brochure (DIONNE)',
                            'structure' => 'Organisation, Titre, [brochure / dépliant / programme], (ville : éditeur, année).',
                            'example' => 'Musée de la civilisation, Programme scolaire 2023-2024, [brochure], (Québec : Musée de la civilisation, 2023).',
                            'tips' => ['Type de document entre crochets.', 'Lieu et éditeur si imprimé.'],
                        ],
                        'vancouver' => [
                            'title' => 'Brochure (Vancouver)',
                            'structure' => 'Organisation. Titre [brochure]. Ville : Éditeur ; année.',
                            'example' => 'Musée de la civilisation. Programme scolaire 2023-2024 [brochure]. Québec : Musée de la civilisation ; 2023.',
                            'tips' => ['Type entre crochets en minuscules.', 'Format compact.'],
                        ],
                    ],
                ],
                'press_release' => [
                    'label' => 'Communiqué de presse',
                    'formats' => [
                        'apa' => [
                            'title' => 'Communiqué (APA)',
                            'structure' => 'Organisation. (Année, mois jour). Titre du communiqué [Communiqué de presse]. URL',
                            'example' => 'UNICEF Canada. (2024, 5 juin). Rapport sur l\'éducation [Communiqué de presse]. https://...',
                            'tips' => ['[Communiqué de presse] entre crochets.', 'Date exacte de publication.'],
                        ],
                        'dionne' => [
                            'title' => 'Communiqué (DIONNE)',
                            'structure' => 'Organisation, « Titre », communiqué de presse, date, URL, [consulté le jour mois année].',
                            'example' => 'UNICEF Canada, « Rapport sur l\'éducation », communiqué de presse, 5 juin 2024, https://..., [consulté le 21 juin 2026].',
                            'tips' => ['Titre entre guillemets.', 'Organisation comme auteur.'],
                        ],
                        'vancouver' => [
                            'title' => 'Communiqué (Vancouver)',
                            'structure' => 'Organisation. Titre [Internet]. Année mois jour [consulté année mois jour]. Disponible : URL',
                            'example' => 'UNICEF Canada. Rapport sur l\'éducation [Internet]. 2024 juin 5 [consulté 2026 juin 21]. Disponible : https://...',
                            'tips' => ['Source institutionnelle fiable.', 'Date de consultation pour le web.'],
                        ],
                    ],
                ],
            ],
        ],
        'audio_video' => [
            'label' => 'Audio et vidéos',
            'cases' => [
                'video_online' => [
                    'label' => 'Vidéo en ligne',
                    'formats' => [
                        'apa' => [
                            'title' => 'Vidéo en ligne (APA)',
                            'structure' => 'Auteur ou chaîne. (Année, mois jour). Titre de la vidéo [Vidéo]. Plateforme. URL',
                            'example' => 'Radio-Canada. (2024, 12 janvier). Les océans du Québec [Vidéo]. YouTube. https://youtube.com/...',
                            'tips' => ['[Vidéo] entre crochets.', 'Plateforme : YouTube, Vimeo, etc.'],
                        ],
                        'dionne' => [
                            'title' => 'Vidéo en ligne (DIONNE)',
                            'structure' => 'Auteur ou chaîne, « Titre », vidéo en ligne, plateforme, date, URL, [consulté le jour mois année].',
                            'example' => 'Radio-Canada, « Les océans du Québec », vidéo en ligne, YouTube, 12 janvier 2024, https://youtube.com/..., [consulté le 21 juin 2026].',
                            'tips' => ['Précise « vidéo en ligne ».', 'Date de consultation obligatoire.'],
                        ],
                        'vancouver' => [
                            'title' => 'Vidéo en ligne (Vancouver)',
                            'structure' => 'Auteur AA. Titre [Internet]. Plateforme ; année mois jour [consulté année mois jour]. Disponible : URL',
                            'example' => 'Radio-Canada. Les océans du Québec [Internet]. YouTube ; 2024 jan 12 [consulté 2026 juin 21]. Disponible : https://youtube.com/...',
                            'tips' => ['[Internet] après le titre.', 'Plateforme avant la date.'],
                        ],
                    ],
                ],
                'podcast' => [
                    'label' => 'Balado / podcast',
                    'formats' => [
                        'apa' => [
                            'title' => 'Balado (APA)',
                            'structure' => 'Auteur. (Année, mois jour). Titre de l\'épisode (No. xx) [Balado]. Nom du balado. URL',
                            'example' => 'Gauthier, P. (2023, 8 mars). La nature au Québec (No. 42) [Balado]. Odyssée nature. https://...',
                            'tips' => ['[Balado] ou [Podcast] entre crochets.', 'Numéro d\'épisode si disponible.'],
                        ],
                        'dionne' => [
                            'title' => 'Balado (DIONNE)',
                            'structure' => 'Auteur, « Titre de l\'épisode », balado Nom du balado, date, URL, [consulté le jour mois année].',
                            'example' => 'Pierre Gauthier, « La nature au Québec », balado Odyssée nature, 8 mars 2023, https://..., [consulté le 21 juin 2026].',
                            'tips' => ['Mot « balado » avant le nom de la série.', 'Épisode entre guillemets.'],
                        ],
                        'vancouver' => [
                            'title' => 'Balado (Vancouver)',
                            'structure' => 'Auteur AA. Titre [Internet]. Nom du balado ; année mois jour [consulté année mois jour]. Disponible : URL',
                            'example' => 'Gauthier P. La nature au Québec [Internet]. Odyssée nature ; 2023 mar 8 [consulté 2026 juin 21]. Disponible : https://...',
                            'tips' => ['Format similaire à une page web.', 'Durée optionnelle si pertinente.'],
                        ],
                    ],
                ],
            ],
        ],
        'encyclopedia' => [
            'label' => 'Encyclopédies et dictionnaires',
            'cases' => [
                'encyclopedia_entry' => [
                    'label' => 'Article d\'encyclopédie',
                    'formats' => [
                        'apa' => [
                            'title' => 'Encyclopédie (APA)',
                            'structure' => 'Auteur, A. A. (Année). Titre de l\'article. In Éditeur (dir.), Nom de l\'encyclopédie. Éditeur. URL',
                            'example' => 'Lavoie, M. (2022). Photosynthèse. In L. Bergeron (dir.), Encyclo Nature. Éditions Atlas. https://...',
                            'tips' => ['Pas de page si en ligne.', 'Nom de l\'encyclopédie en italique.'],
                        ],
                        'dionne' => [
                            'title' => 'Encyclopédie (DIONNE)',
                            'structure' => 'Prénom Nom, « Titre de l\'article », in Nom de l\'encyclopédie, (ville : éditeur, année), URL, [consulté le jour mois année].',
                            'example' => 'Marie Lavoie, « Photosynthèse », in Encyclo Nature, (Montréal : Éditions Atlas, 2022), https://..., [consulté le 21 juin 2026].',
                            'tips' => ['Article entre guillemets.', 'Date de consultation si en ligne.'],
                        ],
                        'vancouver' => [
                            'title' => 'Encyclopédie (Vancouver)',
                            'structure' => 'Auteur AA. Titre [Internet]. Nom de l\'encyclopédie ; année [consulté année mois jour]. Disponible : URL',
                            'example' => 'Lavoie M. Photosynthèse [Internet]. Encyclo Nature ; 2022 [consulté 2026 juin 21]. Disponible : https://...',
                            'tips' => ['Ouvrage de référence collectif.', 'Édition si plusieurs volumes.'],
                        ],
                    ],
                ],
            ],
        ],
        'images' => [
            'label' => 'Images',
            'cases' => [
                'photo' => [
                    'label' => 'Photographie',
                    'formats' => [
                        'apa' => [
                            'title' => 'Photographie (APA)',
                            'structure' => 'Auteur ou photographe. (Année). Titre ou description [Photographie]. Source. URL',
                            'example' => 'Tremblay, J. (2020). Aurore boréale au Québec [Photographie]. Banque d\'images Nord. https://...',
                            'tips' => ['[Photographie] entre crochets.', 'Description si pas de titre officiel.'],
                        ],
                        'dionne' => [
                            'title' => 'Photographie (DIONNE)',
                            'structure' => 'Photographe, Titre ou description, [photographie], source, date, URL, [consulté le jour mois année].',
                            'example' => 'Jean Tremblay, Aurore boréale au Québec, [photographie], Banque d\'images Nord, 2020, https://..., [consulté le 21 juin 2026].',
                            'tips' => ['Crédit du photographe obligatoire.', 'Droits d\'auteur : vérifie la licence.'],
                        ],
                        'vancouver' => [
                            'title' => 'Photographie (Vancouver)',
                            'structure' => 'Auteur AA. Titre [Internet]. Source ; année [consulté année mois jour]. Disponible : URL',
                            'example' => 'Tremblay J. Aurore boréale au Québec [Internet]. Banque d\'images Nord ; 2020 [consulté 2026 juin 21]. Disponible : https://...',
                            'tips' => ['Indique la source de l\'image.', 'Lien vers l\'image originale.'],
                        ],
                    ],
                ],
            ],
        ],
        'maps' => [
            'label' => 'Cartes géographiques',
            'cases' => [
                'map_print' => [
                    'label' => 'Carte imprimée',
                    'formats' => [
                        'apa' => [
                            'title' => 'Carte (APA)',
                            'structure' => 'Auteur ou organisation. (Année). Titre de la carte [Carte]. Éditeur.',
                            'example' => 'Gouvernement du Québec. (2023). Carte des régions du Québec [Carte]. Ministère des Ressources naturelles.',
                            'tips' => ['[Carte] entre crochets.', 'Échelle si indiquée sur la carte.'],
                        ],
                        'dionne' => [
                            'title' => 'Carte (DIONNE)',
                            'structure' => 'Organisation, Titre de la carte, [carte], (ville : éditeur, année).',
                            'example' => 'Gouvernement du Québec, Carte des régions du Québec, [carte], (Québec : MRNF, 2023).',
                            'tips' => ['[carte] en minuscules entre crochets.', 'Échelle après le titre si disponible.'],
                        ],
                        'vancouver' => [
                            'title' => 'Carte (Vancouver)',
                            'structure' => 'Organisation. Titre [carte]. Ville : Éditeur ; année.',
                            'example' => 'Gouvernement du Québec. Carte des régions du Québec [carte]. Québec : MRNF ; 2023.',
                            'tips' => ['Source cartographique officielle privilégiée.', 'Année de publication de la carte.'],
                        ],
                    ],
                ],
            ],
        ],
        'scores' => [
            'label' => 'Partitions musicales',
            'cases' => [
                'score' => [
                    'label' => 'Partition',
                    'formats' => [
                        'apa' => [
                            'title' => 'Partition (APA)',
                            'structure' => 'Compositeur, A. A. (Année). Titre de l\'œuvre [Partition]. Éditeur.',
                            'example' => 'Vivaldi, A. (1725). Les Quatre Saisons [Partition]. Éditions Musicales.',
                            'tips' => ['Compositeur comme auteur.', 'Titre de l\'œuvre en italique.'],
                        ],
                        'dionne' => [
                            'title' => 'Partition (DIONNE)',
                            'structure' => 'Prénom Nom, Titre de l\'œuvre, [partition], (ville : éditeur, année).',
                            'example' => 'Antonio Vivaldi, Les Quatre Saisons, [partition], (Paris : Éditions Musicales, 1725).',
                            'tips' => ['Compositeur en premier.', '[partition] entre crochets.'],
                        ],
                        'vancouver' => [
                            'title' => 'Partition (Vancouver)',
                            'structure' => 'Compositeur AA. Titre [partition]. Ville : Éditeur ; année.',
                            'example' => 'Vivaldi A. Les Quatre Saisons [partition]. Paris : Éditions Musicales ; 1725.',
                            'tips' => ['Année de composition ou d\'édition selon contexte.', 'Arrangeur si partition adaptée.'],
                        ],
                    ],
                ],
            ],
        ],
        'gov' => [
            'label' => 'Publications gouvernementales ou organisationnelles',
            'cases' => [
                'gov_report' => [
                    'label' => 'Rapport gouvernemental',
                    'formats' => [
                        'apa' => [
                            'title' => 'Rapport gouvernemental (APA)',
                            'structure' => 'Organisation gouvernementale. (Année). Titre du rapport (No de publication). URL',
                            'example' => 'Gouvernement du Québec. (2024). Plan d\'action climatique. Ministère de l\'Environnement. https://...',
                            'tips' => ['Organisation comme auteur.', 'Numéro de publication si disponible.'],
                        ],
                        'dionne' => [
                            'title' => 'Rapport gouvernemental (DIONNE)',
                            'structure' => 'Organisation, Titre du rapport, (ville : ministère ou organisme, année).',
                            'example' => 'Gouvernement du Québec, Plan d\'action climatique, (Québec : Ministère de l\'Environnement, 2024).',
                            'tips' => ['Ministère ou organisme responsable.', 'Titre en italique.'],
                        ],
                        'vancouver' => [
                            'title' => 'Rapport gouvernemental (Vancouver)',
                            'structure' => 'Organisation. Titre. Ville : Éditeur ; année.',
                            'example' => 'Gouvernement du Québec. Plan d\'action climatique. Québec : Ministère de l\'Environnement ; 2024.',
                            'tips' => ['Source officielle fiable.', 'URL en note si version web.'],
                        ],
                    ],
                ],
            ],
        ],
        'academic' => [
            'label' => 'Sources académiques',
            'cases' => [
                'thesis' => [
                    'label' => 'Mémoire ou thèse',
                    'formats' => [
                        'apa' => [
                            'title' => 'Thèse (APA)',
                            'structure' => 'Auteur, A. A. (Année). Titre [Thèse de doctorat / Mémoire de maîtrise, Nom de l\'université]. URL',
                            'example' => 'Côté, É. (2023). L\'apprentissage au primaire [Mémoire de maîtrise, Université Laval]. https://...',
                            'tips' => ['Type entre crochets : mémoire ou thèse.', 'Université et URL si disponible.'],
                        ],
                        'dionne' => [
                            'title' => 'Thèse (DIONNE)',
                            'structure' => 'Prénom Nom, Titre, [mémoire ou thèse], université, année.',
                            'example' => 'Émilie Côté, L\'apprentissage au primaire, [mémoire de maîtrise], Université Laval, 2023.',
                            'tips' => ['Précise mémoire ou thèse.', 'Nom de l\'université.'],
                        ],
                        'vancouver' => [
                            'title' => 'Thèse (Vancouver)',
                            'structure' => 'Auteur AA. Titre [dissertation/master\'s thesis]. Ville : Université ; année.',
                            'example' => 'Côté É. L\'apprentissage au primaire [master\'s thesis]. Québec : Université Laval ; 2023.',
                            'tips' => ['Termes en anglais parfois utilisés en Vancouver.', 'Disponible en ligne : ajouter URL.'],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
