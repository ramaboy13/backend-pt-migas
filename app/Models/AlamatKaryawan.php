<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class AlamatKaryawan extends Model
{
    use HasFactory;

    protected $table = 'alamat_karyawan';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'karyawan_id',
        'no_rumah',
        'is_kontrak',
        'desa',
        'kecamatan',
        'kabupaten',
        'provinsi',
        'note'
    ];

    protected $casts = [
        'is_kontrak' => 'boolean'
    ];

    // Relationship dengan Karyawan
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

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