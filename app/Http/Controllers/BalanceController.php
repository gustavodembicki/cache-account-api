<?php

namespace App\Http\Controllers;

use App\Helper\CacheHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BalanceController extends Controller
{
    public function balance(Request $request)
    {
        $data = $request->all();

        if (!CacheHelper::cacheExists("account_{$data["account_id"]}")) {
            return response(0, Response::HTTP_NOT_FOUND);
        }

        $account = CacheHelper::get("account_{$data["account_id"]}");
        
        return response($account["balance"], 200);
    }
}