<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class KasPerusahaan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tb_kas_perusahaan';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'tanggal',
        'sumber_kas_id',
        'keterangan',
        'tipe_transaksi',
        'jumlah',
        'transaksi_operasional_id',
        'saldo_sebelum',
        'saldo_sesudah',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah' => 'decimal:2',
        'saldo_sebelum' => 'decimal:2',
        'saldo_sesudah' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationship dengan SumberKas
    public function sumberKas()
    {
        return $this->belongsTo(SumberKas::class, 'sumber_kas_id');
    }

    // Relationship dengan TransaksiOperasional
    public function transaksiOperasional()
    {
        return $this->belongsTo(TransaksiOperasional::class, 'transaksi_operasional_id');
    }

    // Scope untuk filter 
    public function scopeFilterByDate($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal', [$startDate, $endDate]);
    }

    public function scopeFilterBySumberKas($query, $sumberKasId)
    {
        return $query->where('sumber_kas_id', $sumberKasId);
    }

    public function scopeFilterByTipeTransaksi($query, $tipe)
    {
        return $query->where('tipe_transaksi', $tipe);
    }

    // boot method untuk generate id dan created_by otomatis
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
            if (empty($model->created_by)) {
                $model->created_by = Auth::check() ? Auth::user()->name : 'system';
            }
        });
    }
}
