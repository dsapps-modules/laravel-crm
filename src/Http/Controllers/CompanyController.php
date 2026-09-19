<?php

namespace DsApps\LaravelCrm\Http\Controllers;

use DsApps\LaravelCrm\Http\Requests\CompanyRequest;
use DsApps\LaravelCrm\Http\Resources\CompanyResource;
use DsApps\LaravelCrm\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CompanyController extends Controller
{
    public function index(Request $request): mixed
    {
        $query = Company::query()->whereNull('archived_at');
        if ($search = $request->string('search')->trim()->toString()) $query->where('name', 'like', "%{$search}%");
        return CompanyResource::collection($query->latest('id')->paginate(min($request->integer('per_page', 25), 100)));
    }

    public function store(CompanyRequest $request): CompanyResource { return new CompanyResource(Company::create($request->validated())); }
    public function show(Company $company): CompanyResource { return new CompanyResource($company); }
    public function update(CompanyRequest $request, Company $company): CompanyResource
    {
        $company->update($request->validated());
        return new CompanyResource($company->refresh());
    }
    public function destroy(Company $company): JsonResponse
    {
        $company->update(['archived_at' => now()]);
        return response()->json(null, 204);
    }
}
