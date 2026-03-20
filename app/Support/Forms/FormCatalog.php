<?php

namespace App\Support\Forms;

use App\Support\Forms\Definitions\SST_POP_TA_07_FO_01_Inspeccion_de_Compresor;
use App\Support\Forms\Definitions\SST_POP_TA_08_FO_01_Checklist_de_Herramienta_Electrica_Portatil;

class FormCatalog
{
    public static function definitions(): array
    {
        return [
            SST_POP_TA_07_FO_01_Inspeccion_de_Compresor::class,
            SST_POP_TA_08_FO_01_Checklist_de_Herramienta_Electrica_Portatil::class,
        ];
    }
}