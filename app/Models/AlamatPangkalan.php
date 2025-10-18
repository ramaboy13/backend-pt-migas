<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class AlamatPangkalan extends Model
{
    use HasFactory;

    protected $table = 'alamat_pangkalan';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'pangkalan_id',
        'no_kios',
        'desa',
        'kecamatan',
        'kabupaten',
        'provinsi',
        'harga_satuan',
        'note'
    ];

    protected $casts = [
        'harga_satuan' => 'decimal:2'
    ];

    // Relationship dengan Pangkalan
    public function pangkalan()
    {
        return $this->belongsTo(Pangkalan::class, 'pangkalan_id');
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