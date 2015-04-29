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
        if(Auth::allowed($this->permissions[0])) {
            $content = $this->getSubview($uri, $this);
            if(! Utils::isAjax()) {
                $content = $this->navigation() . $content;
            }
        } else {
            $this->app->redirect("denied");
        }
        return $content;
    }

    private function navigation() {
        $this->navigation = new Navigation($this->activeSubview);
        $this->navigation->setMaxWidth();
        $panelLeft = $this->navigation->addPanel();
        $this->navigation->add('dashboard', i('Dashboard'), Utils::getUrl(array('manage', 'dashboard')), $panelLeft, false, false, '../images/backend-logo.png');

        $panelRight = $this->navigation->addPanel('right');
        if(Auth::allowed($this->permissions[1])) {
            $this->navigation->add('users_container', i('Users'), false, $panelRight, 'user');
            $this->navigation->add('users', i('Users'), Utils::getUrl(array('manage', 'users')), $panelRight, false, 'users_container');
            $this->navigation->add('groups', i('Groups'), Utils::getUrl(array('manage', 'groups')), $panelRight, false, 'users_container');
            $this->navigation->add('permissions', i('Permissions'), Utils::getUrl(array('manage', 'permissions')), $panelRight, false, 'users_container');
        }        
        $this->navigation->add('settings', i('Settings'), Utils::getUrl(array('manage', 'settings')), $panelRight, 'wrench');
        $this->navigation->add('logout', i('Logout'), Utils::getUrl(array('logout')), $panelRight, 'remove');


        
        $this->navigation->setSticky();
        return $this->navigation->render();
    }

}


?>