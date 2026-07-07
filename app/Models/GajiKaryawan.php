<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class GajiKaryawan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tb_gaji_karyawan';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'karyawan_id',
        'bulan',
        'tahun',
        'tanggal_gaji',
        'gapok',
        'total_lembur',
        'total_tunjangan',
        'total_pendapatan',
        'potongan_bpjs_kesehatan',
        'potongan_bpjs_tenagakerja',
        'potongan_lainnya',
        'total_potongan',
        'pph21',
        'gaji_bersih',
        'status',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'bulan' => 'integer',
        'tahun' => 'integer',
        'tanggal_gaji' => 'date',
        'gapok' => 'decimal:2',
        'total_lembur' => 'decimal:2',
        'total_tunjangan' => 'decimal:2',
        'total_pendapatan' => 'decimal:2',
        'potongan_bpjs_kesehatan' => 'decimal:2',
        'potongan_bpjs_tenagakerja' => 'decimal:2',
        'potongan_lainnya' => 'decimal:2',
        'total_potongan' => 'decimal:2',
        'pph21' => 'decimal:2',
        'gaji_bersih' => 'decimal:2',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationship dengan Karyawan
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    // Relationship dengan KomponenGaji untuk lembur
    public function komponenGajiLembur()
    {
        return $this->hasMany(KomponenGaji::class, 'karyawan_id', 'karyawan_id')
            ->where('tipe', 'LEMBUR')
            ->whereYear('tanggal', $this->tahun)
            ->whereMonth('tanggal', $this->bulan);
    }

    // Relationship dengan KomponenGaji untuk tunjangan
    public function komponenGajiTunjangan()
    {
        return $this->hasMany(KomponenGaji::class, 'karyawan_id', 'karyawan_id')
            ->where('tipe', 'TUNJANGAN')
            ->whereYear('tanggal', $this->tahun)
            ->whereMonth('tanggal', $this->bulan);
    }

    // accessor untuk nama bulan
    public function getNamaBulanAttribute(): string
    {
        $bulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return $bulanIndo[$this->bulan] ?? 'Unknown';
    }

    // accessor untuk status display
    public function getStatusDisplayAttribute(): string
    {
        return match ($this->status) {
            'Belum Dibayar' => 'Belum Dibayar',
            'Telah Dibayar' => 'Telah Dibayar',
            default => $this->status
        };
    }

    // boot method untuk generate id dan tanggal gaji otomatis
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
            if (empty($model->tanggal_gaji)) {
                $model->tanggal_gaji = now();
            }
        });
    }
}
