<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HospitalReferral extends Model
{
    protected $table = 'hospital_referrals';

    protected $fillable = [
        'case_id',
        'status',
        'accepted_hospital_id',
        'failure_reason',
    ];

    public function emergencyCase(): BelongsTo
    {
        return $this->belongsTo(
            EmergencyCase::class,
            'case_id',
            'case_id'
        );
    }

    public function acceptedHospital(): BelongsTo
    {
        return $this->belongsTo(
            RumahSakit::class,
            'accepted_hospital_id',
            'rumah_sakit_id'
        );
    }

    public function requests(): HasMany
    {
        return $this->hasMany(
            HospitalReferralRequest::class,
            'hospital_referral_id',
            'id'
        );
    }
}
