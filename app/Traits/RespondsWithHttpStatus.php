<?php

namespace App\Traits;

trait RespondsWithHttpStatus
{
    /**
     * @param $message  
     * @param array $data
     * @param int $status
     * @return \Illuminate\Http\JsonResponse
     */
    protected function success($message, $data = [], $status = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }
    /**
     * @param $message
     * @param int $status
     * @return \Illuminate\Http\JsonResponse
     */
    protected function failure($message, $status = 500)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }
    /**
     * @param array $errors
     * @param int $status
     * @return \Illuminate\Http\JsonResponse
     */
    protected function validationFail($errors = [], $status = 422)
    {
        return response()->json([
            'success' => false,
            'errors' => $errors,
        ], $status);
    }

    protected function anotherFailure($message, $data = [], $status = 500)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $data,
        ], $status);
    }
}