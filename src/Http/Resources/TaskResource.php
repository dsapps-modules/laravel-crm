<?php

namespace DsApps\LaravelCrm\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'contact_id' => $this->contact_id, 'opportunity_id' => $this->opportunity_id, 'assignee_id' => $this->assignee_id, 'title' => $this->title, 'notes' => $this->notes, 'priority' => $this->priority, 'status' => $this->status, 'due_at' => $this->due_at?->toISOString(), 'reminder_at' => $this->reminder_at?->toISOString(), 'completed_at' => $this->completed_at?->toISOString(), 'timezone' => $this->timezone];
    }
}
