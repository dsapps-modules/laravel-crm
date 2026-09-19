<?php

namespace DsApps\LaravelCrm\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OpportunityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id, 'pipeline_id' => $this->pipeline_id, 'pipeline_stage_id' => $this->pipeline_stage_id,
            'contact_id' => $this->contact_id, 'company_id' => $this->company_id, 'title' => $this->title,
            'amount' => $this->amount, 'currency' => $this->currency, 'expected_close_at' => $this->expected_close_at?->toDateString(),
            'status' => $this->status, 'loss_reason' => $this->loss_reason, 'version' => $this->version,
            'created_at' => $this->created_at?->toISOString(), 'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
