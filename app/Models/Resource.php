<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Resource extends Model
{
    protected $table = 'resource';
    protected $primaryKey = 'resource_id';
    public $timestamps = false;

    public const STATUS_TERSEDIA = 'tersedia';
    public const STATUS_DITUGASKAN = 'sedang_ditugaskan';
    public const STATUS_TIDAK_TERSEDIA = 'tidak_tersedia';

    protected $fillable = [
        'instansi_id',
        'lokasi_id',
        'nama_resource',
        'jenis_resource',
        'kapasitas_personel',
        'lokasi',
        'status_ketersediaan',
    ];

    protected $casts = [
        'kapasitas_personel' => 'integer',
    ];

    public static function statusOptions(): array
    {
        return [
            self::STATUS_TERSEDIA => 'Tersedia',
            self::STATUS_DITUGASKAN => 'Sedang Ditugaskan',
            self::STATUS_TIDAK_TERSEDIA => 'Tidak Tersedia',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusOptions()[$this->status_ketersediaan]
            ?? $this->status_ketersediaan;
    }

    public function instansi(): BelongsTo
    {
        return $this->belongsTo(
            Instansi::class,
            'instansi_id',
            'instansi_id'
        );
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(
            Assignment::class,
            'resource_id',
            'resource_id'
        );
    }

    public function lokasiPangkalan(): BelongsTo
    {
        return $this->belongsTo(
            Lokasi::class,
            'lokasi_id',
            'lokasi_id'
        );
    }
}