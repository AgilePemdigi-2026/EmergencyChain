<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">
    <title>Permintaan Rujukan - EmergencyChain</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            margin: 0;
            padding: 24px;
            color: #1f2937;
        }
        .container {
            max-width: 1000px;
            margin: auto;
        }
        .card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: 0 2px 10px #0000000d;
        }
        .muted { color: #6b7280; }
        .success {
            background: #dcfce7;
            padding: 12px;
            border-radius: 8px;
        }
        .error {
            background: #fee2e2;
            padding: 12px;
            border-radius: 8px;
        }
        .actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 16px;
        }
        button {
            border: 0;
            border-radius: 8px;
            padding: 10px 16px;
            cursor: pointer;
            font-weight: bold;
        }
        .accept {
            background: #16a34a;
            color: white;
        }
        .reject {
            background: #dc2626;
            color: white;
        }
        input {
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            width: 260px;
            max-width: 100%;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Permintaan Rujukan Rumah Sakit</h1>
    <p class="muted">
        EmergencyChain - Hospital Matching
    </p>

    @if(session('success'))
        <div class="success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="error">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @forelse($requests as $referralRequest)
        <div class="card">
            <h2>
                Permintaan #{{ $referralRequest->id }}
            </h2>

            <p>
                <strong>Status:</strong>
                {{ ucfirst($referralRequest->status) }}
            </p>

            <p>
                <strong>Urutan percobaan:</strong>
                {{ $referralRequest->attempt_order }}
            </p>

            <p>
                <strong>Dikirim:</strong>
                {{ $referralRequest->requested_at }}
            </p>

            <p>
                <strong>Batas respons:</strong>
                {{ $referralRequest->expires_at }}
            </p>

            @if(
                $referralRequest->status === 'menunggu'
                && $referralRequest->expires_at->isFuture()
            )
                <div class="actions">
                    <form method="POST"
                          action="{{ route(
                              'hospital.referrals.respond',
                              $referralRequest->id
                          ) }}">
                        @csrf
                        <input type="hidden"
                               name="decision"
                               value="diterima">

                        <button class="accept" type="submit">
                            Terima Pasien
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
                               value="ditolak">

                        <input type="text"
                               name="reason"
                               placeholder="Alasan penolakan">

                        <button class="reject" type="submit">
                            Tolak
                        </button>
                    </form>
                </div>
            @elseif(
                $referralRequest->status === 'menunggu'
            )
                <p class="muted">
                    Batas waktu respons telah habis.
                </p>
            @endif
        </div>
    @empty
        <div class="card">
            <p>Belum ada permintaan rujukan.</p>
        </div>
    @endforelse

    {{ $requests->links() }}
</div>
</body>
</html>
