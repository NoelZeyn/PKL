<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;



class Admin extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $table = 'admin';

    protected $fillable = [
        'NID',
        'password',
        'id_penempatan_fk',
        'id_bidang_fk',  
        'tingkatan_otoritas',
        'access',
        'password_changed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Relasi ke Penempatan (untuk Asman)
     */
    public function penempatan()
    {
        return $this->belongsTo(Penempatan::class, 'id_penempatan_fk');
    }

    /**
     * Relasi ke Bidang (untuk Manajer)
     */
    public function bidang()
    {
        return $this->belongsTo(Bidang::class, 'id_bidang_fk');
    }

    /**
     * Relasi ke Data Diri (bisa untuk profile)
     */
    public function dataDiri()
    {
        return $this->hasOne(DataDiri::class, 'id_admin_user_fk', 'id');
    }

    /**
     * Relasi ke Request Pengadaan (pengajuan ATK)
     */
    public function requests()
    {
        return $this->hasMany(RequestPengadaan::class, 'id_users_fk', 'id_request');
    }

    /**
     * Relasi ke Approval (untuk persetujuan ATK)
     */
    public function approvals()
    {
        return $this->hasMany(Approval::class, 'id_admin_fk');
    }

    /**
     * JWT: Return primary key (ID) for token
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * JWT: Return custom claims (optional)
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
