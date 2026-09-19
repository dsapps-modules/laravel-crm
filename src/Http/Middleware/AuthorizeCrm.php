<?php

namespace DsApps\LaravelCrm\Http\Middleware;

use Closure;
use DsApps\LaravelCrm\Contracts\AuthorizationResolver;
use Illuminate\Http\Request;

class AuthorizeCrm
{
    public function __construct(private readonly AuthorizationResolver $resolver) {}

    public function handle(Request $request, Closure $next, string $ability): mixed
    {
        $user = $request->user();
        abort_unless($user && $this->resolver->can($user, $ability), 403);
        return $next($request);
    }
}
