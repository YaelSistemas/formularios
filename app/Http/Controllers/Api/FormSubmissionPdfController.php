<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormSubmission;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class FormSubmissionPdfController extends Controller
{
    public function show(Request $request, Form $form, FormSubmission $submission)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'No autorizado.'], 401);
        }

        if ((int) $submission->form_id !== (int) $form->id) {
            return response()->json(['message' => 'Registro no encontrado para este formulario.'], 404);
        }

        if (!$user->hasRole('Administrador') && $form->status !== 'PUBLICADO') {
            return response()->json(['message' => 'No encontrado.'], 404);
        }

        $submission->load('user');

        $answers = is_array($submission->answers) ? $submission->answers : [];
        $fields = is_array(data_get($form->payload, 'fields')) ? data_get($form->payload, 'fields') : [];
        $formCodeKey = (string) data_get($form->payload, '_code_key', '');

        $view = $this->resolvePdfView($formCodeKey);

        $pdf = Pdf::loadView($view, [
            'form' => $form,
            'submission' => $submission,
            'answers' => $answers,
            'fields' => $fields,
            'userName' => $submission->user?->name,
            'generatedAt' => now(),
        ]);

        $fileName = 'registro_' . $form->id . '_' . $submission->id . '.pdf';

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $fileName . '"');
    }

    private function resolvePdfView(string $formCodeKey): string
    {
        return match ($formCodeKey) {
            'sst_pop_ta_08_fo_01_checklist_herramienta_electrica_portatil'
                => 'pdf.forms.SST_POP_TA_08_FO_01_Checklist_de_Herramienta_Electrica_Portatil',

            default => 'pdf.forms.generic_submission',
        };
    }
}