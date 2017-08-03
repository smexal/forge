<?php

namespace Forge\Core\Classes;

use \Forge\Core\App\App;
use \Forge\Core\Classes\Localization;

class FieldSaver {
    public static function save($item, $field, $data) {
        $lang = static::determineLang($field, $data['language']);

        $key   = $field['key'];
        $lang  = $lang;
        $value = $data[$field['key']];
        $value = isset($field['process:save']) ? call_user_func($field['process:save'], $value) : $value;

         if(isset($field['relation'])) {
            $relation = App::instance()->rd->getRelation($field['relation']);
            // The special case of Directions::REVERSED is not yet implemented here
            $relation->setRightItems($value);

        } else {
            if (is_array($data[$field['key']])) {
                $value = json_encode($value);
            }
            $item->updateMeta($key, $value, $lang);
        }
    }

    public static function remove($item, $field, $lang) {
        $item->updateMeta($field['key'], '', static::determineLang($field, $lang));
    }

    public static function determineLang($field, $lang) {
        if ($field['multilang'] == false) {
            $lang = false;
        } else if(!!$lang) {
            $lang = $lang;
        } else {
            $lang = Localization::getCurrentLanguage();
        }

        return $lang;
    }
}