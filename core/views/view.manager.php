<?php

class Manager extends AbstractView {
    private $navigation = false;
    public $name = 'manage';
    public $permission = 'manage';
    public $permissions = array(
        0 => 'manage',
        1 => 'manage.users',
        2 => 'manage.collections',
        3 => 'manage.builder',
        4 => 'manage.modules',
        5 => 'manage.locales',
        6 => 'manage.configuration'
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
        $this->navigation->add('dashboard', i('Dashboard'), Utils::getUrl(array('manage', 'dashboard')), $panelLeft, false, false, Utils::getUrl(array("images", "forge.svg")), array("logo"));
        if(Auth::allowed($this->permissions[2])) {

          $this->navigation->add('collections', i('Collections'), Utils::getUrl(array('manage', 'collections')), $panelLeft);
          $this->collectionSubmenu($panelLeft);
        }

        if(Auth::allowed($this->permissions[3])) {
          $this->navigation->add('builder', i('Builder'), Utils::getUrl(array('manage', 'builder')), $panelLeft);
        }

        if(Auth::allowed($this->permissions[4])) {
          $this->navigation->add('modules', i('Modules'), Utils::getUrl(array('manage', 'modules')), $panelLeft);
        }

        $panelRight = $this->navigation->addPanel('right');

        if(Auth::allowed($this->permissions[5])) {
          $this->navigation->add('locales_container', i('Localization'), false, $panelRight, 'globe');
          $this->navigation->add('locales', i('Language Configuration'), Utils::getUrl(array('manage', 'locales')), $panelRight, false, 'locales_container');
          $this->navigation->add('string-translation', i('String Translations'), Utils::getUrl(array('manage', 'string-translation')), $panelRight, false, 'locales_container');
        }

        if(Auth::allowed($this->permissions[1])) {
            $this->navigation->add('users_container', i('Users'), false, $panelRight, 'user');
            $this->navigation->add('users', i('Users'), Utils::getUrl(array('manage', 'users')), $panelRight, false, 'users_container');
            $this->navigation->add('groups', i('Groups'), Utils::getUrl(array('manage', 'groups')), $panelRight, false, 'users_container');
            $this->navigation->add('permissions', i('Permissions'), Utils::getUrl(array('manage', 'permissions')), $panelRight, false, 'users_container');
        }
        $this->navigation->add('usermenu', $this->app->user->get('username'), Utils::getUrl(array('manage', 'sites')), $panelRight);
        $this->navigation->add('profile', i('Profile Settings'), Utils::getUrl(array('manage', 'profile')), $panelRight, false, 'usermenu');
        if(Auth::allowed($this->permissions[1])) {
          $this->navigation->add('settings', i('Settings'), Utils::getUrl(array('manage', 'settings')), $panelRight, false, 'usermenu');
          $this->navigation->add('logout', i('Logout'), Utils::getUrl(array('logout')), $panelRight, false, 'usermenu');
        }



        $this->navigation->setSticky();
        return $this->navigation->render();
    }

    private function collectionSubmenu($panelLeft) {
      foreach($this->app->cm->collections as $collection) {
        $this->navigation->add(
          $collection->getPref('name'),
          $collection->getPref('title'),
          Utils::getUrl(array('manage', 'collections', $collection->getPref('name'))),
          $panelLeft,
          false,
          'collections'
        );
      }
    }

}


?>
