<?php

namespace DsApps\LaravelCrm\Http\Controllers;

use DsApps\LaravelCrm\Models\Automation;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AutomationController extends Controller
{
    public function index(): mixed { return Automation::withCount('runs')->latest()->paginate(100); }
    public function store(Request $request): Automation
    {
        return Automation::create($request->validate(['name' => ['required', 'string', 'max:160'], 'trigger' => ['required', 'string', 'max:80'], 'conditions' => ['nullable', 'array'], 'actions' => ['required', 'array', 'min:1'], 'actions.*.type' => ['required', 'in:create_task,move_stage,notify_internal'], 'active' => ['sometimes', 'boolean'], 'max_retries' => ['sometimes', 'integer', 'min:0', 'max:10']]));
    }
    public function show(Automation $automation): Automation { return $automation->loadCount('runs'); }
}
