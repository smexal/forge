<?php

namespace Forge\Core\Abstracts;

use \Forge\Core\Interfaces\ICallableAPI;

use \Forge\Core\App\API;

abstract class APIFacade implements ICallableAPI {
  protected static $instances = [];
  protected $trigger = null;

  public static function instance() {
    $cls = get_called_class();
    if(!array_key_exists($cls, static::$instances)) {
      static::$instances[$cls] = new $cls();
    }
    return static::$instances[$cls];
  }


  public function register() {  
    API::instance()->register($this->trigger, array($this, "call_fix"));
  }


  /**
   * @description: The interface for the \Forge\Core\App\Api is inconsistent
   * and does not call the registered methods always with an array but somtimes
   * only with the first path fragment. This is corrected here.
   */
  public function call_fix($request) {
    if(is_array($request))
      return $this->call($request);

    $method = isset($_REQUEST['request_method']) ? $_REQUEST['request_method'] : $_SERVER['REQUEST_METHOD'];

    if($method === 'POST') {
        $data = $_POST;
    } else { // GET / PUT / PATCH / DELETE
        $data = $_GET;
    }

    $this->call([
        'query' => [$request],
        'data' => $data,
        'method' => $method
    ]);
  }
}