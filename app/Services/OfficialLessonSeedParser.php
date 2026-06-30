<?php

namespace App\Services;

class OfficialLessonSeedParser
{
    private const SECTION_HEADER = '/^(.+?)\s[-–]\s+(Primaire\s+\d+)\s*$/u';

    /** @var list<string> */
    private const FRANCAIS_DOMAINS = [
        'Lecture',
        'Écriture',
        'Grammaire',
        'Conjugaison',
        'Orthographe',
        'Vocabulaire',
    ];

    /** @var list<string> */
    private const BLOCK_MARKERS = [
        'Définition',
        'Explication',
        'Exemple',
        'Exemples',
        'Structure',
        'Formation',
    ];

    /**
     * @return list<array{
     *     subject: string,
     *     level: string,
     *     domain: string,
     *     skill: string,
     *     title: string,
     *     description: string
     * }>
     */
    public function parse(string $raw): array
    {
        $normalized = $this->normalize($raw);
        $lines = preg_split("/\r\n|\n|\r/", $normalized) ?: [];

        $lessons = [];
        $subject = null;
        $level = null;
        $domain = null;
        $subcategory = null;
        $pendingTitle = null;
        $currentTitle = null;
        $currentBlock = null;
        $buffer = [];

        $flushLesson = function () use (&$lessons, &$currentTitle, &$buffer, &$currentBlock, &$subject, &$level, &$domain): void {
            if ($subject === null || $level === null || $domain === null || $currentTitle === null) {
                return;
            }

            $description = trim(implode("\n", $buffer));
            if ($description === '') {
                return;
            }

            $lessons[] = [
                'subject' => $this->normalizeSubjectName($subject),
                'level' => $level,
                'domain' => $domain,
                'skill' => $this->resolveSkill($subject, $domain, $currentTitle),
                'title' => $currentTitle,
                'description' => $description,
            ];

            $currentTitle = null;
            $buffer = [];
            $currentBlock = null;
        };

        foreach ($lines as $index => $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                if ($currentBlock !== null) {
                    $buffer[] = '';
                }

                continue;
            }

            if (preg_match(self::SECTION_HEADER, $trimmed, $matches)) {
                $flushLesson();
                $subject = trim($matches[1]);
                $level = trim($matches[2]);
                $domain = null;
                $subcategory = null;
                $pendingTitle = null;

                continue;
            }

            if ($subject === null) {
                continue;
            }

            if ($domain === null && $this->isFrançaisDomain($trimmed)) {
                $domain = $this->normalizeFrançaisDomain($trimmed);
                $subcategory = null;
                $pendingTitle = null;

                continue;
            }

            if ($domain === null && $this->isDomainLine($lines, $index)) {
                $domain = $trimmed;
                $subcategory = null;
                $pendingTitle = null;

                continue;
            }

            if ($domain !== null && in_array($trimmed, self::BLOCK_MARKERS, true)) {
                if ($currentTitle === null) {
                    $currentTitle = $pendingTitle ?? $subcategory ?? $domain;
                    $pendingTitle = null;
                }

                $currentBlock = $trimmed;
                $buffer[] = '## '.$trimmed;

                continue;
            }

            if ($domain !== null && $this->looksLikeUpcomingLesson($lines, $index)) {
                $flushLesson();
                $pendingTitle = $trimmed;

                continue;
            }

            if ($currentBlock !== null) {
                $buffer[] = $trimmed;
            } elseif ($domain !== null && $subcategory === null && $pendingTitle === null) {
                $subcategory = $trimmed;
            }
        }

        $flushLesson();

