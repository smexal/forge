<?php 

class Navigation {
    private $sticky = false;
    private $panels = array();
    private $active = false;

    public function __construct($active) {
        $this->active = $active;
    }

    public function setSticky() {
        App::instance()->sticky = true;
        $this->sticky = true;
    }

    public function addPanel($position='left') {
        $name = md5(microtime());
        $this->panels[$name] = array('position' => $position, 'items' => array());
        return $name;
    }

    public function add($id, $name, $url, $panel, $icon=false) {
        array_push($this->panels[$panel]['items'], $this->menuItem($id, $name, $url, $icon));
    }

    public function menuItem($id, $name, $url, $icon) {
        return App::instance()->render(TEMPLATE_DIR."assets/", "menuitem", array(
            'name' => $name,
            'url' => $url,
            'icon' => $icon,
            'active' => $this->active == $id ? true : false
        ));
    }

    public function render() {
        return App::instance()->render(TEMPLATE_DIR, "navigation", array(
            'sticky' => $this->sticky,
            'panels' => $this->panels
        ));
    }

}

?>