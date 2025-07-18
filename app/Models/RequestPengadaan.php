<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestPengadaan extends Model
{
    use HasFactory;

    protected $table = 'request';
    protected $primaryKey = 'id_request';
// 7193142JA
    protected $fillable = [
        'id_inventoris_fk',
        'id_users_fk',
        'tanggal_permintaan',
        'status',
        'status_by',
        'keterangan',
        'jumlah',
        'total'
    ];

    public function alat()
    {
        return $this->belongsTo(Alat::class, 'id_inventoris_fk');
    }

    public function user()
    {
        return $this->belongsTo(Admin::class, 'id_users_fk', 'id');
    }

    public function approvals()
    {
        return $this->hasMany(Approval::class, 'id_request_fk');
    }

    // public function pengadaan()
    // {
    //     return $this->hasOne(Pengadaan::class, 'id_request_fk');
    // }
}