        return $lessons;
    }

    private function normalize(string $raw): string
    {
        $text = preg_replace('/\.\s*(Français\s[-–])/u', "\n\n$1", $raw) ?? $raw;
        $text = preg_replace('/\S(Français\s[-–])/u', "\n\n$1", $text) ?? $text;
        $text = preg_replace('/\S(Mathématiques\s[-–])/u', "\n\n$1", $text) ?? $text;
        $text = preg_replace('/\S(Science et technologie\s[-–])/u', "\n\n$1", $text) ?? $text;
        $text = preg_replace('/\S(Univers social\s[-–])/u', "\n\n$1", $text) ?? $text;
        $text = preg_replace("/orthographe(Mathématiques\s[-–])/ui", "orthographe\n\n$1", $text) ?? $text;

        return $text;
    }

    private function normalizeSubjectName(string $subject): string
    {
        return match ($subject) {
            'Science et technologie' => 'Sciences',
            default => $subject,
        };
    }

    /** @param list<string> $lines */
    private function isDomainLine(array $lines, int $index): bool
    {
        $trimmed = trim($lines[$index]);

        if ($trimmed === '' || preg_match(self::SECTION_HEADER, $trimmed)) {
            return false;
        }

        if (in_array($trimmed, self::BLOCK_MARKERS, true)) {
            return false;
        }

        if ($this->looksLikeUpcomingLesson($lines, $index)) {
            return false;
        }

        $next = $this->nextNonEmpty($lines, $index + 1);

        return $next !== null && ! $this->lineIsDefinition($next);
    }

    private function isFrançaisDomain(string $line): bool
    {
        if (in_array($line, self::FRANCAIS_DOMAINS, true)) {
            return true;
        }

        return str_starts_with($line, 'Grammaire');
    }

    private function normalizeFrançaisDomain(string $line): string
    {
        return str_starts_with($line, 'Grammaire') ? 'Grammaire' : $line;
    }

    /** @param list<string> $lines */
    private function looksLikeUpcomingLesson(array $lines, int $index): bool
    {
        $trimmed = trim($lines[$index]);

        if (in_array($trimmed, self::BLOCK_MARKERS, true)) {
            return false;
        }

        if (preg_match(self::SECTION_HEADER, $trimmed)) {
            return false;
        }

        if (in_array($trimmed, self::FRANCAIS_DOMAINS, true) && $trimmed !== 'Vocabulaire') {
            return false;
        }

        if (str_starts_with($trimmed, 'Grammaire')) {
            return false;
        }

        for ($i = $index + 1; $i < min($index + 4, count($lines)); $i++) {
            $next = trim($lines[$i]);
            if ($next === '') {
                continue;
            }

            return $next === 'Définition';
        }

        return false;
    }

    /** @param list<string> $lines */
    private function nextNonEmpty(array $lines, int $start): ?string
    {
        for ($i = $start; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if ($line !== '') {
                return $line;
            }
        }

        return null;
    }

    private function lineIsDefinition(string $line): bool
    {
        return $line === 'Définition';
    }

    private function resolveSkill(string $subject, string $domain, string $title): string
    {
        if ($subject === 'Français') {
            if ($title === 'Vocabulaire' || $domain === 'Vocabulaire') {
                return 'Vocabulaire';
            }

            return match ($domain) {
                'Lecture' => 'Lecture et compréhension',
                'Écriture' => 'Écriture',
                'Grammaire', 'Conjugaison' => 'Grammaire',
                'Orthographe' => 'Orthographe',
                default => 'Lecture et compréhension',
            };
        }

        if ($subject === 'Mathématiques') {
            return match ($domain) {
                'Nombres', 'Opérations' => 'Arithmétique',
                'Géométrie' => 'Géométrie',
                'Mesure' => 'Mesures',
                'Statistique', 'Probabilité' => 'Logique',
                default => 'Arithmétique',
            };
        }

        if ($subject === 'Science et technologie' || $subject === 'Sciences') {
            return match ($domain) {
                'Univers matériel' => 'Univers matériel',
                'Univers vivant' => 'Univers vivant',
                'Terre et espace' => 'Terre et espace',
                'Technologie' => 'Expérimentation',
                default => 'Observation',
            };
        }

        if ($subject === 'Univers social') {
            return match ($domain) {
                'Géographie' => 'Territoires',
                'Histoire' => 'Temps historique',
                default => 'Territoires',
            };
        }

        return $domain;
    }

    public function resolveSubjectForStorage(array $entry): string
    {
        if ($entry['subject'] === 'Univers social') {
            return match ($entry['domain']) {
                'Géographie' => 'Géographie',
                'Histoire' => 'Histoire',
                default => 'Univers social',
            };
        }

        return $entry['subject'];
    }
}
