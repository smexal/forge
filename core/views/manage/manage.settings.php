<?php

class SettingsManagement extends AbstractView {
    public $parent = 'manage';
    public $name = 'settings';
    public $permission = 'manage.settings';
    public $events = array(
        'onUpdateSettings'
    );
    private $keys = array(
        'HOME_PAGE' => 'home_page'
    );

    public function onUpdateSettings() {
        Settings::set($this->keys['HOME_PAGE'], $_POST[$this->keys['HOME_PAGE']]);
        
        App::instance()->addMessage(sprintf(i('Changes saved')), "success");
        App::instance()->redirect(Utils::getUrl(array('manage', 'settings')));
    }

    public function content() {

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
        return $return;
    }

    public function rightFields() {
        return '';
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
}

?>
