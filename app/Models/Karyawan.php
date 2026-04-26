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

    public function komponenGaji()
    {
        return $this->hasMany(KomponenGaji::class, 'karyawan_id');
    }

    // Relationship khusus lembur
    public function lembur()
    {
        return $this->hasMany(KomponenGaji::class, 'karyawan_id')->where('tipe', 'LEMBUR');
    }

    // Relationship khusus tunjangan
    public function tunjangan()
    {
        return $this->hasMany(KomponenGaji::class, 'karyawan_id')->where('tipe', 'TUNJANGAN');
    }

    // Method untuk mendapatkan total lembur per bulan
    public function getTotalLemburPerBulan(int $bulan, int $tahun): float
    {
        return $this->komponenGaji()
            ->where('tipe', 'LEMBUR')
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->sum('nominal');
    }

    // Method untuk mendapatkan total tunjangan per bulan
    public function getTotalTunjanganPerBulan(int $bulan, int $tahun): float
    {
        return $this->komponenGaji()
            ->where('tipe', 'TUNJANGAN')
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->sum('nominal');
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
