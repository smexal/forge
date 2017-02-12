<?php

namespace Forge\Core\Views\Manage\Navigations;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\App;
use \Forge\Core\Classes\ContentNavigation;
use \Forge\Core\Classes\Form;
use \Forge\Core\Classes\Utils;

class AddView extends View {
    public $parent = 'navigation';
    public $permission = 'manage.navigations.add';
    public $name = 'add';
    public $message = '';
    public $events = array(
        'onCreateNewNavigation'
    );

    public function content() {
        return $this->app->render(CORE_TEMPLATE_DIR."views/parts/", "crud.modify", array(
            'title' => i('Create new navigation'),
            'message' => $this->message,
            'form' => $this->form()
        ));
    }

    public function onCreateNewNavigation($data) {
        $position = '';
        if(array_key_exists('position', $data)) {
            $position = $data['position'];
        }
        $this->message = ContentNavigation::create($data['new_name'], $position);
        App::instance()->addMessage(sprintf(i('Navigation %1$s has been created.'), $data['new_name']), "success");
        App::instance()->redirect(Utils::getUrl(array('manage', 'navigation')));
    }

    public function form() {
        $form = new Form(Utils::getUrl(array('manage', 'users', 'add')));
        $form->ajax(".content");
        $form->disableAuto();
        $form->hidden("event", $this->events[0]);
        $form->input("new_name", "new_name", i('Navigation name'), 'input', '');
        $positions = ContentNavigation::getPositions();
        if(count($positions) == 0) {
            $form->subtitle('No Navigation positions set.');
        } else {
            foreach ($positions as $position_id => $position_name) {
                $form->input("position", $position_id, $position_name, 'radio', $position_id);
            }
        }
        $form->submit(i('Create'));
        return $form->render();
    }
}

