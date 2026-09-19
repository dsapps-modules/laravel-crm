<?php

namespace DsApps\LaravelCrm\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CalendarEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'contact_id' => $this->contact_id, 'opportunity_id' => $this->opportunity_id, 'owner_id' => $this->owner_id, 'title' => $this->title, 'notes' => $this->notes, 'start_at' => $this->start_at?->toISOString(), 'end_at' => $this->end_at?->toISOString(), 'all_day' => $this->all_day, 'timezone' => $this->timezone, 'status' => $this->status];
    }
}
