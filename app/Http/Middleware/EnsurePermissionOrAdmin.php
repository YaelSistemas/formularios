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
     *  - ->middleware('perm:formularios.create|formularios.edit') // OR
     */
    public function handle(Request $request, Closure $next, string $permission = ''): Response
    {
        $user = $request->user();

        // 1) No autenticado
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // 2) Administrador = acceso total
        if ($this->isAdmin($user)) {
            return $next($request);
        }

        // 3) Si no se pidió permiso, deja pasar
        $permission = trim((string) $permission);
        if ($permission === '') {
            return $next($request);
        }

        // 4) Permite OR: perm:a|b|c
        $permissions = array_values(
            array_filter(array_map('trim', explode('|', $permission)))
        );

        // 5) Valida si tiene al menos 1 permiso
        foreach ($permissions as $perm) {
            if (method_exists($user, 'can') && $user->can($perm)) {
                return $next($request);
            }

            if (method_exists($user, 'hasPermissionTo')) {
                try {
                    if ($user->hasPermissionTo($perm)) {
                        return $next($request);
                    }
                } catch (\Throwable $e) {
                    // ignore
                }
            }
        }

        return response()->json(['message' => 'Forbidden'], 403);
    }

    protected function isAdmin($user): bool
    {
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasRole')) {
            try {
                if ($user->hasRole('Administrador')) {
                    return true;
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        if (isset($user->is_admin) && (bool) $user->is_admin === true) {
            return true;
        }

        if (isset($user->role)) {
            $role = is_string($user->role) ? $user->role : ($user->role->name ?? null);
            if ($role && mb_strtolower($role) === 'administrador') {
                return true;
            }
        }

        if (isset($user->roles)) {
            foreach ($user->roles as $role) {
                $roleName = is_string($role) ? $role : ($role->name ?? null);
                if ($roleName && mb_strtolower($roleName) === 'administrador') {
                    return true;
                }
            }
        }

        return false;
    }
}