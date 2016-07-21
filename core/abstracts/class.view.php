<?php

abstract class AbstractView implements IView {
    protected static $instances = array();
    public $parent = false;
    public $default = false;
    public $standalone = false;
    public $permission = null;
    public $permissions = array();
    public $events = array();
    public $activeSubview = false;
    public $favicon = WWW_ROOT."images/favicon.png";
    public $allowNavigation = false;

    public function additionalNavigationForm() {
      return array("form" => "");
    }

    public $app = null;

    public function buildURL() {
      $items = array($this);
      $items = array_merge($items, $this->getParentItems($this));
      $parts = array();
      foreach($items as $item) {
        array_push($parts, $item->name);
      }
      return Utils::getUrl($parts);
    }

    private function getParentItems($c) {
      $items = array();
      if($c->parent) {
        $items = array_merge($items, $this->getParentItems($c::instance()));
      }
      return $items;
    }

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

    public function init() {
      return;
    }

    /*
      Registers permission in the database if they do not yet exist.
    */
    public function permissions() {
      if(!is_null($this->permission)) {
        Auth::registerPermissions($this->permission);
      }
      if(count($this->permissions) == 0 || is_null($this->permissions)) {
        return;
      }
      Auth::registerPermissions($this->permissions);
    }

    public function getSubview($uri_components, $parent) {
      $vm = App::instance()->vm;
      if(!is_array($uri_components)) {
        $subview = $uri_components;
      } else {
        if(count($uri_components) == 0) {
          $subview = '';
        } else {
          $this->app->setUri($uri_components);
          $subview = $uri_components[0];
        }
      }
      $found = false;
      $load_main_subview = $subview == '' ? true : false;
      foreach($vm->views as $view) {
        $rc = new ReflectionClass($view);

        if($rc->isAbstract())
          continue;
        $view = $view::instance();
        if($load_main_subview && $view->default
          || $subview == $view->name()
          && $view->parent == $parent->name) {
          $found = true;
          break;
        }
      }
      if(!$found) {
        Logger::error("View '".Utils::getUrl($uri_components)."' not found.");
        App::instance()->redirect('404');
      } else {
        $parent->activeSubview = $view->name;
        return $this->app->content($view);
      }
    }


    public function name() {
        return $this->name;
    }
    public function title() {
      return ucfirst($this->name);
    }
    public function content($uri=array()) {
        return 'content output, default for module.';
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
