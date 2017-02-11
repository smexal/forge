<?php

namespace Forge\Core\Classes;

abstract class Cache {
    public static function readCache($key){
        if(!static::cacheExists($key)) {
            return null;
        }
        return file_get_contents(static::getCachePath($key));
    }

    public static function writeCache($key, $data) {
        file_put_contents(static::getCachePath($key), $data);
    }

    public static function appendCache($key, $data){
        file_put_contents(static::getCachePath($key), $data, FILE_APPEND);
    }

    public static function cacheExists($key) {
        if(!file_exists(static::getCachePath($key))){
            return false;
        }
        return true;
    }

    public static function getCachePath($key) {
        $c_key = static::generateKey($key);
        return DOC_ROOT . "cache/" . $c_key;
    }

    public static function generateKey($key) {
        return md5($key . CACHE_SALT);
    }
}