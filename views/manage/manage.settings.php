<?php 

class SettingsManagement extends AbstractView {
    public $parent = 'manage';
    public $default = true;
    public $name = 'settings';

    public function content() {
        return '<h1>settings</h1>';
    }
}

?>