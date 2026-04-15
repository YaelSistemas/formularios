<?php

namespace App\Support\Forms;

use App\Support\Forms\Definitions\SST_POP_TA_05_FO_02_Inspeccion_de_Equipo_de_Oxicorte;
use App\Support\Forms\Definitions\SST_POP_TA_05_FO_03_Checklist_Maquina_de_Soldar;
use App\Support\Forms\Definitions\SST_POP_TA_07_FO_01_Inspeccion_de_Compresor;
use App\Support\Forms\Definitions\SST_POP_TA_08_FO_01_Checklist_de_Herramienta_Electrica_Portatil;
use App\Support\Forms\Definitions\SST_POP_TA_04_FO_04_Checklist_Linea_Retractil_y_Puntos_Fijos; 

class FormCatalog
{
    public static function definitions(): array
    {
        return [
            SST_POP_TA_05_FO_02_Inspeccion_de_Equipo_de_Oxicorte::class,
            SST_POP_TA_05_FO_03_Checklist_Maquina_de_Soldar::class,
            SST_POP_TA_07_FO_01_Inspeccion_de_Compresor::class,
            SST_POP_TA_08_FO_01_Checklist_de_Herramienta_Electrica_Portatil::class,
            SST_POP_TA_04_FO_04_Checklist_Linea_Retractil_y_Puntos_Fijos::class, 
        ];
    }
}