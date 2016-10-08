<?php 

class Logout extends AbstractView {
    public $name = 'logout';
    public $allowNavigation = true;

    public function content() {
        Auth::destroy();
        App::instance()->redirect(Utils::getHomeUrl());
    }
}

?>