<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pengguna extends Authenticatable
{
    protected $table = 'pengguna';
    protected $primaryKey = 'pengguna_id';
    public $timestamps = false;

    protected $fillable = [
        'nama',
        'no_telp',
        'email',
        'password',
        'role',
        'instansi_id',
        'rumah_sakit_id',
    ];

    protected $hidden = [
        'password',
    ];

    public function emergencyCases(): HasMany
    {
        return $this->hasMany(
            EmergencyCase::class,
            'pengguna_id',
            'pengguna_id'
        );
    }

    public function instansi(): BelongsTo
    {
        return $this->belongsTo(
            Instansi::class,
            'instansi_id',
            'instansi_id'
        );
    }

    public function rumahSakit(): BelongsTo
    {
        return $this->belongsTo(
            RumahSakit::class,
            'rumah_sakit_id',
            'rumah_sakit_id'
        );
    }
}