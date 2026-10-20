<?php

namespace App\Models;

use App\Services\DepartmentGroupService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Training extends BaseModel
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'trainings';

    protected function casts(): array
    {
        return [
            'departemens' => 'array',
            'requires_ojt' => 'boolean',
        ];
    }

    /** @return array<int, string> */
    public function departmentList(): array
    {
        return collect(filled($this->departemens) ? $this->departemens : array_filter([$this->departemen]))
            ->map(fn (string $department): ?string => DepartmentGroupService::normalize($department))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function departmentLabel(): string
    {
        $departments = $this->departmentList();

        if ($departments === []) {
            return 'Semua departemen';
        }

        return implode(', ', $departments);
    }

    public function appliesToDepartment(?string $department): bool
    {
        return $this->departmentList() === [] || in_array(DepartmentGroupService::normalize($department), $this->departmentList(), true);
    }

    public function scopeForDepartment(Builder $query, string $department): Builder
    {
        $department = DepartmentGroupService::normalize($department);

        return $query->where(function (Builder $query) use ($department): void {
            $query->whereJsonContains('departemens', $department)
                ->orWhereJsonLength('departemens', 0)
                ->orWhere(function (Builder $legacyQuery) use ($department): void {
                    $legacyQuery->whereNull('departemens')->where('departemen', $department);
                });
        });
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(MSkill::class, 'skill_id', 'id');
    }
    public function materials(): HasMany
    {
        return $this->hasMany(TrainingMaterial::class, 'training_id', 'id')->orderBy('urutan', 'asc');
    }

    public function quiz(): HasOne
    {
        return $this->postTest();
    }

    /** Post-test determines the completion of Balok 1. */
    public function postTest(): HasOne
    {
        return $this->hasOne(Quiz::class, 'training_id', 'id')->where('tipe', 'post_test');
    }

    public function preTest(): HasOne
    {
        return $this->hasOne(Quiz::class, 'training_id', 'id')->where('tipe', 'pre_test');
    }

    /** Legacy assessment data remains readable but is not used in the active product flow. */
    public function assessment(): HasOne
    {
        return $this->hasOne(Quiz::class, 'training_id', 'id')->where('tipe', 'assessment');
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class, 'training_id', 'id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(TrainingEnrollment::class, 'training_id', 'id');
    }

    public function assignmentLogs(): HasMany
    {
        return $this->hasMany(TrainingAssignmentLog::class, 'training_id', 'id');
    }
}
