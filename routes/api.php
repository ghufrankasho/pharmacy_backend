<?php

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\MedicinedetialController;
use App\Http\Controllers\MedicinePharmacyController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PharmacyController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
 
Route::group(['prefix' => 'auth'], function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/reset_password', [AuthController::class, 'reset_password']);
});

Route::group(['middleware'=>'auth:warehouse','prefix'=>'warehouse'],function($router){ 
 
    //home page requests
    
    Route::get('/home',[WarehouseController::class,'home'])->name("home");//parame id
    Route::get('/',[WarehouseController::class,'show'])->name("warehouse_medicines");
    Route::post('/',[WarehouseController::class,'update']);
    // Route::delete('/medicine/{id}',[MedicineController::class,'destroy']);
    //medicine
    Route::get('/medicine',[MedicineController::class,'index'])->name('indexmedicine');;//parame id
    Route::post('/medicine/add',[MedicineController::class,'store'])->name('addmedicine');
    Route::get('/medicine/search',[MedicineController::class,'search'])->name('searchmedicine');;
    Route::get('/medicine',[MedicineController::class,'show'])->name('showmedicine');;
    Route::post('/medicine',[MedicineController::class,'update'])->name('updatemedicine');;
    Route::delete('/medicine/{id}',[MedicineController::class,'destroy']);
    //medicine Detailes
    // Route::get('/medicineDetials',[MedicinedetialController::class,'index']);//parame id
    Route::post('/medicineDetials/add',[MedicinedetialController::class,'store'])->name('addmedicineDetials');
    Route::get('/medicineDetials/{id}',[MedicinedetialController::class,'show']);
    Route::post('/medicineDetials',[MedicinedetialController::class,'update'])->name('updatemedicineDetials');;
    Route::delete('/medicineDetials/{id}',[MedicinedetialController::class,'destroy']);

   
    //medicine-pharmacy 
    Route::get('/medicinePharmacy',[MedicinePharmacyController::class,'confirmOrder']);
    Route::get('/medicinePharmacy/search',[MedicinePharmacyController::class,'search'])->name('search');
    Route::get('/medicinePharmacy/orders',[MedicinePharmacyController::class,'getOrdersWarehouse']);
    Route::delete('/medicinePharmacy',[MedicinePharmacyController::class,'destroy']);
    
     
});

Route::group(['middleware'=>'auth:pharmacy','prefix'=>'pharmacy'],function($router){ 
    //medicine
    Route::post('/medicine',[MedicineController::class,'search']);
    Route::get('/medicine',[MedicineController::class,'show']);
    
    //medicine-pharmacy 
    Route::post('/medicinePharmacy/sendOrder',[MedicinePharmacyController::class,'sendOrder']);
    Route::get('/medicinePharmacy',[MedicinePharmacyController::class,'getOrders']);
    Route::get('/',[MedicinePharmacyController::class,'showWarehouseOrder']);
    Route::delete('/medicinePharmacy',[MedicinePharmacyController::class,'destroy']);
    //warehouse
    Route::get('/warehouse',[WarehouseController::class,'get']);
    // order

    Route::post('/',[OrderController::class,'update']);
   
    //users orders
    Route::get('/user',[OrderController::class,'get']);
    // Route::get('/warehouse',[WarehouseController::class,'show']);
     
});


Route::group(['middleware'=>'auth:user','prefix'=>'user'],function($router){ 
    //order
    Route::post('/sendOrders',[OrderController::class,'store']);
    Route::get('/orders',[OrderController::class,'show']);
    Route::delete('/orders',[OrderController::class,'destroy']);
    Route::get('/medicine',[MedicineController::class,'show']);
    
    //  pharmacy 
    Route::get('/pharmacy',[PharmacyController::class,'get']);
    // Route::get('/medicinePharmacy',[MedicinePharmacyController::class,'getOrders']);
    
    // //warehouse
    // Route::get('/warehouse',[WarehouseController::class,'index']);
    // Route::get('/warehouse',[WarehouseController::class,'show']);
     
});













Route::get('/view-clear', function() {
    $exitCode = Artisan::call('cache:clear');
    $exitCode = Artisan::call('view:clear');
    $exitCode = Artisan::call('config:cache');
    $exitCode = Artisan::call('route:cache');
    return '<h1>View cache cleared</h1>';
});