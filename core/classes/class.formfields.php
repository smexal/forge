<?php
class Fields {

    public static function build($args, $value='') {
        switch($args['type']) {
            case 'text':
                return self::text($args, $value);
                break;
            case 'linklist':
                return self::linklist($args);
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

    public static function image($args, $value='') {
        if(array_key_exists('saved_value', $args)) {
            $value = $args['saved_value'];
        }
        $mediamanager = new MediaManager();
        $images = $mediamanager->get('images');
        $media_array = array();
        foreach($images as $media) {
            array_push($media_array, array(
                'image' => $media->getUrl(),
                'title' => $media->title,
                'active' => $value == $media->id ? true : false,
                'id' => $media->id
            ));
        }
        return App::instance()->render(CORE_TEMPLATE_DIR."assets/", 'imageselection', array(
            'name' => $args['key'],
            'selection_url' => Utils::getUrl(array('api', 'media', 'list-images')),
            'selection_title' => i('Choose image'),
            'value' => $value,
            'media' => $media_array
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

    public static function wysiwyg($args, $value='') {
        if(array_key_exists('saved_value', $args)) {
            $value = $args['saved_value'];
        }
        return App::instance()->render(CORE_TEMPLATE_DIR."assets/", "tinymce", array(
            'id' => $args['key'],
            'name' => $args['key'],
            'label' => $args['label'],
            'value' => $value,
            'hint' => $args['hint'],
            'disabled' => false
        ));
    }

    public static function button($name) {
        return App::instance()->render(CORE_TEMPLATE_DIR."assets/", "submit", array(
            'text' => $name,
            'level' => 'primary',
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
