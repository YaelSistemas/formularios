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
            'sst_pop_ta_07_fo_01_inspeccion_de_compresor'
                => 'pdf.forms.SST_POP_TA_07_FO_01_Inspeccion_de_Compresor',

            'sst_pop_ta_08_fo_01_checklist_herramienta_electrica_portatil'
                => 'pdf.forms.SST_POP_TA_08_FO_01_Checklist_de_Herramienta_Electrica_Portatil',

            'sst_pop_ta_05_fo_02_inspeccion_de_equipo_de_oxicorte'
                => 'pdf.forms.SST_POP_TA_05_FO_02_Inspeccion_de_Equipo_de_Oxicorte',

            'sst_pop_ta_05_fo_03_checklist_maquina_de_soldar'
                => 'pdf.forms.SST_POP_TA_05_FO_03_Checklist_Maquina_de_Soldar',
            
            'sst_pop_ta_04_fo_04_checklist_linea_retractil_y_puntos_fijos'
                => 'pdf.forms.SST_POP_TA_04_FO_04_Checklist_Linea_Retractil_y_Puntos_Fijos',
            
            'sst_pop_ta_04_fo_03_inspeccion_de_linea_de_vida'
                => 'pdf.forms.SST_POP_TA_04_FO_03_Inspeccion_de_Linea_de_Vida',

            'sst_pop_ta_04_fo_02_inspeccion_de_arnes_de_seguridad'
                => 'pdf.forms.SST_POP_TA_04_FO_02_Inspeccion_de_Arnes_de_Seguridad',

            'sst_pop_ta_04_fo_01_checklist_de_sand_blast'
                => 'pdf.forms.SST_POP_TA_04_FO_01_Checklist_de_Sand_Blast',
            
            'sst_pop_ta_01_fo_08_checklist_de_tirfor'
                => 'pdf.forms.SST_POP_TA_01_FO_08_Checklist_de_Tirfor',

            'sst_pop_ta_01_fo_07_checklist_de_tecle'
                => 'pdf.forms.SST_POP_TA_01_FO_07_Checklist_de_Tecle',
            
            'sst_pop_ta_01_fo_06_checklist_de_polipasto_manual_de_cadena'
                => 'pdf.forms.SST_POP_TA_01_FO_06_Checklist_de_Polipasto_Manual_de_Cadena',

            'sst_pop_ta_01_fo_04_checklist_de_inspeccion_de_escaleras_portatiles'
                => 'pdf.forms.SST_POP_TA_01_FO_04_Checklist_de_Inspeccion_de_Escaleras_Portatiles',

            'sst_pop_ta_01_fo_03_inspeccion_de_equipo_de_proteccion_personal'
                => 'pdf.forms.SST_POP_TA_01_FO_03_Inspeccion_de_Equipo_de_Proteccion_Personal',

            'sst_pgi_ta_02_fo_04_checklist_de_unidades_moviles'
                => 'pdf.forms.SST_PGI_TA_02_FO_04_Checklist_de_Unidades_Moviles',
            
            'sst_pgi_ta_02_fo_03_checklist_de_botiquines'
                => 'pdf.forms.SST_PGI_TA_02_FO_03_Checklist_de_Botiquines',

            'sst_pgi_ta_02_fo_02_checklist_de_extintor'
                => 'pdf.forms.SST_PGI_TA_02_FO_02_Checklist_de_Extintor',

            'sst_pgi_ta_01_fo_01_boleta_de_observaciones'
                => 'pdf.forms.SST_PGI_TA_01_FO_01_Boleta_de_Observaciones',

            'sgi_pop_lg_01_fo_09_checklist_eslingas_de_cadenas'
                => 'pdf.forms.SGI_POP_LG_01_FO_09_Checklist_Eslingas_de_Cadenas',

            'sgi_pop_lg_01_fo_08_inspeccion_de_grua_viajera'
                => 'pdf.forms.sgi_pop_lg_01_fo_08_inspeccion_de_grua_viajera',
            
            'sgi_pop_lg_01_fo_06_checklist_de_mantenimiento_grua_viajera'
                => 'pdf.forms.SGI_POP_LG_01_FO_06_Checklist_de_Mantenimiento_Grua_Viajera',

            'sgi_pop_lg_01_fo_04_checklist_de_mantenimiento_cortadora_de_banda'
                => 'pdf.forms.SGI_POP_LG_01_FO_04_Checklist_de_Mantenimiento_Cortadora_de_Banda',

            'sgi_pop_lg_01_fo_03_checklist_semanal_montacargas'
                => 'pdf.forms.SGI_POP_LG_01_FO_03_Checklist_Semanal_Montacargas',

            'sgi_pop_lg_01_07_checklist_mantenimiento_sistema_electrico'
                => 'pdf.forms.SGI_POP_LG_01_07_Checklist_Mantenimiento_Sistema_Electrico',

            'sgi_pop_gt_01_fo_11_checklist_de_inspeccion_de_estrobos'
                => 'pdf.forms.SGI_POP_GT_01_FO_11_Checklist_de_Inspeccion_de_Estrobos',
                
            default => 'pdf.forms.generic_submission',
        };
    }
}