<?php

namespace App\Services;

class LessonContentEnricher
{
    /** @var array<string, string> */
    private array $domainEmojis = [
        'Lecture' => '📚',
        'Écriture' => '✍️',
        'Grammaire' => '📝',
        'Conjugaison' => '🔄',
        'Orthographe' => '✏️',
        'Vocabulaire' => '💬',
        'Nombres' => '🔢',
        'Opérations' => '➕',
        'Géométrie' => '📐',
        'Mesure' => '📏',
        'Statistique' => '📊',
        'Probabilité' => '🎲',
        'Univers matériel' => '🧪',
        'Univers vivant' => '🌿',
        'Terre et espace' => '🌍',
        'Technologie' => '⚙️',
        'Géographie' => '🗺️',
        'Histoire' => '🏛️',
        'Citoyenneté' => '🤝',
        'Éducation à la citoyenneté' => '🤝',
    ];

    /** @var array<string, string> */
    private array $subjectEmojis = [
        'Français' => '📘',
        'Mathématiques' => '🔢',
        'Sciences' => '🔬',
        'Univers social' => '🌎',
        'Géographie' => '🗺️',
        'Histoire' => '📜',
    ];

    public function enrichTitle(string $title, string $subject, string $category): string
    {
        $emoji = $this->domainEmojis[$category] ?? $this->subjectEmojis[$subject] ?? '📖';

        if (preg_match('/^[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/u', $title)) {
            return $title;
        }

        return $emoji.' '.$title;
    }

    public function enrichDescription(string $description, string $category): string
    {
        $lines = preg_split("/\r\n|\n|\r/", $description) ?: [];
        $enriched = [];

        foreach ($lines as $line) {
            $enriched[] = match (true) {
                str_starts_with($line, '## Définition') => '## 📖 Définition',
                str_starts_with($line, '## Explication') => '## 💡 Explication',
                str_starts_with($line, '## Exemples') => '## ✨ Exemples',
                str_starts_with($line, '## Exemple') => '## ✨ Exemple',
                str_starts_with($line, '## Structure') => '## 🧩 Structure',
                str_starts_with($line, '## Formation') => '## 🛠️ Formation',
                default => $line,
            };
        }

        $body = trim(implode("\n", $enriched));
        $banner = $this->bannerFor($category);

        if ($banner === '') {
            return $body;
        }

        return $banner."\n\n".$body;
    }

    private function bannerFor(string $category): string
    {
        $emoji = $this->domainEmojis[$category] ?? '🎯';

        return match ($category) {
            'Lecture' => "{$emoji} Lecture — Comprends, analyse et profite de chaque texte !",
            'Écriture' => "{$emoji} Écriture — Exprime tes idées avec clarté et créativité.",
            'Grammaire' => "{$emoji} Grammaire — Maîtrise la structure des phrases.",
            'Conjugaison' => "{$emoji} Conjugaison — Voyages dans le temps avec les verbes !",
            'Orthographe' => "{$emoji} Orthographe — Écris juste, écris fort !",
            'Nombres' => "{$emoji} Nombres — Explore, compare et calcule !",
            'Opérations' => "{$emoji} Opérations — Addition, soustraction, multiplication… à toi de jouer !",
            'Géométrie' => "{$emoji} Géométrie — Formes, angles et espace.",
            'Mesure' => "{$emoji} Mesure — Longueur, masse, temps et plus encore.",
            'Statistique' => "{$emoji} Statistique — Lis les données comme un pro.",
            'Probabilité' => "{$emoji} Probabilité — Certain ? Possible ? Impossible ?",
            'Univers matériel' => "{$emoji} Univers matériel — Matière, énergie et phénomènes.",
            'Univers vivant' => "{$emoji} Univers vivant — Plantes, animaux et biodiversité.",
            'Terre et espace' => "{$emoji} Terre et espace — Planètes, saisons et météo.",
            'Technologie' => "{$emoji} Technologie — Invente, construis, comprends.",
            'Géographie' => "{$emoji} Géographie — Territoires, cartes et régions.",
            'Histoire' => "{$emoji} Histoire — Voyage dans le passé du Québec.",
            default => "{$emoji} {$category} — Prêt à apprendre ? C'est parti ! 🚀",
        };
    }
}
