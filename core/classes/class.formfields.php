<?php
class Fields {

    public static function build($args, $value='') {
        switch($args['type']) {
            case 'text':
                return self::text($args, $value);
                break;
            case 'select':
                return self::select($args, $value);
                break;
            case 'multiselect':
                return self::multiselect($args, $value);
                break;
            case 'textarea':
                return self::textarea($args, $value);
                break;
            case 'wysiwyg':
                return self::wysiwyg($args, $value);
                break;
            case 'checkbox':
                return self::checkbox($args, $value);
                break;
            case 'linklist':
                return self::linklist($args);
                break;
        }
    }

    public static function linklist($args) {
        $return = App::instance()->render(CORE_TEMPLATE_DIR."assets/", 'linklist', array(
            'title' => $args['label'],
            'links' => $args['links']
        ));
        return self::boxed($args, $return);
    }

    public static function boxed($args, $content) {
        if($args['boxed']) {
            return App::instance()->render(CORE_TEMPLATE_DIR."assets/", "boxed", array(
                'content' => $content
            ));
        }
    }

    public static function textarea($args, $value='') {
        if(array_key_exists('saved_value', $args)) {
            $value = $args['saved_value'];
        }
        return App::instance()->render(CORE_TEMPLATE_DIR."assets/", "textarea", array(
            'id' => $args['key'],
            'name' => $args['key'],
            'label' => $args['label'],
            'value' => $value,
            'hint' => $args['hint'],
            'disabled' => false
        ));
    }

    public static function text($args, $value='') {
        if(array_key_exists('saved_value', $args)) {
            $value = $args['saved_value'];
        }
        return App::instance()->render(CORE_TEMPLATE_DIR."assets/", "input", array(
            'name' => $args['key'],
            'id' => $args['key'],
            'label' => $args['label'],
            'type' => 'text',
            'hor' => false,
            'noautocomplete' => false,
            'value' => $value,
            'hint' => $args['hint']
        ));
    }

    public static function checkbox($args, $value='') {
        if(array_key_exists('saved_value', $args)) {
            $value = $args['saved_value'];
        }
        return App::instance()->render(CORE_TEMPLATE_DIR."assets/", "input", array(
            'name' => $args['key'],
            'id' => $args['key'],
            'label' => $args['label'],
            'type' => 'checkbox',
            'hor' => false,
            'noautocomplete' => false,
            'value' => $value,
            'hint' => $args['hint']
        ));
    }

    public static function image($args, $value='') {
        if(array_key_exists('saved_value', $args)) {
            $value = $args['saved_value'];
        }
        $media = new Media($value);
        return App::instance()->render(CORE_TEMPLATE_DIR."assets/", 'imageselection', array(
            'label' => $args['label'],
            'name' => $args['key'],
            'change_text' => i('Choose image'),
            'change_url' => Utils::getUrl(array('manage', 'media'), true, array('selection' => 1, 'target' => $args['key'])),
            'selected_image' => $media->getUrl(),
            'no_image' => i('No image selected'),
            'value' => $value
        ));
    }

    public static function select($args, $value='') {
        if(array_key_exists('saved_value', $args)) {
            $value = $args['saved_value'];
        }
        return App::instance()->render(CORE_TEMPLATE_DIR."assets/", "select", array(
            'name' => $args['key'],
            'id' => $args['key'],
            'label' => $args['label'],
            'values' => $args['values'],
            'selected' => $value,
            'hint' => (array_key_exists('hint', $args) ? $args['hint'] : false)
        ));
    }

    public static function multiselect($args, $value='') {
        if(array_key_exists('saved_value', $args)) {
            $value = $args['saved_value'];
        }
        if(array_key_exists('values', $args) && $value) {
            foreach($args['values'] as $key => $available) {
                if(in_array($available['value'], $value)) {
                    $args['values'][$key]['active'] = true;
                }
            }
        }
        return App::instance()->render(CORE_TEMPLATE_DIR."assets/", "multiselect", array(
            'name' => $args['key'],
            'id' => $args['key'],
            'label' => $args['label'],
            'values' => $args['values'],
            'hint' => (array_key_exists('hint', $args) ? $args['hint'] : false)
        ));
    }

    public static function wysiwyg($args, $value='') {
        if(array_key_exists('saved_value', $args)) {
            $value = $args['saved_value'];
        }
        $theme = App::instance()->tm->theme;
        $css = '';
        $formats = '';
        if(!is_null($theme)) {
            $css = $theme->tinyUrl();
            $formats = Utils::json($theme->tinyFormats());
        }
        return App::instance()->render(CORE_TEMPLATE_DIR."assets/", "tinymce", array(
            'id' => $args['key'],
            'name' => $args['key'],
            'label' => $args['label'],
            'value' => $value,
            'hint' => $args['hint'],
            'disabled' => false,
            'formats' => $formats,
            'css' => $css
        ));
    }

    public static function button($name, $level='primary') {
        return App::instance()->render(CORE_TEMPLATE_DIR."assets/", "submit", array(
            'text' => $name,
            'level' => $level,
            'hor' => false
        ));
    }

    public static function hidden($args) {
        return App::instance()->render(CORE_TEMPLATE_DIR."assets/", "hidden", array(
            'name' => $args['name'],
            'value' => $args['value']
        ));
    }

}


?>
