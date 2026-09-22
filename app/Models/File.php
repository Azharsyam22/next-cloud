<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'folder_id',
        'original_name',
        'stored_name',
        'storage_path',
        'mime_type',
        'size',
        'thumbnail_path',
        'is_favorite',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'is_favorite' => 'boolean',
        ];
    }

    /**
     * Pemilik file.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Folder tempat file tersimpan (bisa null jika di root).
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * Tautan berbagi yang terkait dengan file ini (polymorphic).
     */
    public function shares(): MorphMany
    {
        return $this->morphMany(Share::class, 'shareable');
    }

    /**
     * Accessor untuk format ukuran file yang mudah dibaca (KB, MB, GB).
     */
    public function formattedSize(): Attribute
    {
        return Attribute::make(
            get: function () {
                $bytes = $this->size;
                $units = ['B', 'KB', 'MB', 'GB', 'TB'];
                $i = 0;

                while ($bytes >= 1024 && $i < count($units) - 1) {
                    $bytes /= 1024;
                    $i++;
                }

                return round($bytes, 2).' '.$units[$i];
            }
        );
    }

    /**
     * Mengambil ekstensi dari nama file asli.
     */
    public function extension(): Attribute
    {
        return Attribute::make(
            get: fn () => pathinfo($this->original_name, PATHINFO_EXTENSION)
        );
    }

    /**
     * Cek apakah file merupakan gambar.
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Cek apakah file merupakan dokumen PDF.
     */
    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    /**
     * Cek apakah file merupakan video.
     */
    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    /**
     * Cek apakah file merupakan audio.
     */
    public function isAudio(): bool
    {
        return str_starts_with($this->mime_type, 'audio/');
    }

    /**
     * Cek apakah file merupakan arsip zip/tar/rar.
     */
    public function isArchive(): bool
    {
        return in_array($this->mime_type, [
            'application/zip',
            'application/x-rar-compressed',
            'application/x-tar',
            'application/x-7z-compressed',
        ], true);
    }
}
