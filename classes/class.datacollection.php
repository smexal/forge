<?php

abstract class DataCollection implements IDataCollection {
  protected static $instances = array();  
  protected $app;

  protected function preferences() {
    return array(
      'name' => 'collection',
      'title' => i('Data'),
      'add' => i('New item'),
      'alltitle' => i('All Collection Items')
    );
  }

  private function init() {
    $this->app = App::instance();
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
