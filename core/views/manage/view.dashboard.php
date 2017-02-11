<?php

namespace Forge\Core\Views\Manage;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\App;

class DashboardView extends View {
    public $parent = 'manage';
    public $default = true;
    public $name = 'dashboard';
    public $permission = 'manage';

    public function content() {
        return App::instance()->render(CORE_ROOT."ressources/templates/views/sites/", "generic", array(
            'title' => i('Dashboard'),
            'global_actions' => '',
            'content' => $this->getDashboard()
        ));
    }

    public function getDashboard() {
        return 'dash';
    }
}

