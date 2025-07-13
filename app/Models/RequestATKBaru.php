<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestATKBaru extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_alat';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_kategori_fk',
        'id_user_fk',
        'nama_barang',
        'satuan',
        'harga_estimasi',
        'keterangan',
        'catatan',
        'status',
        'status_by',
    ];

    // Relasi ke Kategori
    public function kategori()
    {
        return $this->belongsTo(KategoriPengadaan::class, 'id_kategori_fk', 'id_kategori');
    }

    // Relasi ke Admin/User yang mengajukan
    public function user()
    {
        return $this->belongsTo(Admin::class, 'id_user_fk', 'id');
    }
}
