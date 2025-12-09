<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

trait ApiResponse
{
    /**
     * Success response with data.
     */
    protected function successResponse(
        mixed $data = [],
        string $message = 'Success',
        int $statusCode = 200
    ): JsonResponse {
        $responseData = [
            'success' => true,
            'message' => $message,
        ];

        // Handle Resource/Collection or raw data
        if ($data instanceof JsonResource || $data instanceof ResourceCollection) {
            $responseData['data'] = $data->response()->getData(true)['data'] ?? $data;
        } else {
            $responseData['data'] = $data;
        }

        return response()->json($responseData, $statusCode);
    }

    /**
     * Error response for validation errors.
     */
    protected function validationErrorResponse(
        array $errors,
        string $message = 'Validation Error'
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'data' => [],
            'errors' => $errors,
            'message' => $message,
        ], 422);
    }

    /**
     * Error response for general errors.
     */
    protected function errorResponse(
        string $message = 'Error',
        int $statusCode = 400,
        array $errors = []
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'data' => [],
            'errors' => $errors,
            'message' => $message,
        ], $statusCode);
    }

    /**
     * Not found response.
     */
    protected function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }

    /**
     * Unauthorized response.
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->errorResponse($message, 401);
    }

    /**
     * Forbidden response.
     */
    protected function forbiddenResponse(string $message = 'Forbidden'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }

    /**
     * Created response.
     */
    protected function createdResponse(
        mixed $data = [],
        string $message = 'Created successfully'
    ): JsonResponse {
        return $this->successResponse($data, $message, 201);
    }

    /**
     * Deleted response.
     */
    protected function deletedResponse(string $message = 'Deleted successfully'): JsonResponse
    {
        return $this->successResponse([], $message, 200);
    }
}
