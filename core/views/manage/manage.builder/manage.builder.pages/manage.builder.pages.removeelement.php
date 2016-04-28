<?php

class ManagePagesRemoveElement extends AbstractView {
    public $parent = 'pages';
    public $permission = 'manage.builder.pages.edit';
    public $name = 'remove-element';

    public function content($parts = array()) {
        return 'yes';
    }
}

?>
