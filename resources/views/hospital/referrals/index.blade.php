@extends('layouts.hospital')

@section('title', 'Dashboard Rumah Sakit - EmergencyChain')

@push('styles')
<style>
    .dashboard-label {
        color: #477ce4;
        font-weight: bold;
        letter-spacing: 2px;
        margin: 0 0 10px;
    }

    .dashboard-title {
        font-size: 36px;
        font-weight: 500;
        margin: 0 0 30px;
    }

    .hospital-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 28px;
    }

    .stat-card {
        background: white;
        border: 1px solid #f0e4e4;
        border-radius: 14px;
        padding: 22px 16px;
    }

    .stat-card .label {
        color: #777985;
        font-size: 12px;
    }

    .stat-card .number {
        font-size: 28px;
        font-weight: bold;
        margin-top: 6px;
    }

    .incoming-panel {
        background: white;
        border: 1px solid #f0e4e4;
        border-radius: 16px;
        overflow: hidden;
    }

    .panel-heading {
        padding: 20px;
        border-bottom: 1px solid #eee;
    }

    .case-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        padding: 14px;
    }

    .case-card {
        border: 1px solid #f1e4e4;
        border-radius: 12px;
        padding: 16px;
    }

    .case-number {
        color: #477ce4;
        font-weight: bold;
        font-size: 13px;
    }

    .case-type {
        margin: 14px 0;
        font-size: 15px;
    }

    .case-info {
        background: #fff5f5;
        border-radius: 8px;
        padding: 12px;
        margin-top: 12px;
        font-size: 13px;
    }

    .case-actions {
        display: flex;
        gap: 8px;
        margin-top: 14px;
        align-items: center;
    }

    .case-actions form {
        display: inline;
    }

    .btn {
        padding: 10px 13px;
        border-radius: 9px;
        border: 0;
        font-weight: bold;
        cursor: pointer;
    }

    .btn-accept {
        background: #e9444b;
        color: white;
    }

    .btn-reject {
        background: #fff0f1;
        color: #dc3540;
    }

    .btn-detail {
        background: #f5f1f1;
        color: #555;
        text-decoration: none;
        display: inline-block;
    }

    .status-label {
        color: #6b7280;
        font-size: 12px;
    }

    .alert-success {
        padding: 12px;
        background: #dcfce7;
        border-radius: 8px;
        margin-bottom: 16px;
    }

    .alert-error {
        padding: 12px;
        background: #fee2e2;
        border-radius: 8px;
        margin-bottom: 16px;
    }

    @media (max-width: 1050px) {
        .hospital-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .case-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 550px) {
        .hospital-stats {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<p class="dashboard-label">
    {{ strtoupper(auth()->user()->rumahSakit?->nama_rs ?? 'RUMAH SAKIT') }}
</p>

<h1 class="dashboard-title">Dashboard Rumah Sakit</h1>

@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert-error">
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

<div class="hospital-stats">
    <div class="stat-card">
        <div class="label">Incoming Cases</div>
        <div class="number">{{ $stats['incoming'] }}</div>
    </div>

    <div class="stat-card">
        <div class="label">Menunggu Penerimaan</div>
        <div class="number">{{ $stats['waiting'] }}</div>
    </div>

    <div class="stat-card">
        <div class="label">Diterima Hari Ini</div>
        <div class="number">{{ $stats['accepted_today'] }}</div>
    </div>

    <div class="stat-card">
        <div class="label">Ditolak</div>
        <div class="number">{{ $stats['rejected'] }}</div>
    </div>
</div>

<section class="incoming-panel">
    <div class="panel-heading">
        <strong>Incoming Emergency Cases</strong>
        <p class="status-label">
            Kasus rujukan yang membutuhkan konfirmasi penerimaan
        </p>
    </div>

    <div class="case-grid">
        @forelse($requests as $referralRequest)
            @php
                $isActive = $referralRequest->status === 'menunggu'
                    && $referralRequest->expires_at->isFuture();
            @endphp

            <article class="case-card">
                <div class="case-number">
                    EC-{{ str_pad(
                        $referralRequest->referral->case_id,
                        4,
                        '0',
                        STR_PAD_LEFT
                    ) }}
                </div>

                <p class="case-type">
                    Permintaan Rujukan #{{ $referralRequest->id }}
                </p>

                <p class="status-label">
                    Status: {{ ucfirst($referralRequest->status) }}
                </p>

                <div class="case-info">
                    <strong>Permintaan Rumah Sakit</strong>
                    <p>
                        Percobaan ke-{{ $referralRequest->attempt_order }}
                    </p>
                    <p>
                        Batas respons:
                        {{ $referralRequest->expires_at->format('H:i:s') }}
                    </p>
                </div>
                <a href="{{ route('hospital.referrals.show', $referralRequest->id) }}"
                class="btn btn-detail">
                Lihat Detail
            </a>

                @if($isActive)
                    <div class="case-actions">
                        <form method="POST"
                              action="{{ route(
                                  'hospital.referrals.respond',
                                  $referralRequest->id
                              ) }}">
                            @csrf
                            <input type="hidden"
                                   name="decision"
                                   value="ditolak">
                            <button type="submit" class="btn btn-reject">
                                Tolak
                            </button>
                        </form>

                        <form method="POST"
                              action="{{ route(
                                  'hospital.referrals.respond',
                                  $referralRequest->id
                              ) }}">
                            @csrf
                            <input type="hidden"
                                   name="decision"
                                   value="diterima">
                            <button type="submit" class="btn btn-accept">
                                Terima
                            </button>
                        </form>
                    </div>
                @else
                    <p class="status-label">
                        Permintaan sudah tidak aktif.
                    </p>
                @endif
            </article>
        @empty
            <p style="padding:16px">
                Belum ada permintaan rujukan.
            </p>
        @endforelse
    </div>

    <div style="padding:16px">
        {{ $requests->links() }}
    </div>
</section>
@endsection
