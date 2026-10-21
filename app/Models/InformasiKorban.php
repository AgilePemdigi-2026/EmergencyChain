<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}