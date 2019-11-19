<?php

namespace Forge\Core\Classes;

use \Forge\Core\App\App;
use \Forge\Core\Classes\Localization;

class FieldSaver {

    public static function save($item, $field, $data) {
        /*if($field['key'] == 'status') {
            var_dump($item);
            var_dump($field);
            var_dump($data);
            die();
        }*/

        $lang = static::determineLang($field, $data['language']);

        $data_source = isset($field['data_source_save']) ? $field['data_source_save'] : 'meta';
        
        $value = isset($data[$field['key']]) ? $data[$field['key']] : null;
        $value = isset($field['process:save']) ? call_user_func($field['process:save'], $value) : $value;

        if(is_array($data_source)) {
            $callable = $data_source;
        } else {
            $callable = [__CLASS__, 'save' . ucfirst($data_source)];
            $callable = is_callable($callable) ? $callable : $data_source;
        }

        if(isset($callable) && !is_callable($callable)) {
            throw new \Exception("Can not save field {$field['key']} via the data_source " . substr(print_r($data_source,1), 0,100));
        }
        call_user_func_array($callable, [$item, $field, $value, $lang]);
        
        if(isset($field['subfields'])) {
            $field_count = $value === '' && isset($field['init_count']) ? $field['init_count'] : $value; 

            for($i = 0; $i < $field_count; $i++) {
                $field = FieldUtils::assignSubfieldKeys($field, $i);
                
                foreach($field['subfields'] as $subfield) {
                    FieldSaver::save($item, $subfield, $data);
                }
            }
        }
    }

    private static function saveMeta($item, $field, $value, $lang) {
        if(is_array($value)) {
            $value = json_encode($value);
        }
        $item->updateMeta($field['key'], $value, $lang);
    }

    private static function saveRelation($item, $field, $value, $lang) {
        $value = is_null($value) || $value === '' || $value === false ? [] : explode(',', $value);
        $value = array_map('trim', $value);
        $value = array_map([App::instance()->db, 'escape'], $value);
        if(!is_array($value)) {
            throw new \Exception("Can only save array values as relation");
        }
        $relation = $field['relation'];
        $rel = App::instance()->rd->getRelation($relation['identifier']);
        // The special case of Directions::REVERSED is not yet implemented
        $rel->setRightItems($item->id, $value);
    }

    public static function remove($item, $field, $lang) {
        $lang = static::determineLang($field, $lang);

        $data_source = isset($field['data_source_remove']) ? $field['data_source_remove'] : 'meta';

        if(!is_array($data_source)) {
            $callable = [__CLASS__, 'remove' .ucfirst($data_source)];
            $callable = is_callable($callable) ? $callable : $data_source;
        }

        if(!is_callable($callable)) {
            throw new \Exception("Can not remove field {$field['key']} via the data_source " .substr(print_r($data_source,1), 0,100));
        }
        call_user_func_array($callable, [$item, $field, '', $lang]);

        if(isset($field['subfields'])) {
            foreach($field['subfields'] as &$subfield) {
                FieldSaver::remove($item, $subfield, $data);
            }
        }
    }

    private static function removeMeta($item, $field, $key, $lang) {
        $item->updateMeta($field['key'], '', static::determineLang($field, $lang));
    }
    
    private static function removeRelation($item, $field, $key, $lang) {
        $relation = $field['relation'];
        $rel = App::instance()->rd->getRelation($relation['identifier']);
        $rel->removeAll();
    }

    public static function determineLang($field, $lang) {
        if ($field['multilang'] == false) {
            return '0';
        } else if(!!$lang) {
            $lang = $lang;
        } else {
            $lang = Localization::getCurrentLanguage();
        }

        return $lang;
    }
}