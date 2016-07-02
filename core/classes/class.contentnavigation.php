<?php

class ContentNavigation {
    private $positions = array();

    static private $instance = null;

    public static function create($name, $position) {
        $db = App::instance()->db;
        $db->insert('navigations', array(
            'name' => $name,
            'position' => $position
        ));
        return false;
    }

    public static function registerPosition($id, $name) {
        $inst = self::instance();
        $inst->positions[$id] = $name;
    }

    public static function getNavigations() {
        $db = App::instance()->db;
        return $db->get('navigations');
    }

    public static function getPositions() {
        $inst = self::instance();
        return $inst->positions;
    }

    static public function instance() {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    private function __construct(){}
    private function __clone(){}

}

?>