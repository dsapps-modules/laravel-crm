<?php

use DsApps\LaravelCrm\Http\Controllers\CompanyController;
use DsApps\LaravelCrm\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

Route::prefix(config('crm.api.prefix', 'api/crm/v1'))
    ->middleware(config('crm.api.middleware', ['api', 'auth']))
    ->group(function (): void {
        Route::apiResource('contacts', ContactController::class)->middleware('crm.authorize:crm.contacts.manage');
        Route::apiResource('companies', CompanyController::class)->middleware('crm.authorize:crm.companies.manage');
    });
