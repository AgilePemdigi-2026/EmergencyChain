@extends('layouts.hospital')

@section('title', 'Detail Permintaan Rujukan - EmergencyChain')

@push('styles')
<style>
    .detail-container { max-width: 1050px; margin: auto; }
    .back-link { color: #e54850; text-decoration: none; font-weight: 600; }
    .detail-header { margin: 24px 0; }
    .detail-header h1 { font-size: 30px; margin: 8px 0; }
    .detail-card {
        background: white;
        border: 1px solid #f0e2e2;
        border-radius: 16px;
        padding: 26px;
        margin-bottom: 20px;
    }
    .section-title { font-size: 18px; margin: 0 0 20px; }
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 24px;
    }
    .detail-label { font-size: 13px; color: #888995; margin-bottom: 6px; }
    .detail-value { font-weight: 600; line-height: 1.6; overflow-wrap: anywhere; }
    .status-badge {
        display: inline-block;
        padding: 7px 12px;
        border-radius: 20px;
        background: #fff0d6;
        color: #a66a00;
        font-weight: 700;
        font-size: 13px;
    }
    .action-row { display: flex; gap: 14px; flex-wrap: wrap; }
    .action-row form { flex: 1; min-width: 220px; }
    .action-row button {
        width: 100%;
        border: none;
        border-radius: 10px;
        padding: 14px;
        font-weight: 700;
        cursor: pointer;
    }
    .btn-accept { background: #e54850; color: white; }
    .btn-reject { background: #fff0f0; color: #d83b44; }
    .reason-input {
        width: 100%;
        padding: 12px;
        border: 1px solid #e3d9d9;
        border-radius: 8px;
        margin-bottom: 10px;
    }
    .alert {
        background: #fee2e2;
        color: #991b1b;
        border-radius: 10px;
        padding: 14px;
        margin-bottom: 20px;
    }
    @media (max-width: 700px) {
        .detail-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="detail-container">
    <a href="{{ route('hospital.referrals.index') }}" class="back-link">
        ← Kembali ke Dashboard
    </a>

    <div class="detail-header">
        <p style="color:#477ce4;font-weight:bold">
            HOSPITAL REFERRAL
        </p>
        <h1>Detail Permintaan Rujukan</h1>
        <p>Emergency Case #{{ $case->case_id }}</p>
    </div>

    @if($errors->any())
        <div class="alert">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="detail-card">
        <h2 class="section-title">Informasi Permintaan</h2>

        <div class="detail-grid">
            <div>
                <div class="detail-label">Status Permintaan</div>
                <span class="status-badge">
                    {{ ucfirst($referralRequest->status) }}
                </span>
            </div>

            <div>
                <div class="detail-label">Urutan Percobaan</div>
                <div class="detail-value">
                    {{ $referralRequest->attempt_order }}
                </div>
            </div>

            <div>
                <div class="detail-label">Waktu Permintaan</div>
                <div class="detail-value">
                    {{ $referralRequest->requested_at->format('d M Y, H:i:s') }}
                </div>
            </div>

            <div>
                <div class="detail-label">Batas Respons</div>
                <div class="detail-value">
                    {{ $referralRequest->expires_at->format('d M Y, H:i:s') }}
                </div>
            </div>
        </div>
    </div>

    <div class="detail-card">
        <h2 class="section-title">Informasi Kejadian</h2>

        <div class="detail-grid">
            <div>
                <div class="detail-label">Jenis Kejadian</div>
                <div class="detail-value">
                    {{ $case->jenis_kejadian }}
                </div>
            </div>

            <div>
                <div class="detail-label">Waktu Laporan</div>
                <div class="detail-value">
                    {{ $case->waktu_laporan }}
                </div>
            </div>

            <div>
                <div class="detail-label">Kondisi Korban</div>
                <div class="detail-value">
                    {{ $case->kondisi ?: '-' }}
                </div>
            </div>

            <div>
                <div class="detail-label">Kebutuhan Penanganan</div>
                <div class="detail-value">
                    {{ $case->kebutuhan ?: '-' }}
                </div>
            </div>

            <div>
                <div class="detail-label">Lokasi Kejadian</div>
                <div class="detail-value">
                    {{ $case->lokasi?->alamat ?? 'Lokasi belum tersedia' }}
                </div>
            </div>
        </div>
    </div>

    
{{-- ALASAN PENOLAKAN --}}
@if($referralRequest->status === 'ditolak')
    <div class="detail-card">
        <h2 class="section-title">
            Alasan Penolakan
        </h2>

        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
            <p class="text-sm font-semibold text-red-700">
                Permintaan rujukan ditolak oleh Rumah Sakit.
            </p>

            <p class="mt-2 text-sm text-slate-700">
                {{ $referralRequest->rejection_reason
                    ?: 'Tidak ada alasan penolakan yang dicantumkan.' }}
            </p>
        </div>
    </div>
@endif

    @if(
        $referralRequest->status === 'menunggu'
        && $referralRequest->referral->status === 'mencari'
        && $referralRequest->expires_at->isFuture()
    )
        <div class="detail-card">
            <h2 class="section-title">Respons Rumah Sakit</h2>

            <div class="action-row">
                <form method="POST"
                      action="{{ route('hospital.referrals.respond', $referralRequest->id) }}">
                    @csrf
                    <input type="hidden" name="decision" value="diterima">
                    <button type="submit" class="btn-accept">
                        Terima Permintaan
                    </button>
                </form>

                <form method="POST"
                      action="{{ route('hospital.referrals.respond', $referralRequest->id) }}">
                    @csrf
                    <input type="hidden" name="decision" value="ditolak">
                    <input class="reason-input" type="text" name="reason"
                           placeholder="Alasan penolakan (opsional)"
                           maxlength="1000">
                    <button type="submit" class="btn-reject">
                        Tolak Permintaan
                    </button>
                </form>
            </div>
        </div>
    @else
        <div class="detail-card">
            <strong>Permintaan sudah tidak aktif.</strong>
            <p>Tidak ada respons lanjutan yang dapat diberikan.</p>
        </div>
    @endif
</div>
@endsection
