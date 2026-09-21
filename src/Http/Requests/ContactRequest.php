<?php

namespace DsApps\LaravelCrm\Http\Requests;

use DsApps\LaravelCrm\Support\BrazilianDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContactRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'company_id' => ['nullable', 'integer', 'exists:'.config('crm.table_prefix').'companies,id'],
            'document_type' => ['nullable', 'in:cpf,cnpj'],
            'document' => ['nullable', 'string', 'max:18', Rule::unique(config('crm.table_prefix').'contacts', 'document')->where(fn ($query) => $query->where('document_type', $this->input('document_type')))->ignore($this->route('contact')?->id)],
            'postal_code' => ['nullable', 'digits:8'],
            'street' => ['nullable', 'string', 'max:180'], 'number' => ['nullable', 'string', 'max:30'],
            'complement' => ['nullable', 'string', 'max:120'], 'district' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'], 'state' => ['nullable', 'string', 'size:2'],
            'country' => ['sometimes', 'string', 'size:2'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];
        if ($this->has('document')) $data['document'] = BrazilianDocument::digits($this->input('document'));
        if ($this->has('postal_code')) $data['postal_code'] = BrazilianDocument::digits($this->input('postal_code'));
        if ($this->has('document_type')) $data['document_type'] = strtolower((string) $this->input('document_type'));
        if ($this->has('state')) $data['state'] = strtoupper((string) $this->input('state'));
        if ($this->has('country')) $data['country'] = strtoupper((string) $this->input('country'));
        $this->merge($data);
    }

    public function after(): array
    {
        return [function ($validator): void {
            if ($this->filled('document') && ! $this->filled('document_type')) $validator->errors()->add('document_type', 'Informe se o documento é CPF ou CNPJ.');
            if ($this->filled('document_type') && ! $this->filled('document')) $validator->errors()->add('document', 'Informe o número do documento.');
            if ($this->filled('document') && $this->filled('document_type') && ! BrazilianDocument::valid($this->string('document_type')->toString(), $this->input('document'))) $validator->errors()->add('document', 'CPF ou CNPJ inválido.');
        }];
    }
}
