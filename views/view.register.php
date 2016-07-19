<?php

class RegistrationView extends AbstractView {
    public $name = 'registration';
    public $allowNavigation = true;
    public $events = array(
        "onRegistrationSubmit"
    );

    private $errors = array();
    private $data = array();

    public function content() {
        if(Settings::get('allow_registration')) {
            return $this->getRegistrationForm();
        } else {
            App::instance()->redirect(array('denied'));
        }
    }

    public function onRegistrationSubmit() {
        // check if username is already taken
        $this->errors = User::checkUser($_POST);
        $this->data = $_POST;
        foreach ($this->errors as $e) {
            if($e !== false) {
                return;
            }
        }

        // create user
        User::create($this->data['name'], $this->data['password'], $this->data['email'], true);

        // login
        // make sure it is not redirecting
        Auth::login($this->data['name'], $this->data['password'], false);

        // add user to default group if it a default set.
        $defaultGroup = Settings::get('default_usergroup');
        if($defaultGroup !== 0) {
            App::instance()->db->insert("groups_users", array(
                "groupid" => $defaultGroup,
                "userid" => App::instance()->user->get('id')
            ));
        }

        // send notification email with activation string
        User::sendActivationLink(App::instance()->user->get('id'));

        App::instance()->redirect(array(''));
        // redirect to home
    }

    public function getRegistrationForm() {
        if(! Settings::get('allow_registration')) { 
            return;
        }
        $return = '';
        $return.= Fields::hidden(array(
            "name" => "event", 
            "value" => "onRegistrationSubmit"
        ));
        $return.= Fields::text(array(
            'key' => 'name',
            'label' => i('Username').' *',
            'autocomplete' => false,
            'error' => @$this->errors['name']
        ), @$this->data['name']);
        $return.= Fields::text(array(
            'key' => 'email',
            'label' => i('E-Mail').' *',
            'autocomplete' => false,
            'error' => @$this->errors['email']
        ), @$this->data['email']);
        $return.= Fields::text(array(
            'key' => 'password',
            'label' => i('Password').' *',
            'type' => 'password',
            'autocomplete' => false,
            'error' => @$this->errors['password']
        ));
        $return.= Fields::text(array(
            'key' => 'password_repeat',
            'label' => i('Repeat Password').' *',
            'type' => 'password',
            'autocomplete' => false
        ));
        $return.= Fields::button(i('Complete Registration'));

        $return = App::instance()->render(CORE_ROOT."templates/assets/", "form", array(
                "method" => 'post',
                "action" => '',
                "ajax" => false,
                'horizontal' => false,
                'ajax_target' => '',
                'content' => array($return)
        ));
        return '<div class="wrapped">'.$return.'</div>';

    }
}
