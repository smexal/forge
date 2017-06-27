<?php

namespace Forge\Core\Classes;

use Forge\Core\Classes\CollectionItem;

class Relation {
    protected static $C_LEFT;
    protected static $C_RIGHT;

    public static function register() {
        $db = App::instance()->db;
        $c_left = App::instance()->cm->getCollection(static::$C_LEFT);
        $c_right = App::instance()->cm->getCollection(static::$C_RIGHT);
        
        if(!$c_left) {
            throw new Exception("Collection '$c_left' hasn't been found");
        }

        if(!$c_right) {
            throw new Exception("Collection '$c_right' hasn't been found");
        }

        

    }

}