<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Instansi extends Model
{
    protected $table = 'instansi';
    protected $primaryKey = 'instansi_id';
    public $timestamps = false;

    protected $fillable = [
        'nama_instansi',
        'jenis_instansi',
        'alamat',
        'no_telp',
        'lokasi_id',
    ];

    public function resources(): HasMany
    {
        return $this->hasMany(
            Resource::class,
            'instansi_id',
            'instansi_id'
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