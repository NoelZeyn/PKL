<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alat extends Model
{
    use HasFactory;

    protected $table = 'alat';
    protected $primaryKey = 'id_alat';

    protected $fillable = [
        'id_kategori_fk',
        'nama_barang',
        'stock_min',
        'stock_max',
        'stock',
        'satuan',
        'harga_satuan',
        'harga_estimasi',
        'order',
        'keterangan'
    ];

    public function kategori()
    {
        return $this->belongsTo(KategoriPengadaan::class, 'id_kategori_fk');
    }

    public function requests()
    {
        return $this->hasMany(RequestPengadaan::class, 'id_inventoris_fk');
    }
}
