<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // 1) No autenticado
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // 2) Autenticado pero no admin
        if (!method_exists($user, 'hasRole') || !$user->hasRole('Administrador')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}