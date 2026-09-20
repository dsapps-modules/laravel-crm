<?php

namespace DsApps\LaravelCrm\Http\Controllers;

use DsApps\LaravelCrm\Actions\DeletePipeline;
use DsApps\LaravelCrm\Actions\SyncPipelineStages;
use DsApps\LaravelCrm\Exceptions\PipelineInUse;
use DsApps\LaravelCrm\Http\Requests\PipelineRequest;
use DsApps\LaravelCrm\Http\Requests\UpdatePipelineRequest;
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

    public function update(UpdatePipelineRequest $request, Pipeline $pipeline, SyncPipelineStages $stages): PipelineResource
    {
        try {
            $pipeline = DB::transaction(function () use ($request, $pipeline, $stages): Pipeline {
                $data = $request->safe()->only(['name', 'active']);
                if ($data !== []) $pipeline->update($data);
                if ($request->has('stages')) $stages->execute($pipeline, $request->validated('stages'));
                return $pipeline->load('stages');
            });
        } catch (PipelineInUse|\InvalidArgumentException $exception) {
            abort(422, $exception->getMessage());
        }

        return new PipelineResource($pipeline);
    }

    public function destroy(Pipeline $pipeline, DeletePipeline $delete): mixed
    {
        try {
            DB::transaction(fn (): mixed => $delete->execute($pipeline));
        } catch (PipelineInUse $exception) {
            abort(422, $exception->getMessage());
        }

        return response()->json(['message' => 'Funil excluído com sucesso.']);
    }
}
