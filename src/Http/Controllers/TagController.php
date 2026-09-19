<?php

namespace DsApps\LaravelCrm\Http\Controllers;

use DsApps\LaravelCrm\Models\Tag;
use DsApps\LaravelCrm\Services\TagEntity;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TagController extends Controller
{
    public function index(): mixed { return Tag::orderBy('name')->paginate(100); }
    public function store(Request $request): Tag { return Tag::create($request->validate(['name' => ['required', 'string', 'max:80'], 'color' => ['nullable', 'string', 'max:20']])); }
    public function attach(Request $request, Tag $tag, TagEntity $attach): mixed
    {
        $data = $request->validate(['entity_type' => ['required', 'in:contact,company,opportunity,task'], 'entity_id' => ['required', 'integer', 'min:1']]);
        return $attach->attach($tag->id, $data['entity_type'], $data['entity_id']);
    }
}
