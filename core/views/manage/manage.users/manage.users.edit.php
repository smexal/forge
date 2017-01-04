<?php

namespace Forge\Core\Views;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\App;
use \Forge\Core\Classes\Form;
use \Forge\Core\Classes\User;
use \Forge\Core\Classes\Utils;

use function \Forge\Core\Classes\i;

class ManageEditUser extends View {
    public $parent = 'users';
    public $permission = 'manage.users.edit';
    public $name = 'edit';
    public $message = '';
    private $new_email = false;
    private $new_name = false;
    public $events = array(
        'onEditUser'
    );
    private $user = null;

    public function content($parts = array()) {
      if (is_null($this->user)) {
        $this->user = new User($parts[0]);
      }
      return $this->app->render(CORE_TEMPLATE_DIR."views/parts/", "crud.modify", array(
        'title' => sprintf(i('Edit user %s'), $this->user->get('username')),
        'message' => $this->message,
        'form' => $this->form()
      ));
    }

    public function onEditUser($data) {
      $user = new User($data['user_id']);
      $statusName = $user->setName($data['modify_name']);
      $statusMail = $user->setMail($data['modify_email']);
      // new email has been set.
      $statusPassword = true;
      if (strlen($data["new_password"]) > 0 && strlen($data["new_password_repeat"]) > 0) {
        $statusPassword = $user->setPassword($data['new_password'], $data['new_password_repeat']);
      }
      $this->message = false;
      if ($statusName !== true) {
        $this->message.= $statusName."\n";
      }
      if ($statusMail !== true) {
        $this->message.= $statusMail."\n";
      }
      if ($statusPassword !== true) {
        $this->message.= $statusPassword."\n";
      }
      // everything correct. redirect to list.
      if (! $this->message) {
        App::instance()->addMessage(
          sprintf(i('User modifications on the user %1$s (%2$s) have been saved.'),
          $data['modify_name'],
          $data['modify_email']),
          "success"
        );
        App::instance()->redirect(Utils::getUrl(array('manage', 'users')));
      }
    }

    public function form() {
        $form = new Form(Utils::getUrl(array('manage', 'users', 'edit', $this->user->get('id'))));
        $form->ajax(".content");
        $form->disableAuto();
        $form->hidden("event", $this->events[0]);
        $form->hidden("user_id", $this->user->get('id'));
        $form->input("modify_name", "modify_name", i('Username'), 'input', $this->user->get('username'));
        $form->input("modify_email", "modify_email", i('E-Mail'), 'input', $this->user->get('email'));
        $form->input("new_password", "new_password", i('Password'), 'password', false, i('Leave empty if you don\'t want to change the password.'));
        $form->input("new_password_repeat", "new_password_repeat", i('Repeat password'), 'password');
        $form->submit(i('Save'));
        return $form->render();
    }
}

?>
