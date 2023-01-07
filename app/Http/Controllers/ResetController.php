<?php

namespace App\Http\Controllers;

use App\Helper\CacheHelper;
use App\Helper\ResponseHelper;

class ResetController
{
    public function reset()
    {
        CacheHelper::deleteAll();

        return response();
    }
}