<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\TrainingEnrollment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OjtPdfController extends Controller
{
    private const THEORY_QUESTIONS = [
        'part_name' => 'Sebutkan nama part yang Anda proses.',
        'part_material' => 'Sebutkan nama material part yang Anda proses.',
        'work_tools' => 'Sebutkan alat bantu kerja yang digunakan.',
        'work_process' => 'Sebutkan urutan proses produksi Assembly.',
        'safety' => 'Apakah ada subpart yang Anda proses untuk part yang Anda proses sebelumnya?',
        'control_plan' => 'Sebutkan Control Plan yang ada pada proses.',
        'ng_types' => 'Sebutkan jenis-jenis NG pada part dan jenis NG Assembly.',
        'marking' => 'Apa yang dimaksud dengan proses marking?',
        'scrap' => 'Berapa cycle packing part yang Anda proses?',
        'handling' => 'Apa warna label yang digunakan dan apa saja yang dicantumkan?',
        'k3' => 'Apa yang dimaksud dengan 5R dan K3?',
        'checking' => 'Kenapa proses pengecekan part perlu dilakukan?',
    ];

    private const PRACTICE_ITEMS = [
        'improvement' => 'Frekuensi perbaikan',
        'reject' => 'Reject yang diizinkan',
        'cycle_time' => 'Cycle time & target',
        'apd' => 'Penggunaan APD',
        'five_r' => 'Penerapan 5R',
    ];

    public function downloadForOperator(Request $request, string $enrollment): Response
    {
        $manpower = $request->user()->manpower;
        abort_unless($manpower, 404);

        $enrollment = $this->completedEnrollment($enrollment, $manpower->id);

        return $this->download($enrollment);
    }

    public function downloadForManagement(Request $request, string $enrollment): Response
    {
        $enrollment = $this->completedEnrollment($enrollment);

        if (! $request->user()->is_superadmin && ! $enrollment->training->appliesToDepartment($request->user()->manpower?->trainingDepartment())) {
            abort(403, 'Hasil OJT hanya dapat diunduh pada departemen Anda.');
        }

        return $this->download($enrollment);
    }

    private function completedEnrollment(string $id, ?int $manpowerId = null): TrainingEnrollment
    {
        return TrainingEnrollment::with(['manpower', 'training', 'ojtAssessment.assessor'])
            ->whereKey($id)
            ->when($manpowerId !== null, fn ($query) => $query->where('manpower_id', $manpowerId))
            ->whereHas('ojtAssessment')
            ->firstOrFail();
    }

    private function download(TrainingEnrollment $enrollment): Response
    {
        $filename = sprintf(
            'hasil-ojt-%s-%s.pdf',
            str($enrollment->training->kode_training)->slug(),
            str($enrollment->manpower->mp_id)->slug(),
        );

        return Pdf::loadView('pdf.ojt-result', [
            'assessment' => $enrollment->ojtAssessment,
            'enrollment' => $enrollment,
            'theoryQuestions' => self::THEORY_QUESTIONS,
            'practiceItems' => self::PRACTICE_ITEMS,
        ])->setPaper('a4')->download($filename);
    }
}
