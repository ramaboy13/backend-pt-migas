<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Karyawan extends Model
{
    use HasFactory;

    protected $table = 'tb_karyawan';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'nik',
        'nama',
        'jabatan',
        'gaji_pokok',
        'bpjs_kesehatan',
        'bpjs_tenagakerja',
        'tgl_masuk',
        'aktif',
        'alamat',
    ];

    protected $casts = [
        'gaji_pokok' => 'decimal:2',
        'bpjs_kesehatan' => 'decimal:2',
        'bpjs_tenagakerja' => 'decimal:2',
        'tgl_masuk' => 'date',
        'aktif' => 'boolean',
    ];

    public function lemburKaryawans()
    {
        return $this->hasMany(LemburKaryawan::class, 'karyawan_id');
    }

    public function pendapatans()
    {
        return $this->hasMany(Pendapatan::class, 'karyawan_id');
    }

    public function potongans()
    {
        return $this->hasMany(Potongan::class, 'karyawan_id');
    }

    public function gajiKaryawans()
    {
        return $this->hasMany(GajiKaryawan::class, 'karyawan_id');
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
