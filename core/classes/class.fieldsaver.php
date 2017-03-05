<?php

namespace Forge\Core\Classes;

use \Forge\Core\App\App;
use \Forge\Core\Classes\Localization;

class FieldSaver {
    public static function save($item, $field, $data) {
        $lang = static::determineLang($field, $data['language']);

        $meta_key   = $field['key'];
        $meta_lang  = $lang;
        $meta_value = $data[$field['key']];
        $meta_value = isset($field['process:save']) ? call_user_func($field['process:save'], $meta_value) : $meta_value;

        if (is_array($data[$field['key']])) {
            $meta_value = json_encode($meta_value);
        }
        $item->updateMeta($meta_key, $meta_value, $meta_lang);
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