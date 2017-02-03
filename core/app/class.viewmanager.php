<?php

namespace Forge\Core\App;

class ViewManager {
    public $views = null;

    public function __construct() {
        $this->getViews();
    }

    public function getViewByName($name) {
        foreach ($this->views as $view) {
            $v = $view::instance();
            if ($v->name == $name) {
                return $v;
            }
        }
        return;
    }

    public function getViews() {
        $classes = get_declared_classes();
        $implementsIModule = array();
        foreach ($classes as $klass) {
            $reflect = new \ReflectionClass($klass);
            if ($reflect->implementsInterface('\Forge\Core\Interfaces\IView')) {
                if (! $reflect->isAbstract()) {
                    $implementsIModule[] = $klass;
                }
            }
        }
        $this->views = $implementsIModule;
    }

    public function getNavigationViews() {
        $navViews = array();
        foreach ($this->views as $view) {
            $v = $view::instance();
            if ($v->allowNavigation) {
                array_push($navViews, $v);
            }
        }
        return $navViews;
    }
}


?>
