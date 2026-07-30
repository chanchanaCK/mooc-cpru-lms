<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CreditTransferController extends Controller
{
    /** The learner's own transfer requests + the submission form. */
    public function index(Request $request): View
    {
        $user = $request->user();

        $transfers = $user->transferRequests()->with('program')->latest()->get();
        $programs = Program::published()->orderBy('title')->get(['id', 'title']);

        return view('credit-bank.transfers', compact('transfers', 'programs'));
    }

    /** Submit a new credit-transfer / RPL request. */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'source_type' => ['required', 'in:institution,experience,other'],
            'source_name' => ['required', 'string', 'max:180'],
            'course_name' => ['required', 'string', 'max:180'],
            'credits_requested' => ['required', 'numeric', 'min:0.5', 'max:99'],
            'program_id' => ['nullable', 'exists:programs,id'],
            'evidence_note' => ['nullable', 'string', 'max:2000'],
            'evidence' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
        ]);

        $path = $request->hasFile('evidence')
            ? $request->file('evidence')->store('evidence', 'public')
            : null;

        $request->user()->transferRequests()->create([
            'program_id' => $data['program_id'] ?? null,
            'source_type' => $data['source_type'],
            'source_name' => $data['source_name'],
            'course_name' => $data['course_name'],
            'credits_requested' => $data['credits_requested'],
            'evidence_note' => $data['evidence_note'] ?? null,
            'evidence_path' => $path,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('credit-bank.transfers.index')
            ->with('success', 'ส่งคำขอเทียบโอนแล้ว — รอนายทะเบียนพิจารณา');
    }
}
