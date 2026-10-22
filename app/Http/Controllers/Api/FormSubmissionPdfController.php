<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormSubmission;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class FormSubmissionPdfController extends Controller
{
    public function show(
        Request $request,
        Form $form,
        FormSubmission $submission
    ) {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'No autorizado.',
            ], 401);
        }

        if (
            (int) $submission->form_id !==
            (int) $form->id
        ) {
            return response()->json([
                'message' =>
                    'Registro no encontrado para este formulario.',
            ], 404);
        }

        if (
            !$user->hasRole('Administrador') &&
            $form->status !== 'PUBLICADO'
        ) {
            return response()->json([
                'message' => 'No encontrado.',
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Cargar usuario y versión histórica
        |--------------------------------------------------------------------------
        |
        | El PDF debe depender de la versión que quedó guardada
        | permanentemente en el registro.
        |
        */
        $submission->load([
            'user',
            'formVersion',
        ]);

        $formVersion = $submission->formVersion;

        if (!$formVersion) {
            return response()->json([
                'message' =>
                    'El registro no tiene una versión de formulario asociada.',
            ], 422);
        }

        /*
         * Protección adicional:
         * la versión asociada debe pertenecer al mismo formulario.
         */
        if (
            (int) $formVersion->form_id !==
            (int) $form->id
        ) {
            return response()->json([
                'message' =>
                    'La versión asociada al registro no corresponde al formulario.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Snapshot histórico
        |--------------------------------------------------------------------------
        */
        $versionPayload = is_array($formVersion->payload)
            ? $formVersion->payload
            : [];

        $answers = is_array($submission->answers)
            ? $submission->answers
            : [];

        $fields = is_array(
            data_get(
                $versionPayload,
                'fields'
            )
        )
            ? data_get(
                $versionPayload,
                'fields'
            )
            : [];

        /*
        |--------------------------------------------------------------------------
        | Vista PDF de la versión
        |--------------------------------------------------------------------------
        |
        | Ya no resolvemos el Blade mediante el _code_key actual.
        |
        | V1 utiliza su pdf_view.
        | V2 utilizará su propio pdf_view.
        | V3 utilizará el suyo, etc.
        |
        */
        $view = trim(
            (string) $formVersion->pdf_view
        );

        if (
            $view === '' ||
            !view()->exists($view)
        ) {
            return response()->json([
                'message' =>
                    'La versión del formulario no tiene una vista PDF válida.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Formulario versionado para el Blade
        |--------------------------------------------------------------------------
        |
        | Algunos Blade pueden consultar directamente $form->payload.
        | Por eso no les pasamos el Form actual tal cual.
        |
        | Creamos una copia en memoria y sustituimos únicamente su payload
        | por el snapshot guardado en FormVersion.
        |
        | Esta copia NO se guarda en la base de datos.
        |
        */
        $versionedForm = clone $form;

        $versionedForm->payload =
            $versionPayload;

        /*
        |--------------------------------------------------------------------------
        | Generar PDF
        |--------------------------------------------------------------------------
        */
        $pdf = Pdf::loadView(
            $view,
            [
                'form' => $versionedForm,
                'formVersion' => $formVersion,
                'submission' => $submission,
                'answers' => $answers,
                'fields' => $fields,
                'userName' =>
                    $submission->user?->name,
                'generatedAt' => now(),
            ]
        );

        $fileName =
            'registro_' .
            $form->id .
            '_' .
            $submission->id .
            '.pdf';

        return response(
            $pdf->output(),
            200
        )
            ->header(
                'Content-Type',
                'application/pdf'
            )
            ->header(
                'Content-Disposition',
                'inline; filename="' .
                    $fileName .
                    '"'
            );
    }
}