<?php

class ComponentManager {
    private $components = array();

    public function __construct() {
        $this->components = $this->getComponents();
    }

    public function getComponentsForLevel($level = 'root') {
        $components = array();
        foreach($this->components as $comp) {
            if($comp->getPref('level') == $level) {
                array_push($components, $comp);
            }
        }
        return $components;
    }

    public function instance($type, $data=array()) {
        foreach($this->components as $component) {
            if($component->getPref('id') == $type) {
                $instance_obj = get_class($component);
                $instance = new $instance_obj();
                if(! array_key_exists('id', $data)) {
                    return;
                }
                $instance->id = $data['id'];
                return $instance;
            }
        }
    }

    private function getComponents() {
        $classes = get_declared_classes();
        $implementsIModule = array();
        foreach($classes as $klass) {
            $reflect = new ReflectionClass($klass);
            if($reflect->implementsInterface('IComponent')) {
                $rc = new ReflectionClass($klass);
                if(! $rc->isAbstract())
                    $implementsIModule[] = new $klass();
            }
        }
        return $implementsIModule;
    }

}

?>
