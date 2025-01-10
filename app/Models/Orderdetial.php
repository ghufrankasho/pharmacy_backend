<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orderdetial extends Model
{
    use HasFactory;
    protected $fillable = [
        'medicine_id',
        'order_id',
       
    ];
    public function orders(){
        return $this->belongsTo(Order::class); 
     } 
}