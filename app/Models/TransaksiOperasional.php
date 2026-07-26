<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TransaksiOperasional extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tb_transaksi_operasional';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'tanggal',
        'no_ref',
        'jenis_transaksi',
        'keterangan',
        'pangkalan_id',
        'tabung_id',
        'asset_id',
        'is_pemasukan',
        'qty',
        'unit',
        'harga_satuan',
        'jumlah',
        'kas_perusahaan_id',
        'created_by',
        'user_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'is_pemasukan' => 'boolean',
        'qty' => 'integer',
        'harga_satuan' => 'decimal:2',
        'jumlah' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationship dengan Pangkalan
    public function pangkalan()
    {
        return $this->belongsTo(Pangkalan::class, 'pangkalan_id');
    }

    // Relationship dengan Tabung
    public function tabung()
    {
        return $this->belongsTo(Tabung::class, 'tabung_id');
    }

    // Relationship dengan Asset
    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    // Relationship dengan KasPerusahaan
    public function kasPerusahaan()
    {
        return $this->belongsTo(KasPerusahaan::class, 'kas_perusahaan_id');
    }

    // Relationship dengan User (Pencatat)
    public function pencatat()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scope untuk filter
    public function scopeFilterByDate($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal', [$startDate, $endDate]);
    }

    public function scopePemasukan($query)
    {
        return $query->where('is_pemasukan', true);
    }

    public function scopePengeluaran($query)
    {
        return $query->where('is_pemasukan', false);
    }

    public function scopeByJenisTransaksi($query, $jenis)
    {
        return $query->where('jenis_transaksi', $jenis);
    }

    // Business logic methods
    public function getJenisDisplayAttribute(): string
    {
        return match ($this->jenis_transaksi) {
            'PEMBELIAN_GAS' => 'Pembelian Gas',
            'MAINTENANCE' => 'Maintenance',
            'PENJUALAN_GAS' => 'Penjualan Gas',
            'LAINNYA' => 'Lainnya',
            default => $this->jenis_transaksi
        };
    }

    public function getStatusAttribute(): string
    {
        return $this->is_pemasukan ? 'Pemasukan' : 'Pengeluaran';
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
            if (empty($model->created_by)) {
                $model->created_by = Auth::user()->name ?? 'system';
            }
            if (empty($model->user_id) && Auth::check()) {
                $model->user_id = Auth::id();
            }
            if (empty($model->no_ref)) {
                $prefix = $model->is_pemasukan ? 'IN' : 'OUT';
                $date = date('Ymd');
                $random = strtoupper(Str::random(6));
                $model->no_ref = "TRX-{$prefix}-{$date}-{$random}";
            }
        });
    }
}
