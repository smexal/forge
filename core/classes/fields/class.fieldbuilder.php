<?php

namespace Forge\Core\Classes;

use \Forge\Core\App\App;
use \Forge\Core\Classes\Localization;

class FieldBuilder {

    public static function build($item, $field, $lang=null) {
        $value = FieldLoader::load($item, $field, $lang);
        $value = isset($field['process:load']) ? call_user_func($field['process:load'], $value) : $value;


         if(isset($field['subfields'])) {
            $value = $value === '' && isset($field['init_count']) ? $field['init_count'] : $value; 
            $field['rendered_subfields'] = [];
            for($i = 0; $i++; $i < $value) {
                $field['subfields'] = static::assignSubfieldKeys($field, $i);
                foreach($field['subfields'] as &$subfield) {
                    $field['rendered_subfields'] = FieldBuilder::build($item, $subfield, $lang);
                }
            }
        }

        if(isset($field['process:modifyField']) && is_callable($field['process:modifyField'])) {
            $field = call_user_func($field['process:modifyField'], $field, $item, $value);
        }
        $html  = Fields::build($field, $value);

        if(isset($field['process:afterRender']) && is_callable($field['process:afterRender'])) {
            $html =  call_user_func($field['process:afterRender'], $value, $item);
        }

        return  $html;
    }

    public static function &assignSubfieldKeys(&$parent_field, $iteration=0) {
        $type_count = [];
        $subfields = &$parent_field['subfields'];
        foreach($subfields as &$field) {
            $type_idx = $type_count[$field['type']];
            $type_count[$field['type']] = $type_idx + 1;

            $field['iteration'] = $iteration;
            $field['key'] = isset($field['key']) ? $field['key'] : $field['type'] . '-' . $type_idx;
            
            $field['original_key'] = $field['key'];
            $field['key'] = $parent_field['key'] . '_' . $iteration . '_' .  $field['key'];
        }

        return $parent_field;
    }
}