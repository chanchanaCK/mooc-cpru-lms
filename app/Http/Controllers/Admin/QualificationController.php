<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\QualificationLevel;
use App\Models\QualificationType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class QualificationController extends Controller
{
    public function index(): View
    {
        $types = QualificationType::ordered()->get();
        $levels = QualificationLevel::ordered()->get();

        // Usage counts (programs.type is a slug, programs.nqf_level is a number).
        $typeUsage = Program::selectRaw('type, COUNT(*) as c')->groupBy('type')->pluck('c', 'type');
        $levelUsage = Program::whereNotNull('nqf_level')->selectRaw('nqf_level, COUNT(*) as c')->groupBy('nqf_level')->pluck('c', 'nqf_level');

        return view('admin.qualifications.index', compact('types', 'levels', 'typeUsage', 'levelUsage'));
    }

    /* ---- Qualification types (ประเภทคุณวุฒิ) ---- */

    public function storeType(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120']]);

        QualificationType::create([
            'slug' => $this->uniqueTypeSlug($data['name']),
            'name' => $data['name'],
            'sort_order' => (int) QualificationType::max('sort_order') + 1,
            'is_active' => true,
        ]);

        return back()->with('success', 'เพิ่มประเภทคุณวุฒิแล้ว');
    }

    public function updateType(Request $request, QualificationType $type): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $type->update(['name' => $data['name'], 'is_active' => $request->boolean('is_active')]);

        return back()->with('success', 'บันทึกประเภทคุณวุฒิแล้ว');
    }

    public function destroyType(QualificationType $type): RedirectResponse
    {
        if (Program::where('type', $type->slug)->exists()) {
            return back()->with('error', 'ลบไม่ได้ — มีหลักสูตรใช้ประเภทนี้อยู่ (ปิดใช้งานแทนได้)');
        }

        $type->delete();

        return back()->with('success', 'ลบประเภทคุณวุฒิแล้ว');
    }

    /* ---- Qualification levels (ระดับคุณวุฒิ) ---- */

    public function storeLevel(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'level' => ['required', 'integer', 'min:1', 'max:255', 'unique:qualification_levels,level'],
            'name' => ['required', 'string', 'max:120'],
        ]);

        QualificationLevel::create([
            'level' => $data['level'],
            'name' => $data['name'],
            'sort_order' => $data['level'],
        ]);

        return back()->with('success', 'เพิ่มระดับคุณวุฒิแล้ว');
    }

    public function updateLevel(Request $request, QualificationLevel $level): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120']]);

        $level->update(['name' => $data['name']]);

        return back()->with('success', 'บันทึกระดับคุณวุฒิแล้ว');
    }

    public function destroyLevel(QualificationLevel $level): RedirectResponse
    {
        if (Program::where('nqf_level', $level->level)->exists()) {
            return back()->with('error', 'ลบไม่ได้ — มีหลักสูตรใช้ระดับนี้อยู่');
        }

        $level->delete();

        return back()->with('success', 'ลบระดับคุณวุฒิแล้ว');
    }

    private function uniqueTypeSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'type-' . Str::lower(Str::random(6));
        $slug = $base;
        $i = 1;

        while (QualificationType::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
