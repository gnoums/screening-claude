<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tiempo de vida del token de paciente (en horas)
    |--------------------------------------------------------------------------
    */
    'token_ttl_hours' => (int) env('SCREENING_TOKEN_TTL_HOURS', 72),

    /*
    |--------------------------------------------------------------------------
    | Rate limiting para el endpoint público de token
    |--------------------------------------------------------------------------
    | Peticiones máximas por IP por minuto en la ruta pública del paciente.
    */
    'public_rate_limit' => 30,

    /*
    |--------------------------------------------------------------------------
    | Disclaimer que aparece en todos los reportes PDF
    |--------------------------------------------------------------------------
    */
    'pdf_disclaimer' => 'Screening tool results are not a standalone diagnosis. '
        . 'This report is intended for use by qualified mental health professionals only. '
        . 'Results should be interpreted in the context of a comprehensive clinical evaluation.',
];
