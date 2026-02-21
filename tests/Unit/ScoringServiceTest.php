<?php

namespace Tests\Unit;

use App\Exceptions\ScoringException;
use App\Models\Assessment;
use App\Models\AssessmentOption;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentRule;
use App\Models\Patient;
use App\Models\ScreeningRequest;
use App\Models\ScreeningRequestItem;
use App\Models\User;
use App\Services\ScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Pruebas unitarias del ScoringService con base de datos en memoria.
 */
class ScoringServiceTest extends TestCase
{
    use RefreshDatabase;

    private ScoringService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ScoringService();
    }

    // ----------------------------------------------------------------
    // Helpers para crear datos de prueba
    // ----------------------------------------------------------------

    private function makePhq9(): Assessment
    {
        $assessment = Assessment::create([
            'slug'            => 'phq-9',
            'name'            => 'Patient Health Questionnaire-9',
            'short_name'      => 'PHQ-9',
            'is_active'       => true,
            'estimated_minutes' => 5,
            'credits_cost'    => 1,
        ]);

        // 9 preguntas, 4 opciones cada una (score 0-3)
        for ($q = 1; $q <= 9; $q++) {
            $question = AssessmentQuestion::create([
                'assessment_id' => $assessment->id,
                'order'         => $q,
                'question_text' => "Pregunta {$q} del PHQ-9",
                'is_required'   => true,
            ]);

            $scores = [0, 1, 2, 3];
            $labels = ['Nunca', 'Varios días', 'Más de la mitad de los días', 'Casi todos los días'];
            foreach ($scores as $i => $s) {
                AssessmentOption::create([
                    'assessment_question_id' => $question->id,
                    'order'                  => $i,
                    'option_text'            => $labels[$i],
                    'score_value'            => $s,
                ]);
            }
        }

        // Reglas de interpretación PHQ-9 estándar
        $rules = [
            [0,  4,  'Mínima',   'Sin indicadores significativos de depresión.'],
            [5,  9,  'Leve',     'Síntomas depresivos leves.'],
            [10, 14, 'Moderada', 'Síntomas depresivos moderados.'],
            [15, 19, 'Moderadamente severa', 'Síntomas moderadamente severos.'],
            [20, 27, 'Severa',   'Síntomas depresivos severos.'],
        ];
        foreach ($rules as [$min, $max, $label, $text]) {
            AssessmentRule::create([
                'assessment_id'      => $assessment->id,
                'min_score'          => $min,
                'max_score'          => $max,
                'severity_label'     => $label,
                'interpretation_text' => $text,
            ]);
        }

        return $assessment;
    }

    private function makeItem(Assessment $assessment): ScreeningRequestItem
    {
        $user    = User::factory()->create();
        $patient = Patient::create([
            'user_id'    => $user->id,
            'first_name' => 'Test',
            'last_name'  => 'Paciente',
        ]);

        $request = ScreeningRequest::create([
            'user_id'         => $user->id,
            'patient_id'      => $patient->id,
            'status'          => 'sent',
            'recipient_email' => 'test@example.com',
        ]);

        return ScreeningRequestItem::create([
            'screening_request_id' => $request->id,
            'assessment_id'        => $assessment->id,
            'order'                => 0,
            'status'               => 'pending',
        ]);
    }

    // ----------------------------------------------------------------
    // Tests
    // ----------------------------------------------------------------

    #[Test]
    public function it_calculates_minimal_score_for_all_zero_answers(): void
    {
        $assessment = $this->makePhq9();
        $item       = $this->makeItem($assessment);

        // Construir respuestas: primera opción (score=0) de cada pregunta
        $answers = [];
        foreach ($assessment->questions as $question) {
            $option = $question->options->firstWhere('score_value', 0);
            $answers[$question->id] = $option->id;
        }

        $response = $this->service->score($item, $answers);

        $this->assertEquals(0, $response->total_score);
        $this->assertEquals('Mínima', $response->severity_label);
        $this->assertCount(9, $response->answers);
    }

    #[Test]
    public function it_calculates_severe_score_for_all_max_answers(): void
    {
        $assessment = $this->makePhq9();
        $item       = $this->makeItem($assessment);

        $answers = [];
        foreach ($assessment->questions as $question) {
            $option = $question->options->sortByDesc('score_value')->first();
            $answers[$question->id] = $option->id;
        }

        $response = $this->service->score($item, $answers);

        $this->assertEquals(27, $response->total_score);
        $this->assertEquals('Severa', $response->severity_label);
    }

    #[Test]
    public function it_throws_exception_when_required_questions_missing(): void
    {
        $assessment = $this->makePhq9();
        $item       = $this->makeItem($assessment);

        $this->expectException(ScoringException::class);

        // Solo respondemos 5 de 9 preguntas
        $answers = [];
        $count   = 0;
        foreach ($assessment->questions as $question) {
            if ($count >= 5) {
                break;
            }
            $answers[$question->id] = $question->options->first()->id;
            $count++;
        }

        $this->service->score($item, $answers);
    }

    #[Test]
    public function it_marks_item_as_completed_after_scoring(): void
    {
        $assessment = $this->makePhq9();
        $item       = $this->makeItem($assessment);

        $answers = [];
        foreach ($assessment->questions as $question) {
            $answers[$question->id] = $question->options->first()->id;
        }

        $this->service->score($item, $answers);

        $this->assertEquals('completed', $item->fresh()->status);
        $this->assertNotNull($item->fresh()->completed_at);
    }

    #[Test]
    public function it_saves_snapshots_for_historical_integrity(): void
    {
        $assessment = $this->makePhq9();
        $item       = $this->makeItem($assessment);

        $answers = [];
        foreach ($assessment->questions as $question) {
            $option = $question->options->first();
            $answers[$question->id] = $option->id;
        }

        $response = $this->service->score($item, $answers);

        $firstAnswer = $response->answers->first();
        $this->assertNotNull($firstAnswer->score_value_snapshot);
        $this->assertNotNull($firstAnswer->option_text_snapshot);
    }

    #[Test]
    public function it_throws_exception_when_no_matching_rule_exists(): void
    {
        // Crear prueba SIN reglas de interpretación
        $assessment = Assessment::create([
            'slug'        => 'no-rules',
            'name'        => 'Prueba sin reglas',
            'is_active'   => true,
            'credits_cost' => 1,
        ]);

        $question = AssessmentQuestion::create([
            'assessment_id' => $assessment->id,
            'order'         => 1,
            'question_text' => 'Pregunta única',
            'is_required'   => true,
        ]);
        $option = AssessmentOption::create([
            'assessment_question_id' => $question->id,
            'order'                  => 0,
            'option_text'            => 'Opción A',
            'score_value'            => 5,
        ]);

        $item    = $this->makeItem($assessment);
        $answers = [$question->id => $option->id];

        $this->expectException(ScoringException::class);
        $this->expectExceptionMessageMatches('/regla de interpretación/');

        $this->service->score($item, $answers);
    }
}
