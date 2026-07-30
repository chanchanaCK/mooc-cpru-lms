<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use App\Models\CreditRecord;
use App\Models\CreditTransferRequest;
use App\Models\Program;
use App\Models\ProgramEnrollment;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** Registrar overview: programs, enrolments, banked credits, transfers. */
    public function index(): View
    {
        $stats = [
            'programs' => Program::count(),
            'published' => Program::where('status', 'published')->count(),
            'enrollments' => ProgramEnrollment::count(),
            'completed' => ProgramEnrollment::where('status', 'completed')->count(),
            'credits_banked' => (float) CreditRecord::where('status', 'earned')->sum('credits'),
            'pending_transfers' => CreditTransferRequest::pending()->count(),
        ];

        $recentCompletions = ProgramEnrollment::with(['user', 'program'])
            ->where('status', 'completed')
            ->latest('completed_at')
            ->limit(8)
            ->get();

        return view('registrar.dashboard', compact('stats', 'recentCompletions'));
    }
}
