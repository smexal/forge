<?php

namespace Forge\Core\Classes;

use \Forge\Core\App\App;
use \Forge\Core\Classes\Localization;

class FieldBuilder {
    public static function build($item, $field, $lang=null) {
        $value = FieldLoader::load($item, $field, $lang);
        $value = isset($field['process:load']) ? call_user_func($field['process:load'], $value) : $value;

        if(isset($field['process:modifyField']) && is_callable($field['process:modifyField'])) {
            $field = call_user_func($field['process:modifyField'], $field, $item, $value);
        }
        $html  = Fields::build($field, $value);

        if(isset($field['process:afterRender']) && is_callable($field['process:afterRender'])) {
            $html =  call_user_func($field['process:afterRender'], $value, $item);
        }

        return  $html;
    }
}