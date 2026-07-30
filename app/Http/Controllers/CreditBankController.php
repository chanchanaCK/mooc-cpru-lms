<?php

namespace App\Http\Controllers;

use App\Services\CreditBankService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CreditBankController extends Controller
{
    public function __construct(private readonly CreditBankService $creditBank)
    {
    }

    /** "คลังหน่วยกิตของฉัน" — the learner's credit bank overview. */
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('credit-bank.index', [
            'user' => $user,
            ...$this->creditBank->transcriptData($user),
        ]);
    }

    /** Printable transcript / ใบแสดงผลการเรียน (save-as-PDF from the browser). */
    public function transcript(Request $request): View
    {
        $user = $request->user();

        return view('credit-bank.transcript', [
            'user' => $user,
            ...$this->creditBank->transcriptData($user),
        ]);
    }
}
