<?php

namespace DsApps\LaravelCrm\Http\Controllers;

use DsApps\LaravelCrm\Http\Requests\PipelineRequest;
use DsApps\LaravelCrm\Http\Resources\PipelineResource;
use DsApps\LaravelCrm\Models\Pipeline;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class PipelineController extends Controller
{
    public function index(): mixed { return PipelineResource::collection(Pipeline::with('stages')->where('active', true)->orderBy('name')->paginate(100)); }

    public function store(PipelineRequest $request): PipelineResource
    {
        $pipeline = DB::transaction(function () use ($request): Pipeline {
            $pipeline = Pipeline::create($request->safe()->only(['name', 'active']));
            $pipeline->stages()->createMany($request->validated('stages'));
            return $pipeline->load('stages');
        });
        return new PipelineResource($pipeline);
    }

    public function show(Pipeline $pipeline): PipelineResource { return new PipelineResource($pipeline->load('stages')); }
}
