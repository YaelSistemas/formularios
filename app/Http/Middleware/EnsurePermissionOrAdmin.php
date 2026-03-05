<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermissionOrAdmin
{
    /**
     * Uso:
     *  - ->middleware('perm:formularios.view')
     *  - ->middleware('perm:formularios.create|formularios.edit')   // OR
     */
    public function handle(Request $request, Closure $next, string $permission = ''): Response
    {
        $user = $request->user();

        // 1) No autenticado
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // 2) Admin = acceso total
        if (method_exists($user, 'hasRole') && $user->hasRole('Administrador')) {
            return $next($request);
        }

        // 3) Si no se pidió permiso, por defecto dejamos pasar (puedes cambiar a 403 si lo prefieres)
        $permission = trim((string) $permission);
        if ($permission === '') {
            return $next($request);
        }

        // 4) Permite OR: perm:a|b|c
        $permissions = array_values(array_filter(array_map('trim', explode('|', $permission))));

        // 5) Valida si tiene al menos 1 permiso
        foreach ($permissions as $perm) {
            // Spatie: can() o hasPermissionTo()
            if (method_exists($user, 'can') && $user->can($perm)) {
                return $next($request);
            }

            if (method_exists($user, 'hasPermissionTo')) {
                try {
                    if ($user->hasPermissionTo($perm)) {
                        return $next($request);
                    }
                } catch (\Throwable $e) {
                    // ignore (por si el permiso no existe o guard mismatch)
                }
            }
        }

        return response()->json(['message' => 'Forbidden'], 403);
    }
}