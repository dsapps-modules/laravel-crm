<?php

namespace DsApps\LaravelCrm\Http\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;

class CalendarEventRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'contact_id' => ['nullable', 'integer', 'exists:'.config('crm.table_prefix').'contacts,id'],
            'opportunity_id' => ['nullable', 'integer', 'exists:'.config('crm.table_prefix').'opportunities,id'],
            'owner_id' => ['nullable', 'integer'], 'title' => ['required', 'string', 'max:180'],
            'notes' => ['nullable', 'string'], 'start_at' => ['required', 'date'], 'end_at' => ['required', 'date', 'after:start_at'],
            'all_day' => ['sometimes', 'boolean'], 'timezone' => ['sometimes', 'timezone'], 'status' => ['sometimes', 'in:scheduled,cancelled'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $timezone = $this->input('timezone', 'UTC');
        $data = ['timezone' => $timezone];
        foreach (['start_at', 'end_at'] as $field) {
            if ($this->filled($field)) $data[$field] = CarbonImmutable::parse($this->input($field), $timezone)->utc()->toDateTimeString();
        }
        $this->merge($data);
    }
}
