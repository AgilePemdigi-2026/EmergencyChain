<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InformasiKorban extends Model
{
    protected $table = 'informasi_korban';
    protected $primaryKey = 'informasi_id';
    public $timestamps = false;

    protected $fillable = [
        'rumah_sakit_id',
        'case_id',
        'kondisi_korban',
        'waktu_rujukan',
        'status_penerimaan',
    ];

    public function rumahSakit(): BelongsTo
    {
        return $this->belongsTo(
            RumahSakit::class,
            'rumah_sakit_id',
            'rumah_sakit_id'
        );
    }

    public function emergencyCase(): BelongsTo
    {
        return $this->belongsTo(
            EmergencyCase::class,
            'case_id',
            'case_id'
        );
    }

    public function treatmentHistories(): HasMany
    {
        return $this->hasMany(
            HospitalTreatmentHistory::class,
            'informasi_id',
            'informasi_id'
        );
    }

    public function latestTreatmentHistory(): HasOne
    {
        return $this->hasOne(
            HospitalTreatmentHistory::class,
            'informasi_id',
            'informasi_id'
        )->latestOfMany('id');
    }
}
