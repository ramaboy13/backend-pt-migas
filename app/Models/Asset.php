<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Asset extends Model
{
    use HasFactory;

    protected $table = 'tb_asset';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'nama',
        'identitas',
        'jumlah',
        'catatan',
        'status',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
        });
    }
}
