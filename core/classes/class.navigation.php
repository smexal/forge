<?php

namespace Forge\Core\Classes;

use \Forge\Core\App\App;

class Navigation {
    private $sticky = false;
    private $panels = array();
    private $active = false;
    private $items = array();
    private $maxed = false;

    public function __construct($active) {
        $this->active = $active;
    }

    public function setSticky() {
        App::instance()->sticky = true;
        $this->sticky = true;
    }

    public function addPanel($position='left') {
        $name = uniqid();
        $this->panels[$name] = array('position' => $position, 'items' => array());
        return $name;
    }

    public function add($id, $name, $url, $panel, $icon=false, $parent=false, $img=false, $classes = array()) {
        $this->items[$id]['name']       = $name;
        $this->items[$id]['url']        = $url;
        $this->items[$id]['icon']       = $icon;
        $this->items[$id]['img']        = $img;
        $this->items[$id]['classes']    = $classes;
        $this->items[$id]['children']   = array();
        if(!$parent) {
            $this->panels[$panel]['items'][] = $id;
        } else {
            $this->items[$parent]['children'][] = $id;
        }
    }

    public function setMaxWidth() {
        $this->maxed = true;
    }

    public function menuItem($id) {
        return App::instance()->render(CORE_TEMPLATE_DIR."assets/", "menuitem", array(
            'name' => $this->items[$id]['name'],
            'url' => $this->items[$id]['url'],
            'icon' => $this->items[$id]['icon'],
            'image' => $this->items[$id]['img'],
            'active' => $this->active == $id ? true : false,
            'classes' => implode(" ", $this->items[$id]['classes']),
            'children' => count($this->items[$id]['children']) > 0 ? $this->renderChildren($id) : false
        ));
    }
    public function renderChildren($id) {
        $content = '';
        foreach($this->items[$id]['children'] as $item) {
            $content.=$this->menuItem($item);
        }
        return $content;
    }

    public function render() {
        foreach($this->panels as $panel_key => $panel) {
            $this->panels[$panel_key]['content'] = '';
            foreach($panel['items'] as $item) {
                $this->panels[$panel_key]['content'].= $this->menuItem($item);
            }
        }
        return App::instance()->render(CORE_TEMPLATE_DIR, "navigation", array(
            'sticky' => $this->sticky,
            'panels' => $this->panels,
            'maxed' => $this->maxed
        ));
    }

}

?>
