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
                $rc = new ReflectionClass($klass);
                if(! $rc->isAbstract())
                    $implementsIModule[] = $klass;
            }
        }
        return $implementsIModule;
    }
}


?>