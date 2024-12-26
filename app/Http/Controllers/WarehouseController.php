<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
class WarehouseController extends Controller
{
    public function store(Request $request){
        
        try{
            $validateWarehouse = Validator::make($request->all(), 
            [
                'link' => 'url:http,https|required',
                'id' => 'required|in:1,2,3,4,5',
                'visible'=>'bool',
                'alt'=>'string',
                'expire_date'=>'date',
                'image' => 'file|required|mimeids:image/jpeg,image/png,image/gif,image/svg+xml,image/webp,application/wbmp',
            ]);
            $validateWarehouse->sometimes('image', 'required|mimeids:image/vnd.wap.wbmp', function ($input) {
                  return $input->file('image') !== null && $input->file('image')->getClientOriginalExtension() === 'wbmp';
            });
            if($validateWarehouse->fails()){
                return response()->json([
                    'status' => false,
                     'message' => 'خطأ في التحقق'
                     ,
                    'errors' => $validateWarehouse->errors()
                ], 422);
            }
           
            $Warehouse = Warehouse::create(array_merge(
                $validateWarehouse->validated()
                 ));
         
            $Warehouses=Warehouse::where('id',$Warehouse->id)->get();
            
            $Warehouse->sorting=count($Warehouses);   
             
            if($request->hasFile('image') and $request->file('image')->isValid()){
                $Warehouse->image = $this->storeImage($request->file('image'),'Warehouses'); 
            }
           
            $result=$Warehouse->save();
            $id=$Warehouse->id;
          
           if ($result){
                $this->sort_Warehouses($id);
                return response()->json( [
                    'result'=>"data added successfully"
                   ] , 201);
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
            ['id'=>'required|integer|exists:Warehouses,id']);
            if($validate->fails()){
                    return response()->json([
                        'status' => false,
                        'message' => 'خطأ في التحقق',
                        'errors' => $validate->errors()
                    ], 422);
                }
            
             $validateWarehouse = Validator::make($request->all(), [
                'link' => 'url:http,https',
                'id' => 'in:1,2,3,4,5',
                'alt'=>'string',
                'visible'=>'bool',
                'expire_date'=>'date',
                
                
                'image' => 'file|mimeids:image/jpeg,image/png,image/gif,image/svg+xml,image/webp,application/wbmp',
            ]);
            
            $validateWarehouse->sometimes('image', 'required|mimeids:image/vnd.wap.wbmp', function ($input) {
                return $input->file('image') !== null && $input->file('image')->getClientOriginalExtension() === 'wbmp';
            });
               if($validateWarehouse->fails()){
                    return response()->json([
                        'status' => false,
                        'message' => 'خطأ في التحقق',
                        'errors' => $validate->errors()
                    ], 422);
                }  
                         
            $Warehouse = Warehouse::find($id);
            
         
          if($Warehouse)  
          {  $Warehouse->update($validateWarehouse->validated());
            if($request->hasFile('image') and $request->file('image')->isValid()){
                if($Warehouse->image !=null){
                    $this->deleteImage($Warehouse->image);
                }
                $Warehouse->image = $this->storeImage($request->file('image'),'Warehouses'); 
            }
            $id=$Warehouse->id;
          
            $result=$Warehouse->save();
            if ($result){
                $this->sort_Warehouses($id);
            //    $Warehouses=Warehouse::where('visible',1)->get();
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
                ['id'=>'required|integer|exists:Warehouses,id']);
            if($validate->fails()){
            return response()->json([
                'status' => false,
               'message' => 'خطأ في التحقق',
                'errors' => $validate->errors()
            ], 422);}
          
            $Warehouse=Warehouse::find($id);
           
            if($Warehouse )
            { 
                if($Warehouse->image!=null) 
                {
                    $this->deleteImage($Warehouse->image);
                }
                $id=$Warehouse->id;
                $result= $Warehouse->delete();
                if($result)
                 {
                    $this->sort_Warehouses($id);
                   
             
                return response()->json(
                    [
                        'result'=>"data deleted successfully"
                       ]
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
                ['id'=>'required|integer|exists:warehouses,id']);
            if($validate->fails()){
            return response()->json([
                'status' => false,
               'message' => 'خطأ في التحقق',
                'errors' => $validate->errors()
            ], 422);}
            $warehouse= warehouse::with('medicines')->find($id);
            if( $warehouse){
                
                return response()->json(
                    [
                        'status'=>true,
                        'data'=>$warehouse,
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
    public function index(){
        
          try{
           
            
            $Warehouses=Warehouse::latest()->get();
           if ($Warehouses){
                return response()->json(   [
                    'status'=>true,
                    'data'=>$Warehouses,
                    'message'=>"data obtained successfully"
                   ]
                 , 200);
            }
            else{
                return response()->json([  
                    'status' => false,
                    'message' => 'something went wrong',
                    ], 204);            }

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
          
            $validate = Validator::make( $request->all(),
                ['id'=>'nullable|exists:Warehouses,id']);
            if($validate->fails()){
            return response()->json([
                'status' => false,
               'message' => 'خطأ في التحقق',
                'errors' => $validate->errors()
            ], 404);}
             
            if($request->id != null){
                return $this->show($request->id);
             
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
    public function show_Warehouse($id){
        try {  
             
            $input = [ 'id' =>$id ];
            $validate = Validator::make( $input,
                ['id'=>'required|integer|exists:Warehouses,id']);
            if($validate->fails()){
            return response()->json([
                'status' => false,
               'message' => 'خطأ في التحقق',
                'errors' => $validate->errors()
            ], 422);}
          
            $Warehouse=Warehouse::find($id);
             
            if($Warehouse )
            { 
                
             
                return response()->json(
                 $Warehouse
                 , 200);}

            return response()->json(null, 204);
          }
          catch (ValidationException $e) {
              return response()->json(['errors' => $e->errors()], 422);
          } catch (\Exception $e) {
              return response()->json(['message' => 'An error occurred while deleting the categroy.'], 500);
          }
    }
}