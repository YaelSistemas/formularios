<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\FormVersion;
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

        $lastVersionChange = FormVersion::query()
            ->whereIn(
                'form_id',
                $formIds
            )
            ->max('updated_at');

        $lastChange = collect([
            $lastFormChange,
            $lastSubmissionChange,
            $lastVersionChange,
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
        | Cantidad de registros por formulario
        |--------------------------------------------------------------------------
        |
        | Sin recent_per_form:
        | - Mantiene el comportamiento actual.
        | - Máximo 100 registros por formulario.
        |
        | Con recent_per_form=2:
        | - Devuelve únicamente los 2 registros
        |   más recientes de cada formulario.
        |
        */
    
        $recentPerForm = (int) $request->query(
            'recent_per_form',
            0
        );
    
        /*
         * Evitamos valores negativos o exagerados
         * enviados desde la petición.
         */
        if ($recentPerForm < 0) {
            $recentPerForm = 0;
        }
    
        if ($recentPerForm > 20) {
            $recentPerForm = 20;
        }
    
        /*
         * Si no se solicita modo rápido,
         * conservamos exactamente el límite
         * que ya tenía el sistema.
         */
        $recordsLimit = $recentPerForm > 0
            ? $recentPerForm
            : 100;
    
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
        | Versiones de los formularios accesibles
        |--------------------------------------------------------------------------
        |
        | Se envían todas las versiones del alcance.
        |
        | Esto permite:
        | - consultar registros históricos offline;
        | - conocer la versión vigente;
        | - conservar V1 aunque exista V2/V3.
        |
        */
        
        $formIds = $forms
            ->pluck('id')
            ->map(
                fn ($id) => (int) $id
            )
            ->values();
        
        $formVersions = FormVersion::query()
            ->whereIn(
                'form_id',
                $formIds
            )
            ->orderBy('form_id')
            ->orderBy('version')
            ->get([
                'id',
                'form_id',
                'version',
                'version_key',
                'definition_class',
                'layout_key',
                'pdf_view',
                'payload',
                'fecha_desde',
                'fecha_hasta',
                'descripcion',
                'created_at',
                'updated_at',
            ])
            ->mapWithKeys(function ($version) {
                return [
                    (string) $version->id => [
                        'id' =>
                            $version->id,
        
                        'form_id' =>
                            $version->form_id,
        
                        'version' =>
                            $version->version,
        
                        'version_key' =>
                            $version->version_key,
        
                        'definition_class' =>
                            $version->definition_class,
        
                        'layout_key' =>
                            $version->layout_key,
        
                        'pdf_view' =>
                            $version->pdf_view,
        
                        'payload' =>
                            $version->payload,
        
                        'fecha_desde' =>
                            $version->fecha_desde,
        
                        'fecha_hasta' =>
                            $version->fecha_hasta,
        
                        'descripcion' =>
                            $version->descripcion,
        
                        'created_at' =>
                            $version->created_at,
        
                        'updated_at' =>
                            $version->updated_at,
                    ],
                ];
            });

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
                ->limit($recordsLimit)
                ->get([
                    'id',
                    'form_id',
                    'form_version_id',
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

                        'form_version_id' =>
                            $submission->form_version_id,
    
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
        
            'form_versions' =>
                $formVersions,
        
            'submissions_by_form' =>
                $submissionsByForm,
        ]);
    }
}