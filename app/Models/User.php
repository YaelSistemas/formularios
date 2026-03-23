<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected string $guard_name = 'sanctum';

    protected $fillable = [
        'name',
        'email',
        'password',
        'activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'is_admin',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    public function getIsAdminAttribute(): bool
    {
        return $this->hasRole('Administrador');
    }

    public function getRolesListAttribute(): array
    {
        if ($this->relationLoaded('roles')) {
            return $this->getRelation('roles')->pluck('name')->values()->all();
        }

        return $this->getRoleNames()->values()->all();
    }

    public function empresas(): BelongsToMany
    {
        return $this->belongsToMany(Empresa::class, 'empresa_user')
            ->withPivot(['principal'])
            ->withTimestamps();
    }

    public function grupos(): BelongsToMany
    {
        return $this->belongsToMany(Grupo::class, 'grupo_user')
            ->withTimestamps();
    }

    public function unidadesServicio(): BelongsToMany
    {
        return $this->belongsToMany(
            UnidadServicio::class,
            'unidad_servicio_user',
            'user_id',
            'unidad_servicio_id'
        )->withTimestamps();
    }

    public function assignedForms(): BelongsToMany
    {
        return $this->belongsToMany(Form::class, 'form_user')
            ->withTimestamps();
    }
}