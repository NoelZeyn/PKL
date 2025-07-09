<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryPemakaian extends Model
{
    use HasFactory;

    protected $table = 'history_pemakaian';

    protected $fillable = [
        'id_alat',
        'id_user',
        'jumlah',
        'keterangan',
        'tanggal_pemakaian',
    ];

    public function alat()
    {
        return $this->belongsTo(Alat::class, 'id_alat');
    }

    public function user()
    {
        return $this->belongsTo(Admin::class, 'id_user');
    }
}
