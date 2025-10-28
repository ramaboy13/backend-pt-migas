<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;


class Pangkalan extends Model
{
    use HasFactory;

    protected $table = 'tb_pangkalan';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'regist_id',
        'name',
        'no_ktp',
        'alamat',
        'harga_satuan'
    ];

    protected $casts = [
        'harga_satuan' => 'decimal:2',
    ];

    // Relationship dengan TransaksiOperasional
    public function transaksiOperasionals()
    {
        return $this->hasMany(TransaksiOperasional::class, 'pangkalan_id');
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
