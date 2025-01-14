<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pharmacy;
class PharmacyController extends Controller
{
    public function index(){
        
        try{
         return "ss";
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
}