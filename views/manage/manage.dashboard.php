<?php 

class ManagementDashboard extends AbstractView {
    public $parent = 'manage';
    public $default = true;
    public $name = 'dashboard';
    public $permission = 'manage';

    public function content() {
        return '<div class="padded maxed page-header"><h1>Dashboard</h1></div>';
    }
}

?>