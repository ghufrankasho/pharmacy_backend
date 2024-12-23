<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicinePharmacy extends Model
{
    use HasFactory;
    protected $fillable = [
        'pharmacy_id',
        'medicine_id',
        'confirmed',
        'quantity',
        
    ];
    public function pharmacy(){
        return $this->belongsTo(Pharmacy::class); 
    } 
    public function medicinede(){
        return $this->belongsTo(Medicine::class); 
    } 
}