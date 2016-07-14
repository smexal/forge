<?php

class ViewManager {
    public $views = null;

    public function __construct() {
        $this->views = $this->getViews();
    }

    public function getViews() {
        $classes = get_declared_classes();
        $implementsIModule = array();
        foreach($classes as $klass) {
            $reflect = new ReflectionClass($klass);
            if($reflect->implementsInterface('IView')) {
                if(! $reflect->isAbstract()) {
                    $implementsIModule[] = $klass;
                }
            }
        }
        return $implementsIModule;
    }

    public function getNavigationViews() {
        $navViews = array();
        foreach($this->views as $view) {
            $v = $view::instance();
            if($v->allowNavigation) {
                array_push($navViews, $v);
            }
        }
        return $navViews;
    }
}


?>