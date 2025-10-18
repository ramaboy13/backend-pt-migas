<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Pendapatan extends Model
{
    use HasFactory;

    protected $table = 'tb_pendapatan';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'karyawan_id',
        'tunjangan',
        'periode',
        'total_pendapatan'
    ];

    protected $casts = [
        'periode' => 'date',
        'tunjangan' => 'decimal:2',
        'total_pendapatan' => 'decimal:2'
    ];

    // Relationship dengan Karyawan
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    // Relationship dengan GajiKaryawan
    public function gajiKaryawans()
    {
        return $this->hasMany(GajiKaryawan::class, 'pendapatan_id');
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