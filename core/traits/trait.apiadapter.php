<?php

namespace Forge\Core\Traits;

use \Forge\Core\Classes\Utils;
use \Forge\Core\App\API;
use \Forge\Core\Classes\Logger;

/**
 * An API Adapter trait.
 * Using this trait will allow a class to register Api Methods, which get called with a certain structure.
 * Check "navigationmanager" for example usage.
 */
trait ApiAdapter {

    public function __construct() {
        // make sure this is not been called from the parents __construct
        if(debug_backtrace()[1]['function'] !== '__construct') {
            // only check if class has parent
            if((bool)class_parents($this)) {
                parent::__construct();
            }
        }
        if(!isset($this->apiMainListener)) {
            Logger::error('When using, the trait "ApiAdapter", you have to define a "apiMainListener" variable');
            return;
        }
        API::instance()->register($this->apiMainListener, array($this, 'apiAdapter'));
    }

    public function apiAdapter($data) {
        if(is_array($data)) {
            $main = array_shift($data['query']);
            $query = $data['query'];
            $data = $data['data'];
        } else {
            $main = $data;
            $query = false;
            $data = $_POST;
        }
        $method = Utils::methodName($main);
        if(method_exists($this, $method)) {
            return call_user_func_array(
                [$this, $method],
                [$query, $data]
            );
        }
    }

}

?>
