<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 16mm 12mm; }
        * { box-sizing: border-box; }
        body { color: #1f2937; font-family: DejaVu Sans, sans-serif; font-size: 9px; line-height: 1.35; }
        h1, h2, p { margin: 0; }
        .header { border-bottom: 2px solid #14532d; padding-bottom: 10px; }
        .brand { color: #14532d; font-size: 10px; font-weight: bold; letter-spacing: .6px; text-transform: uppercase; }
        h1 { color: #14532d; font-size: 17px; margin-top: 4px; }
        .subtitle { color: #6b7280; margin-top: 3px; }
        .status { float: right; background: {{ $assessment->is_passed ? '#dcfce7' : '#fef3c7' }}; color: {{ $assessment->is_passed ? '#14532d' : '#92400e' }}; font-weight: bold; padding: 6px 10px; }
        .section { margin-top: 14px; }
        .section-title { background: #14532d; color: #ffffff; font-size: 10px; font-weight: bold; padding: 6px 8px; text-transform: uppercase; }
        table { border-collapse: collapse; width: 100%; }
        td, th { border: 1px solid #cbd5e1; padding: 5px 6px; vertical-align: top; }
        th { background: #f1f5f9; color: #334155; font-weight: bold; text-align: left; }
        .identity td { width: 25%; }
        .identity .value { font-weight: bold; }
        .score { text-align: center; width: 9%; }
        .number { text-align: center; width: 4%; }
        .note { min-height: 34px; white-space: pre-line; }
        .summary td { width: 50%; }
        .footer { border-top: 1px solid #cbd5e1; color: #64748b; font-size: 8px; margin-top: 16px; padding-top: 6px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="status">{{ $assessment->is_passed ? 'LULUS - BALOK 2' : 'PERLU OJT ULANG' }}</div>
        <p class="brand">PT Mada Wikri Tunggal</p>
        <h1>Hasil On the Job Training</h1>
        <p class="subtitle">{{ $enrollment->training->judul }} - Level {{ $enrollment->training->level }} - {{ $enrollment->training->kode_training }}</p>
    </div>

    <div class="section">
        <div class="section-title">Identitas dan pelaksanaan</div>
        <table class="identity">
            <tr><td>Nama</td><td class="value">{{ $enrollment->manpower->nama }}</td><td>NIK</td><td class="value">{{ $enrollment->manpower->nik }}</td></tr>
            <tr><td>Departemen HaRis</td><td class="value">{{ $enrollment->manpower->departemen }}</td><td>Jabatan / Bagian</td><td class="value">{{ $enrollment->manpower->jabatan ?? $enrollment->manpower->bagian }}</td></tr>
            <tr><td>Tanggal</td><td class="value">{{ $assessment->training_date?->translatedFormat('d F Y') }}</td><td>Jam / Tempat</td><td class="value">{{ $assessment->training_time ?? '-' }}{{ $assessment->location ? ' / ' . $assessment->location : '' }}</td></tr>
            <tr><td>Kategori</td><td colspan="3" class="value">{{ collect($assessment->categories)->map(fn ($category) => str($category)->replace('_', ' ')->title())->implode(', ') }}</td></tr>
            <tr><td>Alat peraga</td><td colspan="3">{{ $assessment->tools ?: '-' }}</td></tr>
            <tr><td>Materi training</td><td colspan="3">{{ collect($assessment->materials)->filter()->implode('; ') ?: '-' }}</td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Evaluasi teori</div>
        <table>
            <thead><tr><th class="number">No.</th><th>Pertanyaan</th><th>Jawaban manpower</th><th class="score">Nilai</th></tr></thead>
            <tbody>@foreach($theoryQuestions as $key => $question)<tr><td class="number">{{ $loop->iteration }}</td><td>{{ $question }}</td><td>{{ data_get($assessment->theory_answers, $key . '.answer', '-') }}</td><td class="score">{{ data_get($assessment->theory_answers, $key . '.score', '-') }}</td></tr>@endforeach</tbody>
            <tfoot><tr><th colspan="3">Nilai teori</th><th class="score">{{ $assessment->theory_score }}</th></tr></tfoot>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Tes praktik</div>
        <table>
            <thead><tr><th>Standar</th><th>Pencapaian aktual</th><th class="score">Nilai</th></tr></thead>
            <tbody>@foreach($practiceItems as $key => $item)<tr><td>{{ $item }}</td><td>{{ data_get($assessment->practice_results, $key . '.actual', '-') }}</td><td class="score">{{ data_get($assessment->practice_results, $key . '.score', '-') }}</td></tr>@endforeach</tbody>
            <tfoot><tr><th colspan="2">Nilai praktik</th><th class="score">{{ $assessment->practice_score }}</th></tr></tfoot>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Kesimpulan dan saran</div>
        <table class="summary"><tr><th>Kesimpulan Leader</th><th>Saran untuk manpower</th></tr><tr><td class="note">{{ $assessment->conclusion ?: '-' }}</td><td class="note">{{ $assessment->recommendation ?: '-' }}</td></tr></table>
    </div>

    <div class="section">
        <table class="summary"><tr><td>Penilai OJT<br><br><br><strong>{{ $assessment->assessor?->name ?? '-' }}</strong></td><td>Peserta OJT<br><br><br><strong>{{ $enrollment->manpower->nama }}</strong></td></tr></table>
    </div>

    <p class="footer">Dokumen ini dibuat secara elektronik oleh sistem Pojok Ilmu pada {{ now()->translatedFormat('d F Y H:i') }}. Hasil lulus menandakan Balok 2: mampu mengerjakan dengan bimbingan.</p>
</body>
</html>
