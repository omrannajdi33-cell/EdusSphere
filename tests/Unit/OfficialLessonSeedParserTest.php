<?php

namespace Tests\Unit;

use App\Services\OfficialLessonSeedParser;
use Tests\TestCase;

class OfficialLessonSeedParserTest extends TestCase
{
    public function test_parses_lesson_blocks_from_seed_format(): void
    {
        $raw = <<<'TXT'
Français - Primaire 5
Lecture
Compréhension de textes
Définition

La compréhension de textes est importante.

Explication

On lit entre les lignes.

Exemple

Texte court.

Idée principale
Définition

L'idée principale est le message central.

Explication

Cherche le tronc de l'arbre.
TXT;

        $lessons = app(OfficialLessonSeedParser::class)->parse($raw);

        $this->assertCount(2, $lessons);
        $this->assertSame('Compréhension de textes', $lessons[0]['title']);
        $this->assertSame('Français', $lessons[0]['subject']);
        $this->assertSame('Primaire 5', $lessons[0]['level']);
        $this->assertSame('Lecture et compréhension', $lessons[0]['skill']);
        $this->assertStringContainsString('## Définition', $lessons[0]['description']);
        $this->assertSame('Idée principale', $lessons[1]['title']);
    }
}
