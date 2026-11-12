<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration {
    public function up(): void
    {
        Permission::firstOrCreate([
            'name' => 'asistencias.incidencias.entrada.revisar',
            'guard_name' => 'sanctum',
        ]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
    public function down(): void
    {
        Permission::where('name', 'asistencias.incidencias.entrada.revisar')->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
