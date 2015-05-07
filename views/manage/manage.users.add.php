<?php 

class ManageAddUser extends AbstractView {
    public $parent = 'users';
    public $permission = 'manage.users.add';
    public $name = 'add';
    public $message = '';
    private $new_email = false;
    private $new_name = false;
    public $events = array(
        'onCreateNewUser'
    );    

    public function content() {
        return $this->app->render(TEMPLATE_DIR."views/parts/", "users.modify", array(
            'title' => i('Create new user'),
            'message' => $this->message,
            'form' => $this->form()
        ));
    }

    public function onCreateNewUser($data) {
        $this->message = User::create($data['new_name'], $data['new_password'], $data['new_email']);
        if($this->message) {
            $this->new_email = $data['new_email'];
            $this->new_name = $data['new_name'];
        } else {
            // new user has been created
            App::instance()->addMessage(sprintf(i('User %1$s (%2$s) has been created.'), $data['new_name'], $data['new_email']), "success");
            App::instance()->redirect(Utils::getUrl(array('manage', 'users')));
        }
    }    

    public function form() {
        $form = new Form(Utils::getUrl(array('manage', 'users', 'add')));
        $form->ajax(".content");
        $form->disableAuto();
        $form->hidden("event", $this->events[0]);
        $form->input("new_name", "new_name", i('Username'), 'input', $this->new_name);
        $form->input("new_email", "new_email", i('E-Mail'), 'input', $this->new_email);
        $form->input("new_password", "new_password", i('Password'), 'password');
        $form->submit(i('Create'));
        return $form->render();
    }
}

?>