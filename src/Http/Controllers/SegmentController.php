<?php

namespace DsApps\LaravelCrm\Http\Controllers;

use DsApps\LaravelCrm\Models\Segment;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SegmentController extends Controller
{
    public function index(Request $request): mixed { return Segment::where('entity_type', $request->string('entity_type')->toString())->latest()->paginate(100); }
    public function store(Request $request): Segment
    {
        $data = $request->validate(['entity_type' => ['required', 'in:contact,company,opportunity,task'], 'name' => ['required', 'string', 'max:160'], 'filters' => ['required', 'array']]);
        return Segment::create([...$data, 'owner_id' => $request->user()?->getAuthIdentifier()]);
    }
    public function show(Segment $segment): Segment { return $segment; }
}
