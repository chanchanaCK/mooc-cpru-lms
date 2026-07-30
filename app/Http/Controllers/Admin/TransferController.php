<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CreditTransferRequest;
use App\Services\CreditBankService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransferController extends Controller
{
    public function __construct(private readonly CreditBankService $creditBank)
    {
    }

    public function index(): View
    {
        $pending = CreditTransferRequest::pending()
            ->with(['user', 'program'])
            ->oldest()
            ->get();

        $reviewed = CreditTransferRequest::whereIn('status', ['approved', 'rejected'])
            ->with(['user', 'reviewer'])
            ->latest('reviewed_at')
            ->limit(20)
            ->get();

        return view('admin.transfers.index', compact('pending', 'reviewed'));
    }

    public function approve(Request $request, CreditTransferRequest $transfer): RedirectResponse
    {
        abort_unless($transfer->isPending(), 400, 'คำขอนี้ถูกพิจารณาไปแล้ว');

        $data = $request->validate([
            'credits_awarded' => ['required', 'numeric', 'min:0.5', 'max:99'],
            'grade' => ['nullable', 'string', 'max:4'],
            'review_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->creditBank->approveTransfer(
            $transfer,
            $request->user(),
            (float) $data['credits_awarded'],
            $data['grade'] ?: 'S',
            $data['review_note'] ?? null,
        );

        return back()->with('success', 'อนุมัติและโอนหน่วยกิตเข้าคลังของผู้เรียนแล้ว');
    }

    public function reject(Request $request, CreditTransferRequest $transfer): RedirectResponse
    {
        abort_unless($transfer->isPending(), 400, 'คำขอนี้ถูกพิจารณาไปแล้ว');

        $data = $request->validate([
            'review_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->creditBank->rejectTransfer($transfer, $request->user(), $data['review_note'] ?? null);

        return back()->with('success', 'บันทึกผล “ไม่อนุมัติ” แล้ว');
    }
}
