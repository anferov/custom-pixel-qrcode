<?php

namespace Anferov\QrCode\Generator;

class CacheConfig
{

    private static $useCache = true;

    public static function enableCache()
    {
        self::$useCache = true;
    }

    public static function disableCache()
    {
        self::$useCache = true;
    }

    public static function isCacheEnabled()
    {
        return self::$useCache;
    }

    public static function getCacheDir()
    {
        return self::$useCache ? dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR : false;
    }
}
