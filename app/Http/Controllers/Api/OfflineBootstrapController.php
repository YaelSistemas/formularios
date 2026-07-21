<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Http\Request;

class OfflineBootstrapController extends Controller
{
    public function meta(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'No autorizado',
            ], 401);
        }

        $isAdmin = $user->hasRole('Administrador');

        /*
        |--------------------------------------------------------------------------
        | Formularios accesibles
        |--------------------------------------------------------------------------
        */

        $formsQuery = Form::query()
            ->where('status', 'PUBLICADO');

        if (!$isAdmin) {
            $formsQuery->whereHas(
                'assignedUsers',
                function ($query) use ($user) {
                    $query->where(
                        'users.id',
                        $user->id
                    );
                }
            );
        }

        /*
         * Obtenemos updated_at para detectar cambios
         * en las definiciones de los formularios.
         */
        $forms = $formsQuery->get([
            'id',
            'updated_at',
        ]);

        $formIds = $forms
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Unidades de servicio del usuario
        |--------------------------------------------------------------------------
        |
        | Guardamos tanto ID como nombre porque el filtro de registros
        | utiliza el nombre de la unidad contra answers.taller.
        |
        */

        $unitScope = $isAdmin
            ? collect()
            : $user->unidadesServicio()
                ->get([
                    'unidades_servicio.id',
                    'unidades_servicio.nombre',
                ])
                ->map(function ($unidad) {
                    return [
                        'id' => (int) $unidad->id,
                        'nombre' => trim(
                            (string) $unidad->nombre
                        ),
                    ];
                })
                ->sortBy('id')
                ->values();

        /*
        |--------------------------------------------------------------------------
        | Registros visibles
        |--------------------------------------------------------------------------
        |
        | El scope visibleTo aplica:
        |
        | Administrador:
        | - Todos los registros.
        |
        | Usuario normal:
        | - Sus propios registros.
        | - Registros cuyo answers.taller coincida con una de sus unidades.
        |
        */

        $submissionsQuery = FormSubmission::query()
            ->whereIn('form_id', $formIds)
            ->visibleTo($user);

        $submissionsCount = (clone $submissionsQuery)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Último cambio real
        |--------------------------------------------------------------------------
        */

        $lastFormChange = $forms->max(
            'updated_at'
        );

        $lastSubmissionChange = (clone $submissionsQuery)
            ->max('updated_at');

        $lastChange = collect([
            $lastFormChange,
            $lastSubmissionChange,
        ])
            ->filter()
            ->map(function ($date) {
                return \Illuminate\Support\Carbon::parse(
                    $date
                );
            })
            ->sortDesc()
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Hash del alcance
        |--------------------------------------------------------------------------
        |
        | Detecta:
        | - Formularios asignados o retirados.
        | - Unidades agregadas o retiradas.
        | - Cambios en el nombre de una unidad.
        | - Cambio entre administrador y usuario normal.
        |
        */

        $scopeData = json_encode([
            'is_admin' => $isAdmin,

            'units' => $isAdmin
                ? []
                : $unitScope->all(),

            'forms' => $formIds->all(),
        ], JSON_UNESCAPED_UNICODE);

        $unitScopeHash = md5(
            $scopeData
        );

        return response()->json([
            'forms_count' => $formIds->count(),

            'submissions_count' =>
                $submissionsCount,

            /*
             * Cada registro corresponde a un PDF.
             */
            'pdfs_count' =>
                $submissionsCount,

            'last_change_at' => $lastChange
                ? $lastChange->toISOString()
                : null,

            'unit_scope_hash' =>
                $unitScopeHash,
        ]);
    }

    public function bootstrap(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'No autorizado',
            ], 401);
        }

        $isAdmin = $user->hasRole(
            'Administrador'
        );

        /*
        |--------------------------------------------------------------------------
        | Formularios accesibles
        |--------------------------------------------------------------------------
        */

        $formsQuery = Form::query()
            ->where('status', 'PUBLICADO');

        if (!$isAdmin) {
            $formsQuery->whereHas(
                'assignedUsers',
                function ($query) use ($user) {
                    $query->where(
                        'users.id',
                        $user->id
                    );
                }
            );
        }

        $forms = $formsQuery->get();

        /*
        |--------------------------------------------------------------------------
        | Registros visibles agrupados por formulario
        |--------------------------------------------------------------------------
        */

        $submissionsByForm = [];

        foreach ($forms as $form) {
            $query = FormSubmission::query()
                ->with([
                    'user:id,name',
                ])
                ->where(
                    'form_id',
                    $form->id
                )
                ->visibleTo($user);

            $submissions = $query
                ->orderByDesc('consecutive')
                ->orderByDesc('id')
                ->limit(100)
                ->get([
                    'id',
                    'form_id',
                    'consecutive',
                    'user_id',
                    'answers',
                    'created_at',
                ])
                ->map(function ($submission) {
                    return [
                        'id' =>
                            $submission->id,

                        'form_id' =>
                            $submission->form_id,

                        'consecutive' =>
                            $submission->consecutive,

                        'user_id' =>
                            $submission->user_id,

                        'user_name' =>
                            $submission->user?->name,

                        'answers' =>
                            $submission->answers,

                        'created_at' =>
                            $submission->created_at,
                    ];
                })
                ->values();

            $submissionsByForm[
                $form->id
            ] = $submissions;
        }

        return response()->json([
            'forms' => $forms,

            'submissions_by_form' =>
                $submissionsByForm,
        ]);
    }
}