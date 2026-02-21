<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\AssessmentOption;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentRule;
use App\Models\Patient;
use App\Models\ScreeningRequest;
use App\Models\ScreeningRequestItem;
use App\Models\User;
use App\Services\TokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Pruebas de integración para las rutas públicas del paciente.
 */
class PublicScreeningTest extends TestCase
{
    use RefreshDatabase;

    private TokenService $tokenService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tokenService = app(TokenService::class);
    }

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------

    private function makeFullSetup(): array
    {
        // Assessment
        $assessment = Assessment::create([
            'slug'         => 'test-assessment',
            'name'         => 'Test Assessment',
            'is_active'    => true,
            'credits_cost' => 1,
        ]);

        $question = AssessmentQuestion::create([
            'assessment_id' => $assessment->id,
            'order'         => 1,
            'question_text' => '¿Cómo te has sentido?',
            'is_required'   => true,
        ]);

        $option = AssessmentOption::create([
            'assessment_question_id' => $question->id,
            'order'                  => 0,
            'option_text'            => 'Bien',
            'score_value'            => 1,
        ]);

        AssessmentRule::create([
            'assessment_id'       => $assessment->id,
            'min_score'           => 0,
            'max_score'           => 10,
            'severity_label'      => 'Mínima',
            'interpretation_text' => 'Sin problemas significativos.',
        ]);

        // Psicóloga y paciente
        $user    = User::factory()->create();
        $patient = Patient::create([
            'user_id'    => $user->id,
            'first_name' => 'Ana',
            'last_name'  => 'Gómez',
            'email'      => 'ana@test.com',
        ]);

        // Solicitud
        $request = ScreeningRequest::create([
            'user_id'         => $user->id,
            'patient_id'      => $patient->id,
            'status'          => 'sent',
            'recipient_email' => 'ana@test.com',
        ]);

        $item = ScreeningRequestItem::create([
            'screening_request_id' => $request->id,
            'assessment_id'        => $assessment->id,
            'order'                => 0,
            'status'               => 'pending',
        ]);

        ['plain' => $plain] = $this->tokenService->generate($request);

        return compact('assessment', 'question', 'option', 'request', 'item', 'plain');
    }

    // ----------------------------------------------------------------
    // Tests de show
    // ----------------------------------------------------------------

    #[Test]
    public function patient_can_view_screening_with_valid_token(): void
    {
        ['plain' => $plain] = $this->makeFullSetup();

        $response = $this->get(route('public.screening.show', ['token' => $plain]));

        $response->assertOk();
        $response->assertViewIs('public.screening.show');
        $response->assertSee('¿Cómo te has sentido?');
    }

    #[Test]
    public function invalid_token_shows_error_page(): void
    {
        $response = $this->get(route('public.screening.show', ['token' => 'invalid-token-xyz']));

        $response->assertOk();
        $response->assertViewIs('public.screening.invalid-token');
    }

    #[Test]
    public function expired_token_shows_error_page(): void
    {
        ['plain' => $plain, 'request' => $req] = $this->makeFullSetup();

        // Expirar el token (cargar relación explícitamente)
        $req->load('token')->token->update(['expires_at' => now()->subHour()]);

        $response = $this->get(route('public.screening.show', ['token' => $plain]));

        $response->assertOk();
        $response->assertViewIs('public.screening.invalid-token');
        $response->assertSee('expirado');
    }

    // ----------------------------------------------------------------
    // Tests de submit
    // ----------------------------------------------------------------

    #[Test]
    public function patient_can_submit_answers_and_complete_screening(): void
    {
        ['plain' => $plain, 'question' => $question, 'option' => $option] = $this->makeFullSetup();

        $response = $this->post(
            route('public.screening.submit', ['token' => $plain]),
            ['answers' => [$question->id => $option->id]],
        );

        $response->assertRedirect(route('public.screening.completed'));
    }

    #[Test]
    public function submitting_with_missing_answers_fails_validation(): void
    {
        ['plain' => $plain] = $this->makeFullSetup();

        $response = $this->post(
            route('public.screening.submit', ['token' => $plain]),
            ['answers' => []], // sin respuestas
        );

        $response->assertSessionHasErrors('answers');
    }

    #[Test]
    public function token_is_invalidated_after_completion(): void
    {
        ['plain' => $plain, 'question' => $question, 'option' => $option, 'request' => $req] = $this->makeFullSetup();

        $this->post(
            route('public.screening.submit', ['token' => $plain]),
            ['answers' => [$question->id => $option->id]],
        );

        $this->assertTrue($req->fresh()->token->is_invalidated);
    }

    #[Test]
    public function reusing_completed_token_shows_error_page(): void
    {
        ['plain' => $plain, 'question' => $question, 'option' => $option] = $this->makeFullSetup();

        // Completar
        $this->post(
            route('public.screening.submit', ['token' => $plain]),
            ['answers' => [$question->id => $option->id]],
        );

        // Intentar reusar
        $response = $this->get(route('public.screening.show', ['token' => $plain]));
        $response->assertViewIs('public.screening.invalid-token');
    }

    #[Test]
    public function completed_page_is_accessible_without_token(): void
    {
        $response = $this->get(route('public.screening.completed'));
        $response->assertOk();
        $response->assertViewIs('public.screening.completed');
    }
}
