<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\UnidadServicio;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnidadesServicioController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $perPage = (int) $request->query('per_page', 25);
        $perPage = max(5, min(100, $perPage));

        $rows = UnidadServicio::query()
            ->withCount('users')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('nombre', 'like', "%{$q}%")
                        ->orWhere('descripcion', 'like', "%{$q}%");
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return response()->json($rows);
    }

    public function store(Request $request)
    {
        $messages = [
            'nombre.required' => 'No se puede crear la unidad de servicio porque falta el nombre.',
            'nombre.unique' => 'Ya existe una unidad de servicio con ese nombre.',
        ];

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255', Rule::unique('unidades_servicio', 'nombre')],
            'descripcion' => ['nullable', 'string'],
            'activo' => ['required', 'boolean'],
        ], $messages);

        $row = UnidadServicio::create([
            'nombre' => trim($data['nombre']),
            'descripcion' => filled($data['descripcion'] ?? null) ? trim($data['descripcion']) : null,
            'activo' => (bool) $data['activo'],
        ]);

        $row->loadCount('users');

        return response()->json([
            'ok' => true,
            'unidad_servicio' => $row,
        ], 201);
    }

    public function show(UnidadServicio $unidades_servicio)
    {
        $unidades_servicio->loadCount('users');

        return response()->json([
            'ok' => true,
            'unidad_servicio' => $unidades_servicio,
        ]);
    }

    public function update(Request $request, UnidadServicio $unidades_servicio)
    {
        $messages = [
            'nombre.required' => 'No se puede actualizar la unidad de servicio porque falta el nombre.',
            'nombre.unique' => 'Ya existe una unidad de servicio con ese nombre.',
        ];

        $data = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('unidades_servicio', 'nombre')->ignore($unidades_servicio->id),
            ],
            'descripcion' => ['nullable', 'string'],
            'activo' => ['required', 'boolean'],
        ], $messages);

        $unidades_servicio->update([
            'nombre' => trim($data['nombre']),
            'descripcion' => filled($data['descripcion'] ?? null) ? trim($data['descripcion']) : null,
            'activo' => (bool) $data['activo'],
        ]);

        return response()->json([
            'ok' => true,
            'unidad_servicio' => $unidades_servicio->fresh()->loadCount('users'),
        ]);
    }

    public function destroy(UnidadServicio $unidades_servicio)
    {
        $unidades_servicio->loadCount('users');

        if ((int) $unidades_servicio->users_count > 0) {
            return response()->json([
                'message' => 'No puedes eliminar una unidad de servicio que ya está asignada a uno o más usuarios.',
            ], 422);
        }

        $unidades_servicio->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Unidad de servicio eliminada correctamente.',
        ]);
    }
}