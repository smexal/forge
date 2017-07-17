<?php

namespace Forge\Core\Classes;

use \Forge\Core\App\App;
use \Forge\Core\Classes\Localization;

class FieldBuilder {
    public static function build($item, $field, $lang=null) {
        $value = FieldLoader::load($item, $field, $lang);
        $value = isset($field['process:build']) ? call_user_func($field['process:build'], $value) : $value;
        return Fields::build($field, $value);
    }
}