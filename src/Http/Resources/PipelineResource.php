<?php

namespace DsApps\LaravelCrm\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PipelineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'name' => $this->name, 'active' => $this->active, 'stages' => PipelineStageResource::collection($this->whenLoaded('stages'))];
    }
}
