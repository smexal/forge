<?php

abstract class DataCollection implements IDataCollection {
  public $permission = null;
  protected static $instances = array();
  protected $app;
  public $preferences = array();
  public $name = false;

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
      'all-title' => i('All Collection Items'),
      'add-label' => i('Add item'),
      'single-item' => i('item')
    );
    $this->setup();
    $this->name = $this->getPref('name');
  }

  public function items() {
    $db = App::instance()->db;
    $db->where('type', $this->name);
    return $db->get('collections');
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
