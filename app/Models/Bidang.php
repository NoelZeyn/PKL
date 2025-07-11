<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bidang extends Model
{
    use HasFactory;

    protected $table = 'bidang';

    protected $fillable = ['nama_bidang'];

    public function penempatan()
    {
        return $this->hasMany(Penempatan::class, 'id_bidang_fk');
    }
    public function admin()
    {
        return $this->hasMany(Admin::class, 'id_bidang_fk');
    }
}
