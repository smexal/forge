<?php

namespace Forge\Core\Classes;

use \Forge\Core\App\App;
use \Forge\Core\Classes\Localization;
use \Forge\Core\Classes\Relations\Relation;
use \Forge\Core\Classes\Relations\Enums\Prepares;

class FieldLoader {
    public static function load($item, $field, $lang=null) {
        $lang = static::getFieldLanguage($field, $lang);
        $data_source = isset($field['data_source']) ? $field['data_source'] : 'meta';
        
        $callable = [__CLASS__, 'load' .ucfirst($data_source)];
        $callable = is_callable($callable) ? $callable : $data_source;

        if(!is_callable($callable)) {
            throw new \Exception("Can not load field {$field['key']} via the data_source " .substr(print_r($data_source,1), 0,100));
        }

        $value = call_user_func_array($callable, [$item, $field, $lang]);
        $value = isset($field['process:load']) ? call_user_func($field['process:load'], $value, $lang) : $value;

        return $value;
    }

    private static function loadMeta($item, $field, $lang) {
        return $item->getMeta($field['key'], $lang);
    }

    private static function loadRelation($item, $field, $lang) {
        $relation = $field['relation'];
        $relation = App::instance()->rd->getRelation($relation['identifier']);
        // The special case of Direction::REVERSED is not yet implemented here
        return $relation->getOfLeft($item->id, Prepares::AS_RIGHT_IDS);
    }

    private static function getFieldLanguage($field, $lang=null) {
        if($field['multilang'] == false) {
            return 0;
        }
        
        if(Localization::languageIsActive($lang)) {
            return $lang;
        }

        return Localization::getCurrentLanguage();
    }
}