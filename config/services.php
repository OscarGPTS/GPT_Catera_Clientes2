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

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'auth0' => [
        'client_id' => env('AUTH0_CLIENT_ID'),
        'client_secret' => env('AUTH0_CLIENT_SECRET'),
        'domain' => env('AUTH0_DOMAIN'),
        'cookie_secret' => env('AUTH0_COOKIE_SECRET'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URL', env('APP_URL') . '/auth/google/callback'),
    ],

    'microsoft' => [
        'client_id' => env('MICROSOFT_CLIENT_ID'),
        'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
        'redirect' => env('APP_URL') . '/auth/microsoft/callback',
    ],

    'apple' => [
        'client_id' => env('APPLE_CLIENT_ID'),
        'client_secret' => env('APPLE_CLIENT_SECRET'),
        'redirect' => env('APP_URL') . '/auth/apple/callback',
    ],

    // API de Consultas a Datos (buscador inteligente del dashboard).
    // Servicio externo NL→SQL/REST de solo lectura. Ver docs/API_CONSULTAS.md del proyecto langchain.
    'consultas' => [
        'url'              => rtrim(env('CONSULTAS_API_URL', 'http://localhost:8000'), '/'),
        'origen'           => env('CONSULTAS_ORIGEN', 'cartera_db'),
        'timeout'          => (int) env('CONSULTAS_API_TIMEOUT', 120),
        // Cloudflare Access Service Token (opcional; vacío = no se envían headers).
        'cf_client_id'     => env('CONSULTAS_CF_CLIENT_ID'),
        'cf_client_secret' => env('CONSULTAS_CF_CLIENT_SECRET'),
    ],

];
