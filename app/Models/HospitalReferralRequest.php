<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HospitalReferralRequest extends Model
{
    protected $table = 'hospital_referral_requests';

    protected $fillable = [
        'hospital_referral_id',
        'rumah_sakit_id',
        'attempt_order',
        'status',
        'requested_at',
        'expires_at',
        'responded_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'expires_at' => 'datetime',
            'responded_at' => 'datetime',
        ];
    }

    public function referral(): BelongsTo
    {
        return $this->belongsTo(
            HospitalReferral::class,
            'hospital_referral_id',
            'id'
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
