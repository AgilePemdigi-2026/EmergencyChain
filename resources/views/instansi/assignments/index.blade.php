
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignment Instansi - EmergencyChain</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f6fa;
            margin: 0;
            padding: 30px;
            color: #222;
        }

        .container {
            max-width: 1100px;
            margin: auto;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 22px;
            margin-bottom: 18px;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
        }

        h1, h2 {
            margin-top: 0;
        }

        .success {
            background: #dcfce7;
            color: #166534;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 16px;
        }

        .error {
            color: #b91c1c;
            margin-bottom: 12px;
        }

        .info {
            color: #666;
            font-size: 14px;
        }

        label {
            display: block;
            margin: 12px 0 6px;
            font-weight: bold;
        }

        select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box;
        }

        textarea {
            min-height: 70px;
            resize: vertical;
        }

        button {
            margin-top: 12px;
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            background: #2563eb;
            color: white;
            cursor: pointer;
        }

        button:hover {
            background: #1d4ed8;
        }

        button:disabled {
            background: #999;
            cursor: not-allowed;
        }

        .completed {
            color: #15803d;
            font-weight: bold;
        }
    </style>
</head>

<body>
<div class="container">

    <h1>Assignment Instansi</h1>

    <p>
        Instansi:
        <strong>
            {{ auth()->user()->instansi?->nama_instansi ?? 'Tidak diketahui' }}
        </strong>
    </p>

    <p class="info">
        Daftar penugasan Resource yang dimiliki oleh instansi Anda.
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

    @forelse($assignments as $assignment)

        @php
            $latestStatus = $assignment->statusPenanganan
                ->sortByDesc('waktu_update')
                ->first();
        @endphp

        <div class="card">

            <h2>
                Emergency Case #{{ $assignment->case_id }}
            </h2>

            <p>
                <strong>Jenis kejadian:</strong>
                {{ $assignment->emergencyCase->jenis_kejadian }}
            </p>

            <p>
                <strong>Resource:</strong>
                {{ $assignment->resource->nama_resource }}
            </p>

            <p>
                <strong>Lokasi kejadian:</strong>
                {{ $assignment->emergencyCase->lokasi?->alamat ?? 'Tidak tersedia' }}
            </p>

            <p>
                <strong>Lokasi pangkalan:</strong>
                {{ $assignment->resource->lokasiPangkalan?->alamat ?? 'Tidak tersedia' }}
            </p>

            <p>
                <strong>Status Assignment:</strong>
                {{ ucfirst($assignment->status_assign) }}
            </p>

            <p>
                <strong>Status Penanganan:</strong>
                {{ $latestStatus?->status ?? 'Belum ada pembaruan' }}
            </p>

            @if($latestStatus)
                <p class="info">
                    Pembaruan terakhir:
                    {{ $latestStatus->waktu_update }}
                </p>

                <p>
                    {{ $latestStatus->keterangan }}
                </p>
            @endif

            @if($assignment->status_assign !== 'selesai')

                <form
                    method="POST"
                    action="{{ route(
                        'instansi.assignments.status.update',
                        $assignment->assignment_id
                    ) }}"
                >
                    @csrf
                    @method('PATCH')

                    <label>Status Penanganan</label>

                    <select name="status" required>
                        <option value="menuju_lokasi">
                            Menuju Lokasi
                        </option>

                        <option value="dalam_penanganan">
                            Dalam Penanganan
                        </option>

                        <option value="selesai">
                            Selesai
                        </option>
                    </select>

                    <label>Keterangan</label>

                    <textarea
                        name="keterangan"
                        placeholder="Masukkan perkembangan penanganan..."
                        required
                    ></textarea>

                    <button type="submit">
                        Perbarui Status
                    </button>
                </form>

            @else

                <p class="completed">
                    Penanganan selesai.
                </p>

            @endif

        </div>

    @empty

        <div class="card">
            Belum ada Assignment untuk instansi Anda.
        </div>

    @endforelse

    {{ $assignments->links() }}

</div>
</body>
</html>
