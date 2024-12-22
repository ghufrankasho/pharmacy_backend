<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Tymon\JWTAuth\Contracts\JWTSubject;

class Pharmacy extends  Authenticatable implements JWTSubject
{
    use HasFactory;
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
        'address','logo'
    ];
    public function medicine_pharmacy(){
        return $this->hasMany(MedicinePharmacy::class); 
     } 
    public function orders(){
        return $this->hasMany(Order::class); 
     } 
}