<?php

class Main extends AbstractView {
    public $name = 'main';
    public $default = true;

    public function content($components=array()) {
        return '#saynomore';
    }

}


?>