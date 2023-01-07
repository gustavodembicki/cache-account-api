<?php

namespace App\Http\Controllers;

use App\Helper\CacheHelper;

class ResetController
{
    public function reset()
    {
        CacheHelper::deleteAll();

        return response("OK", 200);
    }
}