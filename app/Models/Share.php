<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class Share extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'shareable_type',
        'shareable_id',
        'token',
        'permission',
        'expires_at',
        'shared_with_user_id',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Booted model event untuk generate token acak aman secara otomatis bila kosong.
     */
    protected static function booted(): void
    {
        static::creating(function (Share $share) {
            if (empty($share->token)) {
                $share->token = Str::random(64);
            }
        });
    }

    /**
     * Pemilik / pembuat tautan berbagi.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * User penerima bila dibagikan secara privat (nullable).
     */
    public function sharedWithUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_with_user_id');
    }

    /**
     * Model target yang dibagikan (File atau Folder).
     */
    public function shareable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Cek apakah masa berlaku tautan sudah habis.
     */
    public function isExpired(): bool
    {
        if ($this->expires_at === null) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    /**
     * Cek apakah tautan masih sah untuk diakses.
     */
    public function isValid(): bool
    {
        return $this->is_active && ! $this->isExpired();
    }

    /**
     * Cek apakah tautan bersifat publik (siapa saja yang punya link).
     */
    public function isPublic(): bool
    {
        return $this->shared_with_user_id === null;
    }

    /**
     * Cek apakah izin mengizinkan pengunduhan file.
     */
    public function canDownload(): bool
    {
        return $this->permission === 'download';
    }

    /**
     * Scope untuk mengambil tautan yang aktif dan belum kedaluwarsa.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function (Builder $q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }
}
