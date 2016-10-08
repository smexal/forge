<?php

class API {
    static private $instance = null;
    private $calls = array();

    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function run($query, $subquery=array(), $post=array()) {
        if(array_key_exists($query, $this->calls)) {
            if(count($subquery) > 1) {
                $args = array(array(
                        'query' => $subquery,
                        'data' => $post
                    )
                );
            } else {
                $args = $subquery;
            }
            return call_user_func_array($this->calls[$query], $args);
        } else {
            return false;
        }
    }

    public function register($query, $callable) {
        if(! array_key_exists($query, $this->calls)) {
            $this->calls[$query] = $callable;
        } else {
            Logger::debug('Tryed to add \"'.$query.'\" to the api, which does already exist.');
        }
    }

    private function __construct(){}
    private function __clone(){}
}

?>