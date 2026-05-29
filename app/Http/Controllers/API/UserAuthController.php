<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UserLoginRequest;
use App\Http\Requests\Auth\UserRegisterRequest;

use App\Repositories\UserAuthRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Resources\UserResource;

class UserAuthController extends Controller
{
    protected $userAuthRepository;

    public function __construct(UserAuthRepository $userAuthRepository)
    {
        $this->userAuthRepository = $userAuthRepository;
    }

    public function register(UserRegisterRequest $request)
    {
        try {

            $user = $this->userAuthRepository->register($request);

            if (!$user) {
                return $this->errorResponse([], 'User registration failed.', 400);
            }

            // Generate JWT Token
            $token = JWTAuth::fromUser($user);

            return $this->successResponse(
                [
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'expires_in' => config('jwt.ttl') * 60,
                    'user' => $user,
                ],
                'User registered successfully'
            );
        } catch (\Exception $e) {

            return $this->errorResponse([], $e->getMessage(), 500);
        }
    }

    public function login(UserLoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        $token = jwtAuth::attempt($credentials);

        if (!$token) {
            return $this->errorResponse([], 'Invalid email or password.', 401);
        }

        return $this->successResponse(
            [
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => config('jwt.ttl') * 60, // seconds
                'user' =>  new UserResource(Auth::user()),
            ],
            'Login successful.'
        );
    }



    public function profile(): JsonResponse
    {
        return $this->successResponse(
            [
                'user' =>  new UserResource(Auth::user()),
            ],
            'Profile fetched successfully.'
        );
    }

   
}
