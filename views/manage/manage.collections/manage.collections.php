<?php

class CollectionManagement extends AbstractView {
    public $parent = 'manage';
    public $name = 'collections';
    public $permission = 'manage.collections';

    public function content($uri=array()) {
      Logger::debug($uri);
    }
}

?>
