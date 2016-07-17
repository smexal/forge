<?php

class SettingsManagement extends AbstractView {
    public $parent = 'manage';
    public $name = 'settings';
    public $permission = 'manage.settings';
    public $events = array(
        'onUpdateSettings'
    );
    private $keys = null;
    private $settings = false;

    private function keys() {
        $this->keys = array(
            'HOME_PAGE' => 'home_page',
            'PRIMARY_COLOR' => 'primary_color',
            'THEME' => 'active_theme',
            'TITLE' => 'title_'.Localization::getCurrentLanguage(),
            "ALLOW_REGISTRATION" => 'allow_registration'
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

        foreach($this->settings->fields as $position) {
            foreach($position as $key => $ignored) {
                Settings::set($key, $_POST[$key]);
            }
        }

        App::instance()->addMessage(sprintf(i('Changes saved')), "success");
        App::instance()->redirect(Utils::getUrl(array('manage', 'settings')));
    }

    public function content() {
        $this->settings = Settings::instance();
        $this->keys();
        return $this->app->render(CORE_TEMPLATE_DIR."views/sites/", "oneform", array(
            'action' => Utils::getUrl(array('manage', 'settings')),
            'event' => $this->events[0],
            'title' => i('Global Settings'),
            'left' => $this->leftFields(),
            'right' => $this->rightFields(),
            'global_actions' => Fields::button(i('Save changes'))
        ));
    }

    public function leftFields() {
        $return = '';
        $return .= $this->getHomePageFields();
        $return .= $this->getThemeSelection();
        $return .= $this->getAllowRegistration();
        if(array_key_exists('left', $this->settings->fields)) {
            foreach($this->settings->fields['left'] as $customField) {
                $return.=$customField;
            }
        }
        return $return;
    }

    public function rightFields() {
        $return = '';
        $return .= $this->getTitleInput();
        $return .= $this->getBackendThemeColor();
        if(array_key_exists('right', $this->settings->fields)) {
            foreach($this->settings->fields['right'] as $customField) {
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

?>
