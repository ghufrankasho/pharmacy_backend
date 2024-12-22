<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicinedetial extends Model
{
    use HasFactory;
    protected $fillable = [
        'medicine_id',
        'component',
        'price',
        'expire_date',
        'quantity'
    ];
    public function medicine(){
        
        return $this->belongsTo(Medicine::class);
    }
    public function order(){
        
        return $this->belongsTo(Order::class);
    }
}