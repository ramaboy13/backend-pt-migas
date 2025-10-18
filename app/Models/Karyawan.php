<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Karyawan extends Model
{
    use HasFactory;

    protected $table = 'tb_karyawan';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'NIK',
        'nama',
        'jabatan',
        'gapok',
        'bpjs_kesehatan',
        'bpjs_tenagakerja',
        'tgl_masuk',
        'is_active'
    ];

    protected $casts = [
        'gapok' => 'decimal:2',
        'bpjs_kesehatan' => 'decimal:2',
        'bpjs_tenagakerja' => 'decimal:2',
        'tgl_masuk' => 'date',
        'is_active' => 'boolean'
    ];

    // Relationship dengan AlamatKaryawan
    public function alamat()
    {
        return $this->hasOne(AlamatKaryawan::class, 'karyawan_id');
    }

    // Relationship dengan LemburKaryawan
    public function lemburKaryawans()
    {
        return $this->hasMany(LemburKaryawan::class, 'karyawan_id');
    }

    // Relationship dengan Pendapatan
    public function pendapatans()
    {
        return $this->hasMany(Pendapatan::class, 'karyawan_id');
    }

    // Relationship dengan Potongan
    public function potongans()
    {
        return $this->hasMany(Potongan::class, 'karyawan_id');
    }

    // Relationship dengan GajiKaryawan
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