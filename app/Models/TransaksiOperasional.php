<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class TransaksiOperasional extends Model
{
    use HasFactory;

    protected $table = 'tb_transaksi_operasional';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tanggal',
        'no_ref',
        'keterangan',
        'pangkalan_id',
        'tabung_id',
        'is_in',
        'qty',
        'unit',
        'harga_satuan',
        'debit',
        'credit'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'is_in' => 'boolean',
        'harga_satuan' => 'decimal:2',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2'
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