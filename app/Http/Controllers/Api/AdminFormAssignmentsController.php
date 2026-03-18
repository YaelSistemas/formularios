<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Form;
use Illuminate\Http\Request;

class AdminFormAssignmentsController extends Controller
{
    /**
     * Devuelve los IDs de usuarios asignados a un formulario.
     */
    public function index(Form $form)
    {
        return response()->json([
            'user_ids' => $form->assignedUsers()
                ->pluck('users.id')
                ->map(fn ($id) => (int) $id)
                ->values(),
        ]);
    }

    /**
     * Guarda o reemplaza las asignaciones de usuarios para un formulario.
     */
    public function store(Request $request, Form $form)
    {
        $data = $request->validate([
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $userIds = collect($data['user_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $form->assignedUsers()->sync($userIds);

        return response()->json([
            'ok' => true,
            'message' => 'Asignaciones guardadas correctamente.',
            'user_ids' => $form->assignedUsers()
                ->pluck('users.id')
                ->map(fn ($id) => (int) $id)
                ->values(),
            'assignments_count' => count($userIds),
        ]);
    }
}