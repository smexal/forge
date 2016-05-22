<?php

abstract class Theme implements ITheme {
    protected static $instances = array();

    static public function instance() {
        $class = get_called_class();
        if(!array_key_exists($class, static::$instances)) {
            static::$instances[$class] = new $class();
        }
        static::$instances[$class]->id = $class;
        return static::$instances[$class];
    }
    private function __construct() {}
    private function __clone() {}

    public function header() {
        return '<head></head>';
    }

    public function footer() {
        return '<footer></footer>';
    }

}

?>
