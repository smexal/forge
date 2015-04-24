<?php 

class UserManagement extends AbstractView {
    public $parent = 'manage';
    public $default = true;
    public $name = 'users';

    public function content() {
        return '<h1>users</h1>';
    }
}

?>