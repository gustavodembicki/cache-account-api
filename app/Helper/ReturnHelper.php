<?php

namespace App\Helper;

use Illuminate\Http\Response as HttpResponse;

class ReturnHelper
{
    /**
     * Static method to default success returns
     * @param array $data
     * @param int $code
     * @return HttpResponse
     */
    public static function success(array $data, int $code = 200): HttpResponse
    {
        return response($data, $code);
    }

    /**
     * Static method to default error returns
     * @param array $data
     * @param int $code
     * @return HttpResponse
     */
    public static function error($data, int $code = 404): HttpResponse
    {
        return response($data, $code);
    }
}