<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataDiri extends Model
{
    use HasFactory;

    protected $table = 'data_diri';
    protected $primaryKey = 'id_admin_user_fk';
    public $incrementing = false;

    protected $fillable = [
        'id_admin_user_fk',
        'nama_lengkap',
        'jabatan',
        'bpjs',
        'kontak',
        'foto_profil',  
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin_user_fk', 'id');
    }
}
