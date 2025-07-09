<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $table = 'admin';

    protected $fillable = [
        'NID',
        'password',
        'id_penempatan_fk',
        'tingkatan_otoritas',
        'access',
        'password_changed_at',
    ];

    protected $hidden = ['password'];

    public function penempatan()
    {
        return $this->belongsTo(Penempatan::class, 'id_penempatan_fk');
    }

    public function dataDiri()
    {
        return $this->hasOne(DataDiri::class, 'id_admin_user_fk', 'id');
    }

    public function requests()
    {
        return $this->hasMany(RequestPengadaan::class, 'id_users_fk');
    }

    public function approvals()
    {
        return $this->hasMany(Approval::class, 'id_admin_fk');
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
