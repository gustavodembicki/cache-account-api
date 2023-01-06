<?php

namespace App\Helper;

use Illuminate\Http\JsonResponse;

class ResponseHelper
{
    /**
     * Static method responsable to centralize the Http Returns
     * 
     * @param int $code
     * @param string $status
     * @param mixed $body
     * @return JsonResponse
     */
    public static function return(int $code, string $status, $body): JsonResponse
    {
        return response()->json([
            "status" => $status,
            "data" => $body
        ], $code);
    }
}