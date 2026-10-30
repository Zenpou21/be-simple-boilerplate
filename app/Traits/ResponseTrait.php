<?php

namespace App\Traits;

/**
 * Every API answer has the same shape, so the frontend reads them all the same way
 * (see apiClient.ts in the React app):
 *
 *   success: { "success": { "message": "Product created.", "data": { ... } } }
 *   failure: { "error":   { "message": "Record not found." } }
 *
 * Usage in a controller:
 *   return $this->dataResponse('success', 200, 'Product found.', $product);
 *   return $this->dataResponse('error', 404, 'Record not found.');
 */
trait ResponseTrait
{
    public static function dataResponse(string $status, int $httpCode, string $message, mixed $data = null)
    {
        $body = ['message' => $message];
        if ($data !== null) {
            $body[$status === 'success' ? 'data' : 'error_thrown'] = $data;
        }

        return response([$status === 'success' ? 'success' : 'error' => $body], $httpCode);
    }
}
