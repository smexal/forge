<?php

namespace Forge\Core\Classes;

use \Forge\Core\App\App;
use \Forge\Core\Classes\Localization;

class FieldBuilder {

    public static function build($item, $field, $lang=null) {
        $value = FieldLoader::load($item, $field, $lang);
        $value = isset($field['process:build']) ? call_user_func($field['process:build'], $value) : $value;

         if(isset($field['subfields'])) {
            $field['rendered_subfields'] = [];
            $field_count = $value === '' && isset($field['init_count']) ? $field['init_count'] : $value; 

            $field = FieldUtils::assignSubfieldKeys($field, 0);
            for($i = 0; $i < $field_count; $i++) {
                $field = FieldUtils::assignSubfieldKeys($field, $i);
                $field['rendered_subfields'][$i] = [];
                foreach($field['subfields'] as &$subfield) {
                    $subfield['rendered'] = FieldBuilder::build($item, $subfield, $lang);
                    $field['rendered_subfields'][$i][] = $subfield;
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


}