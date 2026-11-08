<?php

namespace App\Jobs;

use App\Services\Hospital\HospitalReferralService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessHospitalReferralTimeout implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $requestId
    ) {}

    public function handle(
        HospitalReferralService $service
    ): void {
        $service->processTimeout($this->requestId);
    }
}
