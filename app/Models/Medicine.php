<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'warehouse_id',
        'calssification',
       
       
    ];
    public function warehouse(){
        return $this->belongsTo(Warehouse::class);
    }
    public function medicinedetials(){
       return $this->hasOne(Medicinedetial::class); 
    } 
    public function medicine_pharmacy(){
        return $this->hasMany(MedicinePharmacy::class); 
    } 
    public function waited_orders() {
        return $this->medicine_pharmacy()->where('confirmed', 'wait');
    }
    public function order_detials(){
        return $this->hasMany(Orderdetial::class); 
    } 
}