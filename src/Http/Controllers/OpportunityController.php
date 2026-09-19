<?php

namespace DsApps\LaravelCrm\Http\Controllers;

use DsApps\LaravelCrm\Actions\MoveOpportunityStage;
use DsApps\LaravelCrm\Exceptions\InvalidPipelineStage;
use DsApps\LaravelCrm\Exceptions\OpportunityConflict;
use DsApps\LaravelCrm\Http\Requests\OpportunityRequest;
use DsApps\LaravelCrm\Http\Resources\OpportunityResource;
use DsApps\LaravelCrm\Models\Opportunity;
use DsApps\LaravelCrm\Models\PipelineStage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OpportunityController extends Controller
{
    public function index(Request $request): mixed
    {
        $query = Opportunity::query()->where('status', $request->string('status')->toString() ?: 'open');
        if ($pipeline = $request->integer('pipeline_id')) $query->where('pipeline_id', $pipeline);
        return OpportunityResource::collection($query->latest('id')->paginate(min($request->integer('per_page', 25), 100)));
    }

    public function store(OpportunityRequest $request): OpportunityResource
    {
        $data = $request->validated();
        $this->assertStageBelongsToPipeline((int) $data['pipeline_id'], (int) $data['pipeline_stage_id']);
        $this->assertLossReason($data);
        $opportunity = DB::transaction(function () use ($data, $request): Opportunity {
            $opportunity = Opportunity::create($data);
            $opportunity->stageHistory()->create(['to_stage_id' => $opportunity->pipeline_stage_id, 'from_version' => 0, 'to_version' => 1, 'actor_type' => $request->user()?->getMorphClass(), 'actor_id' => $request->user()?->getAuthIdentifier()]);
            return $opportunity;
        });
        return new OpportunityResource($opportunity);
    }

    public function show(Opportunity $opportunity): OpportunityResource { return new OpportunityResource($opportunity); }

    public function update(OpportunityRequest $request, Opportunity $opportunity): OpportunityResource
    {
        $data = $request->validated();
        $this->assertStageBelongsToPipeline((int) ($data['pipeline_id'] ?? $opportunity->pipeline_id), (int) ($data['pipeline_stage_id'] ?? $opportunity->pipeline_stage_id));
        $this->assertLossReason(array_merge($opportunity->toArray(), $data));
        $version = (int) ($data['version'] ?? $opportunity->version);
        unset($data['version']);
        $updated = Opportunity::query()->whereKey($opportunity->getKey())->where('version', $version)->update([...$data, 'version' => $version + 1]);
        if ($updated !== 1) return response()->json(['message' => 'A oportunidade foi alterada.'], 409);
        return new OpportunityResource($opportunity->refresh());
    }

    public function move(Request $request, Opportunity $opportunity, PipelineStage $stage, MoveOpportunityStage $move): OpportunityResource|JsonResponse
    {
        try {
            return new OpportunityResource($move->execute($opportunity, $stage, $request->integer('version'), $request->user()));
        } catch (InvalidPipelineStage $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        } catch (OpportunityConflict $exception) {
            return response()->json(['message' => $exception->getMessage()], 409);
        }
    }

    private function assertStageBelongsToPipeline(int $pipelineId, int $stageId): void
    {
        abort_unless(PipelineStage::query()->whereKey($stageId)->where('pipeline_id', $pipelineId)->exists(), 422, 'A etapa não pertence ao funil informado.');
    }

    private function assertLossReason(array $data): void
    {
        if (($data['status'] ?? 'open') === 'lost' && blank($data['loss_reason'] ?? null)) {
            throw ValidationException::withMessages(['loss_reason' => 'Informe o motivo da perda.']);
        }
    }
}
