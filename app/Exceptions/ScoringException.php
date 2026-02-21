<?php

namespace App\Exceptions;

use RuntimeException;

class ScoringException extends RuntimeException
{
    public static function missingAnswers(array $missingQuestionIds): self
    {
        $ids = implode(', ', $missingQuestionIds);

        return new self("Faltan respuestas para las preguntas: {$ids}");
    }

    public static function missingRule(int $assessmentId, int $score): self
    {
        return new self(
            "No existe regla de interpretación para la prueba #{$assessmentId} con puntaje {$score}. "
            . "Revisa la configuración de assessment_rules.",
        );
    }

    public static function noQuestions(int $assessmentId): self
    {
        return new self("La prueba #{$assessmentId} no tiene preguntas configuradas.");
    }
}
