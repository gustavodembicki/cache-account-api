<?php

namespace App\Business\Event;

use App\Helper\CacheHelper;
use Exception;

abstract class BaseEventBusiness
{
    /** @var array $data */
    protected $data;

    /** @var array $typesAccepted */
    private $typesAccepted = [
        "deposit", 
        "withdraw", 
        "transfer"
    ];

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
            throw new Exception("Type informed doesnt exists", 422);
        }

        return $this->$actualType();
    }

    /**
     * Private method responsable for deposit rule
     * 
     * @param array $depositData - Used in transfer when the destination doesnt exists
     * @return array
     */
    private function deposit(array $depositData = []): array
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

        return [
            "data" => [
                "destination" => [
                    "id" => $deposit["destination"],
                    "balance" => $deposit["amount"]
                ]
            ],
            "code" => 201
        ];
    }

    /**
     * Private method responsable for withdraw rule
     * 
     * @return array
     */
    private function withdraw(): array
    {
        $accountCacheName = "account_{$this->data["origin"]}";

        if (!CacheHelper::cacheExists($accountCacheName)) {
            throw new Exception(0, 404);
        }

        $accountCache = CacheHelper::get($accountCacheName);

        if ($accountCache["balance"] <= 0 || $accountCache["balance"] < $this->data["amount"]) {
            throw new Exception("This account doesnt have enough balance to withdraw", 403);
        }

        $accountCache["balance"] -= $this->data["amount"];

        CacheHelper::put($accountCacheName, $accountCache);

        return [
            "data" => [
                "origin" => [
                    "id" => $this->data["origin"],
                    "balance" => $accountCache["balance"]
                ]
            ],
            "code" => 201
        ];
    }

    /**
     * Private method responsable for transfer rule
     * 
     * @return array
     */
    private function transfer(): array
    {
        $withdraw = $this->withdraw()["data"]["origin"];
        $deposit = $this->deposit()["data"]["destination"];

        return [
            "data" => [
                "origin" => [
                    "id" => $withdraw["id"],
                    "balance" => $withdraw["balance"]
                ],
                "destination" => [
                    "id" => $deposit["id"],
                    "balance" => $deposit["balance"]
                ]
            ],
            "code" => 201
        ];
    }

    /**
     * Private method responsable to validate if method exists and execute it
     * @param string $method
     * @return string
     */
    private function methodExists(string $method): string
    {
        if (!method_exists($this, $method)) {
            throw new Exception("Method called doesnt exists", 404);
        }

        return $this->$method;
    }
}