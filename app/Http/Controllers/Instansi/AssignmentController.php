<?php

namespace App\Http\Controllers\Instansi;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Pengguna;
use App\Services\Assignment\AssignmentStatusService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    /**
     * Menampilkan Assignment milik Instansi login.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

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

        return view(
            'instansi.assignments.index',
            compact('assignments')
        );
    }

    /**
     * Memperbarui status penanganan Assignment.
     */
    public function updateStatus(
        Request $request,
        int $assignmentId,
        AssignmentStatusService $service
    ): RedirectResponse {
        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in(AssignmentStatusService::ALLOWED_STATUSES),
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
            $message = $result['resource_released']
                ? 'Penanganan selesai. Resource tersedia kembali dan matching ulang telah dijalankan.'
                : 'Penanganan selesai. Resource masih memiliki Assignment aktif lain.';
        } else {
            $message = 'Status penanganan berhasil diperbarui.';
        }

        return redirect()
            ->route('instansi.assignments.index')
            ->with('success', $message);
    }
}
