<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriPengadaan extends Model
{
    use HasFactory;

    protected $table = 'kategori_pengadaan';
    protected $primaryKey = 'id_kategori';

    protected $fillable = ['nama'];

    public function alats()
    {
        return $this->hasMany(Alat::class, 'id_kategori_fk');
    }
}
