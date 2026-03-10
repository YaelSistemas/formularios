<?php

namespace App\Support\Forms;

use App\Support\Forms\Definitions\SST_POP_TA_08_FO_01_Checklist_de_Herramienta_Electrica_Portatil;
use App\Support\Forms\Definitions\VehiculoForm;

class FormCatalog
{
    public static function definitions(): array
    {
        return [
            SST_POP_TA_08_FO_01_Checklist_de_Herramienta_Electrica_Portatil::class,
            VehiculoForm::class,
        ];
    }
}