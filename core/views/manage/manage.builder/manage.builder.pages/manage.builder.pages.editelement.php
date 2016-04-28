<?php

class ManagePagesEditElement extends AbstractView {
    public $parent = 'pages';
    public $permission = 'manage.builder.pages.edit';
    public $name = 'edit-element';

    public function content($parts = array()) {
        return 'yes';
    }
}

?>
