<?php

namespace Forge\Core\App;

use \Forge\Core\Classes\Logger;

class EventHandler {
    private static $instance = null;
    public $events = array();
    public $callables = array();

    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function register($trigger, $callable, $params=null) {
        if(!array_key_exists($trigger, $this->callables)) {
            $this->callables[$trigger] = [];
        }
        array_push($this->callables[$trigger], ['fn' => $callable, 'params' => $params]);
    }

    public function fire($event) {
        $return = null;
        if(array_key_exists($event, $this->callables)) {
            foreach($this->callables[$event] as $callable) {
                $returnable = call_user_func_array($callable['fn'], [$callable['params']]);
                if(!is_null($returnable)) {
                    if(is_null($return)) {
                        $return = '';
                    }
                    $return.= $returnable;
                }
            }
        }
        return $return;
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
                $rc = new \ReflectionClass($view);
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

