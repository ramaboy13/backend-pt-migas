<?php

// app/Models/KomponenGaji.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class KomponenGaji extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tb_komponen_gaji';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'tipe',
        'karyawan_id',
        'tanggal',
        'jam_lembur',
        'total_jam_lembur',
        'upah_perjam',
        'nominal',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_lembur' => 'decimal:2',
        'total_jam_lembur' => 'decimal:2',
        'upah_perjam' => 'decimal:2',
        'nominal' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationship dengan Karyawan
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    // Scope untuk filter tipe LEMBUR
    public function scopeLembur($query)
    {
        return $query->where('tipe', 'LEMBUR');
    }

    // Scope untuk filter tipe TUNJANGAN
    public function scopeTunjangan($query)
    {
        return $query->where('tipe', 'TUNJANGAN');
    }

    // Scope untuk filter berdasarkan periode bulan/tahun
    public function scopePeriode($query, $bulan, $tahun)
    {
        return $query->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan);
    }

    // Accessor untuk tipe dalam bahasa Indonesia
    public function getTipeDisplayAttribute(): string
    {
        return match ($this->tipe) {
            'LEMBUR' => 'Lembur',
            'TUNJANGAN' => 'Tunjangan',
            default => $this->tipe
        };
    }

    // Boot method
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
