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
            return response("Type informed doesnt exists", Response::HTTP_UNPROCESSABLE_ENTITY);
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
        
        CacheHelper::put($accountCacheName, [
            "id" => $this->data["destination"],
            "balance" => $this->data["amount"]
        ]);

        return response([
            "destination" => [
                "id" => $this->data["destination"],
                "balance" => $this->data["amount"]
            ]
        ], !empty($accountCache) ? 200 : 201);
    }

    private function withdraw()
    {
        $accountCacheName = "account_{$this->data["origin"]}";

        if (!CacheHelper::cacheExists($accountCacheName)) {
            return response(0, Response::HTTP_NOT_FOUND);
        }

        $accountCache = CacheHelper::get($accountCacheName);
        $accountCache["balance"] -= $this->data["amount"];

        CacheHelper::put($accountCacheName, $accountCache);

        return response([
            "origin" => [
                "id" => $this->data["origin"],
                "balance" => $accountCache["balance"]
            ]
        ], Response::HTTP_CREATED);
    }

    private function transfer()
    {
        $accountOriginName = "account_{$this->data["origin"]}";
        $accountDestinationName = "account_{$this->data["destination"]}";

        if (!CacheHelper::cacheExists($accountOriginName) || !CacheHelper::cacheExists($accountDestinationName)) {
            return response(0, Response::HTTP_NOT_FOUND);
        }

        $accountOrigin = CacheHelper::get($accountOriginName);
        $accountOrigin["balance"] -= $this->data["amount"];

        $accountDestination = CacheHelper::get($accountDestinationName);
        $accountDestination["balance"] += $this->data["amount"];

        CacheHelper::put($accountOriginName, $accountOrigin);
        CacheHelper::put($accountDestinationName, $accountDestination);

        return response([
            "origin" => [
                "id" => $accountOrigin["id"],
                "balance" => $accountOrigin["balance"]
            ],
            "destination" => [
                "id" => $accountDestination["id"],
                "balance" => $accountDestination["balance"]
            ]
        ], Response::HTTP_CREATED);
    }
}