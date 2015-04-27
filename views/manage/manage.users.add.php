<?php 

class ManageAddUser extends AbstractView {
    public $parent = 'users';
    public $permission = 'manage.users.add';
    public $name = 'add';

    public function content() {
        return $this->app->render(TEMPLATE_DIR."views/parts/", "users.add", array(
            'title' => i('Create new user'),
            'form' => $this->form()
        ));
    }

    public function form() {
        $form = new Form();
        $form->disableAuto();
        $form->hidden("event", "onCreateNewUser");
        $form->input("new_name", "new_name", i('Username'));
        $form->input("new_email", "new_email", i('E-Mail'));
        $form->input("new_password", "new_password", i('Password'), 'password');
        $form->submit(i('Create'));
        return $form->render();
    }
}

?>