<?php

namespace App\Services;

use App\Exceptions\ScoringException;
use App\Models\Assessment;
use App\Models\AssessmentResponse;
use App\Models\AssessmentResponseAnswer;
use App\Models\ScreeningRequestItem;
use Illuminate\Support\Facades\DB;

/**
 * Calcula el puntaje e interpretación de una prueba de tamizaje.
 *
 * Compatible con múltiples escalas (PHQ-9, GAD-7, etc.) siempre que
 * tengan sus assessment_questions, assessment_options y assessment_rules
 * correctamente configuradas en la base de datos.
 *
 * Uso:
 *   $result = app(ScoringService::class)->score($item, $answers);
 *   // $result es un AssessmentResponse persistido
 */
class ScoringService
{
    /**
     * Calcula y persiste el resultado de una prueba.
     *
     * @param  ScreeningRequestItem  $item    Ítem de la solicitud (prueba específica)
     * @param  array<int,int>        $answers Mapa [question_id => option_id]
     *
     * @throws ScoringException
     */
    public function score(ScreeningRequestItem $item, array $answers): AssessmentResponse
    {
        $assessment = $item->assessment()->with(['questions.options', 'rules'])->firstOrFail();

        return DB::transaction(function () use ($item, $assessment, $answers) {
            $this->validateAllAnswered($assessment, $answers);

            $totalScore  = $this->calculateTotalScore($assessment, $answers);
            $rule        = $this->findRule($assessment, $totalScore);

            // Persistir la respuesta principal
            $response = AssessmentResponse::create([
                'screening_request_item_id'    => $item->id,
                'patient_id'                   => $item->screeningRequest->patient_id,
                'total_score'                  => $totalScore,
                'severity_label'               => $rule->severity_label,
                'interpretation_text'          => $rule->interpretation_text,
                'assessment_name_snapshot'     => $assessment->name,
                'assessment_version_snapshot'  => $assessment->version,
                'completed_at'                 => now(),
            ]);

            // Persistir cada respuesta individual con snapshot
            foreach ($assessment->questions as $question) {
                $optionId = $answers[$question->id];
                $option   = $question->options->firstWhere('id', $optionId);

                AssessmentResponseAnswer::create([
                    'assessment_response_id'  => $response->id,
                    'assessment_question_id'  => $question->id,
                    'assessment_option_id'    => $optionId,
                    'score_value_snapshot'    => $option->score_value,
                    'option_text_snapshot'    => $option->option_text,
                ]);
            }

            // Marcar el ítem como completado
            $item->update([
                'status'       => 'completed',
                'completed_at' => now(),
            ]);

            $this->updateRequestStatus($item);

            return $response->load('answers');
        });
    }

    // ----------------------------------------------------------------
    // Private helpers
    // ----------------------------------------------------------------

    /**
     * Verifica que todas las preguntas requeridas tengan respuesta.
     *
     * @throws ScoringException
     */
    private function validateAllAnswered(Assessment $assessment, array $answers): void
    {
        $questions = $assessment->questions;

        if ($questions->isEmpty()) {
            throw ScoringException::noQuestions($assessment->id);
        }

        $required = $questions->where('is_required', true)->pluck('id');
        $answered = collect(array_keys($answers));

        $missing = $required->diff($answered)->values()->all();

        if (! empty($missing)) {
            throw ScoringException::missingAnswers($missing);
        }
    }

    /**
     * Suma los score_value de cada opción seleccionada.
     */
    private function calculateTotalScore(Assessment $assessment, array $answers): int
    {
        $total = 0;

        foreach ($assessment->questions as $question) {
            if (! isset($answers[$question->id])) {
                continue; // pregunta no requerida sin responder
            }

            $optionId = $answers[$question->id];
            $option   = $question->options->firstWhere('id', $optionId);

            if ($option) {
                $total += $option->score_value;
            }
        }

        return $total;
    }

    /**
     * Busca la regla de interpretación para el puntaje dado.
     *
     * @throws ScoringException
     */
    private function findRule(Assessment $assessment, int $score): \App\Models\AssessmentRule
    {
        $rule = $assessment->rules
            ->first(fn ($r) => $score >= $r->min_score && $score <= $r->max_score);

        if (! $rule) {
            throw ScoringException::missingRule($assessment->id, $score);
        }

        return $rule;
    }

    /**
     * Actualiza el status de la solicitud padre según el progreso.
     */
    private function updateRequestStatus(ScreeningRequestItem $completedItem): void
    {
        $request = $completedItem->screeningRequest()->with('items')->first();
        $items   = $request->items;

        $allDone  = $items->every(fn ($i) => $i->status === 'completed');
        $someDone = $items->contains(fn ($i) => $i->status === 'completed');

        $request->update([
            'status'       => $allDone ? 'completed' : ($someDone ? 'partially_completed' : 'sent'),
            'completed_at' => $allDone ? now() : null,
        ]);
    }
}
