<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Tabung extends Model
{
    use HasFactory;

    protected $table = 'tb_tabung';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'nama',
        'berat'
    ];

    // Relationship dengan TransaksiOperasional
    public function transaksiOperasionals()
    {
        return $this->hasMany(TransaksiOperasional::class, 'tabung_id');
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