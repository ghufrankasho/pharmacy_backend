<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\User;
use App\Models\Pharmacy;
use App\Models\Warehouse;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
class AuthController extends Controller
{
    public function __construct(){
        $this->middleware('auth:api',['except'=>['register','login','logout']]);
    }
    // Register a new user or pharmacy
    public function register(Request $request)
    
    {
       
        $validatedData =Validator::make($request->all(), 
           [ 
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'type'=>'required|string',//user,warehouse, pharmacy
            'address'=>'required|string',
            'phone'=>'string',
            'logo' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        $validatedData->sometimes('logo', 'required|mimetypes:image/vnd.wap.wbmp', function ($input) {
            return $input->file('logo') !== null && $input->file('logo')->getClientOriginalExtension() === 'wbmp';
      });
        if($validatedData->fails()){
            return response()->json([
                'status' => false,
                 'message' => 'خطأ في التحقق',
                'errors' => $validatedData->errors()
            ], 422);
        }
     
        if ($request->type == 'user') {
 
            $user = User::create(array_merge(
                $validatedData->validated()
                ));
        } elseif ($request->type  == 'pharmacy') {
            
                $user = Pharmacy::create(array_merge(
                $validatedData->validated()
                ));
                       
            if($request->hasFile('logo') and $request->file('logo')->isValid()){
                $user->image = $this->storeImage($request->file('logo'),'pharmacy'); 
                $user->save();
            }
        } else {
            return response()->json([
                'status'=>false,
                'message' => 'Invalid user type'], 400);
        }

      

        return response()->json(['message' => 'Registration successful', 'user' => $user], 201);
    }

    // Login for user and pharmacy (JWT)
    public function login(Request $request)
    {
        $credentials = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'type'=>'required'
        ]);
        if($credentials->fails()){
            return response()->json([
                'status' => false,
                 'message' => 'خطأ في التحقق',
                'errors' => $credentials->errors()
            ], 422);
        }
        if ($request->type == 'user') {
            $model = User::class;
            $guard = 'user';
        } elseif ($request->type == 'pharmacy') {
            $model = Pharmacy::class;
            $guard = 'pharmacy';
        } else {
            return response()->json(['message' => 'Invalid user type'], 400);
        }

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['message' => 'Could not create token'], 500);
        }

        return response()->json([
            'message' => 'Login successful',
            'access_token' => $token,
        ], 200);
    }

  
    // Logout (JWT for user and pharmacy)
    public function logout(Request $request)
    {
        if ($request->user()) {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'Logged out successfully'], 200);
        }

        return response()->json(['message' => 'User not logged in'], 400);
    }

 
}