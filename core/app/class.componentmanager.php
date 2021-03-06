<?php

namespace Forge\Core\App;

use \Forge\Loader;
use \Forge\Core\Abstracts\Manager;

class ComponentManager extends Manager {
    private $components = array();
    private $app = null;

    protected static $file_pattern = '/(.*)component\.([a-zA-Z][a-zA-Z0-9]*)\.php$/';
    protected static $class_suffix = 'Component';

    public function __construct() {
        parent::__construct();
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

    public function getComponentById($id) {
        return $this->instance($id);
    }

    private function loadThemeComponents() {
        if($this->app->tm->theme) {
            Loader::instance()->loadDirectory($this->app->tm->theme->directory()."components/");
        }
    }

    public function getChildrenOf($id, $position_x = 0) {
        $children = array();
        $db = $this->app->db;
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
        App::instance()->eh->fire("onGetComponents");
        $flush_cache = \triggerModifier('Forge/ComponentManager/FlushCache', MANAGER_CACHE_FLUSH === true);
        $cls_components = static::loadClasses($flush_cache);
        $components = [];
        foreach($cls_components as $cls) {
            if(!(new \ReflectionClass($cls))->isAbstract())
                $components[] = new $cls;
        }
        return $components;
    }

    public function deleteComponent($id) {
        $db = $this->app->db;
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

