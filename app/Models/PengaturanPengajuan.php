<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanPengajuan extends Model
{
    protected $table = 'pengaturan_pengajuan';
    protected $fillable = ['is_open', 'tanggal_mulai', 'tanggal_selesai'];
}
