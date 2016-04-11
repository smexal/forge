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

}


?>
