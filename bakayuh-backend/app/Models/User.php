<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'satker_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    public function satker(): BelongsTo
    {
        return $this->belongsTo(SatuanKerja::class, 'satker_id');
    }

    public function realisasiIku(): HasMany
    {
        return $this->hasMany(RealisasiIku::class, 'created_by');
    }

    public function realisasiRenaksi(): HasMany
    {
        return $this->hasMany(RealisasiRenaksi::class, 'created_by');
    }

    public function evaluasiSakip(): HasMany
    {
        return $this->hasMany(EvaluasiSakip::class, 'created_by');
    }

    public function rbCatatanVerifikasi(): HasMany
    {
        return $this->hasMany(RbCatatanVerifikasi::class, 'sender_id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    public function isAdminKanwil(): bool
    {
        return $this->role === UserRole::AdminKanwil;
    }

    public function isOperatorSatker(): bool
    {
        return $this->role === UserRole::OperatorSatker;
    }

    public function isViewer(): bool
    {
        return $this->role === UserRole::Viewer;
    }

    public function canVerify(): bool
    {
        return in_array($this->role, [UserRole::SuperAdmin, UserRole::AdminKanwil]);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
