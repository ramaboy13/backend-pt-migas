<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Potongan extends Model
{
    use HasFactory;

    protected $table = 'tb_potongan';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'karyawan_id',
        'rp_bpjs_kesehatan',
        'rp_bpjs_tenagakerja',
        'total_potongan',
        'periode'
    ];

    protected $casts = [
        'periode' => 'date',
        'rp_bpjs_kesehatan' => 'decimal:2',
        'rp_bpjs_tenagakerja' => 'decimal:2',
        'total_potongan' => 'decimal:2'
    ];

    // Relationship dengan Karyawan
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    // Relationship dengan GajiKaryawan
    public function gajiKaryawans()
    {
        return $this->hasMany(GajiKaryawan::class, 'potongan_id');
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