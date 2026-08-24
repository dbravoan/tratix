<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'tsa' => [
        'url' => env('TSA_URL', 'https://freetsa.org/tsr'),
        'timeout' => env('TSA_TIMEOUT', 6),
    ],

    'google' => [
        'analytics_id' => env('GOOGLE_ANALYTICS_ID', 'G-TRATIXLEGAL1'),
        'adsense_client' => env('GOOGLE_ADSENSE_CLIENT', 'ca-pub-1234567890123456'),
        'adsense_slot_sidebar' => env('GOOGLE_ADSENSE_SLOT_SIDEBAR', '1234567890'),
        'adsense_slot_banner' => env('GOOGLE_ADSENSE_SLOT_BANNER', '9876543210'),
    ],

];
