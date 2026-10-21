<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RumahSakit extends Model
{
    protected $table = 'rumah_sakit';
    protected $primaryKey = 'rumah_sakit_id';
    public $timestamps = false;

    protected $fillable = [
        'nama_rs',
        'alamat',
        'latitude',
        'longitude',
        'kapasitas',
        'kapasitas_tersedia',
        'status',
    ];

    public function informasiKorban(): HasMany
    {
        return $this->hasMany(
            InformasiKorban::class,
            'rumah_sakit_id',
            'rumah_sakit_id'
        );
    }
}