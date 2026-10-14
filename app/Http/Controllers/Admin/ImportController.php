<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SpreadsheetTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\SpreadsheetRowsImport;
use App\Models\MManpower;
use App\Models\MSkill;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Training;
use App\Models\TrainingEnrollment;
use App\Models\TrainingMaterial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class ImportController extends Controller
{
    public function create(string $type): View
    {
        $types = $this->types();

        abort_unless(array_key_exists($type, $types), 404);

        return view('admin.imports.create', [
            'type' => $type,
            'definition' => $types[$type],
        ]);
    }

    public function createAssignments(): View
    {
        return $this->create('assignments');
    }

    public function template(string $type): BinaryFileResponse
    {
        $definition = $this->definition($type);

        return Excel::download(
            new SpreadsheetTemplateExport($definition),
            'template-' . $type . '.xlsx'
        );
    }

    public function assignmentTemplate(): BinaryFileResponse
    {
        return $this->template('assignments');
    }

    public function store(Request $request, string $type): RedirectResponse
    {
        $definition = $this->definition($type);
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:5120']);

        try {
            $reader = new SpreadsheetRowsImport;
            Excel::import($reader, $request->file('file'));
            $rows = $this->normalizedRows($reader);
            $errors = $this->validateRows($type, $rows);

            if ($errors !== []) {
                return back()->withErrors(['file' => implode(' ', $errors)])->withInput();
            }

            DB::transaction(function () use ($type, $rows): void {
                foreach ($rows as $row) {
                    $this->saveRow($type, $row);
                }
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['file' => 'File belum dapat diimpor. Pastikan format template tidak diubah dan coba kembali.']);
        }

        return redirect()
            ->route($type === 'assignments' ? 'admin.assignments.import' : 'admin.imports.create', $type === 'assignments' ? [] : ['type' => $type])
            ->with('success', count($rows) . ' baris ' . strtolower($definition['label']) . ' berhasil diimpor.');
    }

    public function storeAssignments(Request $request): RedirectResponse
    {
        return $this->store($request, 'assignments');
    }

    /** @return array<string, array{label: string, description: string, headings: array<int, string>, samples: array<int, array<int, string|int>>, notes: array<int, string>}> */
    private function types(): array
    {
        return [
            'skills' => [
                'label' => 'master kompetensi',
                'description' => 'Gunakan satu baris untuk setiap kompetensi atau kemampuan yang dipakai oleh sub-course.',
                'headings' => ['kode_skill', 'nama_skill', 'kategori', 'deskripsi'],
                'samples' => [['IT-SE-GIT', 'Dasar Pemrograman dan Git', 'Software Engineering', 'Pemahaman dasar pengelolaan source code.']],
                'notes' => ['Kode kompetensi harus unik.', 'Baris dengan kode yang sama akan diperbarui.'],
            ],
            'trainings' => [
                'label' => 'training',
                'description' => 'Satu baris adalah satu training yang wajib dipelajari. Level menunjukkan kelompok training, bukan tahapan di dalam training.',
                'headings' => ['kode_training', 'departemen', 'level', 'judul', 'deskripsi', 'nilai_lulus', 'batas_percobaan'],
                'samples' => [['BOX-BATTERY', 'PRODUKSI PLASTIK A; PRODUKSI PLASTIK B', 1, 'BOX BATTERY', 'Training BOX BATTERY.', 80, 3]],
                'notes' => ['Departemen harus sama dengan penamaan dari HaRis. Pisahkan beberapa departemen dengan tanda titik koma (;).', 'Gunakan SEMUA pada kolom departemen untuk training umum.', 'Balok 1 diperoleh setelah post-test lulus, Balok 2 setelah OJT disetujui.'],
            ],
            'materials' => [
                'label' => 'materi pembelajaran',
                'description' => 'Masukkan materi teks, video, gambar, atau PDF untuk sub-course yang sudah tersedia.',
                'headings' => ['kode_training', 'urutan', 'judul_materi', 'tipe_media', 'konten_teks', 'url_media'],
                'samples' => [['SE-L1-01', 1, 'Pengantar Git', 'text', 'Git digunakan untuk mengelola riwayat perubahan source code.', '']],
                'notes' => ['Tipe media: text, video_youtube, video_gdrive, image, atau pdf.', 'URL media wajib diisi untuk video, gambar, atau PDF.'],
            ],
            'questions' => [
                'label' => 'bank soal',
                'description' => 'Satu baris adalah satu soal. Soal yang diimpor otomatis digunakan pada pre-test dan post-test.',
                'headings' => ['kode_training', 'judul_evaluasi', 'durasi_menit', 'nilai_lulus', 'batas_percobaan', 'masa_tunggu_jam', 'pertanyaan', 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'kunci_jawaban'],
                'samples' => [['SE-L1-01', 'Evaluasi Dasar Pemrograman dan Git', 15, 80, 3, 24, 'Apa fungsi Git?', 'Mengelola versi source code', 'Membuat database', 'Mengirim email', 'Menghapus server', 'a']],
                'notes' => ['Satu soal otomatis tersedia pada pre-test dan post-test.', 'Kunci jawaban hanya boleh: a, b, c, atau d.'],
            ],
            'assignments' => [
                'label' => 'penugasan training',
                'description' => 'Tugaskan banyak manpower sekaligus menggunakan kode training dan NPK dari HaRis.',
                'headings' => ['kode_training', 'npk'],
                'samples' => [['BOX-BATTERY-L1', '4067.260721.CKR']],
                'notes' => ['NPK harus sama dengan data HaRis yang telah disinkronkan.', 'Manpower harus aktif dan berada pada departemen training yang sama.', 'Impor ulang tidak membuat penugasan ganda.'],
            ],
        ];
    }

    /** @return array{label: string, description: string, headings: array<int, string>, samples: array<int, array<int, string|int>>, notes: array<int, string>} */
    private function definition(string $type): array
    {
        $types = $this->types();
        abort_unless(array_key_exists($type, $types), 404);

        return $types[$type];
    }

    /** @return array<int, array<string, string|int>> */
    private function normalizedRows(SpreadsheetRowsImport $reader): array
    {
        return $reader->rows
            ->map(function ($row, int $index): array {
                $values = collect($row)
                    ->map(fn ($value): string|int => is_string($value) ? trim($value) : $value)
                    ->all();
                $values['_row'] = $index + 2;

                return $values;
            })
            ->filter(fn (array $row): bool => collect($row)->except('_row')->filter(fn ($value): bool => $value !== null && $value !== '')->isNotEmpty())
            ->values()
            ->all();
    }

    /** @param array<int, array<string, string|int>> $rows @return array<int, string> */
    private function validateRows(string $type, array $rows): array
    {
        if ($rows === []) {
            return ['File tidak memiliki baris data. Gunakan template yang disediakan.'];
        }

        $errors = [];
        foreach ($rows as $row) {
            $rowNumber = $row['_row'];
            $required = match ($type) {
                'skills' => ['kode_skill', 'nama_skill', 'kategori'],
                'trainings' => ['kode_training', 'departemen', 'level', 'judul'],
                'materials' => ['kode_training', 'urutan', 'judul_materi', 'tipe_media'],
                'questions' => ['kode_training', 'pertanyaan', 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'kunci_jawaban'],
                'assignments' => ['kode_training', 'npk'],
            };
            foreach ($required as $field) {
                if (! isset($row[$field]) || $row[$field] === '') {
                    $errors[] = 'Baris ' . $rowNumber . ': kolom ' . $field . ' wajib diisi.';
                }
            }
            if ($type === 'trainings') {
                if (! is_numeric($row['level'] ?? null) || ! in_array((int) $row['level'], [1, 2, 3, 4], true)) {
                    $errors[] = 'Baris ' . $rowNumber . ': level harus bernilai 1 sampai 4.';
                }
            }
            if (in_array($type, ['materials', 'questions'], true) && ! Training::where('kode_training', $row['kode_training'] ?? '')->exists()) {
                $errors[] = 'Baris ' . $rowNumber . ': kode_training tidak ditemukan.';
            }
            if ($type === 'materials' && ! in_array($row['tipe_media'] ?? '', ['text', 'video_youtube', 'video_gdrive', 'image', 'pdf'], true)) {
                $errors[] = 'Baris ' . $rowNumber . ': tipe_media tidak valid.';
            }
            if ($type === 'questions') {
                if (! in_array(strtolower((string) ($row['kunci_jawaban'] ?? '')), ['a', 'b', 'c', 'd'], true)) {
                    $errors[] = 'Baris ' . $rowNumber . ': kunci_jawaban harus a, b, c, atau d.';
                }
            }
            if ($type === 'assignments') {
                $training = Training::where('kode_training', strtoupper((string) ($row['kode_training'] ?? '')))->first();
                $manpower = MManpower::query()
                    ->where('mp_id', (string) ($row['npk'] ?? ''))
                    ->where('status', true)
                    ->whereNotNull('haris_synced_at')
                    ->first();

                if (! $training) {
                    $errors[] = 'Baris ' . $rowNumber . ': kode_training tidak ditemukan.';
                }
                if (! $manpower) {
                    $errors[] = 'Baris ' . $rowNumber . ': NPK tidak ditemukan, tidak aktif, atau belum tersinkron dari HaRis.';
                }
                if ($training && $manpower && ! $training->appliesToDepartment($manpower->departemen)) {
                    $errors[] = 'Baris ' . $rowNumber . ': departemen manpower tidak termasuk dalam departemen training.';
                }
            }
        }

        return array_slice($errors, 0, 12);
    }

    /** @param array<string, string|int> $row */
    private function saveRow(string $type, array $row): void
    {
        match ($type) {
            'skills' => $this->saveSkill($row),
            'trainings' => $this->saveTraining($row),
            'materials' => $this->saveMaterial($row),
            'questions' => $this->saveQuestion($row),
            'assignments' => $this->saveAssignment($row),
        };
    }

    /** @param array<string, string|int> $row */
    private function saveSkill(array $row): void
    {
        MSkill::updateOrCreate(['kode_skill' => strtoupper((string) $row['kode_skill'])], [
            'nama_skill' => $row['nama_skill'], 'kategori' => $row['kategori'], 'deskripsi' => $row['deskripsi'] ?? null,
        ]);
    }

    /** @param array<string, string|int> $row */
    private function saveTraining(array $row): void
    {
        $skill = MSkill::firstOrCreate(['kode_skill' => strtoupper((string) $row['kode_training'])], [
            'nama_skill' => $row['judul'],
            'kategori' => $row['departemen'],
            'deskripsi' => $row['deskripsi'] ?? null,
        ]);
        $departments = collect(explode(';', (string) $row['departemen']))
            ->map(fn (string $department): string => trim($department))
            ->reject(fn (string $department): bool => $department === '')
            ->values()
            ->all();
        $isGeneralTraining = count($departments) === 1 && strtoupper($departments[0]) === 'SEMUA';

        Training::updateOrCreate(['kode_training' => strtoupper((string) $row['kode_training'])], [
            'skill_id' => $skill->id, 'departemen' => $isGeneralTraining || count($departments) !== 1 ? null : $departments[0], 'departemens' => $isGeneralTraining ? [] : $departments, 'level' => (int) $row['level'], 'judul' => $row['judul'],
            'deskripsi' => $row['deskripsi'] ?? null, 'passing_grade' => (int) ($row['nilai_lulus'] ?: 80),
            'max_retries' => (int) ($row['batas_percobaan'] ?: 3), 'requires_ojt' => true,
        ]);
    }

    /** @param array<string, string|int> $row */
    private function saveMaterial(array $row): void
    {
        $training = Training::where('kode_training', $row['kode_training'])->firstOrFail();
        TrainingMaterial::updateOrCreate(['training_id' => $training->id, 'urutan' => (int) $row['urutan']], [
            'judul' => $row['judul_materi'], 'tipe_media' => $row['tipe_media'], 'konten_teks' => $row['konten_teks'] ?? null, 'media_url' => $row['url_media'] ?? null,
        ]);
    }

    /** @param array<string, string|int> $row */
    private function saveQuestion(array $row): void
    {
        $training = Training::where('kode_training', $row['kode_training'])->firstOrFail();
        foreach (['pre_test', 'post_test'] as $type) {
            $quiz = Quiz::firstOrCreate(['training_id' => $training->id, 'tipe' => $type], [
                'judul' => ($row['judul_evaluasi'] ?: 'Evaluasi') . ' ' . $training->judul,
                'durasi_menit' => (int) ($row['durasi_menit'] ?: 15), 'passing_grade' => $type === 'post_test' ? (int) ($row['nilai_lulus'] ?: $training->passing_grade) : 80,
                'max_retries' => (int) ($row['batas_percobaan'] ?: $training->max_retries), 'cooldown_hours' => (int) ($row['masa_tunggu_jam'] ?: 24),
            ]);
            QuizQuestion::create([
                'quiz_id' => $quiz->id, 'pertanyaan' => $row['pertanyaan'], 'tipe_media' => 'none', 'opsi_a' => $row['opsi_a'], 'opsi_b' => $row['opsi_b'],
                'opsi_c' => $row['opsi_c'], 'opsi_d' => $row['opsi_d'], 'kunci_jawaban' => strtolower((string) $row['kunci_jawaban']),
            ]);
        }
    }

    /** @param array<string, string|int> $row */
    private function saveAssignment(array $row): void
    {
        $training = Training::where('kode_training', strtoupper((string) $row['kode_training']))->firstOrFail();
        $manpower = MManpower::where('mp_id', (string) $row['npk'])->firstOrFail();

        TrainingEnrollment::firstOrCreate([
            'training_id' => $training->id,
            'manpower_id' => $manpower->id,
        ], [
            'status' => 'enrolled',
            'ojt_status' => 'not_started',
        ]);
    }
}
