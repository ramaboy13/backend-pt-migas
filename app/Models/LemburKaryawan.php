<?php
// app/Models/LemburKaryawan.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class LemburKaryawan extends Model
{
    use HasFactory;

    protected $table = 'tb_lembur_karyawan';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tanggal',
        'hari',
        'karyawan_id',
        'jam_lembur',
        'total_jam_lembur',
        'upah_lembur_perjam',
        'rupiah_lembur',
        'keterangan'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_lembur' => 'decimal:2',
        'total_jam_lembur' => 'decimal:2',
        'upah_lembur_perjam' => 'decimal:2',
        'rupiah_lembur' => 'decimal:2'
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