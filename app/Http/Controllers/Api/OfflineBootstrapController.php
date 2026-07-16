<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            $formsQuery->whereHas('assignedUsers', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            });
        }
    
        /*
         * Obtenemos también updated_at porque necesitamos detectar
         * cambios en la definición o configuración de los formularios.
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
        */
    
        $unidadIds = $isAdmin
            ? collect()
            : $user->unidadesServicio()
                ->pluck('unidades_servicio.id')
                ->map(fn ($id) => (int) $id)
                ->sort()
                ->values();
    
        /*
        |--------------------------------------------------------------------------
        | Registros visibles
        |--------------------------------------------------------------------------
        */
    
        $submissionsQuery = FormSubmission::query()
            ->whereIn('form_id', $formIds);
    
        if (!$isAdmin) {
            if ($unidadIds->isEmpty()) {
                /*
                 * El usuario puede tener formularios asignados, pero si no tiene
                 * unidades no debe ver registros de otros usuarios.
                 *
                 * No regresamos todo en ceros porque los formularios asignados
                 * sí deben seguir contando.
                 */
                $submissionsQuery->whereRaw('1 = 0');
            } else {
                $submissionsQuery->whereHas(
                    'user.unidadesServicio',
                    function ($query) use ($unidadIds) {
                        $query->whereIn(
                            'unidades_servicio.id',
                            $unidadIds
                        );
                    }
                );
            }
        }
    
        $submissionsCount = (clone $submissionsQuery)->count();
    
        /*
        |--------------------------------------------------------------------------
        | Último cambio real
        |--------------------------------------------------------------------------
        |
        | Revisamos tanto formularios como registros.
        | No usamos now() porque generaría un cambio diferente en cada petición.
        */
    
        $lastFormChange = $forms->max('updated_at');
    
        $lastSubmissionChange = (clone $submissionsQuery)
            ->max('updated_at');
    
        $lastChange = collect([
            $lastFormChange,
            $lastSubmissionChange,
        ])
            ->filter()
            ->map(function ($date) {
                return \Illuminate\Support\Carbon::parse($date);
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
        | - Cambio entre administrador y usuario normal.
        */
    
        $scopeData = json_encode([
            'is_admin' => $isAdmin,
            'units' => $isAdmin
                ? []
                : $unidadIds->all(),
            'forms' => $formIds->all(),
        ]);
    
        $unitScopeHash = md5($scopeData);
    
        return response()->json([
            'forms_count' => $formIds->count(),
            'submissions_count' => $submissionsCount,
    
            /*
             * Actualmente cada registro corresponde a un PDF disponible.
             * Por eso se conserva el mismo conteo.
             */
            'pdfs_count' => $submissionsCount,
    
            /*
             * Será null únicamente cuando no exista ningún formulario
             * ni registro que tenga una fecha de modificación.
             */
            'last_change_at' => $lastChange
                ? $lastChange->toISOString()
                : null,
    
            'unit_scope_hash' => $unitScopeHash,
        ]);
    }

    public function bootstrap(Request $request)
    {
        $user = $request->user();
    
        if (!$user) {
            return response()->json(['message' => 'No autorizado'], 401);
        }
    
        $isAdmin = $user->hasRole('Administrador');
    
        // 🔹 Formularios accesibles
        $formsQuery = \App\Models\Form::query()
            ->where('status', 'PUBLICADO');
    
        if (!$isAdmin) {
            $formsQuery->whereHas('assignedUsers', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }
    
        $forms = $formsQuery->get();
    
        // 🔹 IDs de unidades
        $unidadIds = $user->unidadesServicio()->pluck('unidades_servicio.id');
    
        $submissionsByForm = [];
    
        foreach ($forms as $form) {
            $query = \App\Models\FormSubmission::query()
                ->with(['user:id,name'])
                ->where('form_id', $form->id);
    
            if (!$isAdmin) {
                if ($unidadIds->isEmpty()) {
                    $submissionsByForm[$form->id] = [];
                    continue;
                }
    
                $query->whereHas('user.unidadesServicio', function ($q) use ($unidadIds) {
                    $q->whereIn('unidades_servicio.id', $unidadIds);
                });
            }
    
            $subs = $query
                ->orderByDesc('consecutive')
                ->orderByDesc('id')
                ->limit(100)
                ->get(['id', 'form_id', 'consecutive', 'user_id', 'answers', 'created_at'])
                ->map(function ($sub) {
                    return [
                        'id' => $sub->id,
                        'form_id' => $sub->form_id,
                        'consecutive' => $sub->consecutive,
                        'user_id' => $sub->user_id,
                        'user_name' => $sub->user?->name,
                        'answers' => $sub->answers,
                        'created_at' => $sub->created_at,
                    ];
                })
                ->values();
    
            $submissionsByForm[$form->id] = $subs;
        }
    
        return response()->json([
            'forms' => $forms,
            'submissions_by_form' => $submissionsByForm,
        ]);
    }
}