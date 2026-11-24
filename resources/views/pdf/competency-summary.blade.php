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
        .blocks { border-collapse: collapse; margin: 0 auto; width: auto; }
        .blocks td { border: 1px solid #94a3b8; height: 9px; padding: 0; width: 11px; }
        .blocks td.filled { background: #14532d; border-color: #14532d; }
        .block-number { font-size: 8px; font-weight: bold; margin-top: 2px; text-align: center; }
        .status { font-weight: bold; }
        .status-2 { color: #14532d; }
        .status-1 { color: #92400e; }
        .status-0 { color: #475569; }
        .status-locked { color: #94a3b8; }
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
                <p class="subtitle">Capaian training dan level kompetensi produk karyawan · Pojok Ilmu</p>
            </td>
            <td style="text-align: right; width: 30%;">
                <div class="level-badge">
                    <div class="label">Level saat ini</div>
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
                <td><div class="value">{{ $enrollments->where('block', 2)->count() }}</div><div class="label">Balok 2 (kompeten)</div></td>
                <td><div class="value">{{ $enrollments->where('block', 1)->count() }}</div><div class="label">Balok 1 (menunggu OJT)</div></td>
                <td><div class="value">{{ $enrollments->where('block', 0)->count() }}</div><div class="label">Belum mencapai balok</div></td>
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
                        <th style="width: 12%;">No part</th>
                        <th class="center" style="width: 11%;">Level kompetensi</th>
                        <th class="center" style="width: 11%;">Acceptance level</th>
                        <th style="width: 19%;">Status</th>
                        <th style="width: 12%;">Tanggal capaian</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        @php
                            $enrollment = $row['enrollment'];
                            $statusClass = ! $enrollment->is_unlocked ? 'status-locked' : 'status-' . $enrollment->block;
                            $statusLabel = match (true) {
                                ! $enrollment->is_unlocked => 'Terkunci',
                                $enrollment->block === 2 => 'Kompeten',
                                $enrollment->block === 1 => 'Menunggu OJT',
                                $enrollment->status === 'in_progress' => 'Sedang berjalan',
                                default => 'Belum dimulai',
                            };
                            $achievedAt = match ($enrollment->block) {
                                2 => $enrollment->ojt_assessed_at,
                                1 => $enrollment->post_test_completed_at,
                                default => null,
                            };
                        @endphp
                        <tr class="{{ $enrollment->is_unlocked ? '' : 'locked' }}">
                            <td class="number">{{ $loop->iteration }}</td>
                            <td>
                                <div class="part-name">{{ $row['part_name'] }}</div>
                                <div class="muted"><span class="mono">{{ $enrollment->training->kode_training }}</span> · {{ $enrollment->training->judul }}</div>
                            </td>
                            <td class="mono">{{ $row['part_number'] ?? '-' }}</td>
                            <td class="level-value">{{ $row['competency_level'] }}</td>
                            <td>
                                <table class="blocks"><tr>
                                    @foreach(range(1, 4) as $block)<td class="{{ $block <= $enrollment->block ? 'filled' : '' }}"></td>@endforeach
                                </tr></table>
                                <div class="block-number">{{ $enrollment->block }}</div>
                            </td>
                            <td class="status {{ $statusClass }}">{{ $statusLabel }}</td>
                            <td>{{ $achievedAt?->translatedFormat('d M Y') ?? '-' }}</td>
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
            <tr><td class="term">Balok 1</td><td>Lulus post-test dan menunggu penilaian OJT oleh Leader.</td></tr>
            <tr><td class="term">Balok 2</td><td>Lulus OJT: mampu mengerjakan dengan bimbingan.</td></tr>
            <tr><td class="term">Level kompetensi</td><td>Level training tertinggi untuk produk tersebut yang sudah mencapai Balok 2.</td></tr>
            <tr><td class="term">Terkunci</td><td>Training level berikutnya terbuka setelah seluruh training pada level sebelumnya mencapai Balok 2.</td></tr>
        </table>
    </div>
</body>
</html>
