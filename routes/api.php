<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;

use App\Http\Controllers\Api\Admin\UsersController;
use App\Http\Controllers\Api\Admin\RolesController;
use App\Http\Controllers\Api\Admin\PermissionsController;
use App\Http\Controllers\Api\Admin\RolePermissionsController;
use App\Http\Controllers\Api\Admin\EmpresasController;
use App\Http\Controllers\Api\Admin\GruposController;
use App\Http\Controllers\Api\Admin\UnidadesServicioController;
use App\Http\Controllers\Api\FormsController;
use App\Http\Controllers\Api\FormSubmissionsController;
use App\Http\Controllers\Api\FormSubmissionPdfController;
use App\Http\Controllers\Api\AdminFormAssignmentsController;
use App\Http\Controllers\Api\OfflineBootstrapController;

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
    Route::get('/forms', [FormsController::class, 'index'])
        ->middleware('perm:formularios.view');

    Route::get('/forms/{form}', [FormsController::class, 'show'])
        ->middleware('perm:formularios.view');

    Route::post('/forms/{form}/submit', [FormSubmissionsController::class, 'store'])
        ->middleware('perm:formularios.submit');

    Route::get('/forms/{form}/submissions', [FormSubmissionsController::class, 'index'])
        ->middleware('perm:formularios.submissions.view');

    Route::get('/forms/{form}/submissions/{submission}/history', [FormSubmissionsController::class, 'history'])
        ->middleware('perm:formularios.submissions.view');

    Route::put('/forms/{form}/submissions/{submission}', [FormSubmissionsController::class, 'update'])
        ->middleware('perm:formularios.edit');

    Route::patch('/forms/{form}/submissions/{submission}', [FormSubmissionsController::class, 'update'])
        ->middleware('perm:formularios.edit');

    Route::delete('/forms/{form}/submissions/{submission}', [FormSubmissionsController::class, 'destroy'])
        ->middleware('perm:formularios.delete');

    Route::get('/forms/{form}/submissions/{submission}/pdf', [FormSubmissionPdfController::class, 'show'])
        ->middleware('perm:formularios.submissions.view');

    // Vista para Descarga Offline
    Route::get('/offline/bootstrap-meta', [OfflineBootstrapController::class, 'meta'])
        ->middleware('perm:formularios.submissions.view');
    
    Route::get('/offline/bootstrap', [OfflineBootstrapController::class, 'bootstrap'])
        ->middleware('perm:formularios.submissions.view');
});

