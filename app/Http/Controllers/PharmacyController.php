<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pharmacy;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
class PharmacyController extends Controller
{
    private function index(){
        
        try{
         
            $user = auth('user')->user();
            
            // If no user is authenticated
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'errors' => 'No authenticated user found',
                    'message' => 'No authenticated user found',
                ], 404);
            }
          $Pharmacy=Pharmacy::latest()->get();
         if ($Pharmacy){
              return response()->json( [
                  'status'=>true,
                  'data'=>$Pharmacy,
                  'message'=>"data obtianed successfully"
                 ]
               , 200);
          }
          else{
              return response()->json([  
                  'status' => false,
                  'message' => 'something went wrong',
                  ], 204);
          }

      }
      catch (\Throwable $th) {
          return response()->json([
              'status' => false,
              'message' => $th->getMessage()
          ], 500);
      }
      
    }
    private function show(Request $request){
       
        try {  
          
           
            $validate = Validator::make( $request->all(),
                ['pharmacy_id'=>'required|integer|exists:pharmacies,id']);
            if($validate->fails()){
            return response()->json([
                'status' => false,
               'message' => 'خطأ في التحقق',
                'errors' => $validate->errors()
            ], 422);}
            $Pharmacy= Pharmacy::find($request->pharmacy_id);
            $pharamcy_detail=  $Pharmacy->medicine_pharmacy()->with('medicine')->get();
            if( $pharamcy_detail){
                
                return response()->json(
                    [
                        'status'=>true,
                        'data'=>$pharamcy_detail,
                        'message'=>"data obtained successfully"
                       ]
                 , 200);
                
            }
    
              
                return response()->json([  
                    'status' => false,
                    'message' => 'something went wrong',
                    ], 204);
            
        }
        catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    public function get(Request $request){
       
        try {  
          
           
            if( $request->has('pharmacy_id')){
                return $this->show($request);
             
            }
            else{
                return $this->index();
            }
        }
        catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
          } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while requesting this Warehouse .'], 500);
          }
    }
   
}