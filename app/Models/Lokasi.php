<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lokasi extends Model
{
    protected $table = 'lokasi';
    protected $primaryKey = 'lokasi_id';
    public $timestamps = false;

    protected $fillable = [
        'latitude',
        'longitude',
        'alamat',
    ];

    public function emergencyCases(): HasMany
    {
        return $this->hasMany(EmergencyCase::class, 'lokasi_id', 'lokasi_id');
    }

    public function resources(): HasMany
    {
        return $this->hasMany(
            Resource::class,
            'lokasi_id',
            'lokasi_id'
        );
    }
}