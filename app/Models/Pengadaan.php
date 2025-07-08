<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengadaan extends Model
{
    use HasFactory;

    protected $table = 'pengadaan';
    protected $primaryKey = 'id_pengadaan';

    protected $fillable = [
        'id_request_fk',
        'status',
        'tanggal_estimasi',
        'tanggal_pengadaan'
    ];

    public function request()
    {
        return $this->belongsTo(RequestPengadaan::class, 'id_request_fk');
    }
}
