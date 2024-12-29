<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Pharmacy;
use App\Models\Warehouse;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
class AuthController extends Controller
{
    public function __construct(){
        $this->middleware('auth:api', ['except' => ['register', 'login', 'logout','profile']]);
        $this->middleware('auth:warehouse', ['except' =>['register', 'login', 'logout','profile']]);
        $this->middleware('auth:pharmacy', ['except' =>['register', 'login', 'logout','profile']]);

    }
    // Register a new user or pharmacy
    public function register(Request $request)
    {
       
        $uniqueEmailRule = 'unique:users,email';  // Default is checking the 'users' table

        if ($request->type == 'warehouse') {
            $uniqueEmailRule = 'unique:warehouses,email';  // Check in the 'warehouses' table
        } elseif ($request->type == 'pharmacy') {
            $uniqueEmailRule = 'unique:pharmacies,email';  // Check in the 'pharmacies' table
        }
    
        // Validate the incoming request data
        $validatedData = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', $uniqueEmailRule],  // Apply dynamic unique validation
            'password' => 'required|string|min:8|confirmed',
            'type' => 'required|in:user,warehouse,pharmacy', // user, warehouse, pharmacy
            'address' => 'required|string',
            'phone' => 'string',
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
       
       
        if ($request->type == 'warehouse') {
            
           
            $user = Warehouse::create(array_merge(
                collect($validatedData->validated())->forget('type')->toArray(),
                ['password'=>bcrypt($request->password)]
            ));
        }
        elseif ($request->type == 'user') {
          
            $user = User::create(array_merge(
                $validatedData->validated(), 
                 ['password'=>bcrypt($request->password)]
                ));
        } elseif ($request->type  == 'pharmacy') {
        
                $user = Pharmacy::create(array_merge(
                    collect($validatedData->validated())->forget('type')->toArray(),
                ['password'=>bcrypt($request->password)]
                ));
                if($request->hasFile('logo') and $request->file('logo')->isValid()){
                    $user->logo = $this->storeImage($request->file('logo'),'pharmacy'); 
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
        // Validate the incoming request data
        $validatedData = Validator::make($request->all(),[
            'email' => 'required|email',
            'password' => 'required|string|min:8',
            'type' => 'required|in:user,warehouse,pharmacy',    // user, warehouse, pharmacy
        ]);
        if($validatedData->failed()){
            return response()->json([
                'status' => false,
                'message' => 'خطأ في التحقق',
                'errors' => $validatedData->errors()->first()
            ], 422);
            }
        // Get user based on the provided type
        if ($request->type == 'warehouse') {
            $user = Warehouse::where('email', $request->email)->first();
        } elseif ($request->type == 'user') {
            $user = User::where('email', $request->email)->first();
        } elseif ($request->type == 'pharmacy') {
            $user = Pharmacy::where('email', $request->email)->first();
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Invalid user type'
            ], 400);
        }

    // Check if the user exists and the password is correct
    if (!$user || ! Hash::check($request->password, $user->password)) {
        return response()->json([
            'status' => false,
            'message' => 'Invalid credentials'
        ], 401);
    }

    // Generate JWT token
    $token = JWTAuth::fromUser($user);

    // Return the response with the user data and JWT token
    return response()->json([
        'status' => true,
        'message' => 'Login successful',
        'user' => $user,
        'token' => $token,
    ], 200);
}
     

  
    public function logout(Request $request)
    {
        // Assuming you're using JWT Auth package (Tymon\JWTAuth)
        try {
            // return response()->json(auth()->user());
            // Invalidate the token
          auth()->logout();  // This invalidates the current token

            return response()->json([
                'message' => 'Successfully logged out'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to log out, please try again'
            ], 500);
        }
    }
    public function profile(Request $request)
    {
        // Determine the guard based on the user's type or provided token
        $user = null;
    
        if (auth('api')->check()) {
            $user = auth('api')->user(); // For 'user' type
        } elseif (auth('warehouse')->check()) {
            $user = auth('warehouse')->user(); // For 'warehouse' type
        } elseif (auth('pharmacy')->check()) {
            $user = auth('pharmacy')->user(); // For 'pharmacy' type
        }
    
        if ($user) {
            return response()->json($user);
        }
    
        return response()->json(['message' => 'No user found'], 404);
    }
    

 
}