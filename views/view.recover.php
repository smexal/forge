<?php

namespace Forge\Views;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\App;
use \Forge\Core\App\Auth;
use \Forge\Core\Classes\Fields;
use \Forge\Core\Classes\User;
use \Forge\Core\Classes\Utils;
use \Forge\Core\Classes\Settings;
use \Forge\Core\Classes\Logger;



class RecoverView extends View {
    public $name = 'recover';
    public $allowNavigation = true;
    public $events = array(
        "onRecoverFormSubmit"
    );

    private $errors = array();
    private $data = array();

    public function content($parts = array()) {
        if(Settings::get('allow_registration')) {
            if(count($parts) > 0 && $parts[0] == 'password') {
                return $this->getSetPasswordForm();
            }
            return $this->getRecoverForm();
        } else {
            App::instance()->redirect(array('denied'));
        }
    }

    public function onRecoverFormSubmit() {
        $this->data = $_POST;

        if($this->data['__event'] == 'onRecoverSetPassword') {
            $token = Utils::getUriComponents();
            if(array_key_exists(2, $token)) {
                $token = explode("__", $token[2]);
            }
            if(strtotime('+1 day', $token[1]) > microtime()) {
                $foundUser = false;
                foreach(User::getAll() as $user) {
                    $u = new User($user['id']);
                    if(Utils::hash($u->get('email').$u->get('password')) == $token[0]) {
                        $foundUser = $u;
                        break;
                    }
                }
                $errors = false;
                $this->errors['password'] = User::checkPassword($this->data['password']);
                if($this->errors['password'] !== true) {
                    $errors = true;
                } else {
                    unset($this->errors['password']);
                }
                if($this->data['password'] != $this->data['password_repeat']) {
                    $errors = true;
                    $this->errors['password_repeat'] = i('Invalid password repetition', 'core');
                }
                if(! $errors) {
                    if($foundUser) {
                        $db = App::instance()->db;
                        $db->where($foundUser->get('id'));
                        $db->update('users', [
                            'password' => Utils::password($this->data['password'])
                        ]);
                        App::instance()->addMessage(i('Your new password is set. You can login with your new password.', 'core'), 'success');
                        App::instance()->redirect([]);
                    } else {
                        $this->errors['password'] = i('Your link seems to be broken.', 'core');
                    }
                }
            } else {
                $this->errors['password'] = i('Your password reset link has expired.', 'core');
            }
            return;
        }

        // send recover link....
        if(! Utils::isEmail($this->data['email'])) {
            $this->errors['email'] = i('This does not look like an e-mail address.', 'core');
        } else {
            if( $user = User::exists($this->data['email'])) {
                User::sendRecoveryMail(new User($user));
                App::instance()->addMessage(sprintf(i('The recovery e-mail has been sent to %1$s'), $this->data['email']), "success");
                App::instance()->redirect([]);
            } else {
                $this->errors['email'] = i('No user with this e-mail address.', 'core');
            }
        }
    }

    public function getSetPasswordForm() {
        if(! Settings::get('allow_registration')) {
            App::instance()->redirect(['denied']);
        }
        $return = '';
        $return.= Fields::hidden(array(
            "name" => "__event",
            "value" => "onRecoverSetPassword"
        ));
        $return.= Fields::hidden(array(
            "name" => "event",
            "value" => "onRecoverFormSubmit"
        ));
        $return.= Fields::text(array(
            'key' => 'password',
            'label' => i('New Password'),
            'type' => 'password',
            'autocomplete' => false,
            'error' => @$this->errors['password']
        ), '');
        $return.= Fields::text(array(
            'key' => 'password_repeat',
            'type' => 'password',
            'label' => i('New password repetition'),
            'autocomplete' => false,
            'error' => @$this->errors['password_repeat']
        ), '');
        $return.= Fields::button(i('Set new password', 'core'));

        $return = App::instance()->render(CORE_ROOT."ressources/templates/assets/", "form", array(
                "method" => 'post',
                "action" => '',
                "ajax" => false,
                'horizontal' => false,
                'ajax_target' => '',
                'content' => [$return]
        ));
        return App::instance()->render(CORE_TEMPLATE_DIR.'views/sites/', 'smallcenter-content', [
            'title' => i('New Password', 'core'),
            'lead' => i('Set a new password', 'core'),
            'content' => $return
        ]);
    }

    public function getRecoverForm() {
        if(! Settings::get('allow_registration')) {
            App::instance()->redirect(['denied']);
        }
        $return = '';
        $return.= Fields::hidden(array(
            "name" => "event",
            "value" => "onRecoverFormSubmit"
        ));
        $return.= Fields::text(array(
            'key' => 'email',
            'label' => i('E-Mail'),
            'autocomplete' => false,
            'error' => @$this->errors['email']
        ), @$this->data['email']);
        $return.= Fields::button(i('Send Recovery Link', 'core'));

        $return = App::instance()->render(CORE_ROOT."ressources/templates/assets/", "form", array(
                "method" => 'post',
                "action" => '',
                "ajax" => false,
                'horizontal' => false,
                'ajax_target' => '',
                'content' => [$return]
        ));
        return App::instance()->render(CORE_TEMPLATE_DIR.'views/sites/', 'smallcenter-content', [
            'title' => i('Password Recovery', 'core'),
            'lead' => i('You can send yourself an E-Mail with a link to set a new password with this form.', 'core'),
            'content' => $return
        ]);

    }
}
