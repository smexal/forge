<?php

namespace Forge\Core\Views;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\App;
use \Forge\Core\App\Auth;
use \Forge\Core\Classes\Localization;
use \Forge\Core\Classes\Navigation;
use \Forge\Core\Classes\Utils;

use function \Forge\Core\Classes\i;

class Manager extends View {
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
        6 => 'manage.configuration',
        7 => 'manage.builder.pages',
        8 => 'manage.builder.navigation',
        9 => 'manage.media'
    );

    public function content($uri=array()) {
        if(Auth::allowed($this->permissions[0])) {
            if(count($uri) == 0) {
              array_push($uri, "dashboard");
            }
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
        if(Auth::allowed($this->permissions[2]) && count($this->app->cm->collections) > 0) {
          $this->navigation->add('collections', i('Collections'), Utils::getUrl(array('manage', 'collections')), $panelLeft);
          $this->collectionSubmenu($panelLeft);
        }

        if(Auth::allowed($this->permissions[3])) {
          $this->navigation->add('builder', i('Builder'), Utils::getUrl(array('manage', 'builder')), $panelLeft);
          if(Auth::allowed($this->permissions[7])) {
            $this->navigation->add('Pages', i('Pages'), Utils::getUrl(array('manage', 'pages')), $panelLeft, false, 'builder');
          }
          if(Auth::allowed($this->permissions[8])) {
            $this->navigation->add('Navigations', i('Navigations'), Utils::getUrl(array('manage', 'navigation')), $panelLeft, false, 'builder');
          }
        }

        if(Auth::allowed($this->permissions[4])) {
          $this->navigation->add('modules', i('Modules'), Utils::getUrl(array('manage', 'modules')), $panelLeft);
        }

        if(Auth::allowed($this->permissions[9])) {
          $this->navigation->add('media', i('Media'), Utils::getUrl(array('manage', 'media')), $panelLeft);
        }

        if(Auth::allowed($this->permissions[4])) {
          $this->navigation->add('module_prefs_container', i('Module Preferences'), false, $panelLeft, 'cog');

          // display menu points for active modules
          $mm = App::instance()->mm;
          foreach($mm->getActiveModules() as $mod) {
            $modObject = $mm->getModuleObject($mod);
            $this->navigation->add(
              "pref_".$mod,
              $modObject->name,
              Utils::getUrl(array('manage', 'module-settings', $mod)),
              $panelLeft,
              false,
              'module_prefs_container'
            );
          }
        }

        $panelRight = $this->navigation->addPanel('right');

        $this->navigation->add('language', strtoupper(Localization::getCurrentLanguage()), '', $panelRight);
        // add other languages as submenu
        $languages = Localization::getLanguages();
        foreach($languages as $lang) {
          if($lang['code'] != Localization::getCurrentLanguage()) {
            $this->navigation->add(
              'lang-'.$lang['code'],
              $lang['name'],
              Utils::getCurrentUrl().'?lang='.$lang['code'],
              $panelRight,
              false,
              'language');
          }
        }



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
          $this->navigation->add('settings', i('Global Settings'), Utils::getUrl(array('manage', 'settings')), $panelRight, false, 'usermenu');
          $this->navigation->add('logout', i('Logout'), Utils::getUrl(array('logout')), $panelRight, false, 'usermenu');
        }



        $this->navigation->setSticky();
        return $this->navigation->render();
    }

    private function collectionSubmenu($panelLeft) {
      foreach($this->app->cm->collections as $collection) {
        if(!Auth::allowed($collection::$permission)) {
            continue;
        }
        $collection = $collection::instance();

        $this->navigation->add(
          $collection::$name,
          $collection->getPref('title'),
          Utils::getUrl(array('manage', 'collections', $collection::$name)),
          $panelLeft,
          false,
          'collections'
        );
      }
    }

}


?>
