<?php

namespace DsApps\LaravelCrm\Support;

use DsApps\LaravelCrm\Contracts\AuthorizationResolver;
use Illuminate\Contracts\Auth\Authenticatable;

final class DefaultAuthorizationResolver implements AuthorizationResolver
{
    public function can(Authenticatable $user, string $ability, mixed $resource = null): bool
    {
        return method_exists($user, 'can') && $user->can($ability, $resource);
    }
}
