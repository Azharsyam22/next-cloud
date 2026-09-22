<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'account_type',
        'external_id',
        'quota_bytes',
        'used_bytes',
        'is_suspended',
        'suspended_at',
        'avatar_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'quota_bytes' => 'integer',
            'used_bytes' => 'integer',
            'is_suspended' => 'boolean',
            'suspended_at' => 'datetime',
        ];
    }

    /**
     * Relasi ke folders milik user.
     */
    public function folders(): HasMany
    {
        return $this->hasMany(Folder::class);
    }

    /**
     * Relasi ke files milik user.
     */
    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    /**
     * Relasi ke tautan share yang dibuat oleh user.
     */
    public function shares(): HasMany
    {
        return $this->hasMany(Share::class);
    }

    /**
     * Relasi ke file/folder yang dibagikan secara privat ke user ini.
     */
    public function sharedWithMe(): HasMany
    {
        return $this->hasMany(Share::class, 'shared_with_user_id');
    }

    /**
     * Relasi ke log aktivitas user.
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Cek apakah user berasal dari SSO akademik.
     */
    public function isAcademic(): bool
    {
        return $this->account_type === 'academic';
    }

    /**
     * Cek apakah user adalah pendaftar mandiri publik.
     */
    public function isPublic(): bool
    {
        return $this->account_type === 'public';
    }

    /**
     * Cek apakah sisa kuota user mencukupi untuk file dengan ukuran tertentu.
     */
    public function hasQuotaFor(int $bytes): bool
    {
        return ($this->used_bytes + $bytes) <= $this->quota_bytes;
    }

    /**
     * Menghitung sisa kuota yang tersedia dalam byte.
     */
    public function remainingQuotaBytes(): int
    {
        return max(0, $this->quota_bytes - $this->used_bytes);
    }

    /**
     * Menghitung persentase pemakaian kuota.
     */
    public function quotaUsagePercentage(): float
    {
        if ($this->quota_bytes <= 0) {
            return 0.0;
        }

        return round(($this->used_bytes / $this->quota_bytes) * 100, 2);
    }

    /**
     * Cek apakah akun ditangguhkan.
     */
    public function isSuspended(): bool
    {
        return (bool) $this->is_suspended;
    }

    /**
     * Tangguhkan akun user.
     */
    public function suspend(): void
    {
        $this->update([
            'is_suspended' => true,
            'suspended_at' => now(),
        ]);
    }

    /**
     * Aktifkan kembali akun user.
     */
    public function activate(): void
    {
        $this->update([
            'is_suspended' => false,
            'suspended_at' => null,
        ]);
    }
}
