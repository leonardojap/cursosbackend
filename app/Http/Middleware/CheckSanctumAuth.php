<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;

class CheckSanctumAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $token = str_replace('Bearer ', '', $request->header('Authorization'));

        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }


        $accessToken = PersonalAccessToken::findToken($token);


        $currentDate = date('Y-m-d H:i:s');
        if (!$accessToken) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $isExpiredToken = $accessToken->expires_at < $currentDate;

        if ($isExpiredToken) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // agregamos la información del usuario autenticado al request
        $request->user_id = $accessToken->tokenable_id;


        return $next($request);
    }
}
