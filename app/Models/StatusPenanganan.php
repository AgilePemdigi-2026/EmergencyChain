<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatusPenanganan extends Model
{
    protected $table = 'status_penanganan';
    protected $primaryKey = 'status_id';
    public $timestamps = false;

    protected $fillable = [
        'assignment_id',
        'case_id',
        'status',
        'waktu_update',
        'keterangan',
    ];

    public function emergencyCase(): BelongsTo
    {
        return $this->belongsTo(
            EmergencyCase::class,
            'case_id',
            'case_id'
        );
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(
            Assignment::class,
            'assignment_id',
            'assignment_id'
        );
    }
}
