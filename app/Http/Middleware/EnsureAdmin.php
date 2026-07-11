<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('admin') ?? $request->user();

        if (! $user instanceof Admin) {
            abort(401, 'Unauthenticated.');
        }

        return $next($request);
    }
}
