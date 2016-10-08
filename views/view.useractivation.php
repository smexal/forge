<?php

class UserActivationView extends AbstractView {
    public $name = 'user-verification';

    public function content($parts = array()) {
        if(count($parts) > 0) {
            $result = User::activateByHash($parts[0]);
            if($result) {
                App::instance()->addMessage(i('The user\'s e-mail address has been confirmed.'), "success");
                App::instance()->redirect('');
            } else{
                App::instance()->addMessage(i('Something went wrong with the e-mail confirmation.'));
                App::instance()->redirect('');
            }
        }
    }
}
