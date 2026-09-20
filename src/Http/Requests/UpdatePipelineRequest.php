<?php

namespace DsApps\LaravelCrm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePipelineRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:160'],
            'active' => ['sometimes', 'boolean'],
            'stages' => ['sometimes', 'array', 'min:1'],
            'stages.*.id' => ['sometimes', 'integer', 'distinct'],
            'stages.*.name' => ['required_with:stages', 'string', 'max:160', 'distinct'],
            'stages.*.position' => ['required_with:stages', 'integer', 'min:0', 'distinct'],
        ];
    }
}
