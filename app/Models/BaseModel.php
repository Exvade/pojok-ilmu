<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

abstract class BaseModel extends Model
{
    /**
     * MWT Standard: Jangan allow mass assignment secara default jika tidak menggunakan
     * Form Request. Developer diwajibkan melakukan validasi manual melalui Controller
     * atau menggunakan Form Request.
     *
     * @var array
     */
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->check() && Schema::hasColumn($model->getTable(), 'created_by')) {
                $model->created_by = auth()->id();
            }
        });

        static::updating(function ($model) {
            if (auth()->check() && Schema::hasColumn($model->getTable(), 'updated_by')) {
                $model->updated_by = auth()->id();
            }
        });
    }
}
