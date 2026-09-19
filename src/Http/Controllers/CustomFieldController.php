<?php

namespace DsApps\LaravelCrm\Http\Controllers;

use DsApps\LaravelCrm\Models\CustomField;
use DsApps\LaravelCrm\Services\SetCustomFieldValue;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CustomFieldController extends Controller
{
    public function index(Request $request): mixed { return CustomField::where('entity_type', $request->string('entity_type')->toString())->where('active', true)->orderBy('label')->paginate(100); }
    public function store(Request $request): CustomField
    {
        return CustomField::create($request->validate(['entity_type' => ['required', 'in:contact,company,opportunity,task'], 'key' => ['required', 'alpha_dash', 'max:80'], 'label' => ['required', 'string', 'max:160'], 'type' => ['required', 'in:text,number,date,boolean,select'], 'options' => ['nullable', 'array'], 'required' => ['sometimes', 'boolean']]));
    }
    public function setValue(Request $request, CustomField $customField, SetCustomFieldValue $set): mixed
    {
        $data = $request->validate(['entity_type' => ['required', 'in:contact,company,opportunity,task'], 'entity_id' => ['required', 'integer', 'min:1'], 'value' => ['nullable']]);
        return $set->execute($customField, $data['entity_type'], $data['entity_id'], $data['value'] ?? null);
    }
}
