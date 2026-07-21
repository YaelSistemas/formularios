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
    
        $user = User::query()
            ->with(['roles', 'permissions'])
            ->where('email', $data['email'])
            ->first();
    
        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciales incorrectas.'],
            ]);
        }
    
        if (!(bool) $user->activo) {
            return response()->json([
                'message' => 'Tu usuario no tiene acceso en este momento. Comunícate con tu administrador o con el equipo de Sistemas.',
            ], 403);
        }
    
        // Crear nueva sesión
        $newToken = $user->createToken('pwa');
        $token = $newToken->plainTextToken;
        
        /*
         * Mantener únicamente las 2 sesiones más recientes.
         */
        $tokenIds = $user->tokens()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->pluck('id');
        
        $tokensToDelete = $tokenIds
            ->skip(2)
            ->values();
        
        if ($tokensToDelete->isNotEmpty()) {
            $user->tokens()
                ->whereIn('id', $tokensToDelete->all())
                ->delete();
        }
    
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
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }
    
        /*
         * Elimina únicamente la sesión actual.
         * Las otras sesiones del usuario permanecen activas.
         */
        $request->user()
            ->currentAccessToken()
            ?->delete();
    
        return response()->json([
            'ok' => true,
        ]);
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