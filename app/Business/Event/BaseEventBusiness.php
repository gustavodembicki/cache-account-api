<?php

namespace App\Business\Event;

use App\Helper\CacheHelper;
use App\Helper\ResponseHelper;
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
        $typeExists = false;
        $actualType = "";

        foreach ($this->typesAccepted as $typeAccepted) {
            if ($typeAccepted == $this->data["type"]) {
                $typeExists = true;
                $actualType = $this->data["type"];
                break;
            }
        }

        if (!$typeExists) {
            return ResponseHelper::return(Response::HTTP_UNPROCESSABLE_ENTITY, ["message" => "Type informed doesnt exists"]);
        }

        return $this->$actualType();
    }

    private function deposit()
    {
        if (CacheHelper::cacheExists("account_{$this->data["destination"]}")) {
            $accountCache = CacheHelper::get("account_{$this->data["destination"]}");
            $this->data["amount"] += $accountCache["amount"];
        }
        
        CacheHelper::put("account_{$this->data["destination"]}", $this->data);

        return ResponseHelper::return(gettype($accountCache) != "undefined" ? 200 : 201, [
            "destination" => [
                "id" => $this->data["destination"],
                "balance" => $this->data["amount"]
            ],
            "cache_test" => CacheHelper::get("account_{$this->data["destination"]}") //Temporary 
        ]);
    }

    private function withdraw()
    {
        $accountCacheName = "account_{$this->data["origin"]}";

        if (!CacheHelper::cacheExists($accountCacheName)) {
            return ResponseHelper::return(Response::HTTP_NOT_FOUND);
        }

        $accountCache = CacheHelper::get($accountCacheName);
        $accountCache["amount"] -= $this->data["amount"];

        CacheHelper::put($accountCacheName, $accountCache);

        return ResponseHelper::return(Response::HTTP_CREATED, [
            "origin" => [
                "id" => $this->data["origin"],
                "balance" => $accountCache["amount"]
            ]
        ]);
    }

    private function transfer()
    {

    }
}