<?php

abstract class DataCollection implements IDataCollection {
  public $permission = null;
  protected static $instances = array();
  protected $app;
  public $preferences = array();

  abstract protected function setup();

  public function getPref($name) {
    return $this->preferences[$name];
  }

  private function init() {
    $this->app = App::instance();
    $this->setup();
    if(!is_null($this->permission)) {
      Auth::registerPermissions($this->permission);
    }
    $this->preferences = array(
      'name' => strtolower(get_class($this)),
      'title' => i('Data'),
      'all-title' => i('All Collection Items')
    );
    $this->setup();
  }

  static public function instance() {
    $class = get_called_class();
    if(!array_key_exists($class, static::$instances)) {
        static::$instances[$class] = new $class();
    }
    static::$instances[$class]->init();
    return static::$instances[$class];
  }
  private function __construct() {}
  private function __clone() {}

}

?>
