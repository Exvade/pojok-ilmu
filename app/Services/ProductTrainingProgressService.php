<?php

namespace App\Services;

use App\Models\MManpower;
use App\Models\Training;
use App\Models\TrainingEnrollment;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ProductTrainingProgressService
{
    /**
     * Training yang ditugaskan ke operator, urut kode training, lengkap dengan level kompetensi, status kompeten, next progress, dan kunci level.
     *
     * @return Collection<int, TrainingEnrollment>
     */
    public function operatorEnrollments(MManpower $manpower): Collection
    {
        $enrollments = TrainingEnrollment::with(['training.materials', 'training.s2sProducts', 'training.preTest', 'training.postTest' => fn ($query) => $query->withCount('questions'), 'ojtAssessment'])
            ->where('manpower_id', $manpower->id)
            ->orderBy(Training::select('kode_training')
                ->whereColumn('trainings.id', 'training_enrollments.training_id'))
            ->get();

        $enrollments->each(function (TrainingEnrollment $enrollment) use ($enrollments): void {
            $enrollment->setAttribute('block', $this->blockFor($enrollment));
            $enrollment->setAttribute('is_unlocked', $this->isUnlocked($enrollment, $enrollments));
            $enrollment->setAttribute('blocking_training_titles', $this->blockingEnrollments($enrollment, $enrollments)
                ->map(fn (TrainingEnrollment $item): string => $item->training->judul)
                ->all());
            $enrollment->setAttribute('block_label', $this->labelFor($enrollment));
            $enrollment->setAttribute('is_competent', $this->isCompetent($enrollment));
            $enrollment->setAttribute('is_training_ready', $enrollment->training->isReady());
            $enrollment->setAttribute('achieved_at', $this->achievedAt($enrollment));
            $enrollment->setAttribute('next_progress_at', $this->nextProgressAt($enrollment));
        });

        return $enrollments;
    }

    public function blockFor(TrainingEnrollment $enrollment): int
    {
        if ($enrollment->status === 'passed' && $enrollment->ojt_status === 'approved') {
            return 2;
        }

        return $enrollment->post_test_completed_at ? 1 : 0;
    }

    /** @param Collection<int, TrainingEnrollment> $enrollments */
    public function isUnlocked(TrainingEnrollment $enrollment, Collection $enrollments): bool
    {
        return $this->blockingEnrollments($enrollment, $enrollments)->isEmpty()
            && $this->missingPrerequisiteLevels($enrollment, $enrollments) === [];
    }

    /** @return array<int, int> */
    public function missingPrerequisiteLevels(TrainingEnrollment $enrollment, Collection $enrollments): array
    {
        $targetLevel = $enrollment->training->level;
        if ($targetLevel <= 1) {
            return [];
        }

        $assignedLevels = $enrollments
            ->filter(fn (TrainingEnrollment $item): bool => $item->training->level < $targetLevel)
            ->groupBy(fn (TrainingEnrollment $item): int => $item->training->level);

        return collect(range(1, $targetLevel - 1))
            ->filter(fn (int $level): bool => ! $assignedLevels->has($level))
            ->values()
            ->all();
    }

    /** @return Collection<int, TrainingEnrollment> */
    public function blockingEnrollments(TrainingEnrollment $enrollment, Collection $enrollments): Collection
    {
        return $enrollments
            ->filter(fn (TrainingEnrollment $item): bool => $item->training->level < $enrollment->training->level)
            ->filter(fn (TrainingEnrollment $item): bool => $this->blockFor($item) < 2)
            ->values();
    }

    /** @param Collection<int, TrainingEnrollment> $enrollments */
    public function currentLevel(Collection $enrollments): int
    {
        $levels = $enrollments->groupBy(fn (TrainingEnrollment $enrollment): int => $enrollment->training->level);
        $currentLevel = 0;

        foreach (range(1, 3) as $level) {
            $levelEnrollments = $levels->get($level);
            if (! $levelEnrollments instanceof Collection || $levelEnrollments->isEmpty()) {
                break;
            }

            if (! $levelEnrollments->every(fn (TrainingEnrollment $item): bool => $this->blockFor($item) === 2)) {
                break;
            }

            $currentLevel = $level;
        }

        return $currentLevel;
    }

    /**
     * Tanggal level kompetensi terakhir tercapai: OJT disetujui untuk level 2, post-test untuk level 1.
     */
    public function achievedAt(TrainingEnrollment $enrollment): ?CarbonInterface
    {
        return match ($this->blockFor($enrollment)) {
            2 => $enrollment->ojt_assessed_at ?? $enrollment->post_test_completed_at,
            1 => $enrollment->post_test_completed_at,
            default => null,
        };
    }

    /**
     * Target progres berikutnya: satu bulan setelah level kompetensi terakhir tercapai, sampai level 4.
     */
    public function nextProgressAt(TrainingEnrollment $enrollment): ?CarbonInterface
    {
        if ($this->blockFor($enrollment) >= Training::MAX_COMPETENCY_LEVEL) {
            return null;
        }

        $achievedAt = $this->achievedAt($enrollment);

        return $achievedAt ? Carbon::parse($achievedAt)->addMonthNoOverflow() : null;
    }

    /**
     * Manpower kompeten bila level kompetensinya sudah mencapai acceptance level training.
     */
    public function isCompetent(TrainingEnrollment $enrollment): bool
    {
        return $this->blockFor($enrollment) >= (int) ($enrollment->training->acceptance_level ?: Training::MIN_ACCEPTANCE_LEVEL);
    }

    /**
     * Baris tabel training operator, dikelompokkan per level training. Satu baris per produk S2S;
     * training tanpa produk tetap tampil satu baris memakai judul training.
     *
     * @param  Collection<int, TrainingEnrollment>  $enrollments
     * @return Collection<int, Collection<int, array{enrollment: TrainingEnrollment, part_name: string, part_number: ?string, competency_level: int}>>
     */
    public function levelRows(Collection $enrollments): Collection
    {
        return $enrollments
            ->flatMap(function (TrainingEnrollment $enrollment): array {
                $products = $enrollment->training->s2sProducts->sortBy('kode_part')->values();

                if ($products->isEmpty()) {
                    return [[
                        'enrollment' => $enrollment,
                        'part_name' => $enrollment->training->judul,
                        'part_number' => null,
                        'competency_level' => $this->blockFor($enrollment),
                    ]];
                }

                return $products->map(fn ($product): array => [
                    'enrollment' => $enrollment,
                    'part_name' => $product->nama_part ?: $enrollment->training->judul,
                    'part_number' => $product->kode_part,
                    'competency_level' => $this->blockFor($enrollment),
                ])->all();
            })
            ->groupBy(fn (array $row): int => (int) $row['enrollment']->training->level)
            ->sortKeys();
    }

    public function labelFor(TrainingEnrollment $enrollment): string
    {
        return match ($this->blockFor($enrollment)) {
            2 => 'Level kompetensi 2 - Mampu mengerjakan dengan bimbingan',
            1 => 'Level kompetensi 1 - Lulus post-test',
            default => 'Belum mencapai level kompetensi',
        };
    }
}
