<?php

namespace DsApps\LaravelCrm\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface AuthorizationResolver
{
    public function can(Authenticatable $user, string $ability, mixed $resource = null): bool;
}
