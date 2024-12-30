<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Medicinedetial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
class MedicinedetialController extends Controller
{
    public function store(Request $request){
        
        try{
            $validatemed = Validator::make($request->all(), 
            [
                'quantity' => 'integer|required',
  
                'component'=>'string|required',
                'price'=>'required',
                'expire_date'=>'date|required',
                'medicine_id'=>'required|integer|exists:medicines,id'
              ]);
             
            if($validatemed->fails()){
                return response()->json([
                    'status' => false,
                     'message' => 'خطأ في التحقق'
                     ,
                    'errors' => $validatemed->errors()
                ], 422);
            }
           
            $med = Medicinedetial::create(array_merge(
                $validatemed->validated()
                 ));
         
           
             
            if ($med){
              
                return response()->json( 
                    [
                    'status'=>true,
                    'data'=>$med,
                    'message'=>"data added successfully"]
                    , 201);
            }
            else{
                return response()->json(null, 204);
            }

        }
        catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
       
        
    }
    public function update(Request $request){
        try{
            
            
            
            
             $validatemed = Validator::make($request->all(), [
                'id'=>'required|integer|exists:medicinedetials,id',
                'quantity' => 'integer',
  
                'component'=>'string',
                'price'=>'integer',
                'expire_date'=>'date',
                
              ]);
            
            
               if($validatemed->fails()){
                    return response()->json([
                        'status' => false,
                        'message' => 'خطأ في التحقق',
                        'errors' => $validatemed->errors()
                    ], 422);
                }  
                         
            $med = Medicinedetial::find($request->id);
            
         
          if($med)  
          { 
            $med->update($validatemed->validated());
             
            return response()->json( [
                'status'=>true,
                'data'=>$med,
                'message'=>"data updated successfully"
               ] , 200);
           
          }
            else{
                return response()->json(null, 204);
            }

        }
        catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
      
        
    }
    public function destroy($id){
        try {  
             
            $input = [ 'id' =>$id ];
            $validate = Validator::make( $input,
                ['id'=>'required|integer|exists:medicinedetials,id']);
            if($validate->fails()){
            return response()->json([
                'status' => false,
               'message' => 'خطأ في التحقق',
                'errors' => $validate->errors()
            ], 422);}
          
            $med=Medicinedetial::find($id);
           
            if($med )
            { 
                 
               
                $result= $med->delete();
                if($result)
                 {
                     
                   
             
                return response()->json(
                    ['status'=>true,
                    'data'=>null,
                    'message'=>"data deleted successfully"]
                 , 200);}
                
            }
    
              
                return response()->json(null, 204);
          }
          catch (ValidationException $e) {
              return response()->json(['errors' => $e->errors()], 422);
          } catch (\Exception $e) {
              return response()->json(['message' => 'An error occurred while deleting the categroy.'], 500);
          }
    }
    public function show( $id){
       
        try {  
          
            $input = [ 'id' =>$id ];
            $validate = Validator::make( $input,
                ['id'=>'required|integer|exists:medicinedetials,id']);
            if($validate->fails()){
            return response()->json([
                'status' => false,
               'message' => 'خطأ في التحقق',
                'errors' => $validate->errors()
            ], 422);}
            $med= Medicinedetial::find($id);
            if( $med){
                
                return response()->json(
                    [
                        'status'=>true,
                        'data'=>$med,
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
}