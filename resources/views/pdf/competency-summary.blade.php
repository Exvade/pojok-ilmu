<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Ringkasan Kompetensi - {{ $manpower->nama }}</title>
    <style>
        @page { margin: 16mm 12mm 18mm; }
        * { box-sizing: border-box; }
        body { color: #1f2937; font-family: DejaVu Sans, sans-serif; font-size: 9px; line-height: 1.35; }
        h1, h2, p { margin: 0; }
        .page-footer { position: fixed; bottom: -12mm; left: 0; right: 0; border-top: 1px solid #cbd5e1; color: #64748b; font-size: 7.5px; padding-top: 4px; }
        .page-footer .page-number:after { content: "Halaman " counter(page); }
        .header-wrap { border-bottom: 2px solid #14532d; padding-bottom: 10px; }
        .header td { border: 0; padding: 0; vertical-align: bottom; }
        .brand { color: #14532d; font-size: 10px; font-weight: bold; letter-spacing: .6px; text-transform: uppercase; }
        h1 { color: #14532d; font-size: 18px; margin-top: 4px; }
        .subtitle { color: #6b7280; margin-top: 3px; }
        .level-badge { background: #14532d; color: #ffffff; display: inline-block; padding: 8px 12px; text-align: center; }
        .level-badge .label { font-size: 7.5px; letter-spacing: .6px; text-transform: uppercase; }
        .level-badge .value { font-size: 16px; font-weight: bold; }
        .section { margin-top: 14px; }
        .section-title { background: #14532d; color: #ffffff; font-size: 10px; font-weight: bold; padding: 6px 8px; text-transform: uppercase; }
        table { border-collapse: collapse; width: 100%; }
        td, th { border: 1px solid #cbd5e1; padding: 5px 6px; vertical-align: middle; }
        th { background: #f1f5f9; color: #334155; font-weight: bold; text-align: left; }
        .identity td { width: 25%; }
        .identity .label { color: #64748b; }
        .identity .value { font-weight: bold; }
        .stats td { border: 1px solid #cbd5e1; padding: 8px; text-align: center; width: 20%; }
        .stats .value { color: #14532d; font-size: 16px; font-weight: bold; }
        .stats .label { color: #64748b; font-size: 8px; margin-top: 2px; }
        .center { text-align: center; }
        .number { text-align: center; width: 5%; }
        .muted { color: #64748b; font-size: 8px; }
        .part-name { font-weight: bold; }
        .mono { letter-spacing: .3px; }
        .level-value { font-size: 11px; font-weight: bold; text-align: center; }
        .status { font-weight: bold; white-space: nowrap; }
        .status-competent { color: #14532d; }
        .status-not-competent { color: #92400e; }
        .locked td { color: #94a3b8; }
        .empty { border: 1px dashed #cbd5e1; color: #64748b; padding: 14px; text-align: center; }
        .legend td { border: 0; padding: 2px 8px 2px 0; vertical-align: top; }
        .legend .term { font-weight: bold; white-space: nowrap; width: 22%; }
    </style>
</head>
<body>
    <div class="page-footer">
        <table><tr>
            <td style="border: 0; padding: 0;">Dokumen ini dibuat secara elektronik oleh sistem Pojok Ilmu pada {{ now()->translatedFormat('d F Y H:i') }} WIB.</td>
            <td style="border: 0; padding: 0; text-align: right;"><span class="page-number"></span></td>
        </tr></table>
    </div>

    <div class="header-wrap"><table class="header">
        <tr>
            <td>
                <p class="brand">PT Mada Wikri Tunggal</p>
                <h1>Ringkasan Kompetensi</h1>
                <p class="subtitle">Capaian training dan level kompetensi karyawan · Pojok Ilmu</p>
            </td>
            <td style="text-align: right; width: 30%;">
                <div class="level-badge">
                    <div class="label">Level Manpower</div>
                    <div class="value">{{ $currentLevel ? 'Level ' . $currentLevel : 'Belum ada' }}</div>
                </div>
            </td>
        </tr>
    </table></div>

    <div class="section">
        <div class="section-title">Identitas karyawan</div>
        <table class="identity">
            <tr><td class="label">Nama</td><td class="value">{{ $manpower->nama }}</td><td class="label">NIK</td><td class="value">{{ $manpower->nik }}</td></tr>
            <tr><td class="label">Departemen</td><td class="value">{{ $manpower->departemen ?: '-' }}</td><td class="label">Jabatan / Bagian</td><td class="value">{{ $manpower->jabatan ?: ($manpower->bagian ?: '-') }}</td></tr>
            <tr><td class="label">Plant</td><td class="value">{{ $manpower->nama_plant ?: '-' }}</td><td class="label">Tanggal cetak</td><td class="value">{{ now()->translatedFormat('d F Y') }}</td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Ringkasan capaian</div>
        <table class="stats">
            <tr>
                <td><div class="value">{{ $enrollments->count() }}</div><div class="label">Training ditugaskan</div></td>
                <td><div class="value">{{ $enrollments->where('is_competent', true)->count() }}</div><div class="label">Kompeten</div></td>
                <td><div class="value">{{ $enrollments->where('is_competent', false)->count() }}</div><div class="label">Belum kompeten</div></td>
                <td><div class="value">{{ $enrollments->where('block', 1)->count() }}</div><div class="label">Menunggu OJT</div></td>
                <td><div class="value">{{ $enrollments->where('is_unlocked', false)->count() }}</div><div class="label">Masih terkunci</div></td>
            </tr>
        </table>
    </div>

    @forelse($levelRows as $level => $rows)
        <div class="section">
            <div class="section-title">Level {{ $level }}</div>
            <table>
                <thead>
                    <tr>
                        <th class="number">No</th>
                        <th>Nama part / training</th>
                        <th style="width: 11%;">No part</th>
                        <th class="center" style="width: 10%;">Level kompetensi</th>
                        <th class="center" style="width: 10%;">Acceptance level</th>
                        <th style="width: 13%;">Status</th>
                        <th style="width: 11%;">Tanggal capaian</th>
                        <th style="width: 11%;">Next progress</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        @php($enrollment = $row['enrollment'])
                        <tr class="{{ $enrollment->is_unlocked ? '' : 'locked' }}">
                            <td class="number">{{ $loop->iteration }}</td>
                            <td>
                                <div class="part-name">{{ $row['part_name'] }}</div>
                                <div class="muted"><span class="mono">{{ $enrollment->training->kode_training }}</span> · {{ $enrollment->training->judul }}</div>
                            </td>
                            <td class="mono">{{ $row['part_number'] ?? '-' }}</td>
                            <td class="level-value">{{ $row['competency_level'] }}</td>
                            <td class="level-value">{{ $enrollment->training->acceptance_level }}</td>
                            <td class="status {{ $enrollment->is_competent ? 'status-competent' : 'status-not-competent' }}">{{ $enrollment->is_competent ? 'Kompeten' : 'Belum kompeten' }}</td>
                            <td>{{ $enrollment->achieved_at?->translatedFormat('d M Y') ?? '-' }}</td>
                            <td>{{ $enrollment->next_progress_at?->translatedFormat('d M Y') ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <div class="section">
            <p class="empty">Belum ada training yang ditugaskan.</p>
        </div>
    @endforelse

    <div class="section">
        <div class="section-title">Keterangan</div>
        <table class="legend">
            <tr><td class="term">Level kompetensi</td><td>Capaian manpower pada training tersebut, ditulis 0 sampai 4. Level 1 = lulus post-test, level 2 = lulus OJT (mampu mengerjakan dengan bimbingan). Level 3 dan 4 belum dikembangkan.</td></tr>
            <tr><td class="term">Acceptance level</td><td>Level kompetensi minimal yang ditetapkan pada training agar manpower dinyatakan kompeten.</td></tr>
            <tr><td class="term">Status</td><td>Kompeten bila level kompetensi sudah mencapai acceptance level; selain itu belum kompeten.</td></tr>
            <tr><td class="term">Next progress</td><td>Target progres berikutnya, satu bulan setelah tanggal capaian level kompetensi terakhir. Tidak berlaku setelah level 4.</td></tr>
            <tr><td class="term">Baris abu-abu</td><td>Training level berikutnya masih terkunci sampai seluruh training pada level sebelumnya mencapai level kompetensi 2.</td></tr>
        </table>
    </div>
</body>
</html>
