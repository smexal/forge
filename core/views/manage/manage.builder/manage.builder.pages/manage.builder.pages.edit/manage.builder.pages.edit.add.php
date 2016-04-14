<?php

class ManagePagesEditAdd extends AbstractView {
    public $parent = 'edit';
    public $permission = 'manage.builder.pages.edit';
    public $name = 'add-element';
    private $user = null;
    private $page = false;

    public function content($parts = array()) {
        $page = new Page($parts[0]);
        return App::instance()->render(CORE_TEMPLATE_DIR."views/parts/", "builder.addelement", array(
            "title" => i("Add Element", "core")
        ));
    }
}

?>
