<?php

namespace Forge\Core\Classes\Relations;

use Forge\Core\App\App;

class Utils {

    public static function onlyLeftIds($relations) {
        return array_map(function(&$elem) {
            return $elem['item_left'];
        }, $relations);
    }

    public static function onlyRightIds($relations) {
        return array_map(function(&$elem) {
            return $elem['item_right'];
        }, $relations);
    }


}