<?php

namespace Forge\Core\Views\Manage;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\App;
use \Forge\Core\Classes\Utils;
use \Forge\Core\Classes\AdminDashboard;

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
        $dash = AdminDashboard::instance();

        /* welcome message */
        $dash->registerWidget('forge-widget__welcome', [
            'title' => false,
            'callable' => [$this, 'welcomeWidget']
        ]);

        return $dash->render();
    }

    public function welcomeWidget() {
        $return = '<span class="huge">';
        $return.= sprintf(i('Welcome back to Forge, %1$s', 'core'), '<strong>'.App::instance()->user->get('username').'</strong>');
        $return.= '</span>';
        $return.= '<a href="'.Utils::getUrl(["logout"]).'">'.i('Logout', 'core').'</a>';
        return $return;
    }
}
