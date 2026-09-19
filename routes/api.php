<?php

use DsApps\LaravelCrm\Http\Controllers\CompanyController;
use DsApps\LaravelCrm\Http\Controllers\ContactController;
use DsApps\LaravelCrm\Http\Controllers\OpportunityController;
use DsApps\LaravelCrm\Http\Controllers\PipelineController;
use Illuminate\Support\Facades\Route;

Route::prefix(config('crm.api.prefix', 'api/crm/v1'))
    ->middleware(config('crm.api.middleware', ['api', 'auth']))
    ->group(function (): void {
        Route::apiResource('contacts', ContactController::class)->middleware('crm.authorize:crm.contacts.manage');
        Route::apiResource('companies', CompanyController::class)->middleware('crm.authorize:crm.companies.manage');
        Route::apiResource('pipelines', PipelineController::class)->only(['index', 'store', 'show'])->middleware('crm.authorize:crm.pipelines.manage');
        Route::apiResource('opportunities', OpportunityController::class)->only(['index', 'store', 'show', 'update'])->middleware('crm.authorize:crm.opportunities.manage');
        Route::post('opportunities/{opportunity}/move/{stage}', [OpportunityController::class, 'move'])->middleware('crm.authorize:crm.opportunities.manage');
    });
