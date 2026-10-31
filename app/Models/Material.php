<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'type',
        'file_path',
        'original_name',
        'file_size',
        'mime_type',
        'external_url',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Material $material) {
            if ($material->file_path) {
                if (\Illuminate\Support\Facades\Storage::disk('local')->exists($material->file_path)) {
                    \Illuminate\Support\Facades\Storage::disk('local')->delete($material->file_path);
                }
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($material->file_path)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($material->file_path);
                }
            }
        });
    }

    public function getFormattedFileSizeAttribute(): ?string
    {
        if ($this->file_size === null) {
            return null;
        }

        $bytes = (int) $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    // Relasi tambahan (tidak diwajibkan eksplisit di Bagian 4.3,
    // tapi diperlukan agar akses dua arah Course <-> Material konsisten)

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

