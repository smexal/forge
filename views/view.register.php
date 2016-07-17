<?php

class RegistrationView extends AbstractView {
    public $name = 'registration';
    public $allowNavigation = true;
    public $events = array(
        "onRegistrationSubmit"
    );

    public function content() {
        if(Settings::get('allow_registration')) {
            return $this->getRegistrationForm();
        } else {
            App::instance()->redirect(array('denied'));
        }
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
            'autocomplete' => false
        ));
        $return.= Fields::text(array(
            'key' => 'email',
            'label' => i('E-Mail').' *',
            'autocomplete' => false
        ));
        $return.= Fields::text(array(
            'key' => 'password',
            'label' => i('Password').' *',
            'type' => 'password',
            'autocomplete' => false
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
