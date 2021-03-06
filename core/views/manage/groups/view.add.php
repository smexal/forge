<?php

namespace Forge\Core\Views\Manage\Groups;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\App;
use \Forge\Core\Classes\Group;
use \Forge\Core\Classes\Form;
use \Forge\Core\Classes\Utils;

class AddView extends View {
    public $parent = 'groups';
    public $permission = 'manage.groups.add';
    public $name = 'add';
    public $message = '';
    public $events = array(
        'onCreateNewGroup'
    );

    public function content($uri=[]) {
        return $this->app->render(CORE_TEMPLATE_DIR."views/parts/", "crud.modify", array(
            'title' => i('Create new group'),
            'message' => $this->message,
            'form' => $this->form()
        ));
    }

    public function onCreateNewGroup($data) {
      $status = Group::create($data['new_name']);
      if($status === true) {
        App::instance()->addMessage(sprintf(i('Groups %s has been created.'), $data['new_name']), "success");
        App::instance()->redirect(Utils::getUrl(array('manage', 'groups')));
      } else {
        $this->message = $status;
      }
    }

    public function form() {
        $form = new Form(Utils::getUrl(array('manage', 'groups', 'add')));
        $form->ajax(".content");
        $form->disableAuto();
        $form->hidden("event", $this->events[0]);
        $form->input("new_name", "new_name", i('Group name'), 'input');
        $form->submit(i('Create'));
        return $form->render();
    }
}
