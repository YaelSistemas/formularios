<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    protected function serializeFormSnapshot(Form $form): array
    {
        return [
            'title' => trim((string) $form->title),
            'status' => trim((string) $form->status),
        ];
    }

    protected function serializeUsersByIds(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }

        $users = User::query()
            ->whereIn('id', $userIds)
            ->get(['id', 'name', 'email'])
            ->sortBy(fn ($user) => mb_strtolower((string) ($user->name ?? '')))
            ->values();

        return $users->map(function ($user) {
            return [
                'id' => (int) $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ];
        })->all();
    }

    protected function createHistoryEntry(
        int $formId,
        ?int $actorId,
        string $action,
        array $snapshot,
        ?array $details = null
    ): void {
        FormHistory::create([
            'form_id' => $formId,
            'user_id' => $actorId,
            'action' => $action,
            'snapshot' => $snapshot,
            'details' => $details,
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

        $authUser = $request->user();

        DB::transaction(function () use ($form, $userIds, $authUser) {
            $beforeIds = $form->assignedUsers()
                ->pluck('users.id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();

            $form->assignedUsers()->sync($userIds);

            $afterIds = $form->assignedUsers()
                ->pluck('users.id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();

            $addedIds = array_values(array_diff($afterIds, $beforeIds));
            $removedIds = array_values(array_diff($beforeIds, $afterIds));

            $snapshot = $this->serializeFormSnapshot($form->fresh());

            if (!empty($addedIds)) {
                $this->createHistoryEntry(
                    formId: (int) $form->id,
                    actorId: $authUser?->id,
                    action: 'assigned_users',
                    snapshot: $snapshot,
                    details: [
                        'users' => $this->serializeUsersByIds($addedIds),
                    ]
                );
            }

            if (!empty($removedIds)) {
                $this->createHistoryEntry(
                    formId: (int) $form->id,
                    actorId: $authUser?->id,
                    action: 'unassigned_users',
                    snapshot: $snapshot,
                    details: [
                        'users' => $this->serializeUsersByIds($removedIds),
                    ]
                );
            }
        });

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