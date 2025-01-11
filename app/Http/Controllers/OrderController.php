<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
class OrderController extends Controller
{
    public function store(Request $request){

        try {
            
            $user = auth('user')->user();
            
            // If no user is authenticated
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'errors' => 'No authenticated user found',
                    'message' => 'No authenticated user found',
                ], 404);
            }
            $validate = Validator::make($request->all(), [
                'pharmacy_id'=>'required|integer|exists:pharmacies,id',
                'orders' => 'required|array', // Validate that 'orders' is an array
                'orders.*.medicine_id' => 'required|exists:medicines,id', // Validate each medicine_id
                'orders.*.quantity' => 'required|integer|min:1', // Validate each quantity
                'photo' => 'file|mimetypes:image/jpeg,image/png,image/gif,image/svg+xml,image/webp,application/wbmp',
            ]);
            $validate->sometimes('photo', 'mimetypes:image/vnd.wap.wbmp', function ($input) {
                  return $input->file('photo') !== null && $input->file('photo')->getClientOriginalExtension() === 'wbmp';
            });

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation Error',
                    'errors' => $validate->errors()
                ], 422);
            }
           
            $order = Order::create(array_merge(
                    collect($validate->validated())->forget('orders')->toArray(),
                    [ 'user_id'=>$user->id]
                     ));
            if($request->hasFile('photo') and $request->file('photo')->isValid()){
                        $order->photo = $this->storeImage($request->file('photo'),'orders'); 
                        $order->save();
                        
            } 
            // Create each order
            if($request->has('orders')){$orders = $request->orders; // Extract the orders array
         
            foreach ($orders as $ord) {
                $medicineId = $ord['medicine_id'];
           
                $quantity = $ord['quantity'];

                // Create a new record in the medicine_pharmacies table
                DB::table('orderdetials')->insert([
                    'order_id' => $order->id,
                    'medicine_id' => $medicineId,
                    'quantity' => $quantity,
                 
                ]);
             
            }}

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
    public function show(Request $request){
        try {
             
            $user = auth('user')->user();
            
            // If no user is authenticated
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'errors' => 'No authenticated user found',
                    'message' => 'No authenticated user found',
                ], 404);
            }
            //done , wait, deny
            $type="wait";
            if($request->has('type')){
               if($request->type==1)$type="done"; 
               elseif($request->type==0)$type="deny"; 
                
            }
            $orders=$user->orders()->with('order_detials')->where("confirmed",$type)->get();
            if( $orders){
                return response()->json([
                    'status' => true,
                    "data"=> $orders,
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
    private function showOrder(Request $request){
       
        try {  
          
            $pharmacy = auth('pharmacy')->user();
            
            // If no user is authenticated
            if (!$pharmacy) {
                return response()->json([
                    'status' => false,
                    'errors' => 'No authenticated user found',
                    'message' => 'No authenticated user found',
                ], 404);
            }
            
            $validate = Validator::make( $request->all(),
                ['id'=>'required|integer|exists:orders,id']);
            if($validate->fails()){
            return response()->json([
                'status' => false,
               'message' => 'خطأ في التحقق',
                'errors' => $validate->errors()
            ], 422);}
            $order= Order::with('user','order_detials')->find($request->id);
            if( $order){
                
                return response()->json(
                    [
                        'status'=>true,
                        'data'=>$order,
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
    private function index(){
        
          try{
            $pharmacy = auth('pharmacy')->user();
            
            // If no user is authenticated
            if (!$pharmacy) {
                return response()->json([
                    'status' => false,
                    'errors' => 'No authenticated user found',
                    'message' => 'No authenticated user found',
                ], 404);
            }
            
            $users=$pharmacy->orders()->with('user')->get();
           if ($users){
                return response()->json(   [
                    'status'=>true,
                    'data'=>$users,
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
          
           
            if( $request->has('id')){
                return $this->showOrder($request);
             
            }
            else{
                return $this->index();
            }
        }
        catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
          } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while requesting this user .'], 500);
          }
    }
    public function update(Request $request){
        try{
            
            $user = auth('pharmacy')->user();
            
                 // If no user is authenticated
                if (!$user) {
                    return response()->json([
                        'status' => false,
                        'errors' => 'No authenticated user found',
                        'message' => 'No authenticated user found',
                    ], 404);
                }
             
                $validateMedicine = Validator::make($request->all(), 
                [
                    'order_id'=>'required|integer|exists:orders,id',
                    'confirmed'=>'string',
                ]);
             
             
               if($validateMedicine->fails()){
                    return response()->json([
                        'status' => false,
                        'message' => 'خطأ في التحقق',
                        'errors' => $validateMedicine->errors()
                    ], 422);
                }  
                         
            $order = Order::find($request->order_id);
            
         
          if($order)  
          {  
            $order->update($validateMedicine->validated());
            
            $result=$order->save();
          
            if ($result){
         
                return response()->json( [
                    'status'=>true,
                    'data'=>$order,
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
}