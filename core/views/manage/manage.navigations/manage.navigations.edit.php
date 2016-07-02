<?php

class ManageEditNavigation extends AbstractView {
    public $parent = 'navigation';
    public $permission = 'manage.navigations.add';
    public $name = 'edit';
    public $message = '';
    private $navigation = false;
    public $events = array(
        'onEditNavigation'
    );

    public function content($uri=array()) {
        $this->navigation = ContentNavigation::getById($uri[0]);
        return $this->app->render(CORE_TEMPLATE_DIR."views/parts/", "crud.modify", array(
            'title' => i('Edit navigation'),
            'message' => $this->message,
            'form' => $this->form()
        ));
    }

    public function onEditNavigation($data) {
        $position = '';
        if(array_key_exists('position', $data)) {
            $position = $data['position'];
        }
        $this->message = ContentNavigation::update($data['navigation'], $data['new_name'], $position);
        App::instance()->addMessage(sprintf(i('Navigation %1$s has been created.'), $data['new_name']), "success");
        App::instance()->redirect(Utils::getUrl(array('manage', 'navigation')));
    }

    public function form() {
        $form = new Form(Utils::getUrl(array('manage', 'users', 'add')));
        $form->ajax(".content");
        $form->disableAuto();
        $form->hidden("event", $this->events[0]);
        $form->hidden("navigation", $this->navigation['id']);
        $form->input("new_name", "new_name", i('Navigation name'), 'input', $this->navigation['name']);
        $positions = ContentNavigation::getPositions();
        if(count($positions) == 0) {
            $form->subtitle('No Navigation positions set.');
        } else {
            foreach ($positions as $position_id => $position_name) {
                $form->input("position", $position_id, $position_name, 'radio', $position_id);
            }
        }
        $form->submit(i('Save changes', 'core'));
        return $form->render();
    }
}

?>
