<?php

return [
    'api' => [
        'prefix' => env('CRM_API_PREFIX', 'api/crm/v1'),
        'middleware' => ['api', 'auth'],
    ],
    'table_prefix' => env('CRM_TABLE_PREFIX', 'crm_'),
    'user_model' => env('CRM_USER_MODEL', 'App\\Models\\User'),
    'authorization_resolver' => DsApps\LaravelCrm\Contracts\AuthorizationResolver::class,
];
