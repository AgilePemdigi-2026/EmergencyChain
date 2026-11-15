<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HospitalTreatmentHistory extends Model
{
    protected $table = 'hospital_treatment_histories';

    protected $primaryKey = 'id';

    protected $fillable = [
        'informasi_id',
        'status',
        'keterangan',
        'diubah_oleh',
    ];

    public function informasiKorban(): BelongsTo
    {
        return $this->belongsTo(
            InformasiKorban::class,
            'informasi_id',
            'informasi_id'
        );
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(
            Pengguna::class,
            'diubah_oleh',
            'pengguna_id'
        );
    }
}
