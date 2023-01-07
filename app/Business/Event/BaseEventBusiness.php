<?php

namespace App\Business\Event;

use App\Helper\CacheHelper;
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

    /**
     * Private method responsable for deposit rule
     * 
     * @param array $depositData - Used in transfer when the destination doesnt exists
     * @return Response
     */
    private function deposit(array $depositData = []): Response
    {
        $deposit = empty($depositData) ? $this->data : $depositData;
        $accountCacheName = "account_{$deposit["destination"]}";
        $accountCache = [];
        
        if (CacheHelper::cacheExists($accountCacheName)) {
            $accountCache = CacheHelper::get($accountCacheName);
            $deposit["amount"] += $accountCache["balance"];
        }
        
        CacheHelper::put($accountCacheName, [
            "id" => $deposit["destination"],
            "balance" => $deposit["amount"]
        ]);

        return response([
            "destination" => [
                "id" => $deposit["destination"],
                "balance" => $deposit["amount"]
            ]
        ], Response::HTTP_CREATED);
    }

    /**
     * Private method responsable for withdraw rule
     * 
     * @return Response
     */
    private function withdraw(): Response
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

    /**
     * Private method responsable for transfer rule
     * 
     * @return Response
     */
    private function transfer(): Response
    {
        $accountOriginName = "account_{$this->data["origin"]}";
        $accountDestinationName = "account_{$this->data["destination"]}";

        if (!CacheHelper::cacheExists($accountOriginName)) {
            return response(0, Response::HTTP_NOT_FOUND);
        }

        $accountOrigin = CacheHelper::get($accountOriginName);
        $accountOrigin["balance"] -= $this->data["amount"];

        $accountDestination = CacheHelper::get($accountDestinationName);

        if (!$accountDestination) {
            $responseDeposit = $this->deposit([
                "destination" => $this->data["destination"],
                "amount" => 0
            ]);

            $accountDestination = json_decode($responseDeposit->getContent(), true)["destination"];
        }

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