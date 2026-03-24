<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Carga roles/permisos para responder consistente
        $user = User::query()
            ->with(['roles', 'permissions'])
            ->where('email', $data['email'])
            ->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciales incorrectas.'],
            ]);
        }

        // Validar si el usuario está inactivo
        if (!(bool) $user->activo) {
            return response()->json([
                'message' => 'Tu usuario no tiene acceso en este momento. Comunícate con tu administrador o con el equipo de Sistemas.',
            ], 403);
        }

        // 1 sesión activa por usuario
        $user->tokens()->delete();

        $token = $user->createToken('pwa')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $this->userPayload($user),
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Refresca relaciones por si cambiaron permisos/roles
        $user->loadMissing(['roles', 'permissions']);

        // Si el usuario fue desactivado después de iniciar sesión
        if (!(bool) $user->activo) {
            $user->tokens()->delete();

            return response()->json([
                'message' => 'Tu usuario no tiene acceso en este momento. Comunícate con tu administrador o con el equipo de Sistemas.',
            ], 403);
        }

        return response()->json([
            'user' => $this->userPayload($user),
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $user->tokens()->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Payload estándar para frontend (login y /me iguales).
     */
    private function userPayload(User $user): array
    {
        return [
            'id'          => $user->id,
            'name'        => $user->name,
            'email'       => $user->email,

            // estado del usuario
            'activo'      => (bool) $user->activo,
            'active'      => (bool) $user->activo,

            // arrays limpios
            'roles'       => $user->getRoleNames()->values()->all(),
            'permissions' => $user->getAllPermissions()->pluck('name')->values()->all(),

            'is_admin'    => $user->hasRole('Administrador'),
        ];
    }
}