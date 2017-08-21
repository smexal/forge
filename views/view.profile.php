<?php

namespace Forge\Views;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\App;



class ProfileView extends View {
    public $name = 'profile';
    public $allowNavigation = true;

    public function content() {
        $v = App::instance()->vm->getViewByName('__profile');
        $v->showSubviews = false;
        return App::instance()->content($v);
    }

}

?>