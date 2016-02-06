<?php

class ManageSites extends AbstractView {
    public $parent = 'manage';
    public $name = 'sites';
    public $permission = 'manage.sites';
    public $permissions = array(
            0 => 'manage.sites.add'
    );

    public function content($uri=array()) {
        if(count($uri) == 0) {
          return 'asdf';
        }
    }
}

?>
