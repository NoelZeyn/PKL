<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penempatan extends Model
{
    use HasFactory;

    protected $table = 'penempatan';

    protected $fillable = ['nama_penempatan'];

    public function admins()
    {
        return $this->hasMany(Admin::class, 'id_penempatan_fk');
    }
}
