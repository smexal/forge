<?php

class SettingsManagement extends AbstractView {
    public $parent = 'manage';
    public $name = 'settings';
    public $permission = 'manage.settings';
    public $events = array(
        'onUpdateSettings'
    );
    private $keys = null;

    private function keys() {
        $this->keys = array(
            'HOME_PAGE' => 'home_page',
            'THEME' => 'active_theme',
            'TITLE' => 'title_'.Localization::getCurrentLanguage()
        );
    }

    public function onUpdateSettings() {
        $this->keys();
        Settings::set($this->keys['HOME_PAGE'], $_POST[$this->keys['HOME_PAGE']]);
        Settings::set($this->keys['THEME'], $_POST[$this->keys['THEME']]);
        Settings::set($this->keys['TITLE'], $_POST[$this->keys['TITLE']]);

        App::instance()->addMessage(sprintf(i('Changes saved')), "success");
        App::instance()->redirect(Utils::getUrl(array('manage', 'settings')));
    }

    public function content() {
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
        return $return;
    }

    public function rightFields() {
        $return = '';
        $return .= $this->getTitleInput();
        return $return;
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
            'label' => 'Title your website ['.Localization::getCurrentLanguage().']',
            'hint' => ''
        ), Settings::get($this->keys['TITLE']));
    }
}

?>
