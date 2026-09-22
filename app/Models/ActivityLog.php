<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLog extends Model
{
    use HasFactory;

    /**
     * Nonaktifkan timestamp updated_at bawaan.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'description',
        'ip_address',
        'user_agent',
        'meta',
        'created_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * User yang melakukan aksi (bisa null untuk aksi sistem).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Objek subjek yang dikenai aksi (File, Folder, Share, User, dll).
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Helper praktis untuk mencatat log aktivitas sistem secara otomatis.
     *
     * @param  array<string, mixed>  $meta
     */
    public static function record(
        string $action,
        ?Model $subject = null,
        ?string $description = null,
        array $meta = [],
        ?int $userId = null
    ): self {
        return static::create([
            'user_id' => $userId ?? Auth::id(),
            'action' => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->getKey(),
            'description' => $description,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'meta' => empty($meta) ? null : $meta,
            'created_at' => now(),
        ]);
    }
}
