<?php

namespace Forge\Core\Classes;

use \Forge\Core\App\App;
use \Forge\Core\Classes\Localization;

class FieldLoader {
    public static function load($item, $field, $lang=null) {
        $lang = static::getFieldLanguage($field, $lang);

        $value = $item->getMeta($field['key'], $lang);
        $value = isset($field['process:load']) ? call_user_func($field['process:load'], $value, $lang) : $value;

        return $value;
        
    }

    private static function getFieldLanguage($field, $lang=null) {
        if($field['multilang'] == false) {
            return 0;
        }
        
        if(Localization::languageIsActive($lang)) {
            return $lang;
        }

        return Localization::currentLang();
    }
}