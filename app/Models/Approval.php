<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    use HasFactory;

    protected $table = 'approval';
    protected $primaryKey = 'id_approval';

    protected $fillable = [
        'id_request_fk',
        'id_admin_fk',
        'level_approval',
        'status',
        'tanggal',
        'catatan'
    ];

    public function request()
    {
        return $this->belongsTo(RequestPengadaan::class, 'id_request_fk');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin_fk');
    }
}
