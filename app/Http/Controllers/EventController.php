<?php

namespace App\Http\Controllers;

use App\Business\Event\EventBusiness;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function event(Request $request, EventBusiness $business)
    {
        return $business->typeRead($request->all());
    }
}