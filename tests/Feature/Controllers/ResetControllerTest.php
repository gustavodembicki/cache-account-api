<?php

namespace Tests\Feature\Controllers;

use App\Helper\CacheHelper;
use Illuminate\Http\Response;
use Tests\TestCase;

class ResetControllerTest extends TestCase
{
    /** @test */
    public function shouldDeleteAllCachesInFileStorage()
    {
        //Given
        CacheHelper::put("cache_one", true);
        CacheHelper::put("cache_two", true);

        //When
        $response = $this->post("/reset");

        $cacheOne = CacheHelper::get("cache_one");
        $cacheTwo = CacheHelper::get("cache_two");

        //Then
        $response->assertStatus(Response::HTTP_OK);
        $response->assertContent("OK");

        $this->assertFalse($cacheOne);
        $this->assertFalse($cacheTwo);
    }
}