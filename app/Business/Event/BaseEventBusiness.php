<?php

namespace App\Business\Event;

use Exception;
use Illuminate\Http\Response;

abstract class BaseEventBusiness
{
    /** @var array $data */
    protected $data;

    /** @var array $typesAccepted */
    private $typesAccepted = ["deposit", "withdraw", "transfer"];

    /** 
     * Method responsable to read the type of event and call the correct method
     * 
     */
    public function typeRead()
    {
        print_r($this->data);

        $typeExists = $this->typesAccepted[$this->data["type"]] ?? false;

        if (!$typeExists) {
            throw new Exception("Type informed doesn't exists", Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->typesAccepted[$this->data["type"]]();
    }

    private function deposit()
    {

    }

    private function withdraw()
    {

    }

    private function transfer()
    {

    }
}