<?php

namespace App\Business\Event;

use App\Business\Event\BaseEventBusiness;

class EventBusiness extends BaseEventBusiness
{
    public function triggeredEvent(array $data)
    {
        $this->data = $data;

        return $this->typeRead();
    }
}