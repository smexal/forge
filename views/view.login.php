<?php

namespace Forge\Views;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\App;
use \Forge\Core\App\Auth;
use \Forge\Core\Classes\Form;
use \Forge\Core\Classes\Settings;
use \Forge\Core\Classes\Utils;



class LoginView extends View {
    private $message = false;
    public $name = 'login';
    public $events = array(
        "onLoginSubmit", "onLoginFailed", "onLoginSuccess"
    );

    public function content($uri=[]) {
        if(Auth::any()) {
            $this->app->redirectBack();
        }
        return $this->app->render(CORE_TEMPLATE_DIR, "login", array(
            "title" => i("Login"),
            "message" => $this->message,
            "form" => $this->form()
        ));
    }

    public function form($trigger_event = true) {
        $form = new Form();
        $form->hidden("event", "onLoginSubmit");
        if(! $trigger_event) {
            $form->hidden("disable-trigger", "true");
        }
        $form->input("name", "name", i('Username or E-Mail'));
        $form->input("password", "password", i('Password'), 'password');
        $form->submit(i('Log in'));
        $return = $form->render();

        if(Settings::get('allow_registration')) {
            $return.= '<a href="'.Utils::getUrl(['registration']).'">'.i('Register and create account', 'core').'</a>';
        }

        return $return;
    }

    public function onLoginSuccess() {
        App::instance()->redirectBack();
    }

    public function onLoginFailed() {
        App::instance()->eh->fire("loginFailed");
        $this->message = i('Username and/or password is wrong.');
    }

    public function onLoginSubmit($data) {
        if(array_key_exists('disable-trigger', $data) && $data['disable-trigger'] == 'true') {
            Auth::login($data['name'], $data['password']);
            return;
        }
        Auth::login($data['name'], $data['password'], true);
    }
}
