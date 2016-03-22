<?php

class EventHandler {
    private static $instance = null;
    public $events = array();
   
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function add($event) {
        if(is_array($event)) {
            $this->events = array_merge($this->events, $event);
        } else {
            array_push($this->events, $event);
        }
    }

    public function trigger($event, $data=array()) {
        if(in_array($event, $this->events)) {
            $vm = new ViewManager();
            foreach($vm->views as $view) {
                $rc = new ReflectionClass($view);   
                if($rc->isAbstract())
                  continue;
                if($rc->hasMethod($event)) {
                    $instance = 'instance';
                    $view = $view::$instance();
                    $view->$event($data);
                }
            }
        } else {
            Logger::error('Unknown Event called:"'.$event."'");
        }
    }

    public function init() {
    }

    private function __construct(){
        $this->init();
    }
    private function __clone(){}
}

?>