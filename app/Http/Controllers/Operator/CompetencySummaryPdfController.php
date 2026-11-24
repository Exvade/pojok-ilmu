<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Services\ProductTrainingProgressService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompetencySummaryPdfController extends Controller
{
    /**
     * Unduh ringkasan kompetensi operator yang sedang masuk dalam bentuk PDF.
     */
    public function __invoke(Request $request, ProductTrainingProgressService $progress): Response
    {
        $manpower = $request->user()->manpower;
        abort_unless($manpower, 404);

        $enrollments = $progress->operatorEnrollments($manpower);

        return Pdf::loadView('pdf.competency-summary', [
            'manpower' => $manpower,
            'enrollments' => $enrollments,
            'currentLevel' => $progress->currentLevel($enrollments),
            'levelRows' => $progress->levelRows($enrollments),
        ])->setPaper('a4')->download(sprintf(
            'ringkasan-kompetensi-%s-%s.pdf',
            str($manpower->nik)->slug(),
            now()->format('Ymd'),
        ));
    }
}
