<?php

namespace App\Http\Controllers;

use App\Business\Event\EventBusiness;
use App\Helper\ReturnHelper;
use App\Http\Requests\EventRequest;

class EventController extends Controller
{
    public function event(EventRequest $request, EventBusiness $business)
    {
        try {
            $eventReturn = $business->triggeredEvent($request->all());
            return ReturnHelper::success($eventReturn["data"], $eventReturn["code"]);
        } catch (\Exception $e) {
            return ReturnHelper::error($e->getMessage(), $e->getCode());
        }
    }
}