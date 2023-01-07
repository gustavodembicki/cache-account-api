<?php

namespace App\Helper;

use Illuminate\Support\Facades\Cache;

class CacheHelper
{
    /**
     * Static methods responsable to recover a cache value;
     * 
     * @param string $cacheKey
     * @return mixed
     */
    public static function get(string $cacheKey)
    {
        if (self::cacheExists($cacheKey)) {
            return Cache::get($cacheKey);
        }

        return false;
    }

    /**
     * Static method responsable to add a cache
     * 
     * @param string $cacheKey
     * @param mixed $cacheValue
     * @return bool
     */
    public static function put(string $cacheKey, $cacheValue): bool
    {
        return Cache::put($cacheKey, $cacheValue);
    }

    /**
     * Static method responsable to delete a cache
     * 
     * @param string $cacheKey
     * @return bool
     */
    public static function delete(string $cacheKey): bool
    {
        return Cache::forget($cacheKey);
    }

    /**
     * Static method responsable to delete all saved caches
     * 
     * @return bool
     */
    public static function deleteAll(): bool
    {
        return Cache::flush();
    }

    /**
     * Static method responsable to verify if cache exists
     * 
     * @param string $cacheKey
     * @return bool
     */
    public static function cacheExists(string $cacheKey): bool
    {
        return Cache::has($cacheKey);
    }
}