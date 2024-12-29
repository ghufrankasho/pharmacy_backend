<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Medicinedetial;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class MedicineController extends Controller
{
    public function store(Request $request){
        
        try{
            $validateMedicine = Validator::make($request->all(), 
            [
               
                'name'=>'string|required',
                'calssification'=>'string',
                'warehouse_id'=>'required|integer|exists:warehouses,id',
                'photo' => 'file|required|mimetypes:image/jpeg,image/png,image/gif,image/svg+xml,image/webp,application/wbmp',
            ]);
            $validateMedicine->sometimes('photo', 'required|mimetypes:image/vnd.wap.wbmp', function ($input) {
                  return $input->file('photo') !== null && $input->file('photo')->getClientOriginalExtension() === 'wbmp';
            });
            if($validateMedicine->fails()){
                return response()->json([
                    'status' => false,
                     'message' => 'خطأ في التحقق'
                     ,
                    'errors' => $validateMedicine->errors()
                ], 422);
            }
           
            $Medicine = Medicine::create(array_merge(
                $validateMedicine->validated()
                 ));
            if($request->warehouse_id != null){

                    $warehouse=warehouse::find($request->warehouse_id);
                    $Medicine->warehouse()->associate($warehouse);
                }
             
           
            $result=$Medicine->save();
         
          
           if ($result){
               
                return response()->json( [
                    'status'=>true,
                    'data'=>$Medicine,
                    'message'=>"data added successfully"
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
    public function update(Request $request){
        try{
            
             
                $validateMedicine = Validator::make($request->all(), 
                [
                    'id'=>'required|integer|exists:Medicines,id',
                    'name'=>'string',
                    'calssification'=>'string',
                    'warehouse_id'=>'integer|exists:warehouses,id',
                    'photo' => 'file|mimetypes:image/jpeg,image/png,image/gif,image/svg+xml,image/webp,application/wbmp',
                ]);
                $validateMedicine->sometimes('photo', 'mimetypes:image/vnd.wap.wbmp', function ($input) {
                      return $input->file('photo') !== null && $input->file('photo')->getClientOriginalExtension() === 'wbmp';
                });
             
               if($validateMedicine->fails()){
                    return response()->json([
                        'status' => false,
                        'message' => 'خطأ في التحقق',
                        'errors' => $validateMedicine->errors()
                    ], 422);
                }  
                         
            $Medicine = Medicine::find($request->id);
            
         
          if($Medicine)  
          {  $Medicine->update($validateMedicine->validated());
            if($request->hasFile('photo') and $request->file('photo')->isValid()){
                if($Medicine->photo !=null){
                  $this->deleteImage($Medicine->photo);
                }
                $Medicine->photo = $this->storeImage($request->file('photo'),'Medicines'); 
            }
            if($request->warehouse_id != null){

                $warehouse=warehouse::find($request->warehouse_id);
                $Medicine->warehouse()->associate($warehouse);
            }
          
            $result=$Medicine->save();
            if ($result){
         
                return response()->json( [
                    'status'=>true,
                    'data'=>$Medicine,
                    'message'=>"data updated successfully"
                   ] , 200);
            }
           
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
    public function destroy($id){
        try {  
             
            $input = [ 'id' =>$id ];
            $validate = Validator::make( $input,
                ['id'=>'required|integer|exists:Medicines,id']);
            if($validate->fails()){
            return response()->json([
                'status' => false,
               'message' => 'خطأ في التحقق',
                'errors' => $validate->errors()
            ], 422);}
          
            $Medicine=Medicine::find($id);
           
            if($Medicine )
            { 
                if($Medicine->photo!=null) 
                {
                    $this->deleteImage($Medicine->photo);
                }
               
                $result= $Medicine->delete();
                if($result)
                 {
                  
                   
             
                return response()->json(
                    [
                        'status'=>true,
                        'data'=>'',
                        'message'=>"data deleted successfully"
                       ]
                 , 200);}
                
            }
    
              
                return response()->json([  
                    'status' => false,
                    'message' => 'something went wrong',
                    ], 204);
          }
          catch (ValidationException $e) {
              return response()->json(['errors' => $e->errors()], 422);
          } catch (\Exception $e) {
              return response()->json(['message' => 'An error occurred while deleting the categroy.'], 500);
          }
    }
    public function index(){
        
          try{
           
            
            $Medicines=Medicine::latest()->get();
           if ($Medicines){
                return response()->json( [
                    'status'=>true,
                    'data'=>$Medicines,
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
    public function show( $id){
       
        try {  
          
            $input = [ 'id' =>$id ];
            $validate = Validator::make( $input,
                ['id'=>'required|integer|exists:medicines,id']);
            if($validate->fails()){
            return response()->json([
                'status' => false,
               'message' => 'خطأ في التحقق',
                'errors' => $validate->errors()
            ], 422);}
            $Medicine= Medicine::with("medicinedetials")->find($id);
            if( $Medicine){
                
                return response()->json(
                    [
                        'status'=>true,
                        'data'=>$Medicine,
                        'message'=>"data obtained successfully"
                       ]
                 , 200);
                
            }
    
              
                return response()->json([  
                    'status' => false,
                    'message' => 'something went wrong',
                    ], 204);
            
        }
        catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
          } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while requesting this Medicine .'], 500);
          }
    }
    public function search(Request $request){
        try {
          
            $validatesearch = Validator::make($request->all(), 
            [ 'search' => 'required|string|min:3' ]);    
            if($validatesearch->fails())
            {
                return response()->json([
                    'status' => false,
                   'message' => 'خطأ في التحقق',
                    'errors' => $validatesearch->errors()
                ], 422);
               
            }
             
                 
            $medicines=array();
             
            $m_detiales= Medicinedetial::where('component','LIKE', '%' . $request->search .'%')->with('medicine')
            ->first();
            
            if($m_detiales)
            { 
            
                
                $medicines= $m_detiales['medicine'];
                
            }
          
           
            $data = Medicine::where('name','LIKE', '%' .  $request->search .'%')
                ->orwhere('calssification','LIKE', '%' .  $request->search .'%')->get();      
              
 
            
            if(count($data)>0)
            {
                
                if(count($medicines)>0){
                    
                    $data=$medicines->merge($data);
                }
                $result=array();
                $count=0;
                foreach($data as $medicine){
                    
                    if(! in_array($medicine,$result)  )
                    { 
                        $count+=1;
                        array_push($result , $medicine);
                        
                    }
                }
    
                if ($result)
                { 
                    return response()->json(
                            [
                                'status'=>true,
                                'data'=>$result,
                                'message'=>"data obtianed successfully"
                               ]
                         , 200);
                        
                }
                else{
                    return response()->json( [
                        'status'=>true,
                        'data'=>[],
                        'message'=>"no data found for this search"
                       ],200); 
                    
                }
            }
            else
            {
                if(count($medicines)>0)
                { 
                        $result=array();
                        $count=0;
                        foreach($medicines as $medicine){
                            if(! in_array($medicine,$result)   ){
                            
                                array_push($result , $medicine);
                            }
                        }
                        if ($result)
                        { 
                            return response()->json(
                                    [
                                        'status'=>true,
                                        'data'=>$result,
                                        'message'=>"data obtianed successfully"
                                    ]
                                , 200);
                                
                        }
                    }
                else{
                    return response()->json( [
                        'status'=>true,
                        'data'=>[],
                        'message'=>"no data found for this search"
                    ],200); 
                    
                }
            }
            
        }
        catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } 
        catch (\Exception $e) {
            return response()->json(['message' =>$e,
             'An error  occurred while requesting this Product.'], 500);
        }

    }
}