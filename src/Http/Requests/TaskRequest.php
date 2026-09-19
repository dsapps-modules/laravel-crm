<?php

namespace DsApps\LaravelCrm\Http\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'contact_id' => ['nullable', 'integer', 'exists:'.config('crm.table_prefix').'contacts,id'],
            'opportunity_id' => ['nullable', 'integer', 'exists:'.config('crm.table_prefix').'opportunities,id'],
            'assignee_id' => ['nullable', 'integer'], 'title' => ['required', 'string', 'max:180'],
            'notes' => ['nullable', 'string'], 'priority' => ['sometimes', 'in:low,normal,high,urgent'],
            'status' => ['sometimes', 'in:pending,completed,cancelled'], 'due_at' => ['nullable', 'date'],
            'reminder_at' => ['nullable', 'date'], 'timezone' => ['sometimes', 'timezone'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $timezone = $this->input('timezone', 'UTC');
        $data = ['timezone' => $timezone];
        foreach (['due_at', 'reminder_at'] as $field) {
            if ($this->filled($field)) $data[$field] = CarbonImmutable::parse($this->input($field), $timezone)->utc()->toDateTimeString();
        }
        $this->merge($data);
    }
}
