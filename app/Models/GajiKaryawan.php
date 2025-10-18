<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class GajiKaryawan extends Model
{
    use HasFactory;

    protected $table = 'tb_gaji_karyawan';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'karyawan_id',
        'pendapatan_id',
        'potongan_id',
        'subtotal',
        'pph21',
        'gaji_bersih',
        'periode'
    ];

    protected $casts = [
        'periode' => 'date',
        'subtotal' => 'decimal:2',
        'pph21' => 'decimal:2',
        'gaji_bersih' => 'decimal:2'
    ];

    // Relationship dengan Karyawan
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    // Relationship dengan Pendapatan
    public function pendapatan()
    {
        return $this->belongsTo(Pendapatan::class, 'pendapatan_id');
    }

    // Relationship dengan Potongan
    public function potongan()
    {
        return $this->belongsTo(Potongan::class, 'potongan_id');
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