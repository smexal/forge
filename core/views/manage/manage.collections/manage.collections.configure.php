<?php

class CollectionManagementConfigure extends AbstractView {
    public $parent = 'collections';
    public $name = 'configure';
    public $permission = 'manage.collections.configure';
    public $events = array(
    );

    public function content($uri=array()) {
        return 'conf';
    }
}

?>
