<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        // Whitelist of allowed price IDs for plans list
        'allowed_price_ids' => array_filter([
            env('NEXT_PUBLIC_PRICE_ID_CASUAL_MONTHLY_EUR'),
            env('NEXT_PUBLIC_PRICE_ID_CASUAL_YEARLY_EUR'),
            env('NEXT_PUBLIC_PRICE_ID_PRO_MONTHLY_EUR'),
            env('NEXT_PUBLIC_PRICE_ID_PRO_YEARLY_EUR'),
        ]),
        // Explicit tier mapping for robust detection
        'price_tiers' => [
            'casual' => array_filter([
                env('NEXT_PUBLIC_PRICE_ID_CASUAL_MONTHLY_EUR'),
                env('NEXT_PUBLIC_PRICE_ID_CASUAL_YEARLY_EUR'),
            ]),
            'pro' => array_filter([
                env('NEXT_PUBLIC_PRICE_ID_PRO_MONTHLY_EUR'),
                env('NEXT_PUBLIC_PRICE_ID_PRO_YEARLY_EUR'),
            ]),
        ],
        // Optional CSV to extend the whitelist without code changes
        'allowed_price_ids_csv' => env('STRIPE_ALLOWED_PRICE_IDS'),
        // Optional portal configuration id (pcfg_...)
        'portal_configuration' => env('STRIPE_PORTAL_CONFIGURATION'),
    ],

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
    ],

];
