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
    public function update(Request $request, $id){
        try{
            
            
            $input = [ 'id' =>$id ];
            $validate = Validator::make( $input,
            ['id'=>'required|integer|exists:meds,id']);
            if($validate->fails()){
                    return response()->json([
                        'status' => false,
                        'message' => 'خطأ في التحقق',
                        'errors' => $validate->errors()
                    ], 422);
                }
            
             $validatemed = Validator::make($request->all(), [
                'link' => 'url:http,https',
                'id' => 'in:1,2,3,4,5',
                'alt'=>'string',
                'visible'=>'bool',
                'expire_date'=>'date',
                
                
                'image' => 'file|mimeids:image/jpeg,image/png,image/gif,image/svg+xml,image/webp,application/wbmp',
            ]);
            
            $validatemed->sometimes('image', 'required|mimeids:image/vnd.wap.wbmp', function ($input) {
                return $input->file('image') !== null && $input->file('image')->getClientOriginalExtension() === 'wbmp';
            });
               if($validatemed->fails()){
                    return response()->json([
                        'status' => false,
                        'message' => 'خطأ في التحقق',
                        'errors' => $validate->errors()
                    ], 422);
                }  
                         
            $med = Medicinedetial::find($id);
            
         
          if($med)  
          {  $med->update($validatemed->validated());
            if($request->hasFile('image') and $request->file('image')->isValid()){
                if($med->image !=null){
                    $this->deleteImage($med->image);
                }
                $med->image = $this->storeImage($request->file('image'),'meds'); 
            }
            $id=$med->id;
          
            $result=$med->save();
            if ($result){
                $this->sort_meds($id);
            //    $meds=Medicinedetial::where('visible',1)->get();
                return response()->json( [
                    'result'=>"data updated successfully"
                   ] , 200);
            }
           
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
                ['id'=>'required|integer|exists:meds,id']);
            if($validate->fails()){
            return response()->json([
                'status' => false,
               'message' => 'خطأ في التحقق',
                'errors' => $validate->errors()
            ], 422);}
            $med= Medicinedetial::with('medicines')->find($id);
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