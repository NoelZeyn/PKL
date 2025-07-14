<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penempatan extends Model
{
    use HasFactory;

    protected $table = 'penempatan';

    protected $fillable = ['nama_penempatan', 'id_bidang_fk'];

    public function admins()
    {
        return $this->hasMany(Admin::class, 'id_penempatan_fk');
    }

    public function bidang()
    {
        return $this->belongsTo(Bidang::class, 'id_bidang_fk');
    }
        public function alat()
    {
        return $this->belongsToMany(Alat::class, 'alat_penempatan', 'id_penempatan_fk', 'id_alat_fk')
            ->withPivot(['stock', 'stock_min', 'stock_max'])
            ->withTimestamps();
    }
}
