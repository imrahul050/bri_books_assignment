<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Nette\Schema\Message;

trait ApiResponser
{
    protected function successResponse(
        mixed $data = null,
        string $message,
        int $statusCode = 200
    ): JsonResponse {
        $response = [
            'status'  => true,
            'message' => $message,
            'data'    => $data,
        ];


        return response()->json($response, $statusCode);
    }

    protected function errorResponse(
        mixed $errors = null,
        string $message,
        int $statusCode = 400
    ): JsonResponse {
        $response = [
            'status'  => false,
            'message' => $message,
            'data'  => $errors,
        ];


        return response()->json($response, $statusCode);
    }

    
}