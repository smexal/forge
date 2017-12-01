<?php

namespace Forge\Core\Classes;

use \Forge\Core\App\App;
use \Forge\Core\Classes\Localization;
use \Forge\Core\Classes\Relations\Relation;
use \Forge\Core\Classes\Relations\Enums\Prepares;
use \Forge\Core\Classes\Relations\Enums\Directions;

class FieldLoader {
    
    public static function load($item, $field, $lang=null) {
        $lang = static::getFieldLanguage($field, $lang);
        $data_source = isset($field['data_source_load']) ? $field['data_source_load'] : 'meta';
        
        if(!is_array($data_source)) {
            $callable = [__CLASS__, 'load' .ucfirst($data_source)];
            $callable = is_callable($callable) ? $callable : $data_source;
        }

        if(!is_callable($callable)) {
            throw new \Exception("Can not load field {$field['key']} via the data_source " .substr(print_r($data_source,1), 0,100));
        }




        $value = call_user_func_array($callable, [$item, $field, $lang]);
        if(isset($field['subfields'])) {
            $data = static::loadSubvalues($item, $field, $lang, $value);
            error_log(print_r("Corrected value of repeater from $value to {$data['value']}", 1));
            $value = $data['value'];
        }
        $value = isset($field['process:load']) ? call_user_func($field['process:load'], $value, $lang) : $value;

        return $value;
    }

    public static function loadSubvalues($item, $field, $lang=null, $value=null) {
        $data = [
            'value' => !is_null($value) ? $value :FieldLoader::load($item, $field, $lang),
            'subvalues' => []
        ];

        if(isset($field['subfields'])) {
            $field_count = $data['value'] === '' && isset($field['init_count']) ? $field['init_count'] : $data['value']; 
            $missing_count = 0;
            for($i = 0; $i < $field_count; $i++) {
                $field = FieldUtils::assignSubfieldKeys($field, $i);
                
                $has_entries = false;
                $field['subvalues'][$i] = [];
                foreach($field['subfields'] as &$subfield) {
                    $result = FieldLoader::load($item, $subfield, $lang);
                    $has_entries = false === $result ? $has_entries : true;
                    $field['subvalues'][$i][] = $result;
                }
                if(!$has_entries) {
                    $missing_count++;
                }
            }
        }
        $data['value'] = max(0, min($data['value'], $data['value'] - $missing_count));
        return $data;
    }

    private static function loadMeta($item, $field, $lang) {
        return $item->getMeta($field['key'], $lang);
    }

    private static function loadRelation($item, $field, $lang) {
        $relation_config = $field['relation'];
        $relation = App::instance()->rd->getRelation($relation_config['identifier']);
        
        // The special case of Directions::REVERSED is not yet implemented here
        $left_id = isset($relation_config['left_id']) ? $relation_config['left_id'] : $item->id;
        $direction = isset($relation_config['direction']) ? $relation_config['direction'] : Directions::DIRECTED;

        if($direction === Directions::DIRECTED) {
            $prepares = isset($relation_config['prepares']) ? $relation_config['prepares'] : Prepares::AS_IDS_RIGHT;
            $res = $relation->getOfLeft($left_id, Prepares::AS_IDS_RIGHT);
        } else {
            $prepares = isset($relation_config['prepares']) ? $relation_config['prepares'] : Prepares::AS_IDS_LEFT;
            $res = $relation->getOfRight($left_id, Prepares::AS_IDS_LEFT);
        }

        return $res;
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