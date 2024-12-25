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
    // public function __construct(){
    //     $this->middleware('auth:warehouse',['except'=>['register','login','logout']]);
    // }
    public function index(){
        return "fff";
        
    }
    public function register(Request $request)  {
        return "hhhhhhhhhhhhhh";
        $validator=Validator::make($request->all(),[
            'name'=>'required',
            'email'=>'required|string|email|unique:warehouses',
            'password'=>'required|string|confirmed|min:6'
        ]);
        if($validator->failed()){
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validator->errors()
            ], 400);
        }
        $warehouse=warehouse::create(array_merge(
            $validator->validated(),
            ['password'=>bcrypt($request->password)]
        ));



        return response()->json(
           [ 'message'=>'تم الدخول بنجاح',
             'user'=>$warehouse  
            ],201);
        
    }
    public function login(Request $request)  {
      
        $validator=Validator::make($request->all(),[
            'email'=>'required|email',
            'password'=>'required|string|min:6']);
            
        if($validator->failed()){
            return response()->json([
                'status' => false,
                'message' => 'خطأ في التحقق',
                'errors' => $validator->errors()
            ], 422);
            }
        $warehouse=warehouse::where('email',$request->email)->first();
        if($warehouse) {
                 
            if(Hash::check($request->password, $warehouse->password)){
                $token=auth()->attempt($validator->validated());
                return $this->createNewToken($token);}
            else{
                return response()->json([
                    'status' => false,
                    'message' =>  'كلمة المرور غير صحيحة',
                     
                    ], 422);
                }
        }
        else{
            return response()->json([
                'status' => false,
                'message' =>  'الإيميل غير صحيح',
                 
                ], 422);
        }    
        
    }
    protected function createNewToken($token) {
        auth('api')->factory()->setTTL(180);
        return response()->json([
            'access_token'=>$token,
            'warehouse'=>auth()->user(),
             
        ]);

        
    }
    public function logout() {
        auth()->logout();
        return response()->json(
            [ 'message'=>'تم الخروج بنحاح'
              
             ]);

        
    }
}