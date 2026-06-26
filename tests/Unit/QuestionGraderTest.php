<?php

namespace Tests\Unit;

use App\Support\QuestionGrader;
use PHPUnit\Framework\TestCase;

class QuestionGraderTest extends TestCase
{
    public function test_mcq_evaluation(): void
    {
        $question = (object) [
            'type' => 'mcq',
            'config' => [
                'options' => [['text' => 'A'], ['text' => 'B']],
                'correct' => 1,
            ],
        ];

        $correct = QuestionGrader::evaluate($question, '1');
        $this->assertTrue($correct['gradable']);
        $this->assertTrue($correct['correct']);
        $this->assertSame('B', $correct['correct_label']);

        $wrong = QuestionGrader::evaluate($question, '0');
        $this->assertFalse($wrong['correct']);
        $this->assertSame('B', $wrong['correct_label']);
    }

    public function test_non_gradable_question(): void
    {
        $question = (object) ['type' => 'long_text', 'config' => []];
        $result = QuestionGrader::evaluate($question, 'hello');

        $this->assertFalse($result['gradable']);
        $this->assertNull($result['correct']);
    }
}
