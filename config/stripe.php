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
    | Cada paquete define su price_id de Stripe (crear en el Dashboard de Stripe)
    | y los créditos que otorga al comprar.
    |
    | Para crear un price en Stripe: Dashboard → Products → Add Product
    | Usa precios únicos (one_time), no recurrentes.
    */
    'credit_packages' => [
        'credits_10' => [
            'label'       => '10 créditos',
            'credits'     => 10,
            'price_id'    => env('STRIPE_PRICE_CREDITS_10', 'price_xxxxxx'),
            'amount_mxn'  => 199, // precio referencial en pesos MXN
            'description' => 'Ideal para empezar',
            'popular'     => false,
        ],
        'credits_30' => [
            'label'       => '30 créditos',
            'credits'     => 30,
            'price_id'    => env('STRIPE_PRICE_CREDITS_30', 'price_xxxxxx'),
            'amount_mxn'  => 499,
            'description' => 'Ahorra 16% vs paquete básico',
            'popular'     => true,
        ],
        'credits_100' => [
            'label'       => '100 créditos',
            'credits'     => 100,
            'price_id'    => env('STRIPE_PRICE_CREDITS_100', 'price_xxxxxx'),
            'amount_mxn'  => 1499,
            'description' => 'Mejor precio por crédito',
            'popular'     => false,
        ],
    ],
];
