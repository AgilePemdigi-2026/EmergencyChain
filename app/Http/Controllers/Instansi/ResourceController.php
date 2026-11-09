<?php

namespace App\Http\Controllers\Instansi;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Services\Matching\ResourceMatchingService;

class ResourceController extends Controller
{
    public function __construct(
        private ResourceMatchingService $matchingService
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $baseQuery = Resource::query()
            ->where('instansi_id', $user->instansi_id);

        $summary = [
            'total' => (clone $baseQuery)->count(),

            'tersedia' => (clone $baseQuery)
                ->where(
                    'status_ketersediaan',
                    Resource::STATUS_TERSEDIA
                )
                ->count(),

            'ditugaskan' => (clone $baseQuery)
                ->where(
                    'status_ketersediaan',
                    Resource::STATUS_DITUGASKAN
                )
                ->count(),

            'tidak_tersedia' => (clone $baseQuery)
                ->where(
                    'status_ketersediaan',
                    Resource::STATUS_TIDAK_TERSEDIA
                )
                ->count(),
        ];

        $query = clone $baseQuery;

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('nama_resource', 'ilike', "%{$search}%")
                    ->orWhere('lokasi', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('jenis')) {
            $query->where(
                'jenis_resource',
                $request->string('jenis')->toString()
            );
        }

        if ($request->filled('status')) {
            $query->where(
                'status_ketersediaan',
                $request->string('status')->toString()
            );
        }

        $resources = $query
            ->orderBy('nama_resource')
            ->paginate(10)
            ->withQueryString();

        $jenisOptions = (clone $baseQuery)
            ->select('jenis_resource')
            ->whereNotNull('jenis_resource')
            ->distinct()
            ->orderBy('jenis_resource')
            ->pluck('jenis_resource');

        return view('instansi.units.index', [
            'resources' => $resources,
            'summary' => $summary,
            'jenisOptions' => $jenisOptions,
            'statusOptions' => Resource::statusOptions(),
        ]);
    }



    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_resource' => [
                'required',
                'string',
                'max:100',
            ],

            'jenis_resource' => [
                'required',
                'string',
                'max:50',
            ],

            'kapasitas_personel' => [
                'required',
                'integer',
                'min:1',
                'max:999',
            ],

            'status_ketersediaan' => [
                'required',
                Rule::in([
                    Resource::STATUS_TERSEDIA,
                    Resource::STATUS_TIDAK_TERSEDIA,
                ]),
            ],
        ]);

        $instansi = $request->user()
            ->instansi()
            ->with('lokasiPangkalan')
            ->first();

        if (!$instansi || !$instansi->lokasiPangkalan) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Lokasi pangkalan Instansi belum tersedia. Unit belum dapat ditambahkan.'
                );
        }

        $lokasi = $instansi->lokasiPangkalan;

        Resource::create([
            'instansi_id' => $instansi->instansi_id,
            'lokasi_id' => $lokasi->lokasi_id,
            'nama_resource' => $validated['nama_resource'],
            'jenis_resource' => $validated['jenis_resource'],
            'kapasitas_personel' => $validated['kapasitas_personel'],
            'lokasi' => $lokasi->alamat,
            'status_ketersediaan' => $validated['status_ketersediaan'],
        ]);


        // Jalankan matching ulang jika unit baru tersedia.
        if (
            $validated['status_ketersediaan']
            === Resource::STATUS_TERSEDIA
        ) {
            $this->matchingService->rematchWaitingCases();
        }

        return redirect()
            ->route('instansi.units.index')
            ->with(
                'success',
                'Unit berhasil ditambahkan.'
            );

        
    }

    public function update(Request $request, int $resourceId)
    {
        $resource = $this->ownedResource($request, $resourceId);

        // Unit yang sedang mendapat assignment tidak boleh diedit.
        if ($resource->status_ketersediaan === Resource::STATUS_DITUGASKAN) {
            return back()->with(
                'error',
                'Unit sedang dalam penanganan dan tidak dapat diedit.'
            );
        }

        $validated = $request->validate([
            'nama_resource' => ['required', 'string', 'max:100'],
            'jenis_resource' => ['required', 'string', 'max:50'],
            'kapasitas_personel' => ['nullable', 'integer', 'min:1'],
        ]);

        $resource->update([
            'nama_resource' => $validated['nama_resource'],
            'jenis_resource' => $validated['jenis_resource'],
            'kapasitas_personel' => $validated['kapasitas_personel'] ?? null,
        ]);

        return back()->with('success', 'Unit berhasil diperbarui.');
    }

    public function updateAvailability(
        Request $request,
        int $resourceId
    ): RedirectResponse {
        $resource = $this->ownedResource(
            $request,
            $resourceId);
        if (
            $resource->status_ketersediaan
            === Resource::STATUS_DITUGASKAN
            ){
                return back()->with(
                    'error',
                    'Unit yang sedang ditugaskan tidak dapat diubah ketersediaannya secara manual.'
                    );
            }

        $validated = $request->validate([
            'status_ketersediaan' => [
                'required',
                Rule::in([
                    Resource::STATUS_TERSEDIA,
                    Resource::STATUS_TIDAK_TERSEDIA,
                ]),
            ],
        ]);

        $resource->update([
            'status_ketersediaan' =>
                $validated['status_ketersediaan'],
        ]);

        // Jalankan matching ulang ketika unit tersedia.
        if (
            $validated['status_ketersediaan']
            === Resource::STATUS_TERSEDIA
        ) {
            $this->matchingService->rematchWaitingCases();
        }

        return back()->with(
            'success',
            'Ketersediaan unit berhasil diperbarui.'
        );
    }
        public function destroy(Request $request, int $resourceId)
    {
        $resource = $this->ownedResource($request, $resourceId);

        // Unit yang sedang mendapat assignment tidak boleh dihapus.
        if ($resource->status_ketersediaan === Resource::STATUS_DITUGASKAN) {
            return back()->with(
                'error',
                'Unit sedang dalam penanganan dan tidak dapat dihapus.'
            );
        }

        // Jangan hapus resource yang sudah mempunyai riwayat Assignment.
        if ($resource->assignments()->exists()) {
            return back()->with(
                'error',
                'Unit tidak dapat dihapus karena sudah memiliki riwayat assignment.'
            );
        }

        $resource->delete();

        return back()->with('success', 'Unit berhasil dihapus.');
    }

    private function ownedResource(
        Request $request,
        int $resourceId
    ): Resource {
        return Resource::query()
            ->where(
                'instansi_id',
                $request->user()->instansi_id
            )
            ->where('resource_id', $resourceId)
            ->firstOrFail();
    }
}