<?php

namespace Forge\Core\Views\Manage;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\App;
use \Forge\Core\Classes\Fields;
use \Forge\Core\Classes\Localization;
use \Forge\Core\Classes\Group;
use \Forge\Core\Classes\Settings;
use \Forge\Core\Classes\Pages;
use \Forge\Core\Classes\Utils;

use function \Forge\Core\Classes\i;

class SettingsView extends View {
    public $parent = 'manage';
    public $name = 'settings';
    public $permission = 'manage.settings';
    public $events = array(
        'onUpdateSettings'
    );
    private $keys = null;
    private $settings = false;
    private $tabs = array();

    public function init() {
        $this->tabs = array(
            array(
                'title' => i('General'),
                'id' => 'general',
                'active' => true
            )
        );
    }

    private function keys() {
        $this->keys = array(
            'HOME_PAGE' => 'home_page',
            'PRIMARY_COLOR' => 'primary_color',
            'THEME' => 'active_theme',
            'TITLE' => 'title_'.Localization::getCurrentLanguage(),
            'ALLOW_REGISTRATION' => 'allow_registration',
            'DEFAULT_USER_GROUP' => 'default_usergroup'
        );
    }

    public function onUpdateSettings() {
        $this->settings = Settings::instance();
        $this->keys();
        Settings::set($this->keys['HOME_PAGE'], $_POST[$this->keys['HOME_PAGE']]);
        Settings::set($this->keys['THEME'], $_POST[$this->keys['THEME']]);
        Settings::set($this->keys['TITLE'], $_POST[$this->keys['TITLE']]);
        Settings::set($this->keys['PRIMARY_COLOR'], $_POST[$this->keys['PRIMARY_COLOR']]);
        Settings::set($this->keys['ALLOW_REGISTRATION'], $_POST[$this->keys['ALLOW_REGISTRATION']]);
        Settings::set($this->keys['DEFAULT_USER_GROUP'], $_POST[$this->keys['DEFAULT_USER_GROUP']]);

        foreach($this->settings->fields as $name => $tab) {
            // skip if it's a modules settings fields...
            if(in_array($name, App::instance()->mm->getActiveModules())) {
                continue;
            }

            if(array_key_exists("right", $tab)) {
                foreach($tab['right'] as $key => $ignored) {
                    if(array_key_exists($key, $_POST)) {
                        Settings::set($key, $_POST[$key]);
                    }
                }
            }
            if(array_key_exists("left", $tab)) {
                foreach($tab['left'] as $key => $ignored) {
                    if(array_key_exists($key, $_POST)) {
                        Settings::set($key, $_POST[$key]);
                    }
                }
            }
        }

        App::instance()->addMessage(sprintf(i('Changes saved')), "success");
        App::instance()->redirect(Utils::getUrl(array('manage', 'settings')));
    }

    public function updateTabs() {
        $this->tabs = array_merge($this->tabs, $this->settings->tabs());
    }

    public function content() {
        $this->settings = Settings::instance();
        $this->updateTabs();

        $this->keys();
        return $this->app->render(CORE_TEMPLATE_DIR."views/sites/", "oneform", array(
            'action' => Utils::getUrl(array('manage', 'settings')),
            'event' => $this->events[0],
            'title' => i('Global Settings'),
            'tabs' => $this->tabs,
            'tab_content' => $this->getTabContent(),
            'global_actions' => Fields::button(i('Save changes')),
            'subnavigation' => false,
            'subview' => false
        ));
    }

    public function getTabContent() {
        $tabs = array();
        foreach($this->tabs as $tab) {
            // skip module content
            $skip = false;
            foreach(App::instance()->mm->getActiveModules() as $mod) {
                if($tab['id'] == $mod) {
                    $skip = true;
                    break;
                }
            }
            if($skip) {
                continue;
            }

            array_push($tabs, array(
                'active' => $tab['id'] == 'general' ? true : false,
                'id' => $tab['id'],
                'left' => $this->leftFields($tab['id']),
                'right' => $this->rightFields($tab['id'])
            ));
        }
        return $tabs;
    }

    public function leftFields($tab_id) {
        $return = '';
        if($tab_id == 'general') {
            $return .= $this->getHomePageFields();
            $return .= $this->getThemeSelection();
            $return .= '<hr />';
            $return .= $this->getAllowRegistration();
            $return .= $this->getDefaultUserGroup();
            $return .= '<hr />';
        }

        if(array_key_exists($tab_id, $this->settings->fields)
            && array_key_exists('left', $this->settings->fields[$tab_id])) {
            foreach($this->settings->fields[$tab_id]['left'] as $customField) {
                $return.=$customField;
            }
        }
        return $return;
    }

    public function rightFields($tab_id) {
        $return = '';
        if($tab_id == 'general') {
            $return .= $this->getTitleInput();
            $return .= '<hr />';
            $return .= $this->getBackendThemeColor();
            $return .= '<hr />';
        }
        if(array_key_exists($tab_id, $this->settings->fields)
            && array_key_exists('right', $this->settings->fields[$tab_id])) {

            foreach($this->settings->fields[$tab_id]['right'] as $customField) {
                $return.=$customField;
            }
        }
        return $return;
    }

    private function getAllowRegistration() {
        return Fields::checkbox(array(
            'key' => $this->keys['ALLOW_REGISTRATION'],
            'label' => i('Allow Registration'),
            'hint' => i('If this setting is enabled, the registration view will get available.'),
        ), Settings::get($this->keys['ALLOW_REGISTRATION']));
    }

    private function getDefaultUserGroup() {
        $selection = array(0 => i('None'));

        $groups = Group::getAll();
        foreach($groups as $group) {
            $selection[$group['id']] = $group['name'];
        }
        return Fields::select(array(
            'key' => $this->keys['DEFAULT_USER_GROUP'],
            'label' => 'Select the default user group for new registrations.',
            'values' => $selection
        ), Settings::get($this->keys['DEFAULT_USER_GROUP']));
    }


    private function getThemeSelection() {
        $tm = App::instance()->tm;
        $selection = array();
        foreach($tm->getThemes() as $theme) {
            $selection[$theme] = $theme;
        }
        return Fields::select(array(
            'key' => $this->keys['THEME'],
            'label' => 'Select the theme',
            'values' => $selection
        ), Settings::get($this->keys['THEME']));
    }

    private function getHomePageFields() {
        $pages = Pages::getAll();
        $values = array();
        foreach($pages as $page) {
            $values[$page['id']] = $page['name'];
        }
        return Fields::select(array(
            'key' => $this->keys['HOME_PAGE'],
            'label' => 'Choose the home page',
            'values' => $values
        ), Settings::get($this->keys['HOME_PAGE']));
    }

    private function getTitleInput() {
        return Fields::text(array(
            'key' => $this->keys['TITLE'],
            'label' => i('Title your website ').' ['.Localization::getCurrentLanguage().']',
            'hint' => ''
        ), Settings::get($this->keys['TITLE']));
    }

    private function getBackendThemeColor() {
        return Fields::text(array(
            'key' => $this->keys['PRIMARY_COLOR'],
            'label' => i('Backend Color'),
            'hint' => ''
        ), Settings::get($this->keys['PRIMARY_COLOR']));
    }
}

