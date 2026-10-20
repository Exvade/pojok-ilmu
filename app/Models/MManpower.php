<?php

namespace App\Models;

use App\Services\DepartmentGroupService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class MManpower extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'm_manpower';

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'haris_synced_at' => 'datetime',
        ];
    }

    public function trainingDepartment(): ?string
    {
        return $this->department_group_override
            ?? $this->department_group
            ?? DepartmentGroupService::normalize($this->departemen);
    }

    public function scopeForTrainingDepartment(Builder $query, string $department): Builder
    {
        $department = DepartmentGroupService::normalize($department);

        return $query->where(function (Builder $query) use ($department): void {
            $query->where('department_group_override', $department)
                ->orWhere(function (Builder $groupQuery) use ($department): void {
                    $groupQuery->whereNull('department_group_override')
                        ->where('department_group', $department);
                })->orWhere(function (Builder $legacyQuery) use ($department): void {
                    $legacyQuery->whereNull('department_group_override')
                        ->whereNull('department_group')
                        ->where(function (Builder $departmentQuery) use ($department): void {
                            $departmentQuery->where('departemen', $department);

                            if ($department === DepartmentGroupService::PRODUCTION_PLASTIC) {
                                $departmentQuery->orWhere('departemen', 'like', DepartmentGroupService::PRODUCTION_PLASTIC . '%');
                            }
                        });
                });
        });
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'user_id', 'mp_id');
    }

    public function skillMatrices(): HasMany
    {
        return $this->hasMany(OperatorSkillMatrix::class, 'manpower_id', 'id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(TrainingEnrollment::class, 'manpower_id', 'id');
    }

    public function trainingAssignmentLogs(): HasMany
    {
        return $this->hasMany(TrainingAssignmentLog::class, 'manpower_id', 'id');
    }
}
