<?php

namespace App\Http\Controllers;

use App\Models\MedicinePharmacy;

use App\Models\Medicinedetial;
use App\Models\Medicine;
use App\Models\Pharmacy;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
class MedicinePharmacyController extends Controller
{
    public function sendOrder(Request $request)
    {
        try {
            
            $user = auth('pharmacy')->user();
            
            // If no user is authenticated
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'errors' => 'No authenticated user found',
                    'message' => 'No authenticated user found',
                ], 404);
            }
            $validate = Validator::make($request->all(), [
                'orders' => 'required|array', // Validate that 'orders' is an array
                'orders.*.medicine_id' => 'required|exists:medicines,id', // Validate each medicine_id
                'orders.*.quantity' => 'required|integer|min:1', // Validate each quantity
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation Error',
                    'errors' => $validate->errors()
                ], 422);
            }

             $orders = $request->orders; // Extract the orders array

            // Create each order
            foreach ($orders as $order) {
                $medicineId = $order['medicine_id'];
                $quantity = $order['quantity'];

                // Create a new record in the medicine_pharmacies table
                DB::table('medicine_pharmacies')->insert([
                    'pharmacy_id' => $user->id,
                    'medicine_id' => $medicineId,
                    'quantity' => $quantity,
                 
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Order created successfully',
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the order.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function getOrders(Request $request){
        try {
             
            // Validate the request
            $validate = Validator::make($request->all(), [
                'id' => 'required|exists:pharmacies,id', // Validate the pharmacy ID
              ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation Error',
                    'errors' => $validate->errors()
                ], 422);
            }

 
            $pharmacy = Pharmacy::with('medicine_pharmacy')->find($request->id);
           
            if( $pharmacy){
                return response()->json([
                    'status' => true,
                    "data"=> $pharmacy,
                    'message' => ' data obtained successfully',
                ], 200);
            }
            return response()->json([
                'status' => false,
                "data"=> null,
                'message' => 'something went wrong',
            ], 200);

          
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the order.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function getOrdersWarehouse(){
        try {
             
            
            $user = auth('warehouse')->user();
            
            // If no user is authenticated
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'errors' => 'No authenticated user found',
                    'message' => 'No authenticated user found',
                ], 404);
            }
            $medicines=$user->medicines()->get();
            $orders=collect();
            foreach($medicines as $med){
                
                $orders1=$med->medicine_pharmacy()->with('medicine','pharmacy')->get();
                
            
                $orders=$orders->merge($orders1);
            }
            if ($orders){
         
                return response()->json( [
                    'status'=>true,
                    'data'=>$orders,
                    'message'=>"data obtained successfully"
                   ] , 200);
            
           
          }
            else{
                return response()->json([  
                    'status' => false,
                    'message' => 'something went wrong',
                    ], 204);
                }
            return $orders;
        

          
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the order.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function destroy(Request $request){
            try {  
                 
              
                $validate = Validator::make( $request->all(),
                    ['id'=>'required|exists:medicine_pharmacies,id']);
                if($validate->fails()){
                return response()->json([
                    'status' => false,
                   'message' => 'خطأ في التحقق',
                    'errors' => $validate->errors()
                ], 422);}
              
                $med_pharmacy=MedicinePharmacy::find($request->id);
                
                if($med_pharmacy )
                { 
                    $result= $med_pharmacy->delete();
                    if($result)
                     {
                    
                    return response()->json(
                        [
                        'status'=>true,
                        'data'=>"data deleted successfully",
                        'message'=>"data deleted successfully"
                           ]
                     , 200);}
                    
                }
        
                  
                return response()->json( [
                        'status'=>true,
                        'error'=>"something went wrong ",
                        'message'=>"something went wrong "
                     ], 204);
            }
        catch (ValidationException $e) {
                  return response()->json(['errors' => $e->errors()], 422);
            } catch (\Exception $e) {
                  return response()->json(['message' => 'An error occurred while deleting the categroy.'], 500);
        }
    }
    public function search(Request $request)
    {
        try {
            $user = auth('warehouse')->user();
    
            // If no user is authenticated
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'errors' => 'No authenticated user found',
                    'message' => 'No authenticated user found',
                ], 404);
            }
    
            // Validate the search input
            $validateSearch = Validator::make($request->all(), [
                'search' => 'required|string|min:3'
            ]);
    
            if ($validateSearch->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'خطأ في التحقق',
                    'errors' => $validateSearch->errors()
                ], 422);
            }
    
            $searchTerm = $request->search;
            $final_result = collect();
    
            // Retrieve all medicines related to the authenticated user
            $med_data = $user->medicines()->with('medicine_pharmacy.pharmacy')->get();
            
            foreach ($med_data as $med) {
                $orders = $med->medicine_pharmacy()
                    ->whereHas('pharmacy', function ($query) use ($searchTerm) {
                        $query->where('name', 'LIKE', "%{$searchTerm}%");
                    })
                    ->orWhereHas('medicine', function ($query) use ($searchTerm) {
                        $query->where('name', 'LIKE', "%{$searchTerm}%")
                        ->orwhere('calssification', 'LIKE', "%{$searchTerm}%");
                    })
                    ->with('medicine', 'pharmacy')
                    ->get();
    
                $final_result = $final_result->merge($orders);
            }
            $result=array();
            foreach( $final_result as $req){
                if(! in_array($req,$result)){
                    array_push($result,$req);
                }
            }
            if (count($result)!=0) {
                return response()->json([
                    'status' => true,
                    'data' => $result,
                    'message' => "Data obtained successfully"
                ], 200);
            } else {
                return response()->json([
                    'status' => true,
                    'data' => [],
                    'message' => "No data found for this search"
                ], 204);
            }
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'An error occurred while processing the search request.'
            ], 500);
        }
    }
    public function confirmOrder(Request $request){
        try {  
                 
              
            $validate = Validator::make( $request->all(),
                ['id'=>'required|exists:medicine_pharmacies,id']);
            if($validate->fails()){
            return response()->json([
                'status' => false,
               'message' => 'خطأ في التحقق',
                'errors' => $validate->errors()
            ], 422);}
          
            $med_pharmacy=MedicinePharmacy::find($request->id);
            
            if($med_pharmacy )
            { $med_pharmacy->confirmed="done";
                $result= $med_pharmacy->save();
                if($result)
                 {
                
                return response()->json(
                    [
                    'status'=>true,
                    'data'=>"data confirmed successfully",
                    'message'=>"data confirmed successfully"
                       ]
                 , 200);}
                
            }
    
              
            return response()->json( [
                    'status'=>true,
                    'error'=>"something went wrong ",
                    'message'=>"something went wrong "
                 ], 204);
        }
    catch (ValidationException $e) {
              return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
              return response()->json(['message' => 'An error occurred while deleting the categroy.'], 500);
    } 
    }
    }

    
 