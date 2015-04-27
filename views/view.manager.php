<?php

class Manager extends AbstractView {
    private $navigation = false;
    public $name = 'manage';
    public $permission = 'manage';
    public $permissions = array(
        0 => 'manage',
        1 => 'manage.users.display',
    );

    public function content($uri=array()) {
        if(Auth::any()) {
            if(Auth::allowed($this->permissions[0])) {
                $content = $this->getSubview($uri, $this);
                if(! Utils::isAjax()) {
                    $content = $this->navigation() . $content;
                }
            } else {
                $this->app->redirect("denied");
            }
            return $content;
        } else {
            $this->app->redirect("login", "manage");
        }
    }

    private function navigation() {
        $this->navigation = new Navigation($this->activeSubview);
        $panelLeft = $this->navigation->addPanel();
        $this->navigation->add('dashboard', i('Dashboard'), Utils::getUrl(array('manage', 'dashboard')), $panelLeft, 'home');
        if(Auth::allowed($this->permissions[1])) {
            $this->navigation->add('users_container', i('Users'), false, $panelLeft, 'user');
            $this->navigation->add('users', i('Users'), Utils::getUrl(array('manage', 'users')), $panelLeft, false, 'users_container');
            $this->navigation->add('groups', i('Groups'), Utils::getUrl(array('manage', 'groups')), $panelLeft, false, 'users_container');
            $this->navigation->add('permissions', i('Permissions'), Utils::getUrl(array('manage', 'permissions')), $panelLeft, false, 'users_container');
        }

        $panelRight = $this->navigation->addPanel('right');
        $this->navigation->add('settings', i('Settings'), Utils::getUrl(array('manage', 'settings')), $panelRight, 'wrench');
        $this->navigation->add('logout', i('Logout'), Utils::getUrl(array('logout')), $panelRight, 'remove');

        
        $this->navigation->setSticky();
        return $this->navigation->render();
    }

}


?>