<?php

namespace Forge\Core\Views\Manage\Groups;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\App;
use \Forge\Core\Classes\Group;
use \Forge\Core\Classes\Form;
use \Forge\Core\Classes\Utils;

use function \Forge\Core\Classes\i;

class EditView extends View {
    public $parent = 'groups';
    public $permission = 'manage.groups.edit';
    public $name = 'edit';
    public $message = '';
    public $events = array(
        'onEditGroup'
    );
    private $group = null;

    public function content($parts = array()) {
      if (is_null($this->group)) {
        $this->group = new Group($parts[0]);
      }
      return $this->app->render(CORE_TEMPLATE_DIR."views/parts/", "crud.modify", array(
        'title' => sprintf(i('Edit group %s'), $this->group->get('name')),
        'message' => $this->message,
        'form' => $this->form()
      ));
    }

    public function onEditGroup($data) {
      // ...
      $group = new Group($data['group_id']);
      $this->message = $group->setName($data['modify_name']);

      // everything correct. redirect to list.
      if ($this->message === true) {
        App::instance()->addMessage(i('Successfully renamed group.'),"success");
        App::instance()->redirect(Utils::getUrl(array('manage', 'groups')));
      }
    }

    public function form() {
        $form = new Form(Utils::getUrl(array('manage', 'groups', 'edit', $this->group->get('id'))));
        $form->ajax(".content");
        $form->disableAuto();
        $form->hidden("event", $this->events[0]);
        $form->hidden("group_id", $this->group->get('id'));
        $form->input("modify_name", "modify_name", i('Group name'), 'input', $this->group->get('name'));
        $form->submit(i('Save'));
        return $form->render();
    }
}

?>
