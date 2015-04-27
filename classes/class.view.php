<?php

abstract class AbstractView implements IView {
    protected static $instances = array();
    public $parent = false;
    public $default = false;
    public $permission = null;
    public $permissions = array();
    public $events = array();
    public $activeSubview = false;

    public $app = null;

    public function initEssential() {
        if(is_null($this->app))
            $this->app = App::instance();
        $this->permissions();
        if(is_null($this->permission)) {
          return;
        }
        if(! Auth::allowed($this->permission)) {
          $this->app->redirect('denied');
        }
    }

    /*
      Registers permission in the database if they do not yet exist.
    */
    public function permissions() {
      if(!is_null($this->permission)) {
        Auth::registerPermissions($this->permission);
      }
      if(count($this->permissions) == 0)
        return;
      Auth::registerPermissions($this->permissions);
    }

    public function getSubview($uri_components, $parent) {
      $vm = new ViewManager();
      if(count($uri_components) == 0) {
        $subview = '';
      } else {
        $subview = $uri_components[0];
      }
      $found = false;
      $load_main_subview = $subview == '' ? true : false;
      foreach($vm->views as $view) {
        $rc = new ReflectionClass($view);
        
        if($rc->isAbstract())
          continue;
        $instance = 'instance';
        $view = $view::$instance();
        if($load_main_subview && $view->default || $subview == $view->name()) {
          $found = true;
          break;
        }
      }
      if(!$found) {
        Logger::error("View not found.");
        App::instance()->redirect('404');
      } else {
        $parent->activeSubview = $view->name;
        $view->initEssential();
        array_shift($uri_components);
        return $view->content($uri_components);
      }        
    }
    

    public function name() { 
        return $this->name;
    }
    public function content($uri=array()) {
        return 'content output, default for module.';
    }

    static public function instance() {
        $class = get_called_class();
        if(!array_key_exists($class, static::$instances)) {
            static::$instances[$class] = new $class();
        }
        return static::$instances[$class];
    }
    private function __construct() {}
    private function __clone() {}

}