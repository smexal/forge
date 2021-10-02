<?php

namespace Forge\Core\Views;

use Forge\Core\Abstracts\View;
use Forge\Core\App\App;
use Forge\Core\App\Auth;
use Forge\Core\App\ModifyHandler;
use Forge\Core\Classes\Localization;
use Forge\Core\Classes\Navigation;
use Forge\Core\Classes\Utils;


class ManageView extends View {
    public $allowNavigation = true;
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
                $content = $this->navigation() . $content . $this->getLanguageSelection();
            }
        } else {
            $this->app->redirect("denied");
        }
        return $content;
    }

    private function navigation() {
        $this->navigation = new Navigation($this->activeSubview);
        $this->navigation->setMaxWidth();
        $panelLeft = $this->navigation->addPanel('left', 'leftPanel');
        $this->navigation->add('dashboard', i('Dashboard'), Utils::getUrl(array('manage', 'dashboard')), $panelLeft, 'home');
        if(Auth::allowed($this->permissions[2]) && is_array($this->app->cm->collections) > 0) {
            $this->navigation->add('collections', i('Collections'), Utils::getUrl(array('manage', 'collections')), $panelLeft, 'dns');
            $this->collectionSubmenu($panelLeft);
        }

        if(Auth::allowed($this->permissions[3])) {
            $this->navigation->add('builder', i('Builder'), Utils::getUrl(array('manage', 'builder')), $panelLeft, 'create');
            if(Auth::allowed($this->permissions[7])) {
                $this->navigation->add('Pages', i('Pages'), Utils::getUrl(array('manage', 'pages')), $panelLeft, false, 'builder');
            }
            if(Auth::allowed($this->permissions[8])) {
                $this->navigation->add('Navigations', i('Navigations'), Utils::getUrl(array('manage', 'navigation')), $panelLeft, false, 'builder');
            }
        }

        if(Auth::allowed($this->permissions[4])) {
            $this->navigation->add('modules', i('Modules'), Utils::getUrl(array('manage', 'modules')), $panelLeft, 'view_module');
        }

        if(Auth::allowed($this->permissions[9])) {
            $this->navigation->add('media', i('Media'), Utils::getUrl(array('manage', 'media')), $panelLeft, 'perm_media');
        }

        if(Auth::allowed($this->permissions[4])) {
            // display menu points for active modules
            $mm = App::instance()->mm;
            foreach($mm->getActiveModules() as $mod) {
                $modObject = $mm->getModuleObject($mod);

                if (! is_object($modObject) || ! $modObject->hasSettings())
                    continue;

                if($modObject->defaultSettingsView) {
                    $url = Utils::getUrl(['manage', 'module-settings', $mod, $modObject->defaultSettingsView]);
                } else {
                    $url = Utils::getUrl(['manage', 'module-settings', $mod]);
                }

                $this->navigation->add(
                    "pref_".$mod,
                    $modObject->name,
                    $url,
                    $panelLeft,
                    false,
                    'modules'
              );
            }
        }

        $this->navigation->add('usermenu', $this->app->user->get('username'), Utils::getUrl(array('manage', '__profile')), $panelLeft, 'settings');
        if(Auth::allowed($this->permissions[1])) {
            $this->navigation->add('settings', i('Basic Settings'), Utils::getUrl(array('manage', 'settings')), $panelLeft, false, 'usermenu');
        }

        if(Auth::allowed($this->permissions[1])) {
            $this->navigation->add('users', i('Users'), Utils::getUrl(array('manage', 'users')), $panelLeft, false, 'usermenu');
            $this->navigation->add('groups', i('Groups'), Utils::getUrl(array('manage', 'groups')), $panelLeft, false, 'usermenu');
            $this->navigation->add('permissions', i('Permissions'), Utils::getUrl(array('manage', 'permissions')), $panelLeft, false, 'usermenu');
        }

        if(Auth::allowed($this->permissions[5])) {
            $this->navigation->add('language', i('Language & Translations', 'core'), Utils::getUrl(['manage', 'locales']), $panelLeft, false, 'usermenu');
            $this->navigation->add('string-translation', i('System Translation'), Utils::getUrl(array('manage', 'string-translation')), $panelLeft, false, 'usermenu');
        }

        $panelBottom = $this->navigation->addPanel('right');
        $this->navigation->add('logout', i('Logout'), Utils::getUrl(array('logout')), $panelBottom, 'power_settings_new');


        $this->navigation->setSticky();

        $this->navigation = ModifyHandler::instance()->trigger(
            'modify_manage_navigation',
            $this->navigation
        );

        return $this->navigation->render();
    }

    private function getLanguageSelection() {
        // add other languages as submenu
        $languages = Localization::getLanguages();
        if(count($languages) == 1) {
            return '';
        }

        $language = Localization::getLanguageInformation(Localization::getCurrentLanguage());
        $available = [];
        foreach($languages as $lang) {
            $available[] = [
                'name' => $lang['name'],
                'link' => Utils::getCurrentUrl().'?lang='.$lang['code']
            ];
        }

        return App::instance()->render(CORE_TEMPLATE_DIR.'assets/', "languageselection", [
            'currentName' => $language['name'],
            'available' => $available
        ]);
    }

    private function collectionSubmenu($panelLeft) {
      foreach($this->app->cm->collections as $collection) {
        if(!Auth::allowed($collection->permission)) {
            continue;
        }
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
