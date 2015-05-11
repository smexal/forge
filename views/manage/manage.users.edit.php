<?php

class ManageEditUser extends AbstractView {
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
        if(is_null($this->user)) {
            $this->user = new User($parts[0]);
        }
        Logger::debug(implode(",", $parts));
        return $this->app->render(TEMPLATE_DIR."views/parts/", "users.modify", array(
            'title' => sprintf(i('Edit user %s'), $this->user->get('username')),
            'message' => $this->message,
            'form' => $this->form()
        ));
    }

    public function onEditUser($data) {
        /*
        $status = User::udpate($this->user->get('id'), array(
          "username" => $data['modify_name'],
          "email" => $data['modify_email'],
          "password" => $data['new_password'],
          "repeat" => $data['new_password_repeat']
        ));
        */
        Logger::debug($status);
        /*$this->message = User::create($data['new_name'], $data['new_password'], $data['new_email']);
        if($this->message) {
            $this->new_email = $data['new_email'];
            $this->new_name = $data['new_name'];
        } else {
            // new user has been created
            App::instance()->addMessage(sprintf(i('User %1$s (%2$s) has been created.'), $data['new_name'], $data['new_email']), "success");
            App::instance()->redirect(Utils::getUrl(array('manage', 'users')));
        }*/
    }

    public function form() {
        $form = new Form(Utils::getUrl(array('manage', 'users', 'edit', $this->user->get('id'))));
        $form->ajax(".content");
        $form->disableAuto();
        $form->hidden("event", $this->events[0]);
        $form->input("modify_name", "modify_name", i('Username'), 'input', $this->user->get('username'));
        $form->input("modify_email", "modify_email", i('E-Mail'), 'input', $this->user->get('email'));
        $form->input("new_password", "new_password", i('Password'), 'password', false, i('Leave empty if you don\'t want to change the password.'));
        $form->input("new_password_repeat", "new_password_repeat", i('Repeat password'), 'password');
        $form->submit(i('Save'));
        return $form->render();
    }
}

?>
