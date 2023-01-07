<?php

namespace App\Helper;

use Illuminate\Http\JsonResponse;

class ResponseHelper
{
    /**
     * Static method responsable to centralize the Http Returns
     * 
     * @param int $code
     * @param array $body
     * @return JsonResponse
     */
    public static function return(int $code, array $body = []): JsonResponse
    {
        return response()->json($body, $code);
    }
}