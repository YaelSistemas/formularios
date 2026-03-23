<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grupo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GruposController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $perPage = (int) $request->query('per_page', 25);
        $perPage = max(5, min(100, $perPage));

        $query = Grupo::query()->withCount('users');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nombre', 'like', "%{$q}%")
                    ->orWhere('nombre_mostrar', 'like', "%{$q}%")
                    ->orWhere('descripcion', 'like', "%{$q}%");
            });
        }

        $pag = $query->orderBy('id', 'desc')->paginate($perPage);

        return response()->json([
            'data' => $pag->items(),
            'current_page' => $pag->currentPage(),
            'last_page' => $pag->lastPage(),
            'total' => $pag->total(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255', Rule::unique('grupos', 'nombre')],
            'nombre_mostrar' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $grupo = Grupo::create([
            'nombre' => trim($data['nombre']),
            'nombre_mostrar' => trim($data['nombre_mostrar']),
            'descripcion' => filled($data['descripcion'] ?? null) ? trim($data['descripcion']) : null,
            'activo' => array_key_exists('activo', $data) ? (bool) $data['activo'] : true,
        ]);

        return response()->json([
            'grupo' => $grupo->fresh()->loadCount('users'),
        ], 201);
    }

    public function update(Request $request, Grupo $grupo)
    {
        $data = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('grupos', 'nombre')->ignore($grupo->id),
            ],
            'nombre_mostrar' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $grupo->update([
            'nombre' => trim($data['nombre']),
            'nombre_mostrar' => trim($data['nombre_mostrar']),
            'descripcion' => filled($data['descripcion'] ?? null) ? trim($data['descripcion']) : null,
            'activo' => array_key_exists('activo', $data) ? (bool) $data['activo'] : $grupo->activo,
        ]);

        return response()->json([
            'grupo' => $grupo->fresh()->loadCount('users'),
        ]);
    }

    public function destroy(Grupo $grupo)
    {
        $grupo->loadCount('users');

        if ((int) $grupo->users_count > 0) {
            return response()->json([
                'message' => 'No puedes eliminar un grupo que ya está asignado a uno o más usuarios.',
            ], 422);
        }

        $grupo->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Grupo eliminado correctamente.',
        ]);
    }
}