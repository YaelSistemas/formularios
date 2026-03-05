<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // ✅ Guard para API con Sanctum
        $guard = 'sanctum';

        /**
         * 1) Roles base
         */
        $adminRole = Role::firstOrCreate([
            'name' => 'Administrador',
            'guard_name' => $guard,
        ]);

        $userRole = Role::firstOrCreate([
            'name' => 'Usuario',
            'guard_name' => $guard,
        ]);

        /**
         * 2) Permisos base (Forms)
         */
        $permissions = [
            'formularios.view',
            'formularios.create',
            'formularios.edit',
            'formularios.delete',
            'formularios.submit',
            'formularios.publish', 
            'formularios.submissions.view',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate([
                'name' => $p,
                'guard_name' => $guard,
            ]);
        }

        /**
         * 3) Asignación de permisos
         * - Admin: todo
         * - Usuario: ver + responder
         */
        $adminRole->syncPermissions($permissions);

        $userRole->syncPermissions([
            'formularios.view',
            'formularios.submit',
        ]);

        /**
         * 4) Usuario admin base
         */
        $user = User::firstOrCreate(
            ['email' => 'admin@local.test'],
            [
                'name' => 'Admin',
                'password' => Hash::make('Admin1234*'),
            ]
        );

        // ✅ Asegura rol admin
        if (! $user->hasRole('Administrador')) {
            $user->assignRole($adminRole);
        }
    }
}