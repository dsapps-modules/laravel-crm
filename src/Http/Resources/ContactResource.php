<?php

namespace DsApps\LaravelCrm\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id, 'first_name' => $this->first_name, 'last_name' => $this->last_name,
            'email' => $this->email, 'phone' => $this->phone, 'company_id' => $this->company_id,
            'document_type' => $this->document_type, 'document' => $this->document,
            'postal_code' => $this->postal_code, 'street' => $this->street, 'number' => $this->number,
            'complement' => $this->complement, 'district' => $this->district, 'city' => $this->city,
            'state' => $this->state, 'country' => $this->country,
            'archived_at' => $this->archived_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(), 'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
