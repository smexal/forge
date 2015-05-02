<?php 

class SettingsManagement extends AbstractView {
    public $parent = 'manage';
    public $name = 'settings';
    public $permission = 'manage.settings';

    public function content() {
        return '<h1>settings</h1>';
    }
}

?>