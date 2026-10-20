<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmergencyCase extends Model
{
    protected $table = 'emergency_case';
    protected $primaryKey = 'case_id';
    public $timestamps = false;

    protected $fillable = [
        'lokasi_id',
        'pengguna_id',
        'jenis_kejadian',
        'kondisi',
        'kebutuhan',
        'waktu_laporan',
        'status_laporan',
    ];

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(
            Pengguna::class,
            'pengguna_id',
            'pengguna_id'
        );
    }

    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(
            Lokasi::class,
            'lokasi_id',
            'lokasi_id'
        );
    }

    public function statusPenanganan(): HasMany
    {
        return $this->hasMany(
            StatusPenanganan::class,
            'case_id',
            'case_id'
        );
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(
            Assignment::class,
            'case_id',
            'case_id'
        );
    }

    public function informasiKorban(): HasMany
    {
        return $this->hasMany(
            InformasiKorban::class,
            'case_id',
            'case_id'
        );
    }
}