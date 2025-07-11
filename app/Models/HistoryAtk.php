<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryAtk extends Model
{
    use HasFactory;

    protected $table = 'history_atk';
    protected $primaryKey = 'id_history';

    protected $fillable = [
        'id_admin_fk',
        'id_alat_fk',
        'jenis_aksi',
        'deskripsi',
        'jumlah',
        'tanggal',
    ];

    // Relasi ke Admin
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin_fk', 'id');
    }

    // Relasi ke Alat
    public function alat()
    {
        return $this->belongsTo(Alat::class, 'id_alat_fk', 'id_alat');
    }
}
