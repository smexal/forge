<?php

class ManageSites extends AbstractView {
    public $parent = 'manage';
    public $name = 'sites';
    public $permission = 'manage.sites';

    public function content() {
        return $this->siteList();
    }

    public function siteList() {
      return 'display all sites';
    }
}

?>
