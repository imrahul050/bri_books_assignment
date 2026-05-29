<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (TokenExpiredException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Token has expired.',
                'data'    => [],
            ], 401);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Token is invalid.',
                'data'    => [],
            ], 401);
        } catch (JWTException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Token not provided.',
                'data'    => [],
            ], 401);
        }

        return $next($request);
    }
}