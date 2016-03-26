<?php

abstract class Module implements IModule {
  protected static $instances = array();
  abstract protected function setup();
  
  // initial variables for module
  public $name = null;
  public $version = "0.1";
  public $description = "";
  public $image = CORE_WWW_ROOT."images/default-icon-module.svg";
  public $id = null;

  public function check() {
    if(is_null($this->name)) {
      return sprintf(i('Name for Module not set. Set public->$name in setup Method in Module `%s`'), get_called_class());
    }
    return true;
  }

  static public function instance() {
    $class = get_called_class();
    if(!array_key_exists($class, static::$instances)) {
        static::$instances[$class] = new $class();
    }
    static::$instances[$class]->id = $class;
    static::$instances[$class]->setup();
    return static::$instances[$class];
  }
  private function __construct() {}
  private function __clone() {}

}

?>
