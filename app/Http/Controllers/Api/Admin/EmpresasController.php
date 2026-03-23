<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmpresasController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $perPage = (int) $request->query('per_page', 25);
        $perPage = max(5, min(100, $perPage));

        $query = Empresa::query()->withCount('users');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nombre', 'like', "%{$q}%")
                    ->orWhere('razon_social', 'like', "%{$q}%");
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
            'nombre' => ['required', 'string', 'max:255', Rule::unique('empresas', 'nombre')],
            'razon_social' => ['nullable', 'string', 'max:255'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $empresa = Empresa::create([
            'nombre' => trim($data['nombre']),
            'razon_social' => filled($data['razon_social'] ?? null) ? trim($data['razon_social']) : null,
            'activo' => array_key_exists('activo', $data) ? (bool) $data['activo'] : true,
        ]);

        return response()->json([
            'empresa' => $empresa->fresh()->loadCount('users'),
        ], 201);
    }

    public function update(Request $request, Empresa $empresa)
    {
        $data = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('empresas', 'nombre')->ignore($empresa->id),
            ],
            'razon_social' => ['nullable', 'string', 'max:255'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $empresa->update([
            'nombre' => trim($data['nombre']),
            'razon_social' => filled($data['razon_social'] ?? null) ? trim($data['razon_social']) : null,
            'activo' => array_key_exists('activo', $data) ? (bool) $data['activo'] : $empresa->activo,
        ]);

        return response()->json([
            'empresa' => $empresa->fresh()->loadCount('users'),
        ]);
    }

    public function destroy(Empresa $empresa)
    {
        $empresa->loadCount('users');

        if ((int) $empresa->users_count > 0) {
            return response()->json([
                'message' => 'No puedes eliminar una empresa que ya está asignada a uno o más usuarios.',
            ], 422);
        }

        $empresa->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Empresa eliminada correctamente.',
        ]);
    }
}