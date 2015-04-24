<?php 

class Logout extends AbstractView {
    public $name = 'logout';

    public function content() {
        Auth::destroy();
        App::instance()->redirectBack();
    }
}

?>