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
        $accountCacheName = "account_{$this->data["destination"]}";
        $accountCache = [];
        
        if (CacheHelper::cacheExists($accountCacheName)) {
            $accountCache = CacheHelper::get($accountCacheName);
            $this->data["amount"] += $accountCache["amount"];
        }
        
        CacheHelper::put($accountCacheName, $this->data);

        return ResponseHelper::return(!empty($accountCache) ? 200 : 201, [
            "destination" => [
                "id" => $this->data["destination"],
                "balance" => $this->data["amount"]
            ]
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
        $accountOriginName = "account_{$this->data["origin"]}";
        $accountDestinationName = "account_{$this->data["destination"]}";

        if (!CacheHelper::cacheExists($accountOriginName) || !CacheHelper::cacheExists($accountDestinationName)) {
            return ResponseHelper::return(Response::HTTP_NOT_FOUND);
        }

        $accountOrigin = CacheHelper::get($accountOriginName);
        $accountOrigin["amount"] -= $this->data["amount"];

        $accountDestination = CacheHelper::get($accountDestinationName);
        $accountDestination["amount"] += $this->data["amount"];

        CacheHelper::put($accountOriginName, $accountOrigin);
        CacheHelper::put($accountDestinationName, $accountDestination);

        return ResponseHelper::return(Response::HTTP_CREATED, [
            "origin" => [
                "id" => $this->data["origin"],
                "balance" => $accountOrigin["amount"]
            ],
            "destination" => [
                "id" => $this->data["destination"],
                "balance" => $accountDestination["amount"]
            ]
        ]);
    }
}