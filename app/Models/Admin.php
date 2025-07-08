<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;

    protected $table = 'admin';

    protected $fillable = [
        'NID',
        'password',
        'id_penempatan_fk',
        'tingkatan_otoritas'
    ];

    protected $hidden = ['password'];

    public function penempatan()
    {
        return $this->belongsTo(Penempatan::class, 'id_penempatan_fk');
    }

    public function dataDiri()
    {
        return $this->hasOne(DataDiri::class, 'id_admin_user_fk');
    }

    public function requests()
    {
        return $this->hasMany(RequestPengadaan::class, 'id_users_fk');
    }

    public function approvals()
    {
        return $this->hasMany(Approval::class, 'id_admin_fk');
    }
}
