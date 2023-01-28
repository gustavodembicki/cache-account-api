<?php

namespace Tests\Unit\Business\Event;

use App\Business\Event\EventBusiness;
use App\Helper\CacheHelper;
use Exception;
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

        //Then
        $this->expectException(Exception::class);
        $this->withoutExceptionHandling();
        
        //When
        $this->eventBusiness->triggeredEvent($data);
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
            "data" => [
                "destination" => [
                    "id" => $data["destination"],
                    "balance" => 10
                ]
            ],
            "code" => 201
        ], $response);
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
            "data" => [
                "destination" => [
                    "id" => $data["destination"],
                    "balance" => 30
                ]
            ],
            "code" => 201
            
        ], $response);
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

        //Then
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("0");
        $this->expectExceptionCode(404);
        $this->withoutExceptionHandling();

        //When
        $this->eventBusiness->triggeredEvent($data);
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
            "data" => [
                "origin" => [
                    "id" => $data["origin"],
                    "balance" => 20
                ]
            ],
            "code" => 201
        ], $response);
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

        //Then
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("0");
        $this->expectExceptionCode(404);
        $this->withoutExceptionHandling();

        //When
        $this->eventBusiness->triggeredEvent($data);
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
            "data" => [
                "origin" => [
                    "id" => $data["origin"],
                    "balance" => 100
                ],
                "destination" => [
                    "id" => $data["destination"],
                    "balance" => 60
                ]
            ],
            "code" => 201
        ], $response);
    }
}