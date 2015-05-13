<?php

class ApiView extends AbstractView {
    public $name = 'api';
    public $permission = 'api';

    public function content($components=array()) {
      return "hallo";
    }

}


?>
