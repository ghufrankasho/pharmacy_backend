<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Warehouse extends Authenticatable implements JWTSubject
{
    
    // JWT methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address'
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];
    public function medicines(){

        return $this->hasMany(Medicine::class);
    }
}