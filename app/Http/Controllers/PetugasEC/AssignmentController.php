<?php

namespace App\Http\Controllers\PetugasEC;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(
            $request->user()?->role === 'petugas_ec',
            403
        );

        $baseQuery = Assignment::query();

        // Filter berdasarkan status Assignment asli.
        $status = $request->query('status');

        $allowedStatuses = [
            'diterima',
            'selesai',
            'ditolak',
            'dibatalkan',
        ];

        if (in_array($status, $allowedStatuses, true)) {
            $baseQuery->where('status_assign', $status);
        }

        // Pencarian berdasarkan Assignment ID atau Case ID.
        $search = trim($request->query('search', ''));

        if ($search !== '') {
            $baseQuery->where(function ($query) use ($search) {
                if (ctype_digit($search)) {
                    $query->where('assignment_id', (int) $search)
                        ->orWhere('case_id', (int) $search);
                }
            });
        }

        // Ringkasan keseluruhan Assignment.
        $totalAssignments = Assignment::count();

        $activeAssignments = Assignment::where(
            'status_assign',
            'diterima'
        )->count();

        $completedAssignments = Assignment::where(
            'status_assign',
            'selesai'
        )->count();

        // Daftar Assignment beserta relasinya.
        $assignments = $baseQuery
            ->with([
                'resource.lokasiPangkalan',
                'emergencyCase.lokasi',
                'statusPenanganan' => function ($query) {
                    $query
                        ->orderBy('waktu_update')
                        ->orderBy('status_id');
                },
            ])
            ->orderByDesc('waktu_assign')
            ->paginate(10)
            ->withQueryString();

        return view(
            'petugas-ec.assignments.index',
            compact(
                'assignments',
                'totalAssignments',
                'activeAssignments',
                'completedAssignments',
                'status',
                'search'
            )
        );
    }
}
