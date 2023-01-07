<?php

namespace Tests\Feature\Controllers;

use App\Helper\CacheHelper;
use Illuminate\Http\Response;
use Tests\TestCase;

class BalanceControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        CacheHelper::deleteAll();
    }

    /** @test */
    public function shouldNotFoundAccount()
    {
        //Given
        //When
        $response = $this->get("/balance?account_id=100");

        //Then
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function shouldReturnAccountBalance()
    {
        //Given
        CacheHelper::put("account_100", [
            "id" => 100,
            "balance" => 10
        ]);

        //When
        $response = $this->get("balance?account_id=100");

        //Then
        $response->assertStatus(Response::HTTP_OK);
        $response->assertContent('10');
    }
}