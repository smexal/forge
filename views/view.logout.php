<?php

namespace Forge\Views;

use \Forge\Core\Abstracts as Abstracts;

class Logout extends Abstracts\View {
    public $name = 'logout';
    public $allowNavigation = true;

    public function content() {
        Auth::destroy();
        App::instance()->redirect(Utils::getHomeUrl());
    }
}

?>