<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;

use App\Http\Controllers\Api\Admin\UsersController;
use App\Http\Controllers\Api\Admin\RolesController;
use App\Http\Controllers\Api\Admin\PermissionsController;
use App\Http\Controllers\Api\Admin\RolePermissionsController;
use App\Http\Controllers\Api\Admin\EmpresasController;
use App\Http\Controllers\Api\Admin\GruposController;
use App\Http\Controllers\Api\FormsController;
use App\Http\Controllers\Api\FormSubmissionsController;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Authenticated
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Sesión
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // ---------------- FORMS (Usuarios autenticados) ----------------
    // Lista (admin ve todo; usuario normal solo PUBLICADO)
    Route::get('/forms', [FormsController::class, 'index'])
        ->middleware('perm:formularios.view');

    // Detalle (admin cualquiera; usuario normal solo PUBLICADO)
    Route::get('/forms/{form}', [FormsController::class, 'show'])
        ->middleware('perm:formularios.view');

    // Llenar formulario (guardar respuestas)
    Route::post('/forms/{form}/submit', [FormSubmissionsController::class, 'store'])
        ->middleware('perm:formularios.submit');

    // Ver respuestas del formulario (tabla submissions)
    Route::get('/forms/{form}/submissions', [FormSubmissionsController::class, 'index'])
        ->middleware('perm:formularios.submissions.view');
});

/*
|--------------------------------------------------------------------------
| Admin (Authenticated + Admin role)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'admin'])
    ->prefix('admin')
    ->group(function () {

        // Roles (solo nombres, para asignar en usuarios)
        Route::get('/roles', [UsersController::class, 'roles']);

        // Users CRUD
        Route::get('/users', [UsersController::class, 'index']);
        Route::post('/users', [UsersController::class, 'store']);
        Route::put('/users/{user}', [UsersController::class, 'update']);
        Route::delete('/users/{user}', [UsersController::class, 'destroy']);

        // Roles CRUD
        Route::get('/roles-list', [RolesController::class, 'index']);
        Route::post('/roles-list', [RolesController::class, 'store']);
        Route::put('/roles-list/{role}', [RolesController::class, 'update']);
        Route::delete('/roles-list/{role}', [RolesController::class, 'destroy']);

        // Permissions CRUD
        Route::get('/permissions', [PermissionsController::class, 'index']);
        Route::post('/permissions', [PermissionsController::class, 'store']);
        Route::put('/permissions/{permission}', [PermissionsController::class, 'update']);
        Route::delete('/permissions/{permission}', [PermissionsController::class, 'destroy']);

        // Role <-> Permissions
        Route::get('/roles/{role}/permissions', [RolePermissionsController::class, 'show']);
        Route::put('/roles/{role}/permissions', [RolePermissionsController::class, 'update']);

        Route::get('empresas', [EmpresasController::class, 'index']);
        Route::post('empresas', [EmpresasController::class, 'store']);
        Route::put('empresas/{empresa}', [EmpresasController::class, 'update']);
        Route::delete('empresas/{empresa}', [EmpresasController::class, 'destroy']);
    
        Route::get('grupos', [GruposController::class, 'index']);
        Route::post('grupos', [GruposController::class, 'store']);
        Route::put('grupos/{grupo}', [GruposController::class, 'update']);
        Route::delete('grupos/{grupo}', [GruposController::class, 'destroy']);

        // ---------------- FORMS (Admin CRUD) ----------------
        Route::get('/forms', [FormsController::class, 'adminIndex'])
            ->middleware('perm:formularios.view');

        Route::post('/forms', [FormsController::class, 'store'])
            ->middleware('perm:formularios.create');

        Route::put('/forms/{form}', [FormsController::class, 'update'])
            ->middleware('perm:formularios.edit');

        Route::delete('/forms/{form}', [FormsController::class, 'destroy'])
            ->middleware('perm:formularios.delete');

        // Publicar / Despublicar
        Route::post('/forms/{form}/publish', [FormsController::class, 'publish'])
            ->middleware('perm:formularios.publish');

        Route::post('/forms/{form}/unpublish', [FormsController::class, 'unpublish'])
            ->middleware('perm:formularios.publish');
    });