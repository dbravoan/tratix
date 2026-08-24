<?php

return [
    /*
    | Gateway de cobro. En desarrollo o sin credenciales use "demo" (marca la
    | suscripción directamente). En producción: "stripe".
    */
    'gateway' => env('BILLING_GATEWAY', 'demo'),

    'currency' => env('BILLING_CURRENCY', 'eur'),

    'plans' => [
        'free' => [
            'name' => 'Gratis',
            'monthly_contracts' => 2,
            'price_monthly' => 0,
            'white_label' => false,
            'stripe_price_id' => null,
            'features' => [
                '2 contratos al mes',
                'PDF con marca Tratix',
                '1 plantilla',
                'Enlace de firma 7 días',
            ],
        ],
        'pro' => [
            'name' => 'Pro',
            'monthly_contracts' => null, // ilimitados
            'price_monthly' => 9.0,
            'white_label' => false,
            'stripe_price_id' => env('STRIPE_PRO_PRICE_ID'),
            'features' => [
                'Contratos ilimitados',
                'Todas las plantillas',
                'Exportación de contratos',
                'Enlace de firma 30 días',
            ],
        ],
        'business' => [
            'name' => 'Business',
            'monthly_contracts' => null, // ilimitados
            'price_monthly' => 19.0,
            'white_label' => true, // marca blanca en PDF
            'stripe_price_id' => env('STRIPE_BUSINESS_PRICE_ID'),
            'features' => [
                'Todo lo de Pro',
                'Marca blanca en el PDF',
                'Soporte prioritario',
                'API / exportación avanzada',
            ],
        ],
    ],

    // Referidos: ambas partes ganan (mes gratis/descuento y créditos).
    'referral' => [
        'enabled' => env('REFERRAL_ENABLED', true),
        'referrer_reward_months' => 1,       // 1 mes de Pro gratis al referidor
        'referred_reward_months' => 1,       // 1 mes de Pro gratis al nuevo usuario
        'referrer_credits' => 1,             // créditos sueltos (sellos/verificaciones)
        'referred_credits' => 1,
    ],

    // Créditos sueltos: sellar/descargar sin marca de un contrato extra.
    'credits' => [
        'enabled' => env('CREDITS_ENABLED', true),
        'price_per_credit' => env('CREDIT_PRICE_EUR', 2.0),
        'stripe_price_id' => env('STRIPE_CREDIT_PRICE_ID'),
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],
];
