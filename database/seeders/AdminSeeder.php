<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $guard = 'sanctum';

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | Permisos permitidos
        |--------------------------------------------------------------------------
        */
        $permissions = [
            'formularios.view',
            'formularios.create',
            'formularios.edit',
            'formularios.delete',
            'formularios.submit',
            'formularios.admin.publish',
            'formularios.submissions.view',
            'admin.panel.view',
            'formularios.admin.view',
            'formularios.admin.assign',
            'usuarios.view',
            'usuarios.create',
            'usuarios.edit',
            'usuarios.delete',
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',
            'permisos.view',
            'permisos.create',
            'permisos.edit',
            'permisos.delete',
            'empresas.view',
            'empresas.create',
            'empresas.edit',
            'empresas.delete',
            'grupos.view',
            'grupos.create',
            'grupos.edit',
            'grupos.delete',
            'unidades_servicio.view',
            'unidades_servicio.create',
            'unidades_servicio.edit',
            'unidades_servicio.delete',
        ];

        /*
        |--------------------------------------------------------------------------
        | Crear permisos
        |--------------------------------------------------------------------------
        */
        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => $guard,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Crear solo rol Administrador
        |--------------------------------------------------------------------------
        */
        $adminRole = Role::updateOrCreate(
            [
                'name' => 'Administrador',
                'guard_name' => $guard,
            ],
            [
                'name' => 'Administrador',
                'guard_name' => $guard,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Asignar todos los permisos al Administrador
        |--------------------------------------------------------------------------
        */
        $adminRole->syncPermissions($permissions);

        /*
        |--------------------------------------------------------------------------
        | Crear / actualizar usuario administrador
        |--------------------------------------------------------------------------
        */
        $user = User::updateOrCreate(
            [
                'email' => 'soporte.sistemas3@grupo-vysisa.mx',
            ],
            [
                'name' => 'Yael Alain Romero Cazarez',
                'password' => Hash::make('S1$T3m4s@'),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Asignar solo rol Administrador al usuario
        |--------------------------------------------------------------------------
        */
        $user->syncRoles([$adminRole]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}