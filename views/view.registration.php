<?php

namespace Forge\Views;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\App;
use \Forge\Core\App\Auth;
use \Forge\Core\Classes\Utils;
use \Forge\Core\Classes\Fields;
use \Forge\Core\Classes\User;
use \Forge\Core\Classes\Settings;



class RegistrationView extends View
{
    public $name = 'registration';
    public $allowNavigation = true;
    public $events = array(
        "onRegistrationSubmit"
    );

    private $errors = array();
    private $data = array();

    public function content($parts = array())
    {
        if (Settings::get('allow_registration')) {
            if (count($parts) == 0) {
                $login = '';
                if (defined('REGISTRATION_WITH_LOGIN') && \REGISTRATION_WITH_LOGIN == true) {
                    if (Auth::any()) {
                        App::instance()->redirect(['']);
                    }
                    $registration = $this->getRegistrationForm(true);
                    $login = $this->getLoginForm();
                    return $this->renderLoginAndRegistration($login, $registration);
                } else {
                    return $this->getRegistrationForm();
                }
            } else {
                if ($parts[0] == 'resend-verification') {
                    $this->resendVerification();
                }
            }
        } else {
            App::instance()->redirect(array('denied'));
        }
    }

    private function renderLoginAndRegistration($login, $registration)
    {
        $return = '<div class="row">';
        $return .= '<div class="col-lg-6">';
        $return .= '<h2>' . i('Login', 'core') . '</h2>';
        $return .= '<p>' . i('Login with your existing account.', 'core') . '</p>';
        $return .= '<form method="post">' . $login . '</form>';
        $return .= '<p><a href="' . Utils::getUrl(['recover']) . '">' . i('Forgot your password? Click here to recover.') . '</a></p>';
        $return .= '</div>';
        $return .= '<div class="col-lg-6">';
        $return .= '<h2>' . i('Registration', 'core') . '</h2>';
        $return .= '<p>' . i('Create your user and start using the plattform.', 'core') . '</p>';
        $return .= $registration;
        $return .= '</div>';
        $return .= '</div>';
        return $return;
    }

    private function getLoginForm()
    {
        $return = '';
        $return .= Fields::hidden(array(
            "name" => "event",
            "value" => "onLoginSubmit"
        ));
        $return .= Fields::hidden(array(
            "name" => "redirect",
            "value" => Utils::getCurrentUrl()
        ));
        $return .= Fields::text(array(
            'key' => 'name',
            'label' => i('Username or E-Mail', 'resofy'),
            'autocomplete' => false
        ));
        $return .= Fields::text(array(
            'key' => 'password',
            'label' => i('Password', 'resofy'),
            'type' => 'password',
            'autocomplete' => false
        ));
        $return .= Fields::button(i('Login', 'resofy'));
        return $return;
    }

    public function resendVerification()
    {
        // send notification email with activation string
        App::instance()->addMessage(
            sprintf(i('We sent you another activation Link on %s'), App::instance()->user->get('email')),
            "success"
        );
        User::sendActivationLink(App::instance()->user->get('id'));
        App::instance()->redirectBack();
    }

    public function onRegistrationSubmit()
    {
        $captchaSuccess = false;
        if (array_key_exists("g-recaptcha-response", $_POST)) {
            // validate captcha:

            $url = 'https://www.google.com/recaptcha/api/siteverify';
            $fields = [
                'secret' => '6Ld14J4cAAAAAE3W2XxKgdORVyfK8DqyBefZJlib',
                'response' => $_POST['g-recaptcha-response'],
            ];
            $fields_string = http_build_query($fields);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            //execute post
            $result = curl_exec($ch);
            $result = json_decode($result, true);
            if($result['score'] >= 0.7) {
                $captchaSuccess = true;
            }

        } else {
            return;
        }

        if(!$captchaSuccess) {
            return;
        }

        // check if username is already taken
        $this->errors = User::checkUser($_POST);
        $this->data = $_POST;
        foreach ($this->errors as $e) {
            if ($e !== false) {
                return;
            }
        }

        // create user
        User::create($this->data['name'], $this->data['password'], $this->data['email'], true, $this->data);

        // login
        // make sure it is not redirecting
        Auth::login($this->data['name'], $this->data['password'], false);

        // add user to default group if it a default set.
        $defaultGroup = Settings::get('default_usergroup');
        if ($defaultGroup !== 0) {
            App::instance()->db->insert("groups_users", array(
                "groupid" => $defaultGroup,
                "userid" => App::instance()->user->get('id')
            ));
        }

        // send notification email with activation string
        User::sendActivationLink(App::instance()->user->get('id'));

        App::instance()->addMessage(sprintf(i('Registration successful. Welcome %s'), $this->data['name']), "success");
        App::instance()->redirect(array(''));
        // redirect to home
    }

    public function getRegistrationForm($isolated = false)
    {
        if (!Settings::get('allow_registration')) {
            App::instance()->redirect(['denied']);
        }
        $return = '';
        $return .= Fields::hidden(array(
            "name" => "event",
            "value" => "onRegistrationSubmit"
        ));
        $return .= Fields::text(array(
            'key' => 'name',
            'label' => i('Username'),
            'error' => @$this->errors['name']
        ), @$this->data['name']);
        $return .= Fields::text(array(
            'key' => 'email',
            'label' => i('E-Mail'),
            'error' => @$this->errors['email']
        ), @$this->data['email']);

        // add custom fields...
        foreach (User::getMetaFields() as $field) {
            if ($field['required'] == true) {
                // skip
                if ($field['position'] === 'hidden') {
                    continue;
                }
                $type = $field['type'];
                $return .= Fields::$type([
                    'key' => $field['key'],
                    'label' => $field['label'],
                    'type' => $field['type']
                ], @$this->data[$field['key']]);
            }
        }

        $return .= Fields::text(array(
            'key' => 'password',
            'label' => i('Password'),
            'type' => 'password',
            'error' => @$this->errors['password']
        ));
        $return .= Fields::text(array(
            'key' => 'password_repeat',
            'label' => i('Repeat Password'),
            'type' => 'password'
        ));

        $return .= Fields::button(i('Complete Registration'), 'primary', true);

        $return = App::instance()->render(CORE_ROOT . "ressources/templates/assets/", "form", array(
            "method" => 'post',
            "action" => '',
            "ajax" => false,
            'horizontal' => false,
            'ajax_target' => '',
            'content' => array($return)
        ));
        if ($isolated) {
            return $return;
        }
        return App::instance()->render(CORE_TEMPLATE_DIR . 'views/sites/', 'smallcenter-content', [
            'title' => i('User Registration', 'core'),
            'lead' => i('Register on this site to get access to additional functionality and be a part of this community.', 'core'),
            'content' => $return
        ]);
    }
}
