<?php

namespace Forge\Core\Classes;

class FieldUtils {
    public static function get_field($fields, $key_value, $key_key='key') {
        foreach($fields as $field) {
            if(array_key_exists($key_key, $field) && $field[$key_key] === $key_value) {
                return $field;
            }
        }
    }

    public static function set_field($fields, $field) {
        $key_key = 'key';
        $key_value = $field['key'];

        foreach($fields as $a_key => $old_field) {
            if(array_key_exists($key_key, $old_field) && $old_field[$key_key] === $key_value) {
                $fields[$a_key] = $field;
                return $fields;
            }
        }

        $fields[] = $field;
        return $fields;
    }

    public static function field_data_html($data, $urlencode=true) {
        foreach($data as $key => $value) {
            if(!is_array($value) || is_object($value)) {
                $value = json_encode($value);
                if($urlencode) {
                    $value = rawurlencode($value);
                }
                $data[$key] = $value;
            }
        }

        return $data;
    }

    public static function assignSubfieldKeys($parent_field, $iteration=0) {
        $type_count = [];
        foreach($parent_field['subfields'] as $key => $field) {
            if(!isset($type_count[$field['type']])) {
                $type_count[$field['type']] = 0;
            }
            $type_idx = $type_count[$field['type']];
            $type_count[$field['type']] = $type_idx + 1;

            $field['iteration'] = $iteration;
            
            if(isset($field['original_key'])) {
                $field['key'] = $field['original_key'];
            } else {
                $field['key'] = isset($field['key']) ? $field['key'] : $field['type'] . '-' . $type_idx;
            }
            $field['original_key'] = $field['key'];
            
            $field['key_iteration_prefix'] = $parent_field['key'] . '_';
            $field['key_iteration_suffix'] = '_' . $field['key'];
            $field['key'] = $field['key_iteration_prefix']  . $iteration . $field['key_iteration_suffix'];

            $parent_field['subfields'][$key] = $field;
        }
        return $parent_field;
    }
    
}