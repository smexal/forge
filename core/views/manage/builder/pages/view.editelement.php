<?php

namespace Forge\Core\Views\Manage\Builder\Pages;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\App;
use \Forge\Core\Classes\Fields;
use \Forge\Core\Classes\Utils;

class EditelementView extends View {
    public $parent = 'pages';
    public $permission = 'manage.builder.pages.edit';
    public $name = 'edit-element';
    private $message = false;
    public $events = array(
        'onUpdateContentElement'
    );

    public function onUpdateContentElement($data) {
        $element = App::instance()->com->instance($data['id']);
        $ignored_fields = array("event", "id");
        foreach($element->settings() as $settings) {
            if(array_key_exists($settings['key'], $data)) {
                $element->savePref($settings['key'], $data[$settings['key']]);
            } else {
                $element->savePref($settings['key'], '');
            }
        }
        $this->message = i('Changes saved');
    }

    public function content($parts = array()) {
        if(!is_numeric($parts[0])) {
            return sprintf(i('Unknown object with id `%1$s`.', 'core'), $parts[0]);
        }
        $element = $this->app->com->instance($parts[0]);
        $fields = array();
        foreach($element->settings() as $setting) {
            $call = $setting['type'];
            array_push($fields, Fields::$call($setting));
        }
        // submit button
        array_push($fields, Fields::button(i('Save')));
        // hidden event
        array_push($fields, Fields::hidden(array(
            'name' => 'event',
            'value' => 'onUpdateContentElement'
        )));
        // hidden event
        array_push($fields, Fields::hidden(array(
            'name' => 'id',
            'value' => $element->getId()
        )));

        return $this->app->render(CORE_TEMPLATE_DIR."views/parts/", "crud.modify", array(
            'message' => $this->message,
            'title' => sprintf(i('Modify %s'), $element->getPref('name')),
            'form' => $this->app->render(CORE_TEMPLATE_DIR."assets/", "form", array(
                'method' => 'POST',
                'action' => Utils::getUrl(array('manage', 'pages', 'edit-element', $element->getId())),
                'ajax' => true,
                'ajax_target' => '.content',
                'content' => $fields,
                'horizontal' => false
            ))
        ));
    }
}

