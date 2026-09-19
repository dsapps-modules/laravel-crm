<?php

namespace DsApps\LaravelCrm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PipelineRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            'active' => ['sometimes', 'boolean'],
            'stages' => ['required', 'array', 'min:1'],
            'stages.*.name' => ['required', 'string', 'max:160'],
            'stages.*.position' => ['required', 'integer', 'min:0'],
        ];
    }
}
