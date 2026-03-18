<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * ✅ IMPORTANTE:
     * Si tus rutas API usan auth:sanctum (como en tu proyecto),
     * Spatie debe trabajar con el guard "sanctum" para que roles/permisos
     * se evalúen correctamente en API.
     */
    protected string $guard_name = 'sanctum';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * ✅ Esto hace que cuando regreses el user en JSON (ej. /api/me),
     * vengan también:
     *  - is_admin: true/false
     *  - roles: ["Administrador", ...]
     */
    protected $appends = [
        'is_admin',
        'roles',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * is_admin: bool
     */
    public function getIsAdminAttribute(): bool
    {
        return $this->hasRole('Administrador');
    }

    // ✅ NO usar "roles" como atributo, choca con Spatie
    public function getRolesListAttribute(): array
    {
        // Si la relación ya viene cargada (spatie roles()), úsala
        if ($this->relationLoaded('roles')) {
            return $this->getRelation('roles')->pluck('name')->values()->all();
        }

        // Si no viene cargada, Spatie lo resuelve bien
        return $this->getRoleNames()->values()->all();
    }

    public function empresas()
    {
        return $this->belongsToMany(\App\Models\Empresa::class, 'empresa_user')
            ->withPivot(['principal'])
            ->withTimestamps();
    }

    public function grupos()
    {
        return $this->belongsToMany(\App\Models\Grupo::class, 'grupo_user')->withTimestamps();
    }

    public function unidadesServicio()
    {
        return $this->belongsToMany(
            \App\Models\UnidadServicio::class,
            'unidad_servicio_user',
            'user_id',
            'unidad_servicio_id'
        );
    }

    public function assignedForms()
    {
        return $this->belongsToMany(Form::class, 'form_user')->withTimestamps();
    }
}