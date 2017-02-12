<?php

namespace Forge\Views;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\App;
use \Forge\Core\App\Auth;
use \Forge\Core\Classes\Utils;

class LogoutView extends View {
    public $name = 'logout';
    public $allowNavigation = true;

    public function content() {
        Auth::destroy();
        App::instance()->redirect(Utils::getHomeUrl());
    }
}

?>
