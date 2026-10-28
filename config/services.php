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

    'haris' => [
        'url' => env('HARIS_API_URL'),
        'token' => env('HARIS_API_TOKEN'),
        'sync_at' => env('HARIS_SYNC_AT', '02:00'),
        'retry_times' => env('HARIS_RETRY_TIMES', 3),
        'retry_delay_ms' => env('HARIS_RETRY_DELAY_MS', 1000),
        'scope_plant_code' => env('HARIS_SCOPE_PLANT_CODE', 'PSG'),
        'scope_plant_id' => env('HARIS_SCOPE_PLANT_ID', '3'),
    ],

    's2s' => [
        'base_url' => env('S2S_API_URL'),
        'parts_token' => env('S2S_TO_POJOK_API_TOKEN'),
        'competency_token' => env('POJOK_TO_S2S_API_TOKEN'),
        'competency_sync_enabled' => env('S2S_COMPETENCY_SYNC_ENABLED', false),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
