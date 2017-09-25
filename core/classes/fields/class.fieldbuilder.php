<?php

namespace Forge\Core\Classes;

use \Forge\Core\App\App;
use \Forge\Core\Classes\Localization;

class FieldBuilder {

    public static function build($item, $field, $lang=null) {
        $value = FieldLoader::load($item, $field, $lang);
        $value = isset($field['process:load']) ? call_user_func($field['process:load'], $value) : $value;


         if(isset($field['subfields'])) {
            $field['rendered_subfields'] = [];
            $field_count = $value === '' && isset($field['init_count']) ? $field['init_count'] : $value; 
            
            for($i = 0; $i++; $i < $field_count) {
                $field['subfields'] = FieldUtils::assignSubfieldKeys($field['subfields'], $i);
                
                $field['rendered_subfields'][$i] = [];
                foreach($field['subfields'] as &$subfield) {
                    $field['rendered_subfields'][$i][] = [
                        'data' => $subfield,
                        'render' => FieldBuilder::build($item, $subfield, $lang)
                    ];
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