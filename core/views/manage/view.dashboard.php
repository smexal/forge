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

    public function content($uri=[]) {
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
            'title' => i('Welcome', 'core'),
            'callable' => [$this, 'welcomeWidget']
        ]);

        $dash->registerWidget('forge-widget__stats', [
            'title' => i('Numbers', 'core'),
            'callable' => [$this, 'statsWidget']
        ]);

        $dash->registerWidget('forge-widget__quicky', [
            'title' => i('Quick Access', 'core'),
            'callable' => [$this, 'quickyWidget']
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

    public function statsWidget() {
        $return = '<ul class="numbers content-listing">';
        // amount of pages
        $pages = count(\Forge\Core\Classes\Pages::getAll());
        $return.= '<li>'.sprintf(i('%1$s Pages', 'core'), $pages).'</li>';

        // collections
        $cm = App::instance()->cm;
        $count = 1;
        $max = 4;
        foreach($cm->collections as $collection) {
            $items = count($collection->items());
            $return.= '<li>'.sprintf(i('%1$s %2$s', 'core'), $items, $collection->getPref('title')).'</li>';

            $count++;
            if($count == $max) {
                break;
            }
        }

        // users
        $users = count(\Forge\Core\Classes\User::getAll());
        $return.= '<li>'.sprintf(i('%1$s Users', 'core'), $users).'</li>';


        $return.= '</ul>';
        return $return;
    }

    public function quickyWidget() {
        $return = '<ul class="content-listing">';
        $return.= '<li><a href="'.Utils::getUrl(['manage', 'pages']).'">'.i('show and configure pages', 'core').'</a></li>';
        $return.= '<li><a href="'.Utils::getUrl(['manage', 'users']).'">'.i('add or manage users', 'core').'</a></li>';
        $return.= '<li><a href="'.Utils::getUrl(['manage', 'string-translation']).'">'.i('manage translations', 'core').'</a></li>';
        $return.= '<li><a href="'.Utils::getUrl(['manage', 'media']).'">'.i('upload files and media', 'core').'</a></li>';
        $return.= '</ul>';
        return $return;
    }
}
