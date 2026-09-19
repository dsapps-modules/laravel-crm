<?php

namespace DsApps\LaravelCrm\Http\Controllers;

use DsApps\LaravelCrm\Http\Requests\ContactRequest;
use DsApps\LaravelCrm\Http\Resources\ContactResource;
use DsApps\LaravelCrm\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ContactController extends Controller
{
    public function index(Request $request): mixed
    {
        $query = Contact::query()->whereNull('archived_at');
        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(fn ($q) => $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"));
        }
        return ContactResource::collection($query->latest('id')->paginate(min($request->integer('per_page', 25), 100)));
    }

    public function store(ContactRequest $request): ContactResource
    {
        return new ContactResource(Contact::create($request->validated()));
    }

    public function show(Contact $contact): ContactResource { return new ContactResource($contact); }

    public function update(ContactRequest $request, Contact $contact): ContactResource
    {
        $contact->update($request->validated());
        return new ContactResource($contact->refresh());
    }

    public function destroy(Contact $contact): JsonResponse
    {
        $contact->update(['archived_at' => now()]);
        return response()->json(null, 204);
    }
}
