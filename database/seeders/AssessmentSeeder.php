<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\AssessmentOption;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentRule;
use Illuminate\Database\Seeder;

/**
 * Siembra PHQ-9 y GAD-7 con preguntas, opciones y reglas de interpretación.
 */
class AssessmentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPhq9();
        $this->seedGad7();
    }

    // ----------------------------------------------------------------
    // PHQ-9
    // ----------------------------------------------------------------

    private function seedPhq9(): void
    {
        $assessment = Assessment::updateOrCreate(
            ['slug' => 'phq-9'],
            [
                'name'              => 'Patient Health Questionnaire-9',
                'short_name'        => 'PHQ-9',
                'description'       => 'Cuestionario de Salud del Paciente para detección de depresión.',
                'version'           => '2001',
                'author'            => 'Kroenke K, Spitzer RL, Williams JB',
                'is_active'         => true,
                'estimated_minutes' => 5,
                'credits_cost'      => 1,
            ],
        );

        $questions = [
            'Poco interés o placer al hacer cosas',
            'Sentirse desanimado/a, deprimido/a o sin esperanzas',
            'Problemas para dormir, para mantenerse dormido/a o dormir demasiado',
            'Sentirse cansado/a o con poca energía',
            'Poco apetito o comer demasiado',
            'Sentirse mal consigo mismo/a, sentirse un fracaso o que lo ha decepcionado a sí mismo/a o a su familia',
            'Problemas para concentrarse en cosas tales como leer el periódico o ver la televisión',
            'Moverse o hablar tan lentamente que otras personas lo podrían haber notado, o todo lo contrario — estar tan agitado/a que ha estado moviéndose mucho más de lo normal',
            'Pensamientos de que estaría mejor muerto/a o de que haría daño de alguna manera',
        ];

        $options = [
            ['Nunca', 0],
            ['Varios días', 1],
            ['Más de la mitad de los días', 2],
            ['Casi todos los días', 3],
        ];

        foreach ($questions as $i => $text) {
            $question = AssessmentQuestion::updateOrCreate(
                [
                    'assessment_id' => $assessment->id,
                    'order'         => $i + 1,
                ],
                [
                    'question_text' => $text,
                    'question_code' => 'PHQ' . ($i + 1),
                    'is_required'   => true,
                ],
            );

            foreach ($options as $j => [$label, $score]) {
                AssessmentOption::updateOrCreate(
                    [
                        'assessment_question_id' => $question->id,
                        'score_value'            => $score,
                    ],
                    [
                        'order'       => $j,
                        'option_text' => $label,
                    ],
                );
            }
        }

        $rules = [
            [0,  4,  'Mínima',                'Sin indicadores significativos de depresión. Monitoreo de rutina.', '#4CAF50'],
            [5,  9,  'Leve',                  'Síntomas depresivos leves. Considerar vigilancia clínica.', '#8BC34A'],
            [10, 14, 'Moderada',              'Síntomas moderados. Evaluar plan de tratamiento.', '#FFC107'],
            [15, 19, 'Moderadamente severa',  'Síntomas moderadamente severos. Considerar tratamiento farmacológico o psicoterapia activa.', '#FF9800'],
            [20, 27, 'Severa',               'Síntomas severos. Tratamiento inmediato e intensivo necesario.', '#F44336'],
        ];

        foreach ($rules as [$min, $max, $label, $text, $color]) {
            AssessmentRule::updateOrCreate(
                ['assessment_id' => $assessment->id, 'min_score' => $min],
                [
                    'max_score'           => $max,
                    'severity_label'      => $label,
                    'interpretation_text' => $text,
                    'color_code'          => $color,
                ],
            );
        }
    }

    // ----------------------------------------------------------------
    // GAD-7
    // ----------------------------------------------------------------

    private function seedGad7(): void
    {
        $assessment = Assessment::updateOrCreate(
            ['slug' => 'gad-7'],
            [
                'name'              => 'Generalized Anxiety Disorder-7',
                'short_name'        => 'GAD-7',
                'description'       => 'Escala de trastorno de ansiedad generalizada.',
                'version'           => '2006',
                'author'            => 'Spitzer RL, Kroenke K, Williams JB, Löwe B',
                'is_active'         => true,
                'estimated_minutes' => 4,
                'credits_cost'      => 1,
            ],
        );

        $questions = [
            'Sentirse nervioso/a, ansioso/a o con los nervios de punta',
            'No ser capaz de parar o de controlar la preocupación',
            'Preocuparse demasiado por diferentes cosas',
            'Dificultad para relajarse',
            'Estar tan intranquilo/a que es difícil permanecer sentado/a',
            'Molestarse o ponerse irritable con facilidad',
            'Sentir miedo, como si algo horrible fuera a ocurrir',
        ];

        $options = [
            ['Nunca', 0],
            ['Varios días', 1],
            ['Más de la mitad de los días', 2],
            ['Casi todos los días', 3],
        ];

        foreach ($questions as $i => $text) {
            $question = AssessmentQuestion::updateOrCreate(
                [
                    'assessment_id' => $assessment->id,
                    'order'         => $i + 1,
                ],
                [
                    'question_text' => $text,
                    'question_code' => 'GAD' . ($i + 1),
                    'is_required'   => true,
                ],
            );

            foreach ($options as $j => [$label, $score]) {
                AssessmentOption::updateOrCreate(
                    [
                        'assessment_question_id' => $question->id,
                        'score_value'            => $score,
                    ],
                    [
                        'order'       => $j,
                        'option_text' => $label,
                    ],
                );
            }
        }

        $rules = [
            [0,  4,  'Mínima',   'Ansiedad mínima o ausente.', '#4CAF50'],
            [5,  9,  'Leve',     'Ansiedad leve. Considerar seguimiento.', '#8BC34A'],
            [10, 14, 'Moderada', 'Ansiedad moderada. Evaluar intervención clínica.', '#FFC107'],
            [15, 21, 'Severa',   'Ansiedad severa. Tratamiento activo indicado.', '#F44336'],
        ];

        foreach ($rules as [$min, $max, $label, $text, $color]) {
            AssessmentRule::updateOrCreate(
                ['assessment_id' => $assessment->id, 'min_score' => $min],
                [
                    'max_score'           => $max,
                    'severity_label'      => $label,
                    'interpretation_text' => $text,
                    'color_code'          => $color,
                ],
            );
        }
    }
}
