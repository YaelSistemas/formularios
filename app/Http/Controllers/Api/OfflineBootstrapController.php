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
            return response()->json(['message' => 'No autorizado'], 401);
        }

        $isAdmin = $user->hasRole('Administrador');

        // 🔹 Formularios accesibles
        $formsQuery = Form::query()
            ->where('status', 'PUBLICADO');

        if (!$isAdmin) {
            $formsQuery->whereHas('assignedUsers', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

        $forms = $formsQuery->pluck('id');

        // 🔹 Submissions visibles
        $submissionsQuery = FormSubmission::query()
            ->whereIn('form_id', $forms);

        if (!$isAdmin) {
            $unidadIds = $user->unidadesServicio()->pluck('unidades_servicio.id');

            if ($unidadIds->isEmpty()) {
                return response()->json([
                    'forms_count' => 0,
                    'submissions_count' => 0,
                    'pdfs_count' => 0,
                    'last_change_at' => null,
                    'unit_scope_hash' => 'empty',
                ]);
            }

            $submissionsQuery->whereHas('user.unidadesServicio', function ($q) use ($unidadIds) {
                $q->whereIn('unidades_servicio.id', $unidadIds);
            });
        }

        $submissionsCount = (clone $submissionsQuery)->count();

        // 🔹 Última modificación
        $lastChange = (clone $submissionsQuery)
            ->max('updated_at') ?? now();

        // 🔹 Hash de alcance (MUY IMPORTANTE)
        $unidadIds = $user->unidadesServicio()->pluck('unidades_servicio.id')->sort()->values()->toArray();
        $formIds = $forms->sort()->values()->toArray();

        $scopeData = json_encode([
            'units' => $unidadIds,
            'forms' => $formIds,
        ]);

        $unitScopeHash = md5($scopeData);

        return response()->json([
            'forms_count' => $forms->count(),
            'submissions_count' => $submissionsCount,
            'pdfs_count' => $submissionsCount,
            'last_change_at' => $lastChange,
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