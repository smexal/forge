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

    public function add($name) {
        array_push($this->events, $name);
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
        $this->add("onLoginSubmit");
        $this->add("onLoginFailed");
        $this->add("onLoginSuccess");
    }

    private function __construct(){
        $this->init();
    }
    private function __clone(){}
}

?>