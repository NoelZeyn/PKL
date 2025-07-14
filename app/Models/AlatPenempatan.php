<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlatPenempatan extends Model
{
    use HasFactory;

    protected $table = 'alat_penempatan';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'id_alat_fk',
        'id_penempatan_fk',
        'stock',
        'stock_min',
        'stock_max',
    ];

    // Relasi ke alat
    public function alat()
    {
        return $this->belongsTo(Alat::class, 'id_alat_fk', 'id_alat');
    }

    // Relasi ke penempatan
    public function penempatan()
    {
        return $this->belongsTo(Penempatan::class, 'id_penempatan_fk');
    }
}
