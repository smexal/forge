<?php 

class ManagementDashboard extends AbstractView {
    public $parent = 'manage';
    public $default = true;
    public $name = 'dashboard';
    public $permission = 'manage';

    public function content() {
        return '<h1>dashboard</h1>';
    }
}

?>