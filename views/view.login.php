<?php

class Login extends AbstractView {
    private $message = false;
    public $name = 'login';
    public $events = array(
        "onLoginSubmit", "onLoginFailed", "onLoginSuccess"
    );

    public function content() {
        if(Auth::any()) {
            $this->app->redirectBack();
        }
        return $this->app->render(CORE_TEMPLATE_DIR, "login", array(
            "title" => i("Login"),
            "message" => $this->message,
            "text" => i("login_intro_text"),
            "form" => $this->form()
        ));
    }

    public function form() {
        $form = new Form();
        $form->disableAuto();
        $form->hidden("event", "onLoginSubmit");
        $form->input("name", "name", i('Username'));
        $form->input("password", "password", i('Password'), 'password');
        $form->submit(i('Log in'));
        return $form->render();
    }

    public function onLoginSuccess() {
        App::instance()->redirectBack();
        App::instance()->eh->fire("loginSuccess");
    }

    public function onLoginFailed() {
        App::instance()->eh->fire("loginFailed");
        $this->message = i('Username and/or password is wrong.');
    }

    public function onLoginSubmit($data) {
        Auth::login($data['name'], $data['password']);
    }
}
