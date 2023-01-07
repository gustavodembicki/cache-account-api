<?php

namespace Tests\Unit\Helper;

use App\Helper\CacheHelper;
use Tests\TestCase;

class CacheHelperTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        CacheHelper::deleteAll();
    }

    /** @test */
    public function shouldReturnFalseWhenRecoveringCache()
    {
        //Given
        //When
        $response = CacheHelper::get("unexisting_cache");
        
        //Then
        $this->assertFalse($response);
    }

    /** @test */
    public function shouldReturnCacheDataWhenRecovering()
    {
        //Given
        CacheHelper::put("cache_exists", true);

        //When
        $response = CacheHelper::get("cache_exists");

        //Then
        $this->assertTrue($response);
    }

    /** @test */
    public function shouldPutCache()
    {
        //Given
        //When
        $data = CacheHelper::put("cache_put", true);

        //When
        $this->assertTrue($data);
    }

    /** @test */
    public function shouldDeleteCache()
    {
        //Given
        CacheHelper::put("cache_to_delete", true);

        //When
        $response = CacheHelper::delete("cache_to_delete");

        //Then
        $this->assertTrue($response);
    }

    /** @test */
    public function shouldDeleteAllCaches()
    {
        //Given
        CacheHelper::put("cache_one", true);
        CacheHelper::put("cache_two", true);

        //When
        $response = CacheHelper::deleteAll();

        $cacheOne = CacheHelper::get("cache_one");
        $cacheTwo = CacheHelper::get("cache_two");

        //Then
        $this->assertTrue($response);
        $this->assertFalse($cacheOne);
        $this->assertFalse($cacheTwo);
    }

    /** @test */
    public function shouldCacheExists()
    {
        //Given
        CacheHelper::put("cache_exists", "exists");

        //When
        $response = CacheHelper::cacheExists("cache_exists");

        //Then
        $this->assertEquals("exists", $response);
    }
}