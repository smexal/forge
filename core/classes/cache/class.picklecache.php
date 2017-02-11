<?php

namespace Forge\Core\Classes\Cache;

abstract class PickleCache extends Cache {
    public static function readCache($key){
      $data = parent::readCache($key);
      if(!is_null($data)) {
        return $data;
      }
      return unserialize($data);
    }

    public static function writeCache($key, $data) {
        parent::writeCache($key, serialize($data));
    }

    public static function appendCache($key, $data){
        throw new Error("This is not supported in PickleCache");
    }
}