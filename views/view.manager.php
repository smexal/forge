<?php

class Manager extends AbstractView {
    private $navigation = false;
    public $name = 'manage';

    public function content($uri=array()) {
        if(Auth::any()) {
            $content = $this->getSubview($uri, $this);
            $content = $this->navigation() . $content;
            return $content;
        } else {
            $this->app->redirect("login", "manage");
        }
    }

    private function navigation() {
        $this->navigation = new Navigation($this->activeSubview);
        $panelLeft = $this->navigation->addPanel();
        $this->navigation->add('dashboard', i('Dashboard'), Utils::getUrl(array('manage', 'dashboard')), $panelLeft, 'home');
        $this->navigation->add('users', i('Users'), Utils::getUrl(array('manage', 'users')), $panelLeft, 'user');

        $panelRight = $this->navigation->addPanel('right');
        $this->navigation->add('settings', i('Settings'), Utils::getUrl(array('manage', 'settings')), $panelRight, 'wrench');
        $this->navigation->add('logout', i('Logout'), Utils::getUrl(array('logout')), $panelRight, 'remove');

        
        $this->navigation->setSticky();
        return $this->navigation->render();
    }

}


?>