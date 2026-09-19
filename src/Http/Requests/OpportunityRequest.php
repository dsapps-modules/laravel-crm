<?php

namespace DsApps\LaravelCrm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OpportunityRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'pipeline_id' => ['required', 'integer', 'exists:'.config('crm.table_prefix').'pipelines,id'],
            'pipeline_stage_id' => ['required', 'integer', 'exists:'.config('crm.table_prefix').'pipeline_stages,id'],
            'contact_id' => ['required', 'integer', 'exists:'.config('crm.table_prefix').'contacts,id'],
            'company_id' => ['nullable', 'integer', 'exists:'.config('crm.table_prefix').'companies,id'],
            'title' => ['required', 'string', 'max:180'],
            'amount' => ['nullable', 'numeric', 'decimal:0,4', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3', 'uppercase'],
            'expected_close_at' => ['nullable', 'date'],
            'status' => ['sometimes', 'in:open,won,lost'],
            'loss_reason' => ['nullable', 'string', 'max:180'],
            'version' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('status') === 'lost' && blank($this->input('loss_reason'))) {
            $this->merge(['loss_reason' => null]);
        }
    }
}
