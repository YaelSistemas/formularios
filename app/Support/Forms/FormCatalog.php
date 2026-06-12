<?php

namespace App\Support\Forms;

use App\Support\Forms\Definitions\SST_POP_TA_05_FO_02_Inspeccion_de_Equipo_de_Oxicorte;
use App\Support\Forms\Definitions\SST_POP_TA_05_FO_03_Checklist_Maquina_de_Soldar;
use App\Support\Forms\Definitions\SST_POP_TA_07_FO_01_Inspeccion_de_Compresor;
use App\Support\Forms\Definitions\SST_POP_TA_08_FO_01_Checklist_de_Herramienta_Electrica_Portatil;
use App\Support\Forms\Definitions\SST_POP_TA_04_FO_04_Checklist_Linea_Retractil_y_Puntos_Fijos; 
use App\Support\Forms\Definitions\SST_POP_TA_04_FO_03_Inspeccion_de_Linea_de_Vida;
use App\Support\Forms\Definitions\SST_POP_TA_04_FO_02_Inspeccion_de_Arnes_de_Seguridad;
use App\Support\Forms\Definitions\SST_POP_TA_04_FO_01_Checklist_de_Sand_Blast;
use App\Support\Forms\Definitions\SST_POP_TA_01_FO_08_Checklist_de_Tirfor;
use App\Support\Forms\Definitions\SST_POP_TA_01_FO_07_Checklist_de_Tecle;
use App\Support\Forms\Definitions\SST_POP_TA_01_FO_06_Checklist_de_Polipasto_Manual_de_Cadena;
use App\Support\Forms\Definitions\SST_POP_TA_01_FO_04_Checklist_de_Inspeccion_de_Escaleras_Portatiles;
use App\Support\Forms\Definitions\SST_POP_TA_01_FO_03_Inspeccion_de_Equipo_de_Proteccion_Personal;
use App\Support\Forms\Definitions\SST_PGI_TA_02_FO_04_Checklist_de_Unidades_Moviles;
use App\Support\Forms\Definitions\SST_PGI_TA_02_FO_03_Checklist_de_Botiquines;
use App\Support\Forms\Definitions\SST_PGI_TA_02_FO_02_Checklist_de_Extintor;
use App\Support\Forms\Definitions\SST_PGI_TA_01_FO_01_Boleta_de_Observaciones;
use App\Support\Forms\Definitions\SGI_POP_LG_01_FO_09_Checklist_Eslingas_de_Cadenas;
use App\Support\Forms\Definitions\SGI_POP_LG_01_FO_08_Inspeccion_de_Grua_Viajera;
use App\Support\Forms\Definitions\SGI_POP_LG_01_FO_06_Checklist_de_Mantenimiento_Grua_Viajera;
use App\Support\Forms\Definitions\SGI_POP_LG_01_FO_04_Checklist_de_Mantenimiento_Cortadora_de_Banda;
use App\Support\Forms\Definitions\SGI_POP_LG_01_FO_03_Checklist_Semanal_Montacargas;
use App\Support\Forms\Definitions\SGI_POP_LG_01_07_Checklist_Mantenimiento_Sistema_Electrico;
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
            SST_POP_TA_04_FO_03_Inspeccion_de_Linea_de_Vida::class,
            SST_POP_TA_04_FO_02_Inspeccion_de_Arnes_de_Seguridad::class,
            SST_POP_TA_04_FO_01_Checklist_de_Sand_Blast::class,
            SST_POP_TA_01_FO_08_Checklist_de_Tirfor::class,
            SST_POP_TA_01_FO_07_Checklist_de_Tecle::class,
            SST_POP_TA_01_FO_06_Checklist_de_Polipasto_Manual_de_Cadena::class,
            SST_POP_TA_01_FO_04_Checklist_de_Inspeccion_de_Escaleras_Portatiles::class,
            SST_POP_TA_01_FO_03_Inspeccion_de_Equipo_de_Proteccion_Personal::class,
            SST_PGI_TA_02_FO_04_Checklist_de_Unidades_Moviles::class,
            SST_PGI_TA_02_FO_03_Checklist_de_Botiquines::class,
            SST_PGI_TA_02_FO_02_Checklist_de_Extintor::class,
            SST_PGI_TA_01_FO_01_Boleta_de_Observaciones::class,
            SGI_POP_LG_01_FO_09_Checklist_Eslingas_de_Cadenas::class,
            SGI_POP_LG_01_FO_08_Inspeccion_de_Grua_Viajera::class,
            SGI_POP_LG_01_FO_06_Checklist_de_Mantenimiento_Grua_Viajera::class,
            SGI_POP_LG_01_FO_04_Checklist_de_Mantenimiento_Cortadora_de_Banda::class,
            SGI_POP_LG_01_FO_03_Checklist_Semanal_Montacargas::class,
            SGI_POP_LG_01_07_Checklist_Mantenimiento_Sistema_Electrico::class,
        ];
    }
}