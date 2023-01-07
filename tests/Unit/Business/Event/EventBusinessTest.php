<?php

namespace Tests\Unit\Business\Event;

use App\Business\Event\EventBusiness;
use App\Helper\CacheHelper;
use Tests\TestCase;

class EventBusinessTest extends TestCase
{
    /** @var EventBusiness $eventBusiness */
    private $eventBusiness;

    /**
     * setUp
     */
    public function setUp(): void
    {
        parent::setUp();

        CacheHelper::deleteAll();

        $this->eventBusiness = new EventBusiness();
    }

    /** @test */
    public function shouldReturnTypeDoesntExists()
    {
        //Given
        $data = [
            "type" => "transference",
            "amount" => 10,
            "destination" => 100
        ];

        //When
        $response = $this->eventBusiness->triggeredEvent($data);

        //Then
        $this->assertEquals(422, $response->getStatusCode());
    }

    /** @test */
    public function shouldReturnDepositWithUnexistingAccount()
    {
        //Given
        $data = [
            "type" => "deposit",
            "amount" => 10,
            "destination" => 100
        ];

        //When
        $response = $this->eventBusiness->triggeredEvent($data);

        //Then
        $this->assertEquals([
            "destination" => [
                "id" => $data["destination"],
                "balance" => 10
            ]
        ], json_decode($response->getContent(), true));
    }

    /** @test */
    public function shouldReturnDepositWithExistingAccount()
    {
        //Given
        $data = [
            "type" => "deposit",
            "destination" => 100,
            "amount" => 20
        ];

        CacheHelper::put("account_100", [
            "id" => $data["destination"],
            "balance" => 10
        ]);

        //When
        $response = $this->eventBusiness->triggeredEvent($data);

        //Then
        $this->assertEquals([
            "destination" => [
                "id" => $data["destination"],
                "balance" => 30
            ]
        ], json_decode($response->getContent(), true));
    }

    /** @test */
    public function shouldReturnWithdrawWithNonExistingAccount()
    {
        //Given
        $data = [
            "type" => "withdraw",
            "origin" => 101,
            "amount" => 10
        ];

        //When
        $response = $this->eventBusiness->triggeredEvent($data);

        //Then
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals(0, json_decode($response->getContent(), true));
    }

    /** @test */
    public function shouldReturnWithdrawWithExistingAccount()
    {
        //Given
        $data = [
            "type" => "withdraw",
            "origin" => 100,
            "amount" => 10
        ];
        CacheHelper::put("account_{$data["origin"]}", [
            "id" => $data["origin"],
            "balance" => 30
        ]);

        //When
        $response = $this->eventBusiness->triggeredEvent($data);

        //Then
        $this->assertEquals([
            "origin" => [
                "id" => $data["origin"],
                "balance" => 20
            ]
        ], json_decode($response->getContent(), true));
    }

    /** @test */
    public function shouldReturnTransferWhenOriginDoesntExist()
    {
        //Given
        $data = [
            "type" => "transfer",
            "origin" => 100,
            "destination" => 200,
            "amount" => 50
        ];

        //When
        $response = $this->eventBusiness->triggeredEvent($data);

        //Then
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals(0, json_decode($response->getContent(), true));
    }

    /** @test */
    public function shouldReturnTransfer()
    {
        //Given
        $data = [
            "type" => "transfer",
            "origin" => 100,
            "destination" => 200,
            "amount" => 50
        ];

        CacheHelper::put("account_{$data["origin"]}", [
            "id" => $data["origin"],
            "balance" => 150
        ]);
        CacheHelper::put("account_{$data["destination"]}", [
            "id" => $data["destination"],
            "balance" => 10
        ]);

        //When
        $response = $this->eventBusiness->triggeredEvent($data);

        //Then
        $this->assertEquals([
            "origin" => [
                "id" => $data["origin"],
                "balance" => 100
            ],
            "destination" => [
                "id" => $data["destination"],
                "balance" => 60
            ]
        ], json_decode($response->getContent(), true));
    }
}