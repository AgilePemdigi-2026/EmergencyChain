<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Assignment extends Model
{
    protected $table = 'assignment';
    protected $primaryKey = 'assignment_id';
    public $timestamps = false;

    protected $fillable = [
        'resource_id',
        'case_id',
        'waktu_assign',
        'status_assign',
        'waktu_konfirmasi',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(
            Resource::class,
            'resource_id',
            'resource_id'
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