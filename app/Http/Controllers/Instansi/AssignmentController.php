<?php

namespace App\Http\Controllers\Instansi;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Pengguna;
use App\Services\Assignment\AssignmentStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    /**
     * Dashboard Assignment Instansi.
     */
    public function index(Request $request): View
    {
    $user = $request->user();

    // Data untuk kartu Assignment (dengan pagination).
    $assignments = Assignment::query()
        ->with([
            'resource.lokasiPangkalan',
            'emergencyCase.lokasi',
            'statusPenanganan',
        ])
        ->whereHas('resource', function ($query) use ($user) {
            $query->where('instansi_id', $user->instansi_id);
        })
        ->orderByDesc('waktu_assign')
        ->paginate(15);

    // Data semua lokasi Assignment untuk peta Dashboard.
    $mapLocations = Assignment::query()
        ->with('emergencyCase.lokasi')
        ->whereHas('resource', function ($query) use ($user) {
            $query->where('instansi_id', $user->instansi_id);
        })
        ->get()
        ->map(function ($assignment) {
            $case = $assignment->emergencyCase;
            $lokasi = $case?->lokasi;

            if (
                !$lokasi ||
                $lokasi->latitude === null ||
                $lokasi->longitude === null
            ) {
                return null;
            }

            return [
                'assignment_id' => $assignment->assignment_id,
                'case_id' => $assignment->case_id,
                'jenis_kejadian' => $case->jenis_kejadian,
                'alamat' => $lokasi->alamat,
                'latitude' => (float) $lokasi->latitude,
                'longitude' => (float) $lokasi->longitude,
            ];
        })
        ->filter()
        ->values();

    return view(
        'instansi.assignments.index',
        compact('assignments', 'mapLocations')
    );
}

        /**
     * Detail Assignment milik Instansi yang login.
     */
    public function show(Request $request, int $assignmentId): View
    {
        $user = $request->user();

        $assignment = Assignment::query()
            ->with([
                'resource.lokasiPangkalan',
                'emergencyCase.lokasi',

                'statusPenanganan' => function (
                    $query
                ) {
                    $query
                        ->orderBy('waktu_update')
                        ->orderBy('status_id');
                },
            ])
            ->where(
                'assignment_id',
                $assignmentId
            )
            ->whereHas(
                'resource',
                function ($query) use ($user) {
                    $query->where(
                        'instansi_id',
                        $user->instansi_id
                    );
                }
            )
            ->firstOrFail();

        $latestStatus = $assignment
            ->statusPenanganan
            ->last();

        return view(
    'instansi.assignments.show',
    compact(
        'assignment',
        'latestStatus'
    )
);
    }
    public function updateStatus(
        Request $request,
        int $assignmentId,
        AssignmentStatusService $service
    ): RedirectResponse {
        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in(
                    AssignmentStatusService::ALLOWED_STATUSES
                ),
            ],

            'keterangan' => [
                'required',
                'string',
                'max:5000',
            ],
        ]);

        /** @var Pengguna $user */
        $user = $request->user();

        $result = $service->updateStatus(
            $assignmentId,
            $user,
            $validated['status'],
            $validated['keterangan']
        );

        if ($validated['status'] === 'selesai') {

            $message =
                $result['resource_released']
                    ? 'Penanganan selesai. Resource tersedia kembali dan matching ulang telah dijalankan.'
                    : 'Penanganan selesai. Resource masih memiliki Assignment aktif lain.';

        } else {

            $message =
                'Status penanganan berhasil diperbarui.';
        }

        return redirect()
            ->route(
                'instansi.assignments.show',
                $assignmentId
            )
            ->with(
                'success',
                $message
            );
    }
}