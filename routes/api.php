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
use App\Http\Controllers\Api\AsistenciasController;
use App\Http\Controllers\Api\Admin\TurnosController;
use App\Http\Controllers\Api\KioscoAsistenciasController;
use App\Http\Controllers\Api\Admin\AsistenciasAdminController;
use App\Http\Controllers\Api\Admin\AsistenciaIncidenciasController;
use App\Http\Controllers\Api\AdminFormVersionsController;

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

    // ---------------- CONTROL DE ACCESO / ASISTENCIAS ----------------

    Route::get('/asistencias', [AsistenciasController::class, 'index'])
        ->middleware('perm:asistencias.view');
    
    Route::post('/asistencias/entrada', [AsistenciasController::class, 'entrada'])
        ->middleware('perm:asistencias.create');
    
    Route::post('/asistencias/salida', [AsistenciasController::class, 'salida'])
        ->middleware('perm:asistencias.create');
    
    Route::post('/asistencias/comida/inicio', [AsistenciasController::class, 'inicioComida'])
        ->middleware('perm:asistencias.create');
    
    Route::post('/asistencias/comida/fin', [AsistenciasController::class, 'finComida'])
        ->middleware('perm:asistencias.create');
    
    Route::get('/asistencias/kiosco', [KioscoAsistenciasController::class, 'index'])
        ->middleware('perm:asistencias.kiosco');
    
    Route::post('/asistencias/kiosco/{user}/entrada', [KioscoAsistenciasController::class, 'entrada'])
        ->middleware('perm:asistencias.kiosco');
    
    Route::post('/asistencias/kiosco/{user}/comida/inicio', [KioscoAsistenciasController::class, 'inicioComida'])
        ->middleware('perm:asistencias.kiosco');
    
    Route::post('/asistencias/kiosco/{user}/comida/fin', [KioscoAsistenciasController::class, 'finComida'])
        ->middleware('perm:asistencias.kiosco');
    
    Route::post('/asistencias/kiosco/{user}/salida', [KioscoAsistenciasController::class, 'salida'])
        ->middleware('perm:asistencias.kiosco');

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

        // ---------------- CONTROL DE ACCESO / TURNOS ----------------

        Route::get('/turnos', [TurnosController::class, 'index'])
            ->middleware('perm:turnos.view');
        
        // Historial de Turnos
        Route::get('/turnos/{turno}/history', [TurnosController::class, 'history'])
            ->middleware('perm:turnos.view');
        
        Route::get('/turnos/{turno}', [TurnosController::class, 'show'])
            ->middleware('perm:turnos.view');

        Route::post('/turnos', [TurnosController::class, 'store'])
            ->middleware('perm:turnos.create');

        Route::put('/turnos/{turno}', [TurnosController::class, 'update'])
            ->middleware('perm:turnos.edit');

        Route::delete('/turnos/{turno}', [TurnosController::class, 'destroy'])
            ->middleware('perm:turnos.delete');

        // ---------------- GESTIÓN DE ASISTENCIAS ----------------
        
        Route::get('/asistencias', [AsistenciasAdminController::class, 'index'])
            ->middleware('perm:asistencias.view');

        // Exportaciones
        Route::get('/asistencias/export/excel', [AsistenciasAdminController::class, 'exportExcel'])
            ->middleware('perm:asistencias.view');
        
        Route::get('/asistencias/export/pdf', [AsistenciasAdminController::class, 'exportPdf'])
            ->middleware('perm:asistencias.view');

        Route::get('/asistencias/history', [AsistenciasAdminController::class, 'generalHistory'])
            ->middleware('perm:asistencias.view');
        
        Route::get('/asistencias/{asistencia}', [AsistenciasAdminController::class, 'show'])
            ->middleware('perm:asistencias.view');
        
        // Historial de Asistencias
        Route::get('/asistencias/{asistencia}/history', [AsistenciasAdminController::class, 'history'])
            ->middleware('perm:asistencias.view');
        
        Route::put('/asistencias/{asistencia}', [AsistenciasAdminController::class, 'update'])
            ->middleware('perm:asistencias.edit');
        
        Route::delete('/asistencias/{asistencia}', [AsistenciasAdminController::class, 'destroy'])
            ->middleware('perm:asistencias.delete');

        // ---------------- INCIDENCIAS DE ASISTENCIA ----------------

        Route::get('/asistencia-incidencias', [AsistenciaIncidenciasController::class, 'index'])
            ->middleware('perm:asistencias.incidencias.view');
        
        // Exportaciones
        Route::get('/asistencia-incidencias/export/excel', [AsistenciaIncidenciasController::class, 'exportExcel'])
            ->middleware('perm:asistencias.incidencias.view');
        
        Route::get('/asistencia-incidencias/export/pdf', [AsistenciaIncidenciasController::class, 'exportPdf'])
            ->middleware('perm:asistencias.incidencias.view');

        /*
         * Historial general.
         *
         * IMPORTANTE:
         * Esta ruta debe ir antes de
         * /asistencia-incidencias/{grupoUuid}
         * para que "history" no sea interpretado
         * como un grupo_uuid.
         */
        Route::get('/asistencia-incidencias/history', [AsistenciaIncidenciasController::class, 'generalHistory'])
            ->middleware('perm:asistencias.incidencias.view');
        
        Route::post('/asistencia-incidencias', [AsistenciaIncidenciasController::class, 'store'])
            ->middleware('perm:asistencias.incidencias.create');
        
        /*
         * Historial individual de una incidencia.
         */
        Route::get('/asistencia-incidencias/{grupoUuid}/history', [AsistenciaIncidenciasController::class, 'history'])
            ->middleware('perm:asistencias.incidencias.view');
        
        Route::get('/asistencia-incidencias/{grupoUuid}', [AsistenciaIncidenciasController::class, 'show'])
            ->middleware('perm:asistencias.incidencias.view');
        
        Route::put('/asistencia-incidencias/{grupoUuid}', [AsistenciaIncidenciasController::class, 'update'])
            ->middleware('perm:asistencias.incidencias.edit');
        
        Route::delete('/asistencia-incidencias/{grupoUuid}', [AsistenciaIncidenciasController::class, 'destroy'])
            ->middleware('perm:asistencias.incidencias.delete');

        // ---------------- FORMS ADMIN ----------------
        Route::get('/forms', [FormsController::class, 'adminIndex'])
            ->middleware('perm:formularios.admin.view');

        Route::get('/forms/{form}', [FormsController::class, 'show'])
            ->middleware('perm:formularios.admin.view');

        // Versiones del formulario
        Route::get('/forms/{form}/versions', [AdminFormVersionsController::class, 'index',])
            ->middleware('perm:formularios.admin.view');

        Route::post('/forms/{form}/versions', [AdminFormVersionsController::class, 'store',])
            ->middleware('perm:formularios.admin.view');

        // Editar fecha / descripción de versión programada
        Route::post('/forms/{form}/versions/{version}/update', [AdminFormVersionsController::class, 'update',])
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