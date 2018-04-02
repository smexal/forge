<?php

namespace Forge\Core\Classes;


use Forge\Core\Classes\Logger;
use Forge\Core\App\Auth;

class Cache {

    public static $directory = DOC_ROOT.'cache/';
    public static $cache_time = 3 * 60; // cache valid time = 3 minutes

    public static $whitelist = [
        'manage',
        'logout'
    ];

    public static function write($identifier, $content) {

        if(! USE_CACHE) {
            return;
        }

        // we got some get parameters, dont cache this.
        if(count($_GET) > 0) {
            return;
        }

        // logged in users cant write cache with this Oo
        if(Auth::any()) {
            return;
        }

        if(self::inWhiteList($identifier)) {
            return;
        }

        $filename = self::getName($identifier);
        $cacheFile = fopen(self::$directory.$filename, "w");
        fwrite($cacheFile, $content);
        fclose($cacheFile);
        Logger::debug("New Cache for > ".$identifier);
    }

    public static function valid($identifier) {

        if(! USE_CACHE) {
            return;
        }

        // some post data has been sent, we can not get this from cache.
        if(count($_POST) > 0) {
            return false;
        }

        // we got some get data, we can no get this from cache.
        if(count($_GET) > 0) {
            return false;
        }

        // logged in user cant write cache with this Oo
        if(Auth::any()) {
            return;
        }

        $filename = self::getName($identifier);
        $cacheFile = self::$directory.$filename;
        if(! file_exists($cacheFile)) {
            return false;
        }
        $secondsSinceChange = microtime(true) - filemtime($cacheFile);
        if($secondsSinceChange < self::$cache_time) {
            return true;
        }
    }

    public static function get($identifier) {
        $filename = self::getName($identifier);
        $cacheFile = self::$directory.$filename;
        Logger::debug("Render Cache for > ".$identifier);
        return file_get_contents($cacheFile);
    }

    public static function getName($identifier) {
        return md5($identifier).'.cache';
    }

    public static function inWhiteList($identifier) {
        foreach(self::$whitelist as $whitelist_entry) {
            if(strstr($identifier, $whitelist_entry)) {
                return true;
            }
        }
        return;
    }

}

?>