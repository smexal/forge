<?php

namespace Forge\Core\App;

use \Forge\Core\App\App;
use \Forge\Core\Classes\Logger;

class API {
    const METHODS = ['POST', 'GET', 'PUT',  'PATCH', 'DELETE'];

    static private $instance = null;
    private $calls = array();

    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function exists($query) {
        return array_key_exists($query, $this->calls);
    }

    public static function getAPIURL() {
        return App::instance()->vm->getViewByName('api')->buildURL();
    }

    public function run($query, $subquery=array(), $data=null) {
        $method = isset($_REQUEST['request_method']) ? $_REQUEST['request_method'] : $_SERVER['REQUEST_METHOD'];

        if(!in_array($method, static::METHODS)) {
            static::error(405, i('The provided method is not supported', 'forge'));
        }

        if(is_null($data)) {
            if($method === 'POST') {
                $data = $_POST;
            } else { // GET / PUT / PATCH / DELETE
                $data = $_GET;
            }
        }

        if (array_key_exists($query, $this->calls)) {
            if (count($subquery) > 1) {
                $args = [[
                            'query' => $subquery,
                            'data' => $data,
                            'method' => $method
                        ]];
            } else {
                $args = $subquery;
            }
            return call_user_func_array($this->calls[$query], $args);
        } else {
            return false;
        }
    }

    public function register($query, $callable) {
        if (!array_key_exists($query, $this->calls)) {
            $this->calls[$query] = $callable;
        } else {
            Logger::debug('Tried to add \"'.$query.'\" to the api, which does already exist.');
        }
    }

    public static function error($code, $msg) {
        die(json_encode([
            'code' => $code,
            'message' => i('Somthing bad happened', 'forge'),
            'description' => $msg
        ]));
    } 

    private function __construct(){}
    private function __clone(){}
}