/*
|--------------------------------------------------------------------------
| Admin (Authenticated + permiso panel admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'perm:admin.panel.view'])
    ->prefix('admin')
    ->group(function () {

        // Roles (solo nombres, para asignar en usuarios)
        Route::get('/roles', [UsersController::class, 'roles'])
            ->middleware('perm:usuarios.view');

        // Users CRUD
        Route::get('/users', [UsersController::class, 'index'])
            ->middleware('perm:usuarios.view');

            // Historial de Usuarios
            Route::get('/users/{user}/history', [UsersController::class, 'history'])
                ->middleware('perm:usuarios.view');

        Route::post('/users', [UsersController::class, 'store'])
            ->middleware('perm:usuarios.create');

        Route::put('/users/{user}', [UsersController::class, 'update'])
            ->middleware('perm:usuarios.edit');

        Route::delete('/users/{user}', [UsersController::class, 'destroy'])
            ->middleware('perm:usuarios.delete');

        // Roles CRUD
        Route::get('/roles-list', [RolesController::class, 'index'])
            ->middleware('perm:roles.view');

            // Historial de Roles
            Route::get('/roles-list/{role}/history', [RolesController::class, 'history'])
                ->middleware('perm:roles.view');

        Route::get('/roles-list/{role}', [RolesController::class, 'show'])
            ->middleware('perm:roles.edit');

        Route::post('/roles-list', [RolesController::class, 'store'])
            ->middleware('perm:roles.create');

        Route::put('/roles-list/{role}', [RolesController::class, 'update'])
            ->middleware('perm:roles.edit');

        Route::delete('/roles-list/{role}', [RolesController::class, 'destroy'])
            ->middleware('perm:roles.delete');

        // Permissions CRUD
        Route::get('/permissions', [PermissionsController::class, 'index'])
            ->middleware('perm:permisos.view');

            // Historial de Permisos
            Route::get('/permissions/{permission}/history', [PermissionsController::class, 'history'])
                ->middleware('perm:permisos.view');

        Route::post('/permissions', [PermissionsController::class, 'store'])
            ->middleware('perm:permisos.create');

        Route::put('/permissions/{permission}', [PermissionsController::class, 'update'])
            ->middleware('perm:permisos.edit');

        Route::delete('/permissions/{permission}', [PermissionsController::class, 'destroy'])
            ->middleware('perm:permisos.delete');

        // Role <-> Permissions
        Route::get('/roles/{role}/permissions', [RolePermissionsController::class, 'show'])
            ->middleware('perm:roles.edit');

        Route::put('/roles/{role}/permissions', [RolePermissionsController::class, 'update'])
            ->middleware('perm:roles.edit');

        // Empresas
        Route::get('/empresas', [EmpresasController::class, 'index'])
            ->middleware('perm:empresas.view');

            // Historial de Empresas
            Route::get('/empresas/{empresa}/history', [EmpresasController::class, 'history'])
                ->middleware('perm:empresas.view');

        Route::post('/empresas', [EmpresasController::class, 'store'])
            ->middleware('perm:empresas.create');

        Route::put('/empresas/{empresa}', [EmpresasController::class, 'update'])
            ->middleware('perm:empresas.edit');

        Route::delete('/empresas/{empresa}', [EmpresasController::class, 'destroy'])
            ->middleware('perm:empresas.delete');

        // Grupos
        Route::get('/grupos', [GruposController::class, 'index'])
            ->middleware('perm:grupos.view');

            // Historial de Grupos
            Route::get('/grupos/{grupo}/history', [GruposController::class, 'history'])
                ->middleware('perm:grupos.view');

        Route::post('/grupos', [GruposController::class, 'store'])
            ->middleware('perm:grupos.create');

        Route::put('/grupos/{grupo}', [GruposController::class, 'update'])
            ->middleware('perm:grupos.edit');

        Route::delete('/grupos/{grupo}', [GruposController::class, 'destroy'])
            ->middleware('perm:grupos.delete');

        // Unidades de servicio
        Route::get('/unidades-servicio', [UnidadesServicioController::class, 'index'])
            ->middleware('perm:unidades_servicio.view');

            // Historial de Unidades de Servicio
            Route::get('/unidades-servicio/{unidades_servicio}/history', [UnidadesServicioController::class, 'history'])
                ->middleware('perm:unidades_servicio.view');

        Route::post('/unidades-servicio', [UnidadesServicioController::class, 'store'])
            ->middleware('perm:unidades_servicio.create');

        Route::put('/unidades-servicio/{unidades_servicio}', [UnidadesServicioController::class, 'update'])
            ->middleware('perm:unidades_servicio.edit');

        Route::delete('/unidades-servicio/{unidades_servicio}', [UnidadesServicioController::class, 'destroy'])
            ->middleware('perm:unidades_servicio.delete');

        // ---------------- FORMS ADMIN ----------------
        Route::get('/forms', [FormsController::class, 'adminIndex'])
            ->middleware('perm:formularios.admin.view');

        Route::get('/forms/{form}', [FormsController::class, 'show'])
            ->middleware('perm:formularios.admin.view');

            // Historial de Formularios Admin
            Route::get('/forms/{form}/history', [FormsController::class, 'history'])
                ->middleware('perm:formularios.admin.view');

        // Asignaciones de formularios
        Route::get('/forms/{form}/assignments', [AdminFormAssignmentsController::class, 'index'])
            ->middleware('perm:formularios.admin.assign');

        Route::post('/forms/{form}/assignments', [AdminFormAssignmentsController::class, 'store'])
            ->middleware('perm:formularios.admin.assign');

        // Publicar / Despublicar
        Route::post('/forms/{form}/publish', [FormsController::class, 'publish'])
            ->middleware('perm:formularios.admin.publish');

        Route::post('/forms/{form}/unpublish', [FormsController::class, 'unpublish'])
            ->middleware('perm:formularios.admin.publish');
    });