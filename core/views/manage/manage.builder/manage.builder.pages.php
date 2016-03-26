<?php

class PageBuilderManagement extends AbstractView {
    public $parent = 'manage';
    public $name = 'pages';
    public $permission = 'manage.builder.pages';
    public $permissions = array(
    );

    public function content($uri=array()) {
      return 'asdf';
    }
}

?>
