<?php

namespace App\Http\Middleware;
 
 
use Closure;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    public function handle($request, Closure $next, ...$guards)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Your token has  expired '], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['error' => 'Token is invalid'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['error' => 'Token is absent'], 401);
        }

        return parent::handle($request, $next, ...$guards);
    }
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }


   
}