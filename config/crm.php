<?php

return [
    'api' => [
        'prefix' => env('CRM_API_PREFIX', 'api/crm/v1'),
        'middleware' => ['api', 'auth'],
    ],
    'table_prefix' => env('CRM_TABLE_PREFIX', 'crm_'),
    'user_model' => env('CRM_USER_MODEL', 'App\\Models\\User'),
    'authorization_resolver' => DsApps\LaravelCrm\Contracts\AuthorizationResolver::class,
    'whatsapp' => [
        'meta' => [
            'base_url' => env('CRM_WHATSAPP_META_BASE_URL', 'https://graph.facebook.com'),
            'api_version' => env('CRM_WHATSAPP_META_API_VERSION'),
            'phone_number_id' => env('CRM_WHATSAPP_META_PHONE_NUMBER_ID'),
            'access_token' => env('CRM_WHATSAPP_META_ACCESS_TOKEN'),
            'app_secret' => env('CRM_WHATSAPP_META_APP_SECRET'),
        ],
        'uazapi' => [
            'base_url' => env('CRM_WHATSAPP_UAZAPI_BASE_URL', 'https://api.uzapi.com.br'),
            'token' => env('CRM_WHATSAPP_UAZAPI_TOKEN'),
            'webhook_token' => env('CRM_WHATSAPP_UAZAPI_WEBHOOK_TOKEN'),
        ],
        'http_timeout' => (int) env('CRM_WHATSAPP_HTTP_TIMEOUT', 15),
    ],
    'email' => [
        'brevo' => [
            'base_url' => env('CRM_EMAIL_BREVO_BASE_URL', 'https://api.brevo.com'),
            'api_key' => env('CRM_EMAIL_BREVO_API_KEY'),
            'sender_email' => env('CRM_EMAIL_BREVO_SENDER_EMAIL'),
            'sender_name' => env('CRM_EMAIL_BREVO_SENDER_NAME'),
            'app_tag' => env('CRM_EMAIL_BREVO_APP_TAG', 'laravel_crm'),
            'reply_domain' => env('CRM_EMAIL_BREVO_REPLY_DOMAIN'),
            'webhook_token' => env('CRM_EMAIL_BREVO_WEBHOOK_TOKEN'),
        ],
        'http_timeout' => (int) env('CRM_EMAIL_HTTP_TIMEOUT', 15),
    ],
];
