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

    protected $fillable = [
        'instansi_id',
        'lokasi_id',
        'nama_resource',
        'jenis_resource',
        'lokasi',
        'status_ketersediaan',
    ];

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