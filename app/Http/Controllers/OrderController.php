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
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation Error',
                    'errors' => $validate->errors()
                ], 422);
            }
            $orders = $request->orders; // Extract the orders array
         
            $order = Order::create(array_merge(
                    collect($validate->validated())->forget('orders')->toArray(),
                    [ 'user_id'=>$user->id]
                     ));
          
                    // Create each order
            foreach ($orders as $ord) {
                $medicineId = $ord['medicine_id'];
           
                $quantity = $ord['quantity'];

                // Create a new record in the medicine_pharmacies table
                DB::table('orderdetials')->insert([
                    'order_id' => $order->id,
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
}