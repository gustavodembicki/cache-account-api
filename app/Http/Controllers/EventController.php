<?php

namespace App\Http\Controllers;

use App\Business\Event\EventBusiness;
use App\Http\Requests\EventRequest;

class EventController extends Controller
{
    public function event(EventRequest $request, EventBusiness $business)
    {
        return $business->triggeredEvent($request->all());
    }
}