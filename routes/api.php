<?php

use DsApps\LaravelCrm\Http\Controllers\CompanyController;
use DsApps\LaravelCrm\Http\Controllers\ContactController;
use DsApps\LaravelCrm\Http\Controllers\OpportunityController;
use DsApps\LaravelCrm\Http\Controllers\PipelineController;
use DsApps\LaravelCrm\Http\Controllers\TaskController;
use DsApps\LaravelCrm\Http\Controllers\CalendarEventController;
use DsApps\LaravelCrm\Http\Controllers\TeamController;
use DsApps\LaravelCrm\Http\Controllers\TagController;
use DsApps\LaravelCrm\Http\Controllers\CustomFieldController;
use DsApps\LaravelCrm\Http\Controllers\SegmentController;
use DsApps\LaravelCrm\Http\Controllers\ConversationController;
use DsApps\LaravelCrm\Http\Controllers\MessageController;
use DsApps\LaravelCrm\Http\Controllers\AutomationController;
use DsApps\LaravelCrm\Http\Controllers\ReportController;
use DsApps\LaravelCrm\Http\Controllers\WhatsAppWebhookController;
use DsApps\LaravelCrm\Http\Controllers\BrevoWebhookController;
use DsApps\LaravelCrm\Http\Controllers\BrevoInboundWebhookController;
use DsApps\LaravelCrm\Http\Controllers\EmailCampaignController;
use DsApps\LaravelCrm\Http\Controllers\BrevoMarketingWebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix(config('crm.api.prefix', 'api/crm/v1'))
    ->middleware(config('crm.api.middleware', ['api', 'auth']))
    ->group(function (): void {
        Route::apiResource('contacts', ContactController::class)->middleware('crm.authorize:crm.contacts.manage');
        Route::apiResource('companies', CompanyController::class)->middleware('crm.authorize:crm.companies.manage');
        Route::apiResource('pipelines', PipelineController::class)->only(['index', 'store', 'show'])->middleware('crm.authorize:crm.pipelines.manage');
        Route::apiResource('opportunities', OpportunityController::class)->only(['index', 'store', 'show', 'update'])->middleware('crm.authorize:crm.opportunities.manage');
        Route::post('opportunities/{opportunity}/move/{stage}', [OpportunityController::class, 'move'])->middleware('crm.authorize:crm.opportunities.manage');
        Route::apiResource('tasks', TaskController::class)->middleware('crm.authorize:crm.tasks.manage');
        Route::post('tasks/{task}/complete', [TaskController::class, 'complete'])->middleware('crm.authorize:crm.tasks.manage');
        Route::apiResource('calendar-events', CalendarEventController::class)->middleware('crm.authorize:crm.calendar.manage');
        Route::apiResource('teams', TeamController::class)->only(['index', 'store', 'show'])->middleware('crm.authorize:crm.teams.manage');
        Route::post('teams/{team}/members', [TeamController::class, 'assign'])->middleware('crm.authorize:crm.teams.manage');
        Route::apiResource('tags', TagController::class)->only(['index', 'store'])->middleware('crm.authorize:crm.tags.manage');
        Route::post('tags/{tag}/attach', [TagController::class, 'attach'])->middleware('crm.authorize:crm.tags.manage');
        Route::apiResource('custom-fields', CustomFieldController::class)->only(['index', 'store'])->middleware('crm.authorize:crm.fields.manage');
        Route::post('custom-fields/{customField}/value', [CustomFieldController::class, 'setValue'])->middleware('crm.authorize:crm.fields.manage');
        Route::apiResource('segments', SegmentController::class)->only(['index', 'store', 'show'])->middleware('crm.authorize:crm.segments.manage');
        Route::apiResource('conversations', ConversationController::class)->only(['index', 'store', 'show'])->middleware('crm.authorize:crm.inbox.manage');
        Route::post('conversations/{conversation}/messages', [MessageController::class, 'store'])->middleware('crm.authorize:crm.inbox.manage');
        Route::apiResource('automations', AutomationController::class)->only(['index', 'store', 'show'])->middleware('crm.authorize:crm.automations.manage');
        Route::get('reports/summary', [ReportController::class, 'summary'])->middleware('crm.authorize:crm.reports.view');
        Route::apiResource('email-campaigns', EmailCampaignController::class)->only(['index', 'store', 'show'])->middleware('crm.authorize:crm.email_marketing.manage');
        Route::post('email-campaigns/{emailCampaign}/send', [EmailCampaignController::class, 'send'])->middleware('crm.authorize:crm.email_marketing.manage');
        Route::get('email-campaigns/{emailCampaign}/report', [EmailCampaignController::class, 'report'])->middleware('crm.authorize:crm.email_marketing.manage');
    });

Route::prefix(config('crm.api.prefix', 'api/crm/v1'))->post('webhooks/whatsapp/{provider}', WhatsAppWebhookController::class)->middleware('throttle:whatsapp-webhooks');
Route::prefix(config('crm.api.prefix', 'api/crm/v1'))->post('webhooks/brevo', BrevoWebhookController::class)->middleware('throttle:brevo-webhooks');
Route::prefix(config('crm.api.prefix', 'api/crm/v1'))->post('webhooks/brevo/transactional', BrevoWebhookController::class)->middleware('throttle:brevo-webhooks');
Route::prefix(config('crm.api.prefix', 'api/crm/v1'))->post('webhooks/brevo/inbound', BrevoInboundWebhookController::class)->middleware('throttle:brevo-webhooks');
Route::prefix(config('crm.api.prefix', 'api/crm/v1'))->post('webhooks/brevo/marketing', BrevoMarketingWebhookController::class)->middleware('throttle:brevo-webhooks');
