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
        'meta' => ['base_url' => env('CRM_WHATSAPP_META_BASE_URL', 'https://graph.facebook.com'), 'api_version' => env('CRM_WHATSAPP_META_API_VERSION')],
        'uazapi' => ['base_url' => env('CRM_WHATSAPP_UAZAPI_BASE_URL', 'https://api.uzapi.com.br')],
        'http_timeout' => (int) env('CRM_WHATSAPP_HTTP_TIMEOUT', 15),
    ],
    'email' => [
        'brevo' => ['base_url' => env('CRM_EMAIL_BREVO_BASE_URL', 'https://api.brevo.com')],
        'http_timeout' => (int) env('CRM_EMAIL_HTTP_TIMEOUT', 15),
    ],
];
