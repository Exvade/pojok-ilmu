<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class DepartmentAlias extends BaseModel
{
    use HasUuids, SoftDeletes;
}
