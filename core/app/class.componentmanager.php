<?php

class ComponentManager {
    private $components = array();
    private $app = null;

    public function __construct() {
        $this->app = App::instance();
        $this->loadThemeComponents();
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

    private function loadThemeComponents() {
        $tm = App::instance()->tm;
        if($tm->theme) {
            Loader::instance()->loadDirectory($tm->theme->directory()."components/");
        }
    }

    public function getChildrenOf($id, $position_x = 0) {
        $children = array();
        $db = App::instance()->db;
        $db->where('parent', $id);
        $db->where('position_x', $position_x);
        $components = $db->get('page_elements');
        foreach($components as $comp) {
            if($comp['parent'] == $id) {
                array_push($children, $this->instance($comp['id']));
            }
        }
        return $children;
    }

    public function instance($id, $type=null) {
        if(is_null($type)) {
            $this->app->db->where('id', $id);
            $elm = $this->app->db->getOne('page_elements');
            $type = $elm['elementid'];
        }
        foreach($this->components as $component) {
            if($component->getPref('id') == $type) {
                $instance_obj = get_class($component);
                $instance = new $instance_obj();
                $instance->id = $id;
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

    public function deleteComponent($id) {
        $db = App::instance()->db;
        $db->where('id', $id);
        $db->delete('page_elements');

        $db->where('parent', $id);
        $children = $db->get('page_elements');
        foreach($children as $child) {
            $this->deleteComponent($child['id']);
        }
        return true;
    }

}

?>
