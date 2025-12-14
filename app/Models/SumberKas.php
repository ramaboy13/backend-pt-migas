<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SumberKas extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tb_sumber_kas';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'tipe',
        'nama_bank',
        'nomor_rekening',
        'atas_nama',
        'saldo_awal',
        'saldo_terakhir',
        'aktif',
        'keterangan',
    ];

    protected $casts = [
        'saldo_awal' => 'decimal:2',
        'saldo_terakhir' => 'decimal:2',
        'aktif' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationship dengan KasPerusahaan
    public function kasPerusahaan()
    {
        return $this->hasMany(KasPerusahaan::class, 'sumber_kas_id');
    }

    // Scope untuk filter
    public function scopeActive($query)
    {
        return $query->where('aktif', true);
    }

    public function scopeBank($query)
    {
        return $query->where('tipe', 'BANK');
    }

    public function scopeCash($query)
    {
        return $query->where('tipe', 'CASH');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('nama_bank', 'LIKE', "%{$search}%")
                ->orWhere('nomor_rekening', 'LIKE', "%{$search}%")
                ->orWhere('atas_nama', 'LIKE', "%{$search}%")
                ->orWhere('keterangan', 'LIKE', "%{$search}%");
        });
    }

    // Business logic methods
    public function isBank(): bool
    {
        return $this->tipe === 'BANK';
    }

    public function isCash(): bool
    {
        return $this->tipe === 'CASH';
    }

    public function updateSaldoTerakhir(float $saldoBaru): bool
    {
        $this->saldo_terakhir = $saldoBaru;

        return $this->save();
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
            if (empty($model->saldo_terakhir)) {
                $model->saldo_terakhir = $model->saldo_awal;
            }
        });

        static::updating(function ($model) {
            // Jika update saldo_awal, update juga saldo_terakhir
            if ($model->isDirty('saldo_awal')) {
                $model->saldo_terakhir = $model->saldo_awal;
            }
        });
    }
}
