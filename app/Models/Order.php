<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'pharmacy_id',
        'user_id',
       
    ];
    
    public function order_detials(){
        return $this->hasMany(Orderdetial::class); 
    } 
    public function user(){
        return $this->belongsTo(User::class); 
    } 
    public function pharamcy(){
        return $this->belongsTo(Pharmacy::class); 
    }
}