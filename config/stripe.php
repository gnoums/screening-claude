<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Credenciales de Stripe
    |--------------------------------------------------------------------------
    */
    'key'            => env('STRIPE_KEY'),
    'secret'         => env('STRIPE_SECRET'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Catálogo de paquetes de créditos
    |--------------------------------------------------------------------------
    | label       → clave de traducción (en) para el nombre del paquete
    | description → clave de traducción (en) para la descripción
    | amount_usd  → precio de referencia en USD (solo para mostrar en la vista)
    | price_id    → Price ID de Stripe (configurar en .env)
    */
    'credit_packages' => [
        'starter' => [
            'label'       => 'Starter',
            'credits'     => 5,
            'price_id'    => env('STRIPE_PRICE_STARTER', 'price_xxxxxx'),
            'amount_usd'  => 19.90,
            'description' => 'Ideal for getting started',
            'popular'     => false,
        ],
        'professional' => [
            'label'       => 'Professional',
            'credits'     => 15,
            'price_id'    => env('STRIPE_PRICE_PROFESSIONAL', 'price_xxxxxx'),
            'amount_usd'  => 49.90,
            'description' => 'Save 16% vs Starter',
            'popular'     => true,
        ],
        'clinic' => [
            'label'       => 'Clinic / Team',
            'credits'     => 35,
            'price_id'    => env('STRIPE_PRICE_CLINIC', 'price_xxxxxx'),
            'amount_usd'  => 99.90,
            'description' => 'Best price per assessment',
            'popular'     => false,
        ],
    ],
];
