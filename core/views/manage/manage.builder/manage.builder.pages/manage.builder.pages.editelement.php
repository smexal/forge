<?php

class ManagePagesEditElement extends AbstractView {
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
        foreach($data as $key => $value) {
            if(!in_array($key, $ignored_fields)) {
                $element->savePref($key, $value);
            }
        }
        $this->message = i('Changes saved');
    }

    public function content($parts = array()) {
        if(!is_numeric($parts[0])) {
            return i('Unknown object with id `'.$parts[0].'`.');
        }
        $element = $this->app->com->instance($parts[0]);
        $fields = array();
        foreach($element->settings() as $setting) {
            array_push($fields, Fields::$setting['type']($setting));
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

?>
