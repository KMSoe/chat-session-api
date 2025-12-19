<?php

namespace App\Trait;

trait ResponseHandlerTrait {
    
    public function successResponse($data = [], $message = "Success", $code = 200)
    {
    	$response = [
            'success' => true,
            'data' => $data,
            'message' => $message,
        ];
        return response()->json($response, $code);
    }

    public function failureResponse($message = "Error", $code = 200, $errors = [])
    {
    	$response = [
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ];
        return response()->json($response, $code);
    }
}